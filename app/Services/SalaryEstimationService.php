<?php

namespace App\Services;

use App\Models\EmployeeCV;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SalaryEstimationService
{
    private Carbon $dateAge18;

    /**
     * Retrieves an existing EmployeeCV application or creates a new one.
     * If the provided $application object has an ID, it's returned.
     * Otherwise, it attempts to find an application using 'applicationId' from the session or request.
     * If not found, a new EmployeeCV is created, its ID is stored in the session, and the new application is returned.
     *
     */
    public function getOrCreateApplication($application)
    {
        if ($application->id) {
            return $application;
        }

        $applicationId = session('applicationId') ?? request('applicationId');

        if ($applicationId) {
            $employeeCv = EmployeeCV::find($applicationId);
            $employeeCv->timestamps = false;
            $employeeCv->last_viewed = now();
            $employeeCv->save();
            $employeeCv->timestamps = true;

            return $employeeCv;
        }

        $newApplication = EmployeeCV::create();
        $newApplication->timestamps = false;
        $newApplication->last_viewed = now();
        $newApplication->save();
        $newApplication->timestamps = true;
        session(['applicationId' => $newApplication->id]);

        return $newApplication;
    }

    /**
     * Adjusts education and work experience data for an EmployeeCV application.
     * This includes adjusting start dates based on age (18+), calculating competence points,
     * handling overlaps between education and work experience, and capping competence points based on employee group.
     *
     * @param  EmployeeCV  $application The EmployeeCV application to adjust.
     * @return EmployeeCV The adjusted EmployeeCV application.
     */
    public function adjustEducationAndWork($application)
    {
        $this->dateAge18 = Carbon::parse($application->birth_date)->addYears(18);
        $employeeGroup = (new EmployeeCV)->getPositionsLaddersGroups()[$application->job_title];

        $adjustedEducation = $application->education ?? [];
        $adjustedWorkExperience = $application->work_experience ?? [];
        $competencePoints = 0;

        // Limit education to 18 or more years of age
        $adjustedEducation = $this->adjustEducationDate($application->work_start_date, $adjustedEducation);

        // Limit education to work start date

        // calculate competence points
        [$adjustedEducation, $competencePoints] = $this->calculateCompetencePointsForEducation($application, $adjustedEducation);

        // Move capped education to work experience
        $adjustedEducation = $this->moveCappedEducationToWorkExperience($application, $employeeGroup, $competencePoints, $adjustedEducation, $adjustedWorkExperience);

        // Cap competence points for each group
        $application->competence_points = $this->capCompetencePoints($employeeGroup, $competencePoints);

        // Limit work experience to 18 or more years of age
        $adjustedWorkExperience = $this->adjustWorkExperienceStartDate($application, $adjustedWorkExperience);

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

    /**
     * Adjusts work experience start dates to be on or after the applicant's 18th birthday
     * and end dates to be before the application's work start date.
     * Goes through each work experience entry and does the following:
     * - If the end date is before the applicant turned 18, remove the entry.
     * - If the start date is before the applicant turned 18 and the end date is after,
     *   update the start date to be the day after the applicant turned 18, and add a comment.
     */
    private function adjustWorkExperienceStartDate(EmployeeCV $application, array $adjustedWorkExperience = []): array
    {

        foreach ($adjustedWorkExperience as $id => $work) {
            $workStartDate = Carbon::parse($work['start_date']);
            $workEndDate = Carbon::parse($work['end_date']);

            if ($workEndDate->lessThan($this->dateAge18)) {
                unset($adjustedWorkExperience[$id]);

                continue;
            }

            if ($workStartDate->lessThan($this->dateAge18) && $workEndDate->greaterThanOrEqualTo($this->dateAge18)) {
                $this->removeDuplicates($adjustedWorkExperience);
                $adjustedWorkExperience[$id]['start_date'] = $this->dateAge18->addDay()->toDateString();
                $adjustedWorkExperience[$id]['comments'] = @$adjustedWorkExperience[$id]['comments'] . 'Endret start til etter 18 års alder. ';

                continue;
            }

            if ($this->dateAge18->diffInYears(Carbon::parse($work['start_date'])) < 0) {
                unset($adjustedWorkExperience[$id]);

                continue;
            }

            if ($workStartDate->lessThan($application->work_start_date) && $workEndDate->greaterThanOrEqualTo($application->work_start_date)) {
                $this->removeDuplicates($adjustedWorkExperience);
                $adjustedWorkExperience[$id]['end_date'] = Carbon::parse($application->work_start_date)->subDay()->toDateString();
                $adjustedWorkExperience[$id]['comments'] = @$adjustedWorkExperience[$id]['comments'] . 'Endret sluttdato til dagen før tiltredelsesdato. ';

                continue;
            }
        }

        return $adjustedWorkExperience;
    }

    /**
     * Adjusts education start dates to be on or after the applicant's 18th birthday.
     * Does the following:
     * - If the education ended before the applicant turned 18, remove the entry.
     * - If the education started before the applicant turned 18 and the end date is after,
     *   update the start date to be the 18th birthday, and add a comment (unless it's VGS/fagskole).
     *
     */
    private function adjustEducationDate($workStartDate, array $education): array
    {

        $educationAdjusted = [];
        foreach ($education as $id => $edu) {
            $eduStartDate = Carbon::parse($edu['start_date']);
            $eduEndDate = Carbon::parse($edu['end_date']);

            if ($eduEndDate->lessThan($this->dateAge18)) {
                continue;  // Remove if education ended before 18
            }

            if ($eduStartDate->lessThan($this->dateAge18) && $eduEndDate->greaterThanOrEqualTo($this->dateAge18) && ! $this->containsAnyString($edu['topic_and_school'], ['videregående', 'vgs', 'fagskole'])) {
                $edu['start_date'] = $this->dateAge18->toDateString();
                $edu['comments'] = @$edu['comments'] . 'Endret start til 18 års alder. ';
            }

            if ($this->dateAge18->diffInYears(Carbon::parse($edu['start_date'])) < 0) {
                continue; // Remove if still under 18 after adjustment
            }

            // if ($eduEndDate->greaterThan($workStartDate)) {

            //     $edu['end_date'] = Carbon::parse($workStartDate)->subDay()->toDateString();
            //     $edu['comments'] = @$edu['comments'].'Endret sluttdato til tiltredelsesdato. ';

            // }
            $educationAdjusted[] = $edu;
        }

        return $educationAdjusted;
    }

    /**
     * Calculates the total competence points for the given education entries and updates the entries with these points.
     * Goes through each education entry, calculates the competence points for it,
     * and adds it to the total. Also adds the competence points to the education
     * array itself.
     */
    private function calculateCompetencePointsForEducation(EmployeeCV $application, array $adjustedEducation): array
    {
        $competencePoints = 0;
        $incompleteDegreeYears = 0;

        foreach ($adjustedEducation as $id => $education) {
            $competencePoint = $this->calculateCompetencePoints($application, $education);
            if ($education['study_points'] != 'bestått' && $education['study_points'] >= 60 && $education['relevance'] && empty($education['highereducation'])) {
                $incompleteDegreeYears += $education['study_points'] / 60;
                if ($incompleteDegreeYears > 2) {
                    $competencePoint = 0;
                }
            }
            $competencePoints += $competencePoint;
            $adjustedEducation[$id]['competence_points'] = $competencePoint;
        }

        return [$adjustedEducation, $competencePoints];
    }

    /**
     * Caps the competence points based on the employee's job group and ladder.
     * Determines the maximum allowable competence points for an employee based
     * on their group and ladder. The competence points are then capped to this
     * maximum value.
     */
    private function capCompetencePoints(array $employeeGroup, int $competencePoints): int
    {
        $maxPoints = [
            'A' => 7,
            'B' => 7,
            'C' => $employeeGroup['group'] === 2 ? 5 : 2,
            'D' => 4,
            'E' => 7,
            'F' => 7,
        ];

        return min($competencePoints, $maxPoints[$employeeGroup['ladder']]);
    }

    /**
     * Moves education entries that exceed the competence point cap for the employee's group to work experience.
     * Also moves education entries that provide zero competence points to work experience.
     * The `$competencePoints` and `$adjustedEducation` parameters are passed by reference and modified directly.
     */
    private function moveCappedEducationToWorkExperience($application, array $employeeGroup, int &$competencePoints, array &$adjustedEducation, array &$adjustedWorkExperience): array
    {
        if (in_array($employeeGroup['ladder'], ['A', 'B', 'E', 'F'], true) && $competencePoints > 7) {
            $adjustedEducation = $this->moveExcessEducationToWork(7, $adjustedEducation, $adjustedWorkExperience, $competencePoints);
        } elseif ($employeeGroup['ladder'] === 'C' && $employeeGroup['group'] === 2 && $competencePoints > 5) {
            $adjustedEducation = $this->moveExcessEducationToWork(5, $adjustedEducation, $adjustedWorkExperience, $competencePoints);
            $application->competence_points = min($competencePoints, 5);
        } elseif ($employeeGroup['ladder'] === 'C' && $employeeGroup['group'] === 1 && $competencePoints > 2) {
            $adjustedEducation = $this->moveExcessEducationToWork(2, $adjustedEducation, $adjustedWorkExperience, $competencePoints);
        } elseif ($employeeGroup['ladder'] === 'D' && $competencePoints > 4) {
            $adjustedEducation = $this->moveExcessEducationToWork(4, $adjustedEducation, $adjustedWorkExperience, $competencePoints);
        }
        $this->moveEducationWithZeroCompetenceToWork($adjustedEducation, $adjustedWorkExperience);

        return $adjustedEducation;
    }

    /**
     * Adjusts the end date of education entries.
     * For each education entry, if the end date is the first day of a month,
     * it changes the end date to the last day of the previous month and appends
     * a comment indicating the change. This is done to ensure correct calculations.
     */
    public function adjustEducation($adjustedEducation)
    {
        $educationArray = [];
        foreach ($adjustedEducation as $education) {
            $eduEndDate = Carbon::parse($education['end_date']);
            if ($eduEndDate->day === 1) {
                $education['end_date'] = $eduEndDate->subDay()->toDateString();
                $education['comments'] = @$education['comments'] . 'Endret sluttdato til siste dag i forrige måned for korrekt utregning. ';
            }
            $educationArray[] = $education;
        }

        return $educationArray;
    }

    /**
     * Checks if a given text contains any of the specified search strings (case-insensitive).
     * Case insensitive search.
     */
    public function containsAnyString($text, array $searchStrings): bool
    {
        foreach ($searchStrings as $string) {
            if (stripos(strtolower($text), strtolower($string)) !== false) {

                return true;
            }
        }

        return false;
    }

    /**
     * Converts an education entry into a work experience-like array structure.
     * This function takes an educational entry and transforms it to resemble a work
     * experience entry by mapping fields such as title, percentage, start date,
     * and end date. It assigns a specific workplace type to indicate the conversion
     * and appends relevant comments. The conversion is useful for calculating
     * seniority based on educational background.
     */
    private function convertEducationToWork($education)
    {
        return [
            'title_workplace' => $education['topic_and_school'],
            'percentage' => floatval($education['percentage']),
            'start_date' => $education['start_date'],
            'end_date' => $education['end_date'],
            'workplace_type' => 'education_converted',
            'relevance' => @$education['relevance'],
            'comments' => @$education['comments'] . 'Utdanning gjort om til å gi uttelling i ansiennitet. ',
            'original' => false,
            'id' => $education['id'],
        ];
    }

    /**
     * Calculates competence points for a single education entry based on its type, study points, relevance,
     * and the employee's job group ladder.
     */
    private function calculateCompetencePoints($application, $education)
    {

        if (strtolower($education['study_points']) === 'bestått') {
            $months = Carbon::parse($education['start_date'])->diffInMonths($education['end_date']);

            return ($months >= 9) && $education['relevance'] ? 1 : 0;
        }
        $employeeGroup = (new EmployeeCV)->getPositionsLaddersGroups()[$application->job_title];
        switch (@$education['highereducation']) {
            case 'bachelor':
                if ($education['study_points'] >= 180) {
                    if (in_array($employeeGroup['ladder'], ['A', 'B', 'E', 'F'], true)) {

                        return $education['relevance'] ? 3 : 1;
                    } elseif ($employeeGroup['ladder'] === 'D') {

                        return $education['relevance'] ? 2 : 1;
                    }
                } else {
                    return 0;
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
                } else {
                    return 0;
                }
            case 'cand.theol.':
                if (in_array($employeeGroup['ladder'], ['A', 'B', 'E', 'F'], true)) {
                    return 7;
                } elseif ($employeeGroup['ladder'] === 'D') {
                    return 4;
                } else {
                    return 0;
                }
            default:
                if ($education['study_points'] >= 60 && $education['relevance']) {

                    return 1;
                } else {

                    return 0;
                }
        }
    }

    /**
     * Moves education entries that exceed the maximum competence points to work experience.
     * This method takes the original education array and splits it into two parts:
     * 1. The part that fits within the max competence points ($maxCompetencePoints).
     * 2. The part that exceeds the max competence points and is converted to work experience.
     * The method sorts the education array in descending order based on the following criteria:
     * competence points, relevance, and study points ('bestått' treated as high value).
     * The `$education`, `$workExperience`, and `$competencePoints` parameters are modified by reference.
     */
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

    /**
     * Moves education entries that provide zero competence points to the work experience array.
     * The `$workExperience` array is modified by reference.
     */
    private function moveEducationWithZeroCompetenceToWork($education, &$workExperience)
    {

        foreach ($education ?? [] as $edu) {
            if ($edu['competence_points'] == 0) {
                $workExperience[] = $this->convertEducationToWork($edu);
            }
        }
    }

    /**
     * Adjusts work experience periods to avoid overlaps with education periods, unless specific conditions allow it.
     * This method iterates through the given work experiences and adjusts the periods
     * by checking overlaps with education periods. If certain conditions are met, such as
     * relevance and type of education, the overlap is allowed. Otherwise, the work period
     * is segmented to avoid overlaps. Additionally, for "freechurch" type workplaces after
     * May 1, 2014, the percentage is adjusted to 100%.
     */
    private function adjustWorkExperience($workExperience, $education)
    {
        $adjustedWork = [];

        foreach ($workExperience ?? [] as $work) {
            $workStart = Carbon::parse($work['start_date']);
            $workEnd = Carbon::parse($work['end_date']);
            $currentStart = $workStart;

            // Adjust percentage for "freechurch" type after May 1, 2014.
            if (array_key_exists('workplace_type', $work) && $work['workplace_type'] === 'freechurch' && $workStart->greaterThanOrEqualTo(Carbon::parse('2014-05-01'))) {
                $work['percentage'] = 100;
            }

            foreach ($education ?? [] as $edu) {
                $eduStart = Carbon::parse($edu['start_date']);
                $eduEnd = Carbon::parse($edu['end_date']);

                // Check if the work overlaps with education and meets the special conditions. If education has been moved to work, allow it to overlap.
                $overlapAllowed =
                    $eduEnd->greaterThanOrEqualTo(Carbon::parse('2015-01-01')) && in_array($edu['highereducation'], ['bachelor', 'master', 'cand.theol.'], true) // || $eduStart->greaterThanOrEqualTo(Carbon::parse('2015-01-01')
                    && $edu['relevance'] && array_key_exists('workplace_type', $work) && in_array($work['workplace_type'], ['freechurch', 'other_christian'])
                    || $this->in_array_r($edu['topic_and_school'], $work);


                if (! $overlapAllowed && $this->datesOverlap($currentStart, $workEnd, $eduStart, $eduEnd)) {

                    // Create a segment before the education starts.
                    $work['comments'] = Str::finish(@$work['comments'], 'Følger regler for ansiennitet sammen med kompetansepoenggivende utdanning. ');
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

    /**
     * Recursively searches for a given value within a nested array.
     */
    private function in_array_r($needle, $haystack, $strict = false)
    {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enforces a 100% work percentage limit per month across all work experiences.
     * Splits work experiences into accurately dated segments based on overlaps and enforces a 100% total work percentage limit
     * across all concurrent work experiences within any given time interval.
     * Original work experiences are prioritized (e.g., by their start date) when capping percentages.
     * It also handles special conditions like the "freechurch" rule and merges consecutive segments
     * that have the same title, (capped) percentage, and other relevant properties.
     */
    private function enforceWorkPercentageLimit($workExperience)
    {
        // Sort original work experiences by start date to define priority for capping
        $sortedOriginalWorkExperience = collect($workExperience)->sortBy('start_date')->values()->all();
        if (empty($workExperience)) {
            return [];
        }

        // Step 1: Collect all unique start and (end date + 1 day) to define atomic intervals
        $eventPoints = [];
        foreach ($workExperience as $work) {
            try { // Use $sortedOriginalWorkExperience if priority is based on initial sort for event points
                $eventPoints[] = Carbon::parse($work['start_date']);
                $eventPoints[] = Carbon::parse($work['end_date'])->addDay();
            } catch (\Exception $e) {
                // Skip invalid date entries
                continue;
            }
        }

        if (empty($eventPoints)) {
            return [];
        }

        // Sort and unique the event points
        usort($eventPoints, function (Carbon $a, Carbon $b) {
            return $a->timestamp <=> $b->timestamp;
        });

        $uniqueEventPoints = [];
        if (!empty($eventPoints)) {
            $uniqueEventPoints[] = $eventPoints[0];
            for ($idx = 1; $idx < count($eventPoints); $idx++) {
                if ($eventPoints[$idx]->notEqualTo($eventPoints[$idx - 1])) {
                    $uniqueEventPoints[] = $eventPoints[$idx];
                }
            }
        }
        if (count($uniqueEventPoints) < 2) { // Need at least two points to form an interval
            return [];
        }

        $splitWork = [];
        $workSpcialConditionDate = Carbon::parse('2014-05-01');

        // Step 2: Iterate through atomic intervals defined by unique event points
        for ($i = 0; $i < count($uniqueEventPoints) - 1; $i++) {
            $intervalStart = $uniqueEventPoints[$i];
            $intervalEnd = $uniqueEventPoints[$i + 1]->copy()->subDay(); // End date of the interval (inclusive)

            if ($intervalStart->greaterThan($intervalEnd)) {
                continue; // Skip zero or negative length intervals
            }

            $currentIntervalTotalPercentage = 0;

            // Iterate through the *sorted* original work experiences to apply capping logic
            foreach ($sortedOriginalWorkExperience as $originalWork) {
                if ($currentIntervalTotalPercentage >= 100) {
                    break; // This interval is full, no need to process more work items for it
                }

                $originalWorkStart = Carbon::parse($originalWork['start_date']);
                $originalWorkEnd = Carbon::parse($originalWork['end_date']);

                // Check if the original work period overlaps with the current atomic interval
                if ($originalWorkStart->lte($intervalEnd) && $originalWorkEnd->gte($intervalStart)) {
                    $requestedPercentage = floatval($originalWork['percentage']);
                    $segmentRelevance = @$originalWork['relevance'];
                    $segmentComments = $originalWork['comments'] ?? '';
                    $workplaceType = @$originalWork['workplace_type'];

                    // Apply "freechurch" logic: if the type is 'freechurch' and the interval starts on/after the special date
                    if (array_key_exists('workplace_type', $originalWork) && $workplaceType === 'freechurch' && $intervalStart->greaterThanOrEqualTo($workSpcialConditionDate)) {
                        $requestedPercentage = 100;
                        // Relevance and comments related to this rule will be set when creating the segment
                    }

                    $availablePercentageInInterval = 100 - $currentIntervalTotalPercentage;
                    $allocatedPercentage = min($requestedPercentage, $availablePercentageInInterval);

                    if ($allocatedPercentage > 0) {
                        $finalSegmentComments = $segmentComments;
                        $finalSegmentRelevance = $segmentRelevance;

                        // If freechurch rule was triggered, update comments and relevance for the segment
                        if (array_key_exists('workplace_type', $originalWork) && $workplaceType === 'freechurch' && $intervalStart->greaterThanOrEqualTo($workSpcialConditionDate)) {
                            $newCommentPart = '100% Ansiennitet i Frikirkestillinger etter 1 mai 2014.';
                            $finalSegmentComments = $segmentComments ? $segmentComments . ' ' . $newCommentPart : $newCommentPart;
                            $finalSegmentRelevance = 1; // true
                        }

                        $splitWork[] = [
                            'title_workplace' => $originalWork['title_workplace'],
                            'workplace_type' => $workplaceType,
                            'percentage' => $allocatedPercentage,
                            'start_date' => $intervalStart->toDateString(),
                            'end_date' => $intervalEnd->toDateString(),
                            'relevance' => $finalSegmentRelevance,
                            'comments' => trim($finalSegmentComments) ?: null,
                            'original' => false,
                            'id' => @$originalWork['id'],
                        ];
                        $currentIntervalTotalPercentage += $allocatedPercentage;
                    }
                }
            }
        }

        // Step 3: Merge consecutive segments with the same title and percentage.
        return $this->mergeConsecutiveSegments($splitWork);
    }

    /**
     * Merges consecutive work segments if they share the same title, percentage, and are contiguous.
     * This takes an array of work segments and merges consecutive segments that have the same title and percentage.
     * The start and end dates of the merged segments are updated accordingly.
     */
    private function mergeConsecutiveSegments($workSegments)
    {
        $mergedWork = [];
        $previous = null;

        foreach ($workSegments ?? [] as $current) {
            if (
                $previous &&
                $previous['title_workplace'] === $current['title_workplace'] &&
                $previous['percentage'] === floatval($current['percentage']) &&
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

    /**
     * Calculates the study percentage based on start date, end date, and study points.
     * The calculation is as follows:
     * - If the study points are 60 or more, the percentage is calculated based on the expected years and months.
     * - If the study points are less than 60, the percentage is calculated based on the actual time taken, with a grace period of 3 months.
     * - The percentage is rounded to the nearest multiple of 10.
     */
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

    /**
     * Calculates the total work experience in months from an array of work experience data.
     * Each work experience segment is expected to have a start date, end date, and percentage.
     * The total months is calculated by summing the difference in months for each segment,
     * multiplied by the work percentage for that segment.
     */
    public static function calculateTotalWorkExperienceMonths($workExperienceData): float
    {
        $totalMonths = 0;
        foreach ($workExperienceData ?? [] as $workExperience) {
            try {
                $startDate = Carbon::parse($workExperience['start_date']);
                $endDate = Carbon::parse($workExperience['end_date']);

                // Ensure the end date is not before the start date
                if ($endDate->lessThan($startDate)) {
                    continue;
                }
                $factor = ($workExperience['relevance'] != true) ? 0.5 : 1;
                // Calculate the difference in months
                $diffInMonths = self::calculateDateDifference($workExperience['start_date'], $workExperience['end_date']); // $startDate->diffInMonths($endDate->addDay()); //add day is a workaround so it gets closer to the excel sheet

                // Adjust for partial percentages
                $totalMonths += ($diffInMonths * $workExperience['percentage'] * $factor) / 100;
            } catch (\Exception $e) {
                // Handle invalid date formats gracefully

                continue;
            }
        }

        return $totalMonths;
    }

    /**
     * Calculates the difference between two dates in months, mimicking Excel's DATEDIF behavior for "M".
     * This method is not perfectly accurate for all calendar month differences but aims to replicate
     * a specific Excel calculation logic.
     * @param string $xDate The start date string.
     * @param string $yDate The end date string.
     * @return float The difference in months.
     */
    public static function calculateDateDifference($xDate, $yDate)
    {
        // Parse the input dates
        $startDate = Carbon::parse($xDate);
        $endDate = Carbon::parse($yDate);

        // Calculate the difference in months
        $yearDiff = $endDate->year - $startDate->year;
        $monthDiff = $endDate->month - $startDate->month;
        $dayDiff = $endDate->day - $startDate->day;

        // Determine the number of days in the endDate's month
        $daysInMonth = in_array($endDate->month, [1, 3, 5, 7, 8, 10, 12]) ? 30 : 29;

        // Calculate the total difference in months with fractional part
        $totalMonths = ($yearDiff * 12) + $monthDiff + ($dayDiff / $daysInMonth);

        // Return the result
        return $totalMonths;
    }

    /**
     * Calculates the difference in years between two Carbon dates, including decimals.
     * This takes into account leap years by dividing by 365.25.
     */
    public static function getYearsDifferenceWithDecimals(Carbon $startDate, Carbon $endDate): float
    {
        // Get the total number of days between the two dates
        $totalDays = $startDate->diffInDays($endDate);

        // Convert days to years (with decimals)
        return $totalDays / 365.25; // Accounting for leap years
    }

    /**
     * Subtracts a given number of months (with decimals) from a Carbon date.
     * This function first subtracts the integer part of the months from the date,
     * and then subtracts the fractional part of the months in days from the
     * resulting date. The fractional part is rounded up.
     */
    public static function subMonthsWithDecimals(Carbon $date, float $totalMonths): Carbon
    {
        // Separate the integer and fractional parts of the total months
        $integerMonths = (int) $totalMonths;
        $fractionalMonths = $totalMonths - $integerMonths;

        // Subtract the integer months from the date
        $newDate = $date->copy()->subMonths($integerMonths);

        // Handle the fractional part
        if ($fractionalMonths > 0) {
            // Calculate days based on the average length of a month (30.44 days)
            $fractionalDays = $fractionalMonths * 365.25 / 12;
            $newDate = $newDate->subDays((int) round($fractionalDays));
        }

        return $newDate;
    }

    /**
     * Calculates the ladder position based on work start date and total calculated work experience months.
     *
     * @param  \Carbon\Carbon  $workStartDate The official start date of the current work.
     * @param  float  $calculatedTotalWorkExperienceMonths The total work experience in months, potentially including decimals.
     * @return int The calculated ladder position as an integer (years of experience until now).
     */
    public static function ladderPosition(Carbon $workStartDate, $calculatedTotalWorkExperienceMonths): int
    {
        $workExperienceStartDate = SalaryEstimationService::subMonthsWithDecimals($workStartDate, $calculatedTotalWorkExperienceMonths);
        // $yearTilWorkStart = $workStartDate->diffInYears($workExperienceStartDate);
        $yearTilNowDifference = $workExperienceStartDate->diffInYears(Carbon::now());

        return intval($yearTilNowDifference);
    }

    /**
     * Creates timeline and table data from education and work experience records.
     * This function takes education and work experience data, and creates a timeline
     * and table data that can be used to display the data in a table.
     */
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
                'percentage' => floatval($education['percentage']),
                'type' => 'education',
                'comments' => @$education['comments'],
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
                'percentage' => floatval($workExperience['percentage']),
                'type' => 'work',
                'comments' => @$workExperience['comments'],
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

    /**
     * Updates a collection of education or work experience items by adding missing 'id' and 'percentage' fields,
     * and setting 'relevance' to true for 'freechurch' workplace types.
     */
    public function updateMissingDatasetItems($items)
    {
        return collect($items)->map(function ($item) {
            if (! isset($item['id'])) {
                $item['id'] = Str::uuid()->toString(); // Generate a unique ID
            }
            if (! isset($item['percentage'])) {
                $item['percentage'] = floatval($item['study_percentage']) ?? floatval($item['work_percentage']) ?? 0;
            }
            if (@$item['workplace_type'] == 'freechurch') {
                $item['relevance'] = true;
            }

            return $item;
        });
    }

    /**
     * Checks if two date ranges overlap.
     */
    private function datesOverlap($start1, $end1, $start2, $end2)
    {
        return $start1->lte($end2) && $end1->gte($start2);
    }

    /**
     * Removes duplicate work experiences from an array based on a composite key of title, start date, and end date.
     * Duplicates are determined by matching the title, start date, and end date.
     * The first occurrence of a duplicate is kept, and subsequent duplicates are removed.
     */
    private function removeDuplicates($workExperience)
    {
        return collect($workExperience)->unique(function ($work) {
            return $work['title_workplace'] . $work['start_date'] . $work['end_date'];
        })->values()->all();
    }
}
