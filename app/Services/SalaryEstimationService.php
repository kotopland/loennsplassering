<?php

namespace App\Services;

use App\Models\EmployeeCV;
use Carbon\Carbon;

class SalaryEstimationService
{
    public function checkForSavedApplication($application)
    {
        if ($application->id) {
            return $application;
        }

        $applicationId = session('applicationId') ?? request('applicationId');

        if ($applicationId) {
            return EmployeeCV::find($applicationId);
        }

        $newApplication = EmployeeCV::create();
        session(['applicationId' => $newApplication->id]);

        return $newApplication;
    }

    // ## CHATGPT START
    // Main method remains unchanged.
    public function adjustEducationAndWork($application)
    {

        $dateAge18 = Carbon::parse($application->birth_date)->addYears(18);

        $adjustedEducation = [];
        $adjustedWorkExperience = $application->work_experience ?? [];
        $competencePoints = 0;

        // Process education
        foreach ($application->education ?? [] as $id => $education) {
            $eduStartDate = Carbon::parse($education['start_date']);
            $eduEndDate = Carbon::parse($education['end_date']);

            // If both start and end dates are before birthDate, skip it.
            if ($eduEndDate->lessThan($dateAge18)) {
                unset($adjustedEducation[$id]);

                continue;
            }

            // If start date is before birthDate and end date is after, adjust start date.
            if ($eduStartDate->lessThan($dateAge18) && $eduEndDate->greaterThanOrEqualTo($dateAge18) && ! $this->containsAnyString($education['topic_and_school'], ['videregående', 'vgs', 'fagskole'])) {
                $education['start_date'] = $dateAge18->toDateString();
                $adjustedEducation[$id][] = ['comments' => 'Endrer start til 18 års alder.'];

            }

            // Skip if employee was under 18 at adjusted start date.
            if ($dateAge18->diffInYears(Carbon::parse($education['start_date'])) < 0) {
                continue;
                unset($adjustedEducation[$id]);
            }

            $competencePoint = $this->calculateCompetencePoints($application, $education);
            $competencePoints += $competencePoint;
            $education['competence_points'] = $competencePoint;

            $adjustedEducation[] = $education;
        }

        // Process work_experience
        foreach ($application->work_experience ?? [] as $id => $work) {
            $workStartDate = Carbon::parse($work['start_date']);
            $workEndDate = Carbon::parse($work['end_date']);

            // If both start and end dates are before birthDate, skip it.
            if ($workEndDate->lessThan($dateAge18)) {
                unset($adjustedWorkExperience[$id]);

                continue;
            }

            // If start date is before birthDate and end date is after, adjust start date.
            if ($workStartDate->lessThan($dateAge18) && $workEndDate->greaterThanOrEqualTo($dateAge18)) {
                $this->removeDuplicates($adjustedWorkExperience);

                // $work['start_date'] = $dateAge18->addMonth()->toDateString();
                $adjustedWorkExperience[$id]['start_date'] = $dateAge18->addDay()->toDateString();
                $adjustedWorkExperience[$id][] = ['comments' => 'Endrer start til etter 18 års alder.'];

                continue;
            }
            // Skip if employee was under 18 at adjusted start date.
            if ($dateAge18->diffInYears(Carbon::parse($work['start_date'])) < 0) {
                unset($adjustedWorkExperience[$id]);

                continue;
            }

        }
        // dd($adjustedWorkExperience);
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

        // Adjust education for overlaps
        $adjustedEducation = $this->adjustEducation($adjustedEducation);

        // Adjust work experience for overlaps
        $adjustedWorkExperience = $this->adjustWorkExperience($adjustedWorkExperience, $adjustedEducation);

        // Remove duplicates
        $adjustedWorkExperience = $this->removeDuplicates($adjustedWorkExperience);

        // Set adjusted values
        $application->education_adjusted = $adjustedEducation;
        $application->work_experience_adjusted = $adjustedWorkExperience;

        return $application;
    }

    public function adjustEducation($adjustedEducation)
    {
        $educationArray = [];
        foreach ($adjustedEducation as $education) {
            $eduEndDate = Carbon::parse($education['end_date']);
            if ($eduEndDate->day === 1) {
                $education['end_date'] = $eduEndDate->subDay()->toDateString();
                $educationArray[] = $education;
            }
        }

        return $educationArray;
    }

    public function containsAnyString($text, array $searchStrings): bool
    {
        foreach ($searchStrings as $string) {
            if (stripos(strtolower($text), strtolower($string)) !== false) {

                return true;
            }
        }

        return false;
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

    private function calculateCompetencePoints($application, $education)
    {
        if (strtolower($education['study_points']) === 'bestått') {
            $months = Carbon::parse($education['start_date'])->diffInMonths($education['end_date']);

            return ($months >= 9) && $education['relevance'] ? 1 : 0;
        }
        $employeeGroup = EmployeeCV::positionsLaddersGroups[$application->job_title];
        switch (@$education['highereducation']) {
            case 'bachelor':

                if ($education['study_points'] >= 180) {
                    if (in_array($employeeGroup['ladder'], ['A', 'B', 'E', 'F'], true)) {
                        return $education['relevance'] ? 3 : 1;
                    } elseif ($employeeGroup['ladder'] === 'D') {
                        return $education['relevance'] ? 2 : 1;
                    }
                }
            case 'master':
                if ($education['study_points'] >= 300) {
                    if (in_array($employeeGroup['ladder'], ['A', 'B', 'E', 'F'], true)) {
                        return $education['relevance'] ? 7 : 6;
                    } elseif ($employeeGroup['ladder'] === 'D') {
                        return $education['relevance'] ? 4 : 3;
                    }
                } elseif ($education['study_points'] >= 120) {
                    if (in_array($employeeGroup['ladder'], ['A', 'B', 'E', 'F'], true)) {
                        return 3;
                    } elseif ($employeeGroup['ladder'] === 'D') {
                        return 2;
                    }
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

        foreach ($sortedEducation ?? [] as $edu) {
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

        foreach ($workExperience ?? [] as $work) {
            $workStart = Carbon::parse($work['start_date']);
            $workEnd = Carbon::parse($work['end_date']);
            $currentStart = $workStart;

            // Adjust work_percentage for "freechurch" type after May 1, 2014.
            if ($work['workplace_type'] === 'freechurch' && $workStart->greaterThanOrEqualTo(Carbon::parse('2014-05-01'))) {
                $work['work_percentage'] = 100;
            }

            foreach ($education ?? [] as $edu) {
                $eduStart = Carbon::parse($edu['start_date']);
                $eduEnd = Carbon::parse($edu['end_date']);

                // Check if the work overlaps with education and meets the special conditions.
                $overlapAllowed = in_array($work['workplace_type'], ['freechurch', 'other_christian']) &&
                                  $eduEnd->greaterThanOrEqualTo(Carbon::parse('2015-01-01')) && $edu['relevance'];

                if (! $overlapAllowed && $this->datesOverlap($currentStart, $workEnd, $eduStart, $eduEnd)) {
                    // Create a segment before the education starts.
                    if ($currentStart->lessThan($eduStart)) {
                        $adjustedWork[] = array_merge($work, [
                            'start_date' => $currentStart->toDateString(),
                            'end_date' => $eduStart->subDay()->toDateString(),
                        ]);
                    }

                    // Update the current start date to the day after education ends.
                    $currentStart = $eduEnd->addDay();
                }
            }

            // Add the remaining part of the work period.
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
        foreach ($sortedWork ?? [] as $work) {
            $workStart = Carbon::parse($work['start_date']);
            $workEnd = Carbon::parse($work['end_date']);

            // Adjust work_percentage for "freechurch" type after May 1, 2014.
            $workSpcialConditionDate = Carbon::parse('2014-05-01');

            while ($workStart->lessThanOrEqualTo($workEnd)) {
                $monthKey = $workStart->format('Y-m'); // Use year-month as a key.

                if (Carbon::parse($monthKey.'-01')->greaterThanOrEqualTo($workSpcialConditionDate) && $work['workplace_type'] === 'freechurch') {
                    $work['work_percentage'] = 100;
                    $work['relevance'] = 1;
                }
                // Calculate the available percentage for this month.
                $availablePercentage = 100 - ($monthlyPercentage[$monthKey] ?? 0);

                if ($availablePercentage <= 0) {
                    // If no available percentage, skip this month.
                    $workStart->addMonth();

                    continue;
                }

                // Calculate the percentage for this month.
                $allocatedPercentage = min($work['work_percentage'], $availablePercentage);

                if ($workStart->lessThanOrEqualTo(Carbon::parse($work['start_date']))) {
                    $workStart = Carbon::parse($work['start_date']);
                } else {
                    $workStart = $workStart->copy()->startOfMonth();
                }

                if ($workEnd->lessThanOrEqualTo($workStart->copy()->endOfMonth())) {
                    $arrayWorkEnd = $workEnd->toDateString();
                } else {
                    $arrayWorkEnd = $workStart->copy()->endOfMonth()->toDateString();
                }
                // Add the split segment to the collection.
                $splitWork[] = [
                    'title_workplace' => $work['title_workplace'],
                    'workplace_type' => @$work['workplace_type'],
                    'work_percentage' => $allocatedPercentage,
                    'start_date' => $workStart->toDateString(),
                    'end_date' => $arrayWorkEnd,
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

        foreach ($workSegments ?? [] as $current) {
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

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        // Validate dates: start must be before end
        if ($start->greaterThanOrEqualTo($end)) {
            throw new \InvalidArgumentException('The start date must be before the end date.');
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

        foreach ($workExperienceData ?? [] as $workExperience) {
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

    public function createTimelineData($educationData, $workExperienceData)
    {
        $allData = [];
        $timeline = [];
        foreach ($educationData ?? [] as $education) {
            $endDate = Carbon::parse($education['end_date']);

            if ($endDate->day === 1) {
                $endDate->subDay();
            }
            $endDate = $endDate->format('Y-m-d');

            $allData[] = [
                'title' => $education['topic_and_school'],
                'start_date' => $education['start_date'],
                'end_date' => $endDate,
                'percentage' => $education['study_percentage'],
                'type' => 'education',
            ];
        }
        foreach ($workExperienceData ?? [] as $workExperience) {
            $endDate = Carbon::parse($workExperience['end_date']);

            if ($endDate->day === 1) {
                $endDate->subDay();
            }
            $endDate = $endDate->format('Y-m-d');

            $allData[] = [
                'title' => $workExperience['title_workplace'],
                'start_date' => $workExperience['start_date'],
                'end_date' => $endDate,
                'percentage' => $workExperience['work_percentage'],
                'type' => 'work',
            ];
        }

        if (count($allData) !== 0) {

            // 2. Determine the timeline
            $earliestMonth = min(array_map(function ($item) {
                return strtotime($item['start_date']);
            }, $allData));
            $latestMonth = max(array_map(function ($item) {
                return strtotime($item['end_date']);
            }, $allData));

            $currentMonth = $earliestMonth;
            while ($currentMonth <= $latestMonth) {
                $timeline[] = date('Y-m', $currentMonth);
                $currentMonth = strtotime('+1 month', $currentMonth);
            }
        }

        return [
            'timeline' => $timeline,
            'tableData' => $allData,
        ];
    }
}
