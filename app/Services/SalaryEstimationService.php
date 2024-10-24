<?php

namespace App\Services;

use App\Models\EmployeeCV;
use Carbon\Carbon;

class SalaryEstimationService
{
    // private array $salaryEstimation;

    // public function __construct()
    // {
    //     $this->salaryEstimation = [];
    // }

    // public function getSalaryEstimation(): array
    // {
    //     return $this->salaryEstimation;
    // }

    /**
     * Method to process the employee's educatoin based on a ruleset
     */
    // public function processEducation(EmployeeCV $application)
    // {

    //     // remove records when the employee was 18 years old
    //     // convert records not finished before employment start to work experience
    //     // For A,B,E,F
    //     // give 1 point to relevant bestått education (1 or 2 years)
    //     // give 1 point to relevant bestått education (1 or 2 years)
    //     // give 6 points to relevant master and bachelor
    //     // give 3 points to relevant bachelor
    //     // give 3 points to general master
    //     // give 1 points to general bachelor
    //     // One year full time study (100%) gives 120 points from September 1st till June 1st the next year. If the study is taken over a longer term, the study percentage will then be reduced.
    //     // if the emplyee gains more than 7 points, that education periods should be recorded as experiance. The experience record's work_percentage should be the same percentage as recorded in the study.
    //     $educationData = $application->education;
    //     $workExperienceData = $application->work_experience ?? [];
    //     $eighteenYearsAgo = Carbon::parse($application->birth_date)->subYears(18);
    //     $workStartDate = Carbon::parse($application->work_start_date);

    //     $adjustedEducationData = [];
    //     $competencePoints = 0;

    //     foreach ($educationData as $id => $education) {
    //         $educationStartDate = Carbon::parse($education['start_date']);
    //         $educationEndDate = Carbon::parse($education['end_date']);

    //         // Rule 1: Remove records when the employee was 18 years old or younger
    //         if ($educationEndDate->lte($eighteenYearsAgo)) {
    //             continue; // Skip this record
    //         }

    //         // Rule 2: Convert records not finished before employment start to work experience
    //         if ($educationEndDate->gte($workStartDate)) {
    //             $workExperienceData[] = [
    //                 'title_workplace' => $education['topic_and_school'],
    //                 'workplace_type' => null, // Assuming workplace type is not relevant here
    //                 'work_percentage' => $education['study_percentage'],
    //                 'start_date' => $education['start_date'],
    //                 'end_date' => $education['end_date'],
    //                 'relevance' => $education['relevance'],
    //             ];

    //             continue; // Skip this record in education
    //         }

    //         // Calculate competence points based on rules 3 to 7
    //         if ($education['relevance']) {
    //             if ($education['study_points'] === 'passed') {
    //                 $competencePoints += 1; // Rules 2 and 3
    //             } elseif ($education['highereducation'] === 'master') {
    //                 $competencePoints += 3; // Rule 4
    //             } elseif ($education['highereducation'] === 'bachelor') {
    //                 $competencePoints += 3; // Rule 5
    //             }
    //         } else {
    //             if ($education['highereducation'] === 'master') {
    //                 $competencePoints += 3; // Rule 6
    //             } elseif ($education['highereducation'] === 'bachelor') {
    //                 $competencePoints += 1; // Rule 7
    //             }
    //         }

    //         // Rule 8: Convert excess education to work experience if competence points exceed 7
    //         if ($competencePoints > 7) {
    //             $workExperienceData[] = [
    //                 'title_workplace' => $education['topic_and_school'],
    //                 'workplace_type' => null,
    //                 'work_percentage' => $education['study_percentage'],
    //                 'start_date' => $education['start_date'],
    //                 'end_date' => $education['end_date'],
    //                 'relevance' => $education['relevance'],
    //             ];
    //             $competencePoints -= ($education['highereducation'] === 'master') ? 3 : 1; // Deduct points based on education level
    //         } else {
    //             $adjustedEducationData[$id] = $education;
    //             $adjustedEducationData[$id]['competence_points'] = ($education['highereducation'] === 'master' || $education['highereducation'] === 'bachelor') ? 3 : 1;
    //         }
    //     }

    //     // Update the employeeCV object with the adjusted data
    //     $application->education = $adjustedEducationData;
    //     $application->work_experience = $workExperienceData;

    //     return $application;
    // }

    // ## CHATGPT START
    // Main method remains unchanged.
    public function adjustEducationAndWork($application)
    {

        $birthDate = Carbon::parse($application->birth_date);

        $adjustedEducation = [];
        $adjustedWorkExperience = $application->work_experience ?? [];
        $competencePoints = 0;

        // Process education
        foreach ($application->education as $education) {
            $eduStartDate = Carbon::parse($education['start_date']);

            // Skip if employee was under 18
            if ($birthDate->diffInYears($eduStartDate) < 18) {

                continue;
            }

            // Transfer to work if overlapping employment start
            // if ($eduEndDate->greaterThan($workStartDate)) {
            //     $adjustedWorkExperience[] = $this->convertEducationToWork($education);

            //     continue;
            // }

            $competencePoint = $this->calculateCompetencePoints($education);
            $competencePoints += $competencePoint;
            $education['competence_points'] = $competencePoint;

            // $competencePoints += $this->calculateCompetencePoints($education);
            $adjustedEducation[] = $education;
        }

        $employeeGroup = EmployeeCV::positionsLaddersGroups[$application->job_title];

        // Cap competence points at 7
        if (in_array($employeeGroup['ladder'], ['A', 'B', 'E', 'F'], true) && $competencePoints > 7) {
            $adjustedEducation = $this->moveExcessEducationToWork(
                7,
                $adjustedEducation,
                $adjustedWorkExperience,
                $competencePoints
            );

        } elseif ($employeeGroup['ladder'] === 'C' && $employeeGroup['group'] === 2 && $competencePoints > 5) {
            $adjustedEducation = $this->moveExcessEducationToWork(
                5,
                $adjustedEducation,
                $adjustedWorkExperience,
                $competencePoints
            );
            $application->competence_points = min($competencePoints, 5);
        } elseif ($employeeGroup['ladder'] === 'C' && $employeeGroup['group'] === 1 && $competencePoints > 2) {
            $adjustedEducation = $this->moveExcessEducationToWork(
                2,
                $adjustedEducation,
                $adjustedWorkExperience,
                $competencePoints
            );
        } elseif ($employeeGroup['ladder'] === 'D' && $competencePoints > 4) {
            $adjustedEducation = $this->moveExcessEducationToWork(
                4,
                $adjustedEducation,
                $adjustedWorkExperience,
                $competencePoints
            );
        }
        // adjust competence points
        if (in_array($employeeGroup['ladder'], ['A', 'B', 'E', 'F'], true)) {
            $application->competence_points = min($competencePoints, 7);
        } elseif ($employeeGroup['ladder'] === 'C' && $employeeGroup['group'] === 2) {
            $application->competence_points = min($competencePoints, 5);
        } elseif ($employeeGroup['ladder'] === 'C' && $employeeGroup['group'] === 1) {
            $application->competence_points = min($competencePoints, 2);
        } elseif ($employeeGroup['ladder'] === 'D') {
            $application->competence_points = min($competencePoints, 4);
        }

        // Adjust work experience for overlaps
        $adjustedWorkExperience = $this->adjustWorkExperience($adjustedWorkExperience, $adjustedEducation);

        // Remove duplicates
        $adjustedWorkExperience = $this->removeDuplicates($adjustedWorkExperience);

        // Set adjusted values
        $application->education_adjusted = $adjustedEducation;
        $application->work_experience_adjusted = $adjustedWorkExperience;

        return $application;
    }

    private function convertEducationToWork($education)
    {
        return [
            'title_workplace' => $education['topic_and_school'],
            'work_percentage' => $education['study_percentage'],
            'start_date' => $education['start_date'],
            'end_date' => $education['end_date'],
            'workplace_type' => 'education_converted',
            'relevance' => @$education['relevance'],
        ];
    }

    private function calculateCompetencePoints($education)
    {
        if (strtolower($education['study_points']) === 'bestått') {
            $months = Carbon::parse($education['start_date'])->diffInMonths($education['end_date']);

            return ($months >= 9) && $education['relevance'] ? 1 : 0;
        }

        switch (@$education['highereducation']) {
            case 'bachelor':
                if ($education['study_points'] >= 180) {
                    return $education['relevance'] ? 3 : 1;
                }
            case 'master':
                if ($education['study_points'] >= 120) {
                    return 3;
                }
            default:
                if ($education['study_points'] >= 60) {
                    return 1;
                }
        }
    }

    private function moveExcessEducationToWork($maxCompetencePoints, &$education, &$workExperience, &$competencePoints)
    {
        $remainingPoints = $maxCompetencePoints;
        $newEducation = [];

        $sortedEducation = collect($education)->sort(function ($a, $b) {
            // Step 1: Compare competence_points (descending).
            if ($a['competence_points'] != $b['competence_points']) {
                return $b['competence_points'] <=> $a['competence_points'];
            }

            // Step 2: Compare relevance (descending).
            if ($a['relevance'] != $b['relevance']) {
                return $b['relevance'] <=> $a['relevance'];
            }

            // Step 3: Compare study_points (converted, descending).
            $pointsA = ($a['study_points'] === 'bestått') ? 999 : (int) $a['study_points'];
            $pointsB = ($b['study_points'] === 'bestått') ? 999 : (int) $b['study_points'];

            return $pointsB <=> $pointsA;
        })->values()->all();

        foreach ($sortedEducation as $edu) {
            $points = $edu['competence_points'];

            if ($remainingPoints - $points >= 0) {
                $remainingPoints -= $points;
                $newEducation[] = $edu;
            } else {
                $workExperience[] = $this->convertEducationToWork($edu);
            }
        }

        return $newEducation;
    }

    private function adjustWorkExperience($workExperience, $education)
    {
        $adjustedWork = [];

        foreach ($workExperience as $work) {
            $workStart = Carbon::parse($work['start_date']);
            $workEnd = Carbon::parse($work['end_date']);
            $currentStart = $workStart;  // Track the current start date.

            foreach ($education as $edu) {
                $eduStart = Carbon::parse($edu['start_date']);
                $eduEnd = Carbon::parse($edu['end_date']);

                // If the work period overlaps with the education period, split it.
                if ($this->datesOverlap($currentStart, $workEnd, $eduStart, $eduEnd)) {
                    // Create a segment before the education starts (if applicable).
                    if ($currentStart->lessThan($eduStart)) {
                        $adjustedWork[] = array_merge($work, [
                            'start_date' => $currentStart->toDateString(),
                            'end_date' => $eduStart->subDay()->toDateString(),
                        ]);
                    }

                    // Update the current start date to the day after this education ends.
                    $currentStart = $eduEnd->addDay();
                }
            }

            // Add the remaining part of the work period (if any).
            if ($currentStart->lessThanOrEqualTo($workEnd)) {
                $adjustedWork[] = array_merge($work, [
                    'start_date' => $currentStart->toDateString(),
                    'end_date' => $workEnd->toDateString(),
                ]);
            }
        }

        // Ensure no overlapping work percentages exceed 100%.
        return $this->enforceWorkPercentageLimit($adjustedWork);
    }

    private function enforceWorkPercentageLimit($workExperience)
    {
        // Step 1: Sort work experiences by start date.
        $sortedWork = collect($workExperience)->sortBy('start_date')->values();
        $monthlyPercentage = []; // Track work percentage per month.
        $splitWork = [];

        // Step 2: Split work experiences month-by-month.
        foreach ($sortedWork as $work) {
            $workStart = Carbon::parse($work['start_date']);
            $workEnd = Carbon::parse($work['end_date']);

            while ($workStart->lessThanOrEqualTo($workEnd)) {
                $monthKey = $workStart->format('Y-m'); // Use year-month as a key.

                // Calculate the available percentage for this month.
                $availablePercentage = 100 - ($monthlyPercentage[$monthKey] ?? 0);

                if ($availablePercentage <= 0) {
                    // If no available percentage, skip this month.
                    $workStart->addMonth();

                    continue;
                }

                // Calculate the percentage for this month.
                $allocatedPercentage = min($work['work_percentage'], $availablePercentage);

                // Add the split segment to the collection.
                $splitWork[] = [
                    'title_workplace' => $work['title_workplace'],
                    'workplace_type' => @$work['workplace_type'],
                    'work_percentage' => $allocatedPercentage,
                    'start_date' => $workStart->copy()->startOfMonth()->toDateString(),
                    'end_date' => $workStart->copy()->endOfMonth()->toDateString(),
                    'relevance' => @$work['relevance'],
                ];

                // Update the monthly percentage tracker.
                $monthlyPercentage[$monthKey] = ($monthlyPercentage[$monthKey] ?? 0) + $allocatedPercentage;

                // Move to the next month.
                $workStart->addMonth();
            }
        }

        // Step 3: Merge consecutive segments with the same title and percentage.
        $test = $this->mergeConsecutiveSegments($splitWork);
        if (count($test) === 1) {
            $test[0]['end_date'] = $work['end_date'];
        }

        return $test;
    }

    private function mergeConsecutiveSegments($workSegments)
    {
        $mergedWork = [];
        $previous = null;

        foreach ($workSegments as $current) {
            if (
                $previous &&
                $previous['title_workplace'] === $current['title_workplace'] &&
                $previous['work_percentage'] === $current['work_percentage'] &&
                Carbon::parse($previous['end_date'])->addDay()->equalTo(Carbon::parse($current['start_date']))
            ) {
                // If consecutive, extend the previous segment's end date.
                $previous['end_date'] = $current['end_date'];
            } else {
                // If not consecutive, push the previous segment and start a new one.
                if ($previous) {
                    $mergedWork[] = $previous;
                }
                $previous = $current;
            }
        }

        // Push the last segment if it exists.
        if ($previous) {
            $mergedWork[] = $previous;
        }

        return $mergedWork;
    }

    private function datesOverlap($start1, $end1, $start2, $end2)
    {
        return $start1->lte($end2) && $end1->gte($start2);
    }

    private function removeDuplicates($workExperience)
    {
        return collect($workExperience)->unique(function ($work) {
            return $work['title_workplace'].$work['start_date'].$work['end_date'];
        })->values()->all();
    }
    // ## CHATGPT END ##

    public static function calculateStudyPercentage(string $startDate, string $endDate, int $studyPoints): float
    {
        // Constants
        $fullTimePointsPerYear = 60;        // Standard points for a full-time year
        $activeStudyMonthsPerYear = 10;     // Active study months per year
        $allowedExtraMonths = 3;            // Extra months allowed without penalty

        // Parse input dates using Carbon
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Validate dates: start must be before end
        if ($start->greaterThanOrEqualTo($end)) {
            throw new InvalidArgumentException('The start date must be before the end date.');
        }

        if ($studyPoints >= 60) {
            $expectedStudyYears = $studyPoints / 60;
            $studyMonths = $start->diffInMonths($end) + 3;
            $percentage = $expectedStudyYears * 100 / ($studyMonths / 12);
        } else {

            // Calculate total months between start and end dates
            $totalMonths = $start->diffInMonths($end);

            // Calculate expected years and months based on study points
            $yearsRequired = $studyPoints / $fullTimePointsPerYear;
            $expectedMonths = $yearsRequired * $activeStudyMonthsPerYear;

            // Include allowed extra months
            $expectedMonthsWithGrace = $expectedMonths + $allowedExtraMonths;

            // If the actual time is within the expected range (+3 months), return 100%
            if ($totalMonths <= $expectedMonthsWithGrace) {
                return 100.0;
            }

            // Otherwise, calculate the percentage based on actual time taken
            $percentage = ($expectedMonths / $totalMonths) * 100;
        }
        // Ensure the percentage doesn't exceed 100%
        $percentage = min($percentage, 100);

        // Round to the nearest multiple of 10
        return (int) ceil($percentage / 10) * 10;
    }

    public static function calculateTotalWorkExperienceMonths($workExperienceData)
    {
        $totalMonths = 0;

        foreach ($workExperienceData as $workExperience) {
            $startDate = Carbon::parse($workExperience['start_date']);
            $endDate = Carbon::parse($workExperience['end_date']);

            // Calculate the difference in months
            $diffInMonths = ($endDate->format('Y') - $startDate->format('Y')) * 12 + $endDate->format('n') - $startDate->format('n') + 1;

            // Multiply by work percentage and add to the total
            $totalMonths += ($diffInMonths * $workExperience['work_percentage']) / 100;
        }

        return $totalMonths;
    }

    public static function getYearsDifferenceWithDecimals(Carbon $startDate, Carbon $endDate): float
    {
        // Get the total number of days between the two dates
        $totalDays = $startDate->diffInDays($endDate);

        // Convert days to years (with decimals)
        return $totalDays / 365.25; // Accounting for leap years
    }

    public static function addMonthsWithDecimals(Carbon $date, float $totalMonths): Carbon
    {
        // Separate the integer and fractional parts of the total months
        $integerMonths = (int) $totalMonths;
        $fractionalMonths = $totalMonths - $integerMonths;

        // Add the integer part to the date
        $newDate = $date->copy()->subMonths($integerMonths);

        // Calculate the days to add for the fractional part
        $daysInMonth = $newDate->daysInMonth; // Get the number of days in the current month
        $fractionalDays = ceil($fractionalMonths * $daysInMonth); // Round up fractional days

        // Add the fractional days
        return $newDate->subDays($fractionalDays);
    }
}
