<?php

namespace App\Jobs;

use App\Exports\ExistingSheetExport;
use App\Mail\ExcelGeneratedMail;
use App\Mail\SimpleEmail;
use App\Models\EmployeeCV;
use App\Services\SalaryEstimationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class ExportExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $applicationId;

    public $email;

    /**
     * Create a new job instance.
     */
    public function __construct($applicationId, $email)
    {
        $this->applicationId = $applicationId;
        $this->email = $email;
    }

    /**
     * Execute the job.
     */
    public function handle(SalaryEstimationService $salaryEstimationService)
    {
        try {
            // Log the start of the process
            Log::info("Starting Excel generation for Application ID: {$this->applicationId}");

            // Fetch the application and process the data

            $application = EmployeeCV::find($this->applicationId);
            if (! $application) {
                throw new Exception("Application not found for ID: {$this->applicationId}");
            }

            $application = $salaryEstimationService->adjustEducationAndWork($application);
            Log::info("Application data adjusted successfully for ID: {$this->applicationId}");
            // Calculate total work experience in months
            $calculatedTotalWorkExperienceMonths = SalaryEstimationService::calculateTotalWorkExperienceMonths($application->work_experience_adjusted);
            Log::info("Total work experience calculated for ID: {$this->applicationId}");

            Log::info("Excel data prepared for Application ID: {$this->applicationId}");

            // Prepare data for Excel
            $data = $this->prepareExcelData($application, $calculatedTotalWorkExperienceMonths);
            if (is_null($data)) {
                Log::error("Too much data and the excel file could not be generated. Application ID: {$this->applicationId}");
            }
            // Create the Excel file and save it
            $originalFilePath = '14lonnsskjema.xlsx';
            $modifiedFilePath = 'modified_14lonnsskjema.xlsx';
            $export = new ExistingSheetExport($data, $originalFilePath);

            $export->modifyAndSave($modifiedFilePath);
            Log::info("Excel file saved successfully for Application ID: {$this->applicationId}");

            Log::info("Trying to send email to:: {$this->email}");
            $subject = 'Foreløpig beregning av din lønnsplassering';
            $body = 'Denne eposten ble generert på nettstedet '.config('app.name');
            $body .= (! is_null($data)) ? 'Vedlagt ligger en maskinberegnet lønnsplassering (Med sannysnligheter for feil).' : 'Det ble generert altfor mange linjer og det ble ikke plass i Excel skjemaet. Bruk derfor nettsiden til å se din beregning.';
            $body .= 'Du kan se og endre ditt skjema ved å trykke på denne linken: <a href="'.route('open-application', $this->applicationId).'">'.route('open-application', $this->applicationId).'</a>. Skjemaer slettes ett år etter at det er blitt åpnet.';
            Mail::to($this->email)->send(new SimpleEmail($subject, $body, $modifiedFilePath));

            // Send the email with the Excel file as an attachment
            // Mail::to($this->email)->send(new ExcelGeneratedMail($modifiedFilePath));
            Log::info("Email sent successfully to {$this->email} for Application ID: {$this->applicationId}");
        } catch (Exception $e) {
            // Log the error message and stack trace
            Log::error("Error processing Application ID: {$this->applicationId} - ".$e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);
            // Optionally, rethrow the exception to mark the job as failed
            throw $e;
        }
    }

    /**
     * Prepare the data for Excel export.
     */
    private function prepareExcelData($application, $calculatedTotalWorkExperienceMonths)
    {

        $data = [
            ['row' => 8, 'column' => 'E', 'value' => $application->job_title, 'datatype' => 'text'],
            ['row' => 7, 'column' => 'E', 'value' => $application->birth_date, 'datatype' => 'date'],
            ['row' => 9, 'column' => 'E', 'value' => $application->work_start_date, 'datatype' => 'date'],
            // ['row' => 9, 'column' => 'R', 'value' => $application->work_start_date, 'datatype' => 'date'],
        ];

        $row = 15;
        foreach ($application->education_adjusted as $item) {

            $data[] = ['row' => $row, 'column' => 'B', 'value' => $item['topic_and_school'], 'datatype' => 'text'];
            $data[] = ['row' => $row, 'column' => 'S', 'value' => $item['start_date'], 'datatype' => 'date'];
            $data[] = ['row' => $row, 'column' => 'T', 'value' => $item['end_date'], 'datatype' => 'date'];
            $data[] = ['row' => $row, 'column' => 'U', 'value' => $item['study_points'], 'datatype' => 'text'];
            $data[] = ['row' => $row, 'column' => 'AA', 'value' => $item['highereducation'].($item['relevance'] ? 'relevant' : ''), 'datatype' => 'text'];
            $row++;
        }

        if (count($application->education_adjusted) <= 11 && count($application->work_experience) <= 15) {
            // short education / experience lines
            $row = 28;
        } elseif (count($application->education_adjusted) > 11 || count($application->work_experience) > 15) {
            // long education / experience lines
            $row = 39;
        } elseif (count($application->education_adjusted) > 21 || count($application->work_experience) > 29) {
            return null;
        }
        foreach ($application->work_experience as $enteredItem) {
            $data[] = ['row' => $row, 'column' => 'B', 'value' => $enteredItem['title_workplace'], 'datatype' => 'text'];
            $data[] = ['row' => $row, 'column' => 'P', 'value' => $enteredItem['work_percentage'] / 100, 'datatype' => 'number'];
            $data[] = ['row' => $row, 'column' => 'Q', 'value' => $enteredItem['start_date'], 'datatype' => 'date'];
            $data[] = ['row' => $row, 'column' => 'R', 'value' => $enteredItem['end_date'], 'datatype' => 'date'];
            $data[] = ['row' => $row, 'column' => 'AB', 'value' => 'Opprinnelig registrert', 'datatype' => 'text'];
            $data[] = ['row' => $row, 'column' => 'AC', 'value' => @$enteredItem['relevance'] ? 'relevant' : '', 'datatype' => 'text'];
            $row++;
        }

        foreach ($application->work_experience_adjusted as $adjustedItem) {
            $data[] = ['row' => $row, 'column' => 'B', 'value' => $adjustedItem['title_workplace'], 'datatype' => 'text'];
            $data[] = ['row' => $row, 'column' => 'P', 'value' => $adjustedItem['work_percentage'] / 100, 'datatype' => 'number'];
            $data[] = ['row' => $row, 'column' => 'Q', 'value' => $adjustedItem['start_date'], 'datatype' => 'date'];
            $data[] = ['row' => $row, 'column' => 'R', 'value' => $adjustedItem['end_date'], 'datatype' => 'date'];
            $data[] = ['row' => $row, 'column' => 'T', 'value' => @$adjustedItem['relevance'] ? 1 : 0.5, 'datatype' => 'number'];
            $data[] = ['row' => $row, 'column' => 'AB', 'value' => 'Maskinelt modifisert', 'datatype' => 'text'];
            $row++;
        }

        $salaryCategory = EmployeeCV::positionsLaddersGroups[$application->job_title];

        if (count($application->education_adjusted) <= 11 && count($application->work_experience) <= 15) {
            // short education / experience lines
            $row = 62;
        } elseif (count($application->education_adjusted) > 11 || count($application->work_experience) > 15) {
            // long education / experience lines
            $row = 88;
        }

        // Calculating the ladder position based on the employee’s total work experience in years, rounded down to the nearest integer
        $ladderPosition = intval(SalaryEstimationService::getYearsDifferenceWithDecimals(
            SalaryEstimationService::addMonthsWithDecimals(Carbon::parse($application->work_start_date), $calculatedTotalWorkExperienceMonths),
            Carbon::now())
        );

        $ladder = $salaryCategory['ladder'];
        $group = $salaryCategory['group'] !== ('B' || 'D') ? $salaryCategory['group'] : '';
        $salaryPlacement = EmployeeCV::salaryLadders[$salaryCategory['ladder']][$salaryCategory['group']][$ladderPosition];
        $data[] = ['row' => $row, 'column' => 'S', 'value' => $ladder, 'datatype' => 'text'];
        $data[] = ['row' => $row + 2, 'column' => 'S', 'value' => $group, 'datatype' => 'text'];
        $data[] = ['row' => $row + 5, 'column' => 'S', 'value' => $salaryPlacement, 'datatype' => 'text'];
        $data[] = ['row' => $row + 9, 'column' => 'S', 'value' => $application->competence_points, 'datatype' => 'text'];

        return $data;
    }
}
