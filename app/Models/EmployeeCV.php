<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeCV extends Model
{
    use HasFactory, HasUuids;

    protected $protected = [];

    protected $guarded = [];

    protected $table = 'employee_cvs';

    protected $casts = [
        'personal_info' => 'json',
        'education' => 'json',
        'work_experience' => 'json',
        'last_viewed' => 'datetime',
    ];

    public function getPositionsLaddersGroups()
    {

        return Position::all()->mapWithKeys(function ($position) {
            return [
                $position->name => [
                    'ladder' => $position->ladder,
                    'group' => $position->group,
                    'description' => $position->description,
                ],
            ];
        })->toArray();
    }

    public function getSalaryLadders()
    {
        return SalaryLadder::all()->groupBy('ladder')->map(function ($grouped) {
            return $grouped->mapWithKeys(function ($item) {
                return [$item->group => $item->salaries];
            });
        })->toArray();
    }

    public static function getSalary($level1, $level2, $position)
    {
        // Check if the specified levels exist
        if (! isset(SalaryLadder::where('ladder', $level1)->where('group', $level2)->first()->salaries)) {
            return null; // or handle the error as needed
        }

        $ladder = SalaryLadder::where('ladder', $level1)->where('group', $level2)->first()->salaries;
        // Clamp the position to the range of the ladder array
        $position = max(0, min($position, count($ladder) - 1));

        return $ladder[$position];
    }

    public function isReadOnly()
    {
        if (auth()->check())
            return false;

        return ($this->status === 'generated' || $this->status === 'submitted') ? true : false;
    }

    public function getWorkplaceCategory()
    {
        if (!$this->job_title) {
            return null;
        }

        $parts = explode(':', $this->job_title, 2);
        $category = $parts[0];

        if (in_array($category, ['Menighet', 'FriBU', 'Hovedkontoret'])) {
            return $category;
        }

        if (in_array($category, ['Lederstilling Fellesarbeidet'])) {
            return 'Hovedkontoret';
        }

        return null;
    }

    public function isStep1Completed(): bool
    {
        return !empty($this->job_title) && !empty($this->birth_date) && !empty($this->work_start_date);
    }

    public function hasEducationErrors(): bool
    {
        if (empty($this->education) || !is_array($this->education)) {
            return false;
        }

        foreach ($this->education as $item) {
            if (in_array(null, [
                @$item['topic_and_school'],
                @$item['start_date'],
                @$item['end_date'],
                @$item['study_points'],
                @$item['percentage'],
                @$item['relevance'],
            ], true)) {
                return true;
            }
        }

        return false;
    }

    public function hasExperienceErrors(): bool
    {
        if (empty($this->work_experience) || !is_array($this->work_experience)) {
            return false;
        }

        foreach ($this->work_experience as $item) {
            if (in_array(null, [
                @$item['title_workplace'],
                ($item['percentage'] ?? '') === '' ? null : $item['percentage'],
                ($item['start_date'] ?? '') === '' ? null : $item['start_date'],
                ($item['end_date'] ?? '') === '' ? null : $item['end_date'],
                @$item['relevance'],
            ], true)) {
                return true;
            }
        }

        return false;
    }

    public function areSteps1To4Completed(): bool
    {
        return $this->isStep1Completed() && !$this->hasEducationErrors() && !$this->hasExperienceErrors();
    }
}
