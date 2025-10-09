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
}
