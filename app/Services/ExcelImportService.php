<?php

namespace App\Services;

use App\Models\EmployeeCV;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ExcelImportService
{
    public function processExcelFile($file): EmployeeCV
    {
        $data = $this->getExcelData($file);

        return $this->createApplication($data);
    }

    private function getExcelData($file): array
    {
        return Excel::toArray([], $file)[0]; // Get first sheet
    }

    private function createApplication(array $data): EmployeeCV
    {
        $application = EmployeeCV::create();

        $application->birth_date = $this->formatExcelDate($data[6][4]);
        $application->job_title = $data[7][4];
        $application->work_start_date = $this->formatExcelDate($data[8][4]);

        $application->education = $this->extractEducation($data);
        $application->work_experience = $this->extractWorkExperience($data);

        $application->save();

        return $application;
    }

    private function formatExcelDate($date): string
    {
        // If empty, return empty string
        if (empty($date)) {
            return '';
        }

        try {
            // Handle Excel numeric dates
            if ($this->isValidExcelDate($date)) {
                return Date::excelToDateTimeObject($date)->format('Y-m-d');
            }

            // Handle string dates in DD.MM.YYYY format
            if (is_string($date) && preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) {
                $dateTime = \DateTime::createFromFormat('d.m.Y', $date);
                if ($dateTime !== false) {
                    return $dateTime->format('Y-m-d');
                }
            }

            return '';
        } catch (\Exception $e) {
            return '';
        }
    }

    private function isValidExcelDate($date): bool
    {
        return is_numeric($date) && $date > 0;
    }

    private function extractEducation(array $data): array
    {
        $education = [];
        $startRow = 14;

        foreach ($data as $row => $column) {
            if ($row < $startRow) {
                continue;
            }

            if (str_contains($column[1], 'Ansiennitetsopplysninger:')) {
                break;
            }

            if (empty(trim($column[1]))) {
                continue;
            }

            try {
                $education[] = $this->formatEducationRow($column);
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException('Feil i datoformatet i excel-arket. Sjekk at alle datoer er på formatet ÅÅÅÅ-MM-DD.');
            }
        }

        return $education;
    }

    private function formatEducationRow(array $column): array
    {
        $studyPercentage = $this->calculateStudyPercentage($column);

        return [
            'topic_and_school' => $column[1],
            'start_date' => $this->formatExcelDate($column[18]),
            'end_date' => $this->formatExcelDate($column[19]),
            'study_points' => $column[20],
            'percentage' => $studyPercentage,
        ];
    }

    private function calculateStudyPercentage(array $column): string
    {
        if (strtolower($column[20]) == 'bestått') {
            return '100';
        }

        if ($this->isValidExcelDate($column[18]) &&
            $this->isValidExcelDate($column[19]) &&
            is_numeric($column[20])) {
            return SalaryEstimationService::calculateStudyPercentage(
                $this->formatExcelDate($column[18]),
                $this->formatExcelDate($column[19]),
                intval($column[20])
            );
        }

        return '';
    }

    private function extractWorkExperience(array $data): array
    {
        $workExperience = [];
        $inWorkExperienceSection = false;

        foreach ($data as $column) {
            if (str_contains($column[1], 'Ansiennitetsopplysninger:')) {
                $inWorkExperienceSection = true;

                continue;
            }

            if ($inWorkExperienceSection) {
                if (str_contains($column[1], 'PS: husk også ')) {
                    break;
                }

                if (! empty(trim($column[1]))) {
                    $workExperience[] = $this->formatWorkExperienceRow($column);
                }
            }
        }

        return $this->removeDuplicateExperiences($workExperience);
    }

    private function formatWorkExperienceRow(array $column): array
    {

        return [
            'title_workplace' => $column[1],
            'percentage' => is_numeric($column[15]) ? floatval($column[15]) * 100 : '',
            'start_date' => $this->formatExcelDate($column[16]),
            'end_date' => $this->formatExcelDate($column[17]),
            'relevance' => $column[19] == 1 ? true : false,
        ];
    }

    private function removeDuplicateExperiences(array $experiences): array
    {
        // First, create a lookup key for each experience
        $uniqueExperiences = [];
        foreach ($experiences as $experience) {
            $key = $experience['title_workplace'].'|'.
                   $experience['start_date'].'|'.
                   $experience['end_date'];

            // If key doesn't exist, add it
            if (! isset($uniqueExperiences[$key])) {
                $uniqueExperiences[$key] = $experience;

                continue;
            }

            // If current experience is relevant and existing one isn't, replace it
            if (($experience['relevance'] ?? false) && ! ($uniqueExperiences[$key]['relevance'] ?? false)) {
                $uniqueExperiences[$key] = $experience;
            }
        }

        // Convert back to indexed array
        return array_values($uniqueExperiences);
    }
}
