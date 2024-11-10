<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeCV extends Model
{
    use HasFactory,HasUuids;

    protected $protected = [];

    protected $guarded = [];

    protected $table = 'employee_cvs';

    protected $casts = [
        'education' => 'json',
        'work_experience' => 'json',
        'last_viewed' => 'datetime',
    ];

    public const positionsLaddersGroups = [
        'Menighet: Pastor' => [
            'ladder' => 'A',
            'group' => 1,
            'description' => ['Ingen menighet kan ha mer enn en hovedpastor. I menigheter med kun én pastor velges stillingen hovedpastor.'],
        ],
        'Menighet: Hovedpastor' => [
            'ladder' => 'A',
            'group' => 2,
            'description' => ['Ingen menighet kan ha mer enn en hovedpastor. De andre skal ha stilling pastor.'],
        ],
        'Menighet: Menighetsarbeidere med overordnet lederansvar i menigheten' => [
            'ladder' => 'B',
            'group' => 1,
            'description' => [''],
        ],
        'Menighet: Menighetsarbeider' => [
            'ladder' => 'B',
            'group' => 1,
            'description' => [''],
        ],
        'Menighet: Barne- og ungdomsarbeider' => [
            'ladder' => 'B',
            'group' => 1,
            'description' => [''],
        ],
        'Menighet: Kontorleder' => [
            'ladder' => 'B',
            'group' => 1,
            'description' => [''],
        ],
        'Hovedkontoret: Konsulent' => [
            'ladder' => 'B',
            'group' => 1,
            'description' => [''],
        ],
        'Hovedkontoret: Journalist' => [
            'ladder' => 'B',
            'group' => 1,
            'description' => [''],
        ],
        'Hovedkontoret: Kontoransatte med fagansvar' => [
            'ladder' => 'B',
            'group' => 1,
            'description' => [''],
        ],
        'FriBU: Konsulent' => [
            'ladder' => 'B',
            'group' => 1,
            'description' => [''],
        ],
        'FriBU: Nettverksleder' => [
            'ladder' => 'B',
            'group' => 1,
            'description' => [''],
        ],
        'Menighet: Renholder' => [
            'ladder' => 'C',
            'group' => 1,
            'description' => [''],
        ],
        'Menighet: Stillinger uten krav om fagkompetanse' => [
            'ladder' => 'C',
            'group' => 1,
            'description' => [''],
        ],
        'Menighet: Kontoransatt' => [
            'ladder' => ['2', 'C'],
            'group' => 2,
            'description' => [''],
        ],
        'Menighet: Vaktmester' => [
            'ladder' => ['2', 'C'],
            'group' => 2,
            'description' => [''],
        ],
        'Hovedkontoret: Kontoransatt' => [
            'ladder' => ['2', 'C'],
            'group' => 2,
            'description' => [''],
        ],
        'FriBU: Kontoransatt' => [
            'ladder' => ['2', 'C'],
            'group' => 2,
            'description' => [''],
        ],
        'Hovedkontoret: Utsendinger utestasjonert' => [
            'ladder' => 'D',
            'group' => 1,
            'description' => [''],
        ],
        'Hovedkontoret: Utsendinger på hjemme-opphold' => [
            'ladder' => 'D',
            'group' => 1,
            'description' => [''],
        ],
        'Hovedkontoret: Mellomleder' => [
            'ladder' => 'E',
            'group' => 1,
            'description' => [''],
        ],
        'Lederstilling Fellesarbeidet: Tilsynsmann' => [
            'ladder' => 'E',
            'group' => 2,
            'description' => [''],
        ],
        'Hovedkontoret: Prosjektleder' => [
            'ladder' => 'F',
            'group' => 1,
            'description' => [''],
        ],
        'Hovedkontoret: Seniorkonsulent' => [
            'ladder' => 'F',
            'group' => 1,
            'description' => [''],
        ],
        'FriBU: Prosjektleder' => [
            'ladder' => 'F',
            'group' => 1,
            'description' => [''],
        ],
        'FriBU: Seniorkonsulent' => [
            'ladder' => 'F',
            'group' => 1,
            'description' => [''],
        ],
        'Menighet: Nybrottsarbeider/menighetsplanter' => [
            'ladder' => 'F',
            'group' => 1,
            'description' => [''],
        ],
        'Menighet: Administrasjonsleder/daglig Leder' => [
            'ladder' => 'F',
            'group' => 1,
            'description' => [''],
        ],
        'Hovedkontoret: Menighetsveileder' => [
            'ladder' => 'F',
            'group' => 2,
            'description' => [''],
        ],
        'Hovedkontoret: Rådgiver' => [
            'ladder' => 'F',
            'group' => 2,
            'description' => [''],
        ],
        'Hovedkontoret: Økonomiansvarlig' => [
            'ladder' => 'F',
            'group' => 2,
            'description' => [''],
        ],
        'Hovedkontoret: Personalansvarlig' => [
            'ladder' => 'F',
            'group' => 2,
            'description' => [''],
        ],
        'FriBU: Rådgiver' => [
            'ladder' => 'F',
            'group' => 2,
            'description' => [''],
        ],
        'FriBU: Administrasjonsleder' => [
            'ladder' => 'F',
            'group' => 2,
            'description' => [''],
        ],
    ];

    public const salaryLadders = [
        'A' => [
            '1' => [45, 45, 46, 46, 47, 47, 48, 48, 49, 49, 50, 50, 51, 51, 52, 52, 53, 53, 53, 53, 54, 54, 54, 54, 55],
            '2' => [51, 51, 52, 52, 53, 53, 54, 54, 55, 55, 56, 56, 57, 57, 58, 58, 59, 59, 59, 59, 60, 60, 60, 60, 61],
        ],
        'B' => [
            '1' => [39, 39, 40, 40, 41, 41, 42, 42, 43, 43, 44, 44, 45, 45, 46, 46, 47, 47, 47, 47, 48, 48, 48, 48, 49],
        ],
        'C' => [
            '1' => [31, 31, 32, 32, 33, 33, 34, 34, 35, 35, 36, 36, 37, 37, 38, 38, 39, 39, 39, 39, 40, 40, 40, 40, 41],
            '2' => [37, 37, 38, 38, 39, 39, 40, 40, 41, 41, 42, 42, 43, 43, 44, 44, 45, 45, 45, 45, 46, 46, 46, 46, 47],
        ],
        'D' => [
            '1' => [42, 42, 43, 43, 44, 44, 45, 45, 46, 46, 47, 47, 48, 48, 49, 49, 50, 50, 50, 50, 51, 51, 51, 51, 52],
        ],
        'E' => [
            '1' => [53, 53, 54, 54, 55, 55, 56, 56, 57, 57, 58, 58, 59, 59, 60, 60, 61, 61, 61, 61, 62, 62, 62, 62, 63],
            '2' => [55, 55, 56, 56, 57, 57, 58, 58, 59, 59, 60, 60, 61, 61, 62, 62, 63, 63, 63, 63, 64, 64, 64, 64, 65],
        ],
        'F' => [
            '1' => [45, 45, 46, 46, 47, 47, 48, 48, 49, 49, 50, 50, 51, 51, 52, 52, 53, 53, 53, 53, 54, 54, 54, 54, 55],
            '2' => [51, 51, 52, 52, 53, 53, 54, 54, 55, 55, 56, 56, 57, 57, 58, 58, 59, 59, 59, 59, 60, 60, 60, 60, 61],
        ],
    ];

    public function positionsLaddersGroupsReversedList()
    {
        $reversedArray = [];

        foreach (self::positionsLaddersGroups as $group => $ladders) {
            foreach ($ladders as $ladder => $positions) {
                foreach ((array) $positions as $position) {
                    foreach ((array) $position as $title) {
                        $reversedArray[$title][] = [$ladder, $group];
                    }
                }
            }
        }

        return $reversedArray;
    }

    public static function getSalary($level1, $level2, $position)
    {
        // Check if the specified levels exist
        if (! isset(self::salaryLadders[$level1][$level2])) {
            return null; // or handle the error as needed
        }

        $ladder = self::salaryLadders[$level1][$level2];
        // Clamp the position to the range of the ladder array
        $position = max(0, min($position, count($ladder) - 1));

        return $ladder[$position];
    }
}
