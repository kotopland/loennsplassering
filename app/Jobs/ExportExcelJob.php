<?php

namespace App\Jobs;

use App\Exports\ExistingSheetExport;
use App\Mail\SimpleEmail;
use App\Models\EmployeeCV;
use App\Services\SalaryEstimationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class ExportExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $applicationId;

    private string $email;

    public $timeout = 180;

    /**
     * Create a new job instance.
     */
    public function __construct(string $applicationId, string $email)
    {
        $this->applicationId = $applicationId;
        $this->email = $email;
    }

    /**
     * Execute the job.
     */
    public function handle(SalaryEstimationService $salaryEstimationService): void
    {
        try {
            Log::channel('info_log')->info("Starting Excel generation for Application ID: {$this->applicationId}");

            $application = $this->fetchApplication();
            $application = $salaryEstimationService->adjustEducationAndWork($application);
            $totalMonths = $this->calculateTotalWorkExperienceMonths($application);
            $data = $this->prepareExcelData($application, $totalMonths);

            $this->generateAndSendExcel($data, $application);

            Log::channel('info_log')->info("Email sent successfully for Application ID: {$this->applicationId}");
        } catch (Exception $e) {
            Log::error("Error processing Application ID: {$this->applicationId} - ".$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            $this->sendErrorNotification($e->getMessage());  // Send email with error
            throw $e;  // Rethrow to mark job as failed
        }
    }

    /**
     * Fetch the application by ID.
     */
    private function fetchApplication(): EmployeeCV
    {
        $application = EmployeeCV::find($this->applicationId);

        if (! $application) {
            throw new Exception("Application not found for ID: {$this->applicationId}");
        }

        Log::channel('info_log')->info("Application fetched successfully for ID: {$this->applicationId}");

        return $application;
    }

    /**
     * Calculate total work experience in months.
     */
    private function calculateTotalWorkExperienceMonths(EmployeeCV $application): int
    {
        $totalMonths = SalaryEstimationService::calculateTotalWorkExperienceMonths($application->work_experience_adjusted);
        Log::channel('info_log')->info("Total work experience calculated for ID: {$this->applicationId}: {$totalMonths}");

        return $totalMonths;
    }

    /**
     * Generate the Excel file and send it via email.
     */
    private function generateAndSendExcel(?array $data): void
    {
        $originalFilePath = $data['filepaths']['originalFilePath'];
        $modifiedFilePath = $data['filepaths']['modifiedFilePath'];

        $export = new ExistingSheetExport($data['data'], $originalFilePath);
        $export->modifyAndSave($modifiedFilePath);

        Log::channel('info_log')->info("Excel file saved successfully for Application ID: {$this->applicationId}");

        $subject = 'Foreløpig beregning av din lønnsplassering';
        $body = $this->generateEmailBody($data['data']);
        Mail::to($this->email)->send(new SimpleEmail($subject, $body, $modifiedFilePath));
        Mail::to(config('app.report_email'))->send(new SimpleEmail('Sendt epost: '.$subject, $body, $modifiedFilePath));
    }

    /**
     * Generate the email body.
     */
    private function generateEmailBody(?array $data): string
    {
        $body = 'Denne eposten ble generert på nettstedet '.config('app.name').'<br/><br/>';
        $body .= $data
            ? ' Vedlagt ligger en maskinberegnet lønnsplassering (med forbehold om feil).'
            : ' Det ble generert for mange linjer, og Excel-skjemaet kunne ikke bli behandlet maskinelt. Derimot kan du med linken nedenfor se plasseringen.';
        $body .= ' Du kan se og endre ditt skjema ved å trykke på denne linken: <a href="'.
                 route('open-application', $this->applicationId).'">'.
                 route('open-application', $this->applicationId).'</a>.';
        $body .= ' Skjemaer slettes ett år etter at det er blitt åpnet.';

        return $body;
    }

    /**
     * Prepare the data for Excel export.
     */
    private function prepareExcelData(EmployeeCV $application, int $totalMonths): ?array
    {
        $sheet1 = [
            ['row' => 8, 'column' => 'E', 'value' => $application->job_title, 'datatype' => 'text'],
            ['row' => 7, 'column' => 'E', 'value' => $application->birth_date, 'datatype' => 'date'],
            ['row' => 9, 'column' => 'E', 'value' => $application->work_start_date, 'datatype' => 'date'],
        ];
        $sheet2 = [];

        $rowSheet1 = 15;
        $rowSheet2 = 4;

        $wECollection = collect(array_merge($application->education, $application->work_experience));
        foreach ($application->education_adjusted ?? [] as $item) {
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'B', 'value' => $item['topic_and_school'], 'datatype' => 'text'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'S', 'value' => @$wECollection->firstWhere('id', $item['id'])['start_date'] ?? '', 'datatype' => 'date'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'T', 'value' => @$wECollection->firstWhere('id', $item['id'])['end_date'] ?? '', 'datatype' => 'date'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'U', 'value' => $item['study_points'], 'datatype' => 'text'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'V', 'value' => $adjustedItem['comments'] ?? '', 'datatype' => 'text'];
            $text = 'Registrert av bruker';
            $text .= @$item['highereducation'] ? ' som '.$item['highereducation'] : '';
            $text .= @$item['relevance'] ? ' og registrert som relevant.' : '';
            $text .= isset($item['competence_points']) && intval($item['competence_points']) >= 0 ? ' Gitt '.$item['competence_points'].' kompetansepoeng.' : '';
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'AB', 'value' => $text, 'datatype' => 'text'];
            $sheet2[] = ['row' => $rowSheet2, 'column' => 'H', 'value' => isset($item['competence_points']) && intval($item['competence_points']) == 0 ? ' Gitt bare ansiennitet. '.$item['competence_points'].' kompetansepoeng.' : '', 'datatype' => 'text'];
            $rowSheet1++;
            $rowSheet2++;
        }
        $adjustedEducation = collect($application->education_adjusted);
        $originalEducation = collect($application->education);
        $existingTopics = $adjustedEducation->pluck('topic_and_school')->toArray();

        // Filter the original education to only include those not present in the adjusted.
        $nonDuplicateOriginal = $originalEducation->filter(function ($item) use ($existingTopics) {
            return ! in_array($item['topic_and_school'], $existingTopics);
        });
        foreach ($nonDuplicateOriginal ?? [] as $item) {
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'B', 'value' => $item['topic_and_school'], 'datatype' => 'text'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'S', 'value' => @$wECollection->firstWhere('id', $item['id'])['start_date'] ?? '', 'datatype' => 'date'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'T', 'value' => @$wECollection->firstWhere('id', $item['id'])['end_date'] ?? '', 'datatype' => 'date'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'U', 'value' => $item['study_points'], 'datatype' => 'text'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'V', 'value' => $adjustedItem['comments'] ?? '', 'datatype' => 'text'];
            $text = 'Registrert av bruker';
            $text .= @$item['highereducation'] ? ' som '.$item['highereducation'] : '';
            $text .= @$item['relevance'] ? ' og registrert som relevant' : '';
            $text .= ! isset($item['competence_points']) ? '. Flytet til ansiennitet og gir uttelling i perider som ikke overskrider 100% ansiennitet.' : '';
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'AB', 'value' => $text, 'datatype' => 'text'];

            $rowSheet1++;
        }

        if (count($application->education ?? []) <= 11 && (count($application->work_experience ?? []) + count($application->work_experience_adjusted ?? [])) <= 15) {
            // short education / experience lines
            $rowSheet1 = 28;
            $originalFilePath = '14lonnsskjema.xlsx'; // Stored in storage/app/public
            $modifiedFilePath = 'modified_14lonnsskjema-'.$application->id.'.xlsx'; // New modified file path
        } elseif (count($application->education) <= 21 && (count($application->work_experience) + count($application->work_experience_adjusted ?? [])) <= 29) {
            // long education / experience lines
            $rowSheet1 = 39;
            $originalFilePath = '14lonnsskjema-expanded.xlsx'; // Stored in storage/app/public
            $modifiedFilePath = 'modified_14lonnsskjema-expanded-'.$application->id.'.xlsx'; // New modified file path
        } elseif (count($application->education) > 21 || (count($application->work_experience) + count($application->work_experience_adjusted ?? [])) > 29) {
            // long education / experience lines
            $rowSheet1 = 55;
            $originalFilePath = '14lonnsskjema-extraexpanded.xlsx'; // Stored in storage/app/public
            $modifiedFilePath = 'modified_14lonnsskjema-extraexpanded-'.$application->id.'.xlsx'; // New modified file path
            // return null;
        } else {
            throw new InvalidArgumentException('Det er for mange linjer utdannelse eller ansiennitet at det ikke passer inni lønnsskjema excel arket.');
        }

        foreach ($application->work_experience ?? [] as $enteredItem) {
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'B', 'value' => $enteredItem['title_workplace'], 'datatype' => 'text'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'P', 'value' => @$enteredItem['percentage'] / 100, 'datatype' => 'number'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'Q', 'value' => @$wECollection->firstWhere('id', $enteredItem['id'])['start_date'] ?? '', 'datatype' => 'date'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'R', 'value' => @$wECollection->firstWhere('id', $enteredItem['id'])['end_date'] ?? '', 'datatype' => 'date'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'V', 'value' => $adjustedItem['comments'] ?? '', 'datatype' => 'text'];
            $text = 'Registrert av bruker ';
            $text .= @$enteredItem['relevance'] ? ' og registrert som relevant. Se beregninger av ansiennitet gjort maskinelt under.' : '';
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'AB', 'value' => $text, 'datatype' => 'text'];
            $rowSheet1++;
        }

        foreach ($application->work_experience_adjusted ?? [] as $adjustedItem) {
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'B', 'value' => $adjustedItem['title_workplace'], 'datatype' => 'text'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'P', 'value' => @floatval($wECollection->firstWhere('id', $adjustedItem['id'])['percentage'] ?? 0) / 100, 'datatype' => 'number'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'Q', 'value' => @$wECollection->firstWhere('id', $adjustedItem['id'])['start_date'] ?? '', 'datatype' => 'date'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'R', 'value' => @$wECollection->firstWhere('id', $adjustedItem['id'])['end_date'] ?? '', 'datatype' => 'date'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'W', 'value' => $adjustedItem['percentage'] / 100, 'datatype' => 'number'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'X', 'value' => $adjustedItem['start_date'], 'datatype' => 'date'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'Y', 'value' => $adjustedItem['end_date'], 'datatype' => 'date'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'T', 'value' => @$adjustedItem['relevance'] ? 1 : 0.5, 'datatype' => 'number'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'V', 'value' => $adjustedItem['comments'] ?? '', 'datatype' => 'text'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'AB', 'value' => 'Maskinelt behandlet felt', 'datatype' => 'text'];
            $rowSheet1++;
        }

        $salaryCategory = (new EmployeeCV)->getPositionsLaddersGroups()[$application->job_title];

        if (count($application->education ?? []) <= 11 && (count($application->work_experience ?? []) + count($application->work_experience_adjusted ?? [])) <= 15) {
            // short education / experience lines
            $rowSheet1 = 62;
        } elseif (count($application->education) <= 21 && (count($application->work_experience) + count($application->work_experience_adjusted)) <= 29) {
            // long education / experience lines
            $rowSheet1 = 88;
        } elseif (count($application->education) > 21 || (count($application->work_experience) + count($application->work_experience_adjusted)) > 29) {
            // long education / experience lines
            $rowSheet1 = 126;
        }

        // Calculating the ladder position based on the employee’s total work experience in years, rounded down to the nearest integer
        $ladderPosition = intval(SalaryEstimationService::getYearsDifferenceWithDecimals(
            SalaryEstimationService::addMonthsWithDecimals(Carbon::parse($application->work_start_date), $totalMonths),
            Carbon::parse($application->work_start_date))
        );

        $ladder = $salaryCategory['ladder'];
        $group = in_array($salaryCategory['ladder'], ['B', 'D']) ? '' : $salaryCategory['group'];
        $salaryPlacement = EmployeeCV::getSalary($salaryCategory['ladder'], $salaryCategory['group'], $ladderPosition);
        $sheet1[] = ['row' => $rowSheet1, 'column' => 'S', 'value' => $ladder, 'datatype' => 'text'];
        $sheet1[] = ['row' => $rowSheet1 + 2, 'column' => 'S', 'value' => $group, 'datatype' => 'text'];
        $sheet1[] = ['row' => $rowSheet1 + 5, 'column' => 'S', 'value' => $salaryPlacement, 'datatype' => 'text'];
        $sheet1[] = ['row' => $rowSheet1 + 9, 'column' => 'S', 'value' => $application->competence_points, 'datatype' => 'text'];

        return ['filepaths' => ['modifiedFilePath' => $modifiedFilePath, 'originalFilePath' => $originalFilePath],
            'data' => ['sheet1' => $sheet1, 'sheet2' => $sheet2],
        ];
    }

    public function sendErrorNotification(string $message): void
    {
        // Send email with error
        $subject = 'Feil ved prosessering av Lønnsplassering (Excel fil)';
        $body = $this->generateEmailBody(null);
        Mail::to($this->email)->send(new SimpleEmail($subject, $body, null));
    }
}
