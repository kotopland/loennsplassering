<?php

namespace App\Services;

use App\Models\EmployeeCV;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SalaryEstimationService
{
    private Carbon $dateAge18;

    /**
     * If $application has an id, return it. Otherwise, try to find an EmployeeCV model by applicationId in the session or request.
     * If such a model is found, return it. Otherwise, create a new EmployeeCV model, store its id in the session, and return it.
     *
     * @param  EmployeeCV  $application
     * @return EmployeeCV
     */
    /******  300ede99-5281-42f5-8c24-01aa9fb21cad  *******/
    public function getOrCreateApplication($application)
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

    /**
     * Adjust education and work experience start dates, calculate competence points, and adjust education and work experience for overlaps.
     *
     * @param  EmployeeCV  $application
     * @return EmployeeCV
     */
    /******  2463dbfe-9a47-4941-af3f-029e533ca94a  *******/
    public function adjustEducationAndWork($application)
    {
        $this->dateAge18 = Carbon::parse($application->birth_date)->addYears(18);
        $employeeGroup = (new EmployeeCV)->getPositionsLaddersGroups()[$application->job_title];

        $adjustedEducation = $application->education ?? [];
        $adjustedWorkExperience = $application->work_experience ?? [];
        $competencePoints = 0;

        // Limit education to 18 or more years of age
        $adjustedEducation = $this->adjustEducationStartDate($adjustedEducation);

        // calculate competence points
        [$adjustedEducation, $competencePoints] = $this->calculateCompetencePointsForEducation($application, $adjustedEducation);

        // Move capped education to work experience
        $adjustedEducation = $this->moveCappedEducationToWorkExperience($application, $employeeGroup, $competencePoints, $adjustedEducation, $adjustedWorkExperience);

        // Cap competence points for each group
        $application->competence_points = $this->capCompetencePoints($employeeGroup, $competencePoints);

        // Limit work experience to 18 or more years of age
        $adjustedWorkExperience = $this->adjustWorkExperienceStartDate($application);

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
     * Adjust work experience start date to be after 18 years of age.
     *
     * Goes through each work experience entry and does the following:
     * - If the end date is before the applicant turned 18, remove the entry.
     * - If the start date is before the applicant turned 18 and the end date is after,
     *   update the start date to be the day after the applicant turned 18, and add a comment.
     * - If the start date is before the applicant turned 18, remove the entry.
     */
    /******  99be0cd1-0216-460a-b21c-2f9240f775a4  *******/
    private function adjustWorkExperienceStartDate(EmployeeCV $application): array
    {
        $adjustedWorkExperience = $application->work_experience ?? [];

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
                $adjustedWorkExperience[$id]['comments'] = @$adjustedWorkExperience[$id]['comments'].'Endret start til etter 18 års alder. ';

                continue;
            }

            if ($this->dateAge18->diffInYears(Carbon::parse($work['start_date'])) < 0) {
                unset($adjustedWorkExperience[$id]);

                continue;
            }

            if ($workStartDate->lessThan($application->work_start_date) && $workEndDate->greaterThanOrEqualTo($application->work_start_date)) {
                $this->removeDuplicates($adjustedWorkExperience);
                $adjustedWorkExperience[$id]['end_date'] = Carbon::parse($application->work_start_date)->subDay()->toDateString();
                $adjustedWorkExperience[$id]['comments'] = @$adjustedWorkExperience[$id]['comments'].'Endret sluttdato til dagen før tiltredelsesdato. ';

                continue;
            }
        }

        return $adjustedWorkExperience;
    }

    /**
     * Adjusts education start dates to be after the applicant turned 18.
     * Does the following:
     * - If the education ended before the applicant turned 18, remove the entry.
     * - If the education started before the applicant turned 18 and the end date is after,
     *   update the start date to be the day after the applicant turned 18, and add a comment.
     * - If the education started before the applicant turned 18, remove the entry.
     *
     * @param  array  $education  The array of education entries to be adjusted.
     * @return array The adjusted education array.
     */
    /******  4d22b404-9c9d-4b83-b4ab-511071b7c1f9  *******/
    private function adjustEducationStartDate(array $education): array
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
                $edu['comments'] = @$edu['comments'].'Endret start til 18 års alder. ';
            }

            if ($this->dateAge18->diffInYears(Carbon::parse($edu['start_date'])) < 0) {
                continue; // Remove if still under 18 after adjustment
            }
            $educationAdjusted[] = $edu;
        }

        return $educationAdjusted;
    }

    /**
     * Calculates the total competence points for the given education entries.
     *
     * Goes through each education entry, calculates the competence points for it,
     * and adds it to the total. Also adds the competence points to the education
     * array itself.
     */
    /******  edc0bfb1-b7ef-4ded-8e10-22df58ce95d2  *******/
    private function calculateCompetencePointsForEducation(EmployeeCV $application, array $adjustedEducation): array
    {
        $competencePoints = 0;
        foreach ($adjustedEducation as $id => $education) {
            $competencePoint = $this->calculateCompetencePoints($application, $education);
            $competencePoints += $competencePoint;
            $adjustedEducation[$id]['competence_points'] = $competencePoint;
        }

        return [$adjustedEducation, $competencePoints];
    }

    /**
     * Caps the given competence points based on the employee group's ladder.
     *
     * Determines the maximum allowable competence points for an employee based
     * on their group and ladder. The competence points are then capped to this
     * maximum value.
     *
     * @param  array  $employeeGroup  The employee group containing ladder and other details.
     * @param  int  $competencePoints  The calculated competence points to be capped.
     * @return int The capped competence points.
     */
    /******  b214128c-6c33-4d5b-807f-77bfbcaca60a  *******/
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
     * Adjusts education and work experience by moving excess education to work experience if the calculated competence points
     * exceed the maximum allowed for the employee group and ladder.
     *
     * @param  EmployeeCV  $application  The application containing the education and work experience.
     * @param  array  $employeeGroup  The employee group containing ladder and other details.
     * @param  int  $competencePoints  The calculated competence points to be capped.
     * @param  array  $adjustedEducation  The adjusted education array.
     * @param  array  $adjustedWorkExperience  The adjusted work experience array.
     * @return array The adjusted education array.
     */
    /******  400f58d9-5618-409f-92b9-e3f4452d5212  *******/
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

        return $adjustedEducation;
    }

    /**
     * Adjusts the end date of each education entry in the provided array.
     *
     * For each education entry, if the end date is the first day of a month,
     * it changes the end date to the last day of the previous month and appends
     * a comment indicating the change. This is done to ensure correct calculations.
     *
     * @param  array  $adjustedEducation  The array of education entries to be adjusted.
     * @return array The adjusted array with modified end dates and comments.
     */
    /******  b425233a-5516-4668-9fcb-50cbdf905ff6  *******/
    public function adjustEducation($adjustedEducation)
    {
        $educationArray = [];
        foreach ($adjustedEducation as $education) {
            $eduEndDate = Carbon::parse($education['end_date']);
            if ($eduEndDate->day === 1) {
                $education['end_date'] = $eduEndDate->subDay()->toDateString();
                $education['comments'] = @$education['comments'].'Endret sluttdato til siste dag i forrige måned for korrekt utregning. ';

            }
            $educationArray[] = $education;
        }

        return $educationArray;
    }

    /**
     * Check if a given text contains any of the given search strings.
     *
     * Case insensitive search.
     *
     * @param  string  $text  The text to search in
     * @param  array  $searchStrings  The strings to search for
     * @return bool True if any of the search strings are found in the text.
     */
    /******  1543c389-650b-4fc2-af87-ea95c78d5a23  *******/
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
     * Converts an education entry into a work experience format.
     *
     * This function takes an educational entry and transforms it to resemble a work
     * experience entry by mapping fields such as title, percentage, start date,
     * and end date. It assigns a specific workplace type to indicate the conversion
     * and appends relevant comments. The conversion is useful for calculating
     * seniority based on educational background.
     *
     * @param  array  $education  The educational entry to be converted.
     * @return array The transformed work experience entry.
     */
    /******  7443cbbb-8cfe-49bb-87bc-d69514dc0dec  *******/
    private function convertEducationToWork($education)
    {
        return [
            'title_workplace' => $education['topic_and_school'],
            'percentage' => $education['percentage'],
            'start_date' => $education['start_date'],
            'end_date' => $education['end_date'],
            'workplace_type' => 'education_converted',
            'relevance' => @$education['relevance'],
            'comments' => @$education['comments'].'Utdanning gjort om til å gi uttelling i ansiennitet. ',
            'original' => false,
            'id' => $education['id'],
        ];
    }

    /**
     * Calculates the competence points based on the education entry and the employee group's ladder.
     *
     * @param  EmployeeCV  $application  The application containing the education entry.
     * @param  array  $education  The education entry.
     * @return int The calculated competence points.
     */
    /******  3eee7ad1-c5c3-46a4-a425-ea18977f73ff  *******/
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

    /**
     * Moves excess education to work experience to not exceed max competence points.
     *
     * This method takes the original education array and splits it into two parts:
     * 1. The part that fits within the max competence points ($maxCompetencePoints).
     * 2. The part that exceeds the max competence points and is converted to work experience.
     *
     * The method sorts the education array in descending order based on the following criteria:
     * 1. Competence points.
     * 2. Relevance.
     * 3. Study points (converted to a numerical value, with 'bestått' being treated as 999).
     *
     * @param  int  $maxCompetencePoints  The maximum allowed competence points.
     * @param  array  $education  The original education array.
     * @param  array  $workExperience  The array of work experience.
     * @param  int  $competencePoints  The current competence points.
     * @return array The new education array, with excess education moved to work experience.
     */
    /******  529b8cf6-8e2f-453d-8855-05ea070d301a  *******/
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
     * Adjusts work experiences based on overlapping education periods and special conditions.
     *
     * This method iterates through the given work experiences and adjusts the periods
     * by checking overlaps with education periods. If certain conditions are met, such as
     * relevance and type of education, the overlap is allowed. Otherwise, the work period
     * is segmented to avoid overlaps. Additionally, for "freechurch" type workplaces after
     * May 1, 2014, the percentage is adjusted to 100%.
     *
     * @param  array  $workExperience  The array of work experience entries to be adjusted.
     * @param  array  $education  The array of education periods to check for overlaps.
     * @return array The adjusted work experiences with ensured non-overlapping periods.
     */
    /******  6a613fc8-f9a8-4c6b-93f2-63b7b776e4a0  *******/
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
                // Check if the work overlaps with education and meets the special conditions.
                $overlapAllowed =
                    $eduEnd->greaterThanOrEqualTo(Carbon::parse('2015-01-01')) && in_array($edu['highereducation'], ['bachelor', 'master'], true)// || $eduStart->greaterThanOrEqualTo(Carbon::parse('2015-01-01')
                    && $edu['relevance'] && array_key_exists('workplace_type', $work) && in_array($work['workplace_type'], ['freechurch', 'other_christian']);

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
     * Enforces the limit of 100% work percentage per month across all work experiences.
     * Splits work experiences into month-by-month segments and adjusts the percentage
     * for each segment to not exceed 100%. Additionally, it merges consecutive segments
     * with the same title and percentage.
     *
     * @param  array  $workExperience  The array of work experiences to be adjusted.
     * @return array The adjusted work experiences with ensured non-overlapping periods
     *               and no more than 100% work percentage per month.
     */
    /******  b82614c4-57ff-45b8-9072-45261409db46  *******/
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

            // Adjust percentage for "freechurch" type after May 1, 2014.
            $workSpcialConditionDate = Carbon::parse('2014-05-01');

            while ($workStart->lessThanOrEqualTo($workEnd)) {
                $monthKey = $workStart->format('Y-m'); // Use year-month as a key.

                if (Carbon::parse($monthKey.'-01')->greaterThanOrEqualTo($workSpcialConditionDate) && array_key_exists('workplace_type', $work) && $work['workplace_type'] === 'freechurch') {
                    $work['percentage'] = 100;
                    $work['relevance'] = 1;
                    $work['comments'] = @$work['comments'].'100% Ansiennitet i Frikirkestillinger etter 1 mai 2014. ';
                }

                // Calculate the available percentage for this month.
                $availablePercentage = 100 - ($monthlyPercentage[$monthKey] ?? 0);

                if ($availablePercentage <= 0) {
                    // If no available percentage, skip this month.
                    $workStart->addMonth();

                    continue;
                }

                // Calculate the percentage for this month.
                $allocatedPercentage = min($work['percentage'], $availablePercentage);

                // Split work into the required time range.
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
                    'percentage' => $allocatedPercentage,
                    'start_date' => $workStart->toDateString(),
                    'end_date' => $arrayWorkEnd,
                    'relevance' => @$work['relevance'],
                    'comments' => @$work['comments'],
                    'original' => false,
                    'id' => @$work['id'],
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

    /**
     * Merge consecutive work segments that have the same title and percentage.
     *
     * This takes an array of work segments and merges consecutive segments that have the same title and percentage.
     * The start and end dates of the merged segments are updated accordingly.
     *
     * @param  array  $workSegments  An array of work segments with the following properties:
     *                               - title_workplace: The title of the workplace.
     *                               - percentage: The percentage of the work segment.
     *                               - start_date: The start date of the work segment.
     *                               - end_date: The end date of the work segment.
     *                               - relevance: The relevance of the work segment.
     *                               - comments: The comments for the work segment.
     *                               - original: Whether the segment is an original input or a split segment.
     *                               - id: The ID of the original segment if it is a split segment.
     * @return array The merged work segments.
     */
    /******  33ec9169-93ad-4e85-83fb-fbe0b7554d56  *******/
    private function mergeConsecutiveSegments($workSegments)
    {
        $mergedWork = [];
        $previous = null;

        foreach ($workSegments ?? [] as $current) {
            if (
                $previous &&
                $previous['title_workplace'] === $current['title_workplace'] &&
                $previous['percentage'] === $current['percentage'] &&
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
     * Calculates the percentage of the study points completed based on the given start and end dates.
     *
     * The calculation is as follows:
     * - If the study points are 60 or more, the percentage is calculated based on the expected years and months.
     * - If the study points are less than 60, the percentage is calculated based on the actual time taken, with a grace period of 3 months.
     * - The percentage is rounded to the nearest multiple of 10.
     *
     * @param  string  $startDate  The start date of the study period.
     * @param  string  $endDate  The end date of the study period.
     * @param  int  $studyPoints  The number of study points completed.
     * @return float The percentage of the study points completed, rounded to the nearest multiple of 10.
     *
     * @throws \InvalidArgumentException If the start date is not before the end date.
     */
    /******  4bfe5843-824d-431c-91de-5f6d4b11d386  *******/
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
     * Calculate the total work experience in months from an array of work experience data.
     * Each work experience segment is expected to have a start date, end date, and percentage.
     * The total months is calculated by summing the difference in months for each segment,
     * multiplied by the work percentage for that segment.
     *
     * @param  array  $workExperienceData  An array of work experience segments, each with the following properties:
     *                                     - start_date: The start date of the segment.
     *                                     - end_date: The end date of the segment.
     *                                     - percentage: The work percentage for the segment.
     * @return float The total work experience in months.
     */
    /******  7ce7ef75-bebc-4f26-b834-c801cb9a070b  *******/
    public static function calculateTotalWorkExperienceMonths($workExperienceData)
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
                $factor = ($workExperience['relevance'] !== true) ? 0.5 : 1;
                // Calculate the difference in months
                $diffInMonths = $startDate->diffInMonths($endDate);
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
     * Get the difference in years between two dates, with decimals.
     *
     * This takes into account leap years by dividing by 365.25.
     *
     * @param  Carbon  $startDate  The start date.
     * @param  Carbon  $endDate  The end date.
     * @return float The difference in years.
     */
    /******  4ad0faaa-2219-4a20-842b-fce609b8326e  *******/
    public static function getYearsDifferenceWithDecimals(Carbon $startDate, Carbon $endDate): float
    {
        // Get the total number of days between the two dates
        $totalDays = $startDate->diffInDays($endDate);

        // Convert days to years (with decimals)
        return $totalDays / 365.25; // Accounting for leap years
    }

    /**
     * Adds a given number of months (with decimals) to a date.
     *
     * This function first subtracts the integer part of the months from the date,
     * and then subtracts the fractional part of the months in days from the
     * resulting date. The fractional part is rounded up.
     *
     * @param  Carbon  $date  The date to modify.
     * @param  float  $totalMonths  The total number of months to add, with decimals.
     * @return Carbon The modified date.
     */
    /******  5af4060f-387c-4c75-961b-5f9e863bb306  *******/
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

    /**
     * Create a timeline and table data for education and work experience.
     *
     * This function takes education and work experience data, and creates a timeline
     * and table data that can be used to display the data in a table.
     *
     * @param  array  $educationData  The education data.
     * @param  array  $workExperienceData  The work experience data.
     * @return array An array containing the timeline and table data.
     */
    /******  995d6c3a-855d-44f6-b8da-40dd89582e9c  *******/
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
                'percentage' => $education['percentage'],
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
                'percentage' => $workExperience['percentage'],
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
     * Updates an array of items with missing fields.
     *
     * @param  array  $items  The array of items to update.
     * @return array The updated array of items.
     */
    /******  37f847a4-e2fd-444d-b824-1cbbe181467e  *******/
    public function updateMissingDatasetItems($items)
    {
        return collect($items)->map(function ($item) {
            if (! isset($item['id'])) {
                $item['id'] = Str::uuid()->toString(); // Generate a unique ID
            }
            if (! isset($item['percentage'])) {
                $item['percentage'] = $item['study_percentage'] ?? $item['work_percentage'] ?? 0;
            }
            if (@$item['workplace_type'] == 'freechurch') {
                $item['relevance'] = true;
            }

            return $item;
        });
    }

    /**
     * Check if two date ranges overlap.
     *
     * @param  \Carbon\Carbon  $start1  Start of the first date range.
     * @param  \Carbon\Carbon  $end1  End of the first date range.
     * @param  \Carbon\Carbon  $start2  Start of the second date range.
     * @param  \Carbon\Carbon  $end2  End of the second date range.
     * @return bool Whether the two date ranges overlap.
     */
    /******  7af9193d-5acb-4eb4-9528-d6558e94ad21  *******/
    private function datesOverlap($start1, $end1, $start2, $end2)
    {
        return $start1->lte($end2) && $end1->gte($start2);
    }

    /**
     * Remove duplicates from an array of work experiences.
     *
     * Duplicates are determined by matching the title, start date, and end date.
     * The first occurrence of a duplicate is kept, and subsequent duplicates are removed.
     *
     * @param  array  $workExperience  The array of work experiences to deduplicate.
     * @return array The deduplicated array of work experiences.
     */
    /******  72de418c-8796-4264-9e44-a1d9d55caee6  *******/
    private function removeDuplicates($workExperience)
    {
        return collect($workExperience)->unique(function ($work) {
            return $work['title_workplace'].$work['start_date'].$work['end_date'];
        })->values()->all();
    }
}
