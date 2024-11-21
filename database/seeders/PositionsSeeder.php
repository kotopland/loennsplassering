<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionsSeeder extends Seeder
{
    public function run()
    {
        DB::table('positions')->select('id')->delete();
        $positions = [
            'Menighet: Pastor' => [
                'ladder' => 'A',
                'group' => 1,
                'description' => 'Ingen menighet kan ha mer enn en hovedpastor. I menigheter med kun én pastor velges stillingen hovedpastor.',
            ],
            'Menighet: Hovedpastor' => [
                'ladder' => 'A',
                'group' => 2,
                'description' => 'Ingen menighet kan ha mer enn en hovedpastor. De andre skal ha stilling pastor.',
            ],
            'Menighet: Menighetsarbeidere med overordnet lederansvar i menigheten' => [
                'ladder' => 'B',
                'group' => 1,
                'description' => '',
            ],
            'Menighet: Menighetsarbeider' => [
                'ladder' => 'B',
                'group' => 1,
                'description' => '',
            ],
            'Menighet: Barne- og ungdomsarbeider' => [
                'ladder' => 'B',
                'group' => 1,
                'description' => '',
            ],
            'Menighet: Kontorleder' => [
                'ladder' => 'B',
                'group' => 1,
                'description' => '',
            ],
            'Hovedkontoret: Konsulent' => [
                'ladder' => 'B',
                'group' => 1,
                'description' => '',
            ],
            'Hovedkontoret: Journalist' => [
                'ladder' => 'B',
                'group' => 1,
                'description' => '',
            ],
            'Hovedkontoret: Kontoransatte med fagansvar' => [
                'ladder' => 'B',
                'group' => 1,
                'description' => '',
            ],
            'FriBU: Konsulent' => [
                'ladder' => 'B',
                'group' => 1,
                'description' => '',
            ],
            'FriBU: Nettverksleder' => [
                'ladder' => 'B',
                'group' => 1,
                'description' => '',
            ],
            'Menighet: Renholder' => [
                'ladder' => 'C',
                'group' => 1,
                'description' => '',
            ],
            'Menighet: Stillinger uten krav om fagkompetanse' => [
                'ladder' => 'C',
                'group' => 1,
                'description' => '',
            ],
            'Menighet: Kontoransatt' => [
                'ladder' => 'C',
                'group' => 2,
                'description' => '',
            ],
            'Menighet: Vaktmester' => [
                'ladder' => 'C',
                'group' => 2,
                'description' => '',
            ],
            'Hovedkontoret: Kontoransatt' => [
                'ladder' => 'C',
                'group' => 2,
                'description' => '',
            ],
            'FriBU: Kontoransatt' => [
                'ladder' => 'C',
                'group' => 2,
                'description' => '',
            ],
            'Hovedkontoret: Utsendinger utestasjonert' => [
                'ladder' => 'D',
                'group' => 1,
                'description' => '',
            ],
            'Hovedkontoret: Utsendinger på hjemme-opphold' => [
                'ladder' => 'D',
                'group' => 1,
                'description' => '',
            ],
            'Hovedkontoret: Mellomleder' => [
                'ladder' => 'E',
                'group' => 1,
                'description' => '',
            ],
            'Lederstilling Fellesarbeidet: Tilsynsmann' => [
                'ladder' => 'E',
                'group' => 2,
                'description' => '',
            ],
            'Hovedkontoret: Prosjektleder' => [
                'ladder' => 'F',
                'group' => 1,
                'description' => '',
            ],
            'Hovedkontoret: Seniorkonsulent' => [
                'ladder' => 'F',
                'group' => 1,
                'description' => '',
            ],
            'FriBU: Prosjektleder' => [
                'ladder' => 'F',
                'group' => 1,
                'description' => '',
            ],
            'FriBU: Seniorkonsulent' => [
                'ladder' => 'F',
                'group' => 1,
                'description' => '',
            ],
            'Menighet: Nybrottsarbeider/menighetsplanter' => [
                'ladder' => 'F',
                'group' => 1,
                'description' => '',
            ],
            'Menighet: Administrasjonsleder/daglig Leder' => [
                'ladder' => 'F',
                'group' => 1,
                'description' => '',
            ],
            'Hovedkontoret: Menighetsveileder' => [
                'ladder' => 'F',
                'group' => 2,
                'description' => '',
            ],
            'Hovedkontoret: Rådgiver' => [
                'ladder' => 'F',
                'group' => 2,
                'description' => '',
            ],
            'Hovedkontoret: Økonomiansvarlig' => [
                'ladder' => 'F',
                'group' => 2,
                'description' => '',
            ],
            'Hovedkontoret: Personalansvarlig' => [
                'ladder' => 'F',
                'group' => 2,
                'description' => '',
            ],
            'FriBU: Rådgiver' => [
                'ladder' => 'F',
                'group' => 2,
                'description' => '',
            ],
            'FriBU: Administrasjonsleder' => [
                'ladder' => 'F',
                'group' => 2,
                'description' => '',
            ],
        ];

        foreach ($positions as $name => $details) {
            Position::create([
                'name' => $name,
                'ladder' => $details['ladder'],
                'group' => $details['group'],
                'description' => $details['description'] ?? null,
            ]);
        }
    }
}
