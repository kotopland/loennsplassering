<?php

namespace App\Services;

use App\Exports\ExistingSheetExport;
use App\Models\EmployeeCV;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ExcelGenerationService
{
    private SalaryEstimationService $salaryEstimationService;

    public function __construct(SalaryEstimationService $salaryEstimationService)
    {
        $this->salaryEstimationService = $salaryEstimationService;
    }

    public function generateExcel(string $applicationId): array
    {
        Log::channel('info_log')->info("Starting Excel generation for Application ID: {$applicationId}");

        $application = $this->fetchApplication($applicationId);
        $application = $this->salaryEstimationService->adjustEducationAndWork($application);
        $totalMonths = SalaryEstimationService::calculateTotalWorkExperienceMonths($application->work_experience_adjusted);
        $data = $this->prepareExcelData($application, $totalMonths);

        $this->generateAndStoreExcel($data, $applicationId);

        Log::channel('info_log')->info("Excel file generated for Application ID: {$applicationId}");

        return $data;
    }

    private function fetchApplication(string $applicationId): EmployeeCV
    {
        $application = EmployeeCV::find($applicationId);

        if (! $application) {
            throw new \Exception("Application not found for ID: {$applicationId}");
        }

        Log::channel('info_log')->info("Application fetched successfully for ID: {$applicationId}");

        return $application;
    }

    private function generateAndStoreExcel(array $data, string $applicationId): void
    {
        $originalFilePath = $data['filepaths']['originalFilePath'];
        $modifiedFilePath = $data['filepaths']['modifiedFilePath'];

        $export = new ExistingSheetExport($data['data'], $originalFilePath);
        $export->modifyAndSave($modifiedFilePath);

        EmployeeCV::where('id', $applicationId)->update([
            'status' => 'generated',
            'generated_file_path' => $modifiedFilePath,
            'generated_file_timestamp' => now(),
        ]);

        Log::channel('info_log')->info("Excel file stored locally and database updated for Application ID: {$applicationId}");
    }

    private function prepareExcelData(EmployeeCV $application, float $totalMonths): array
    {
        $sheet1 = [
            ['row' => 3, 'column' => 'E', 'value' => $application->personal_info['name'], 'datatype' => 'text'],
            ['row' => 4, 'column' => 'E', 'value' => $application->personal_info['mobile'], 'datatype' => 'text'],
            ['row' => 4, 'column' => 'Q', 'value' => $application->personal_info['email'], 'datatype' => 'text'],
            ['row' => 5, 'column' => 'E', 'value' => $application->personal_info['address'], 'datatype' => 'text'],
            ['row' => 6, 'column' => 'E', 'value' => $application->personal_info['postal_code'], 'datatype' => 'text'],
            ['row' => 6, 'column' => 'H', 'value' => $application->personal_info['postal_place'], 'datatype' => 'text'],
            ['row' => 7, 'column' => 'Q', 'value' => "'" . $application->personal_info['bank_account'], 'datatype' => 'text'], // Added bank_account
            ['row' => 7, 'column' => 'E', 'value' => $application->birth_date, 'datatype' => 'date'],
            ['row' => 8, 'column' => 'E', 'value' => $application->job_title, 'datatype' => 'text'],
            ['row' => 9, 'column' => 'E', 'value' => $application->work_start_date, 'datatype' => 'date'],
            ['row' => 10, 'column' => 'E', 'value' => $application->personal_info['position_size'], 'datatype' => 'text'],
            ['row' => 11, 'column' => 'E', 'value' => $application->personal_info['employer_and_place'], 'datatype' => 'text'],
            ['row' => 12, 'column' => 'G', 'value' => "{$application->personal_info['manager_name']} / {$application->personal_info['manager_mobile']} / {$application->personal_info['manager_email']}", 'datatype' => 'text'],
            ['row' => 12, 'column' => 'P', 'value' => "{$application->personal_info['manager_mobile']} / {$application->personal_info['manager_mobile']} / {$application->personal_info['manager_email']}", 'datatype' => 'text'],
            ['row' => 12, 'column' => 'R', 'value' => "{$application->personal_info['manager_email']} / {$application->personal_info['manager_mobile']} / {$application->personal_info['manager_email']}", 'datatype' => 'text'],
            ['row' => 13, 'column' => 'G', 'value' => "{$application->personal_info['congregation_name']} / {$application->personal_info['congregation_mobile']} / {$application->personal_info['congregation_email']}", 'datatype' => 'text'],
            ['row' => 13, 'column' => 'P', 'value' => "{$application->personal_info['congregation_name']} / {$application->personal_info['congregation_mobile']} / {$application->personal_info['congregation_email']}", 'datatype' => 'text'],
            ['row' => 13, 'column' => 'R', 'value' => "{$application->personal_info['congregation_name']} / {$application->personal_info['congregation_mobile']} / {$application->personal_info['congregation_email']}", 'datatype' => 'text'],
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
            // $sheet1[] = ['row' => $rowSheet1, 'column' => 'V', 'value' => $item['comments'] ?? '', 'datatype' => 'text'];
            $text = 'Registrert av bruker';
            $text .= @$item['highereducation'] ? ' som ' . $item['highereducation'] : '';
            $text .= @$item['relevance'] ? ' og registrert som relevant.' : '';
            $text .= isset($item['competence_points']) && intval($item['competence_points']) >= 0 ? ' Gitt ' . $item['competence_points'] . ' kompetansepoeng.' : '';
            $text .= $item['comments'] ?? '';
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'AB', 'value' => $text, 'datatype' => 'text'];
            $sheet2[] = ['row' => $rowSheet2, 'column' => 'AC', 'value' => isset($item['competence_points']) && intval($item['competence_points']) == 0 ? ' Gitt bare ansiennitet. ' . $item['competence_points'] . ' kompetansepoeng.' : '', 'datatype' => 'text'];
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
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'V', 'value' => $item['comments'] ?? '', 'datatype' => 'text'];
            $text = 'Registrert av bruker';
            $text .= @$item['highereducation'] ? ' som ' . $item['highereducation'] : '';
            $text .= @$item['relevance'] ? ' og registrert som relevant' : '';
            $text .= ! isset($item['competence_points']) ? '. Flytet til ansiennitet og gir uttelling i perider som ikke overskrider 100% ansiennitet.' : '';
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'AB', 'value' => $text, 'datatype' => 'text'];

            $rowSheet1++;
        }

        if (count($application->education ?? []) <= 11 && (count($application->work_experience ?? []) + count($application->work_experience_adjusted ?? [])) <= 15) {
            // short education / experience lines
            $rowSheet1 = 28;
            $originalFilePath = '14lonnsskjema.xlsx'; // Stored in storage/app/public
            $modifiedFilePath = 'generert-lonnsskjema-' . $application->id . '.xlsx'; // New modified file path
        } elseif (count($application->education) <= 21 && (count($application->work_experience) + count($application->work_experience_adjusted ?? [])) <= 29) {
            // long education / experience lines
            $rowSheet1 = 39;
            $originalFilePath = '14lonnsskjema-expanded.xlsx'; // Stored in storage/app/public
            $modifiedFilePath = 'generert-lonnsskjema-' . $application->id . '.xlsx'; // New modified file path
        } elseif (count($application->education) > 21 || (count($application->work_experience) + count($application->work_experience_adjusted ?? [])) > 29) {
            // long education / experience lines
            $rowSheet1 = 55;
            $originalFilePath = '14lonnsskjema-extraexpanded.xlsx'; // Stored in storage/app/public
            $modifiedFilePath = 'generert-lonnsskjema-' . $application->id . '.xlsx'; // New modified file path
        } else {
            throw new InvalidArgumentException('Det er for mange linjer utdannelse eller ansiennitet at det ikke passer inni lønnsskjema excel arket.');
        }

        foreach ($application->work_experience ?? [] as $enteredItem) {
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'B', 'value' => $enteredItem['title_workplace'], 'datatype' => 'text'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'P', 'value' => @$enteredItem['percentage'] / 100, 'datatype' => 'number'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'Q', 'value' => @$wECollection->firstWhere('id', $enteredItem['id'])['start_date'] ?? '', 'datatype' => 'date'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'R', 'value' => @$wECollection->firstWhere('id', $enteredItem['id'])['end_date'] ?? '', 'datatype' => 'date'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'AB', 'value' => $enteredItem['comments'] ?? '', 'datatype' => 'text'];
            $text = 'Registrert av bruker ';
            $text .= @$enteredItem['relevance'] ? ' og registrert som relevant. Se beregninger av ansiennitet gjort maskinelt under.' : '';
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'AC', 'value' => $text, 'datatype' => 'text'];
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
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'AB', 'value' => $adjustedItem['comments'] ?? '', 'datatype' => 'text'];
            $sheet1[] = ['row' => $rowSheet1, 'column' => 'AC', 'value' => 'Maskinelt behandlet felt', 'datatype' => 'text'];
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
            $rowSheet1 = 127;
        }

        // Calculating the ladder position based on the employee’s total work experience in years, rounded down to the nearest integer
        $ladderPosition = SalaryEstimationService::ladderPosition(Carbon::parse($application->work_start_date), $totalMonths);

        $ladder = $salaryCategory['ladder'];
        $group = in_array($salaryCategory['ladder'], ['B', 'D']) ? '' : $salaryCategory['group'];
        $salaryPlacement = EmployeeCV::getSalary($salaryCategory['ladder'], $salaryCategory['group'], $ladderPosition);
        $sheet1[] = ['row' => $rowSheet1, 'column' => 'S', 'value' => $ladder, 'datatype' => 'text'];
        $sheet1[] = ['row' => $rowSheet1 + 2, 'column' => 'S', 'value' => $group, 'datatype' => 'text'];
        $sheet1[] = ['row' => $rowSheet1 + 5, 'column' => 'S', 'value' => $salaryPlacement, 'datatype' => 'text'];
        $sheet1[] = ['row' => $rowSheet1 + 9, 'column' => 'S', 'value' => $application->competence_points, 'datatype' => 'text'];

        return [
            'filepaths' => ['modifiedFilePath' => $modifiedFilePath, 'originalFilePath' => $originalFilePath],
            'data' => ['sheet1' => $sheet1, 'sheet2' => $sheet2, 'salaryPlacement' => ($salaryPlacement + $application->competence_points)],
            'application' => $application,
        ];
    }
}
