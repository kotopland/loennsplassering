<?php

namespace Database\Seeders;

use App\Models\EmployeeCV;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create / Update Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@frikirken.no'],
            [
                'name' => 'Admin Bruker',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );

        $this->command->info("Admin user ready: {$admin->email} (Password: password)");

        // 2. Create Realistic Test Employee CVs
        $testApplications = [
            [
                'job_title' => 'Menighet: Pastor',
                'work_start_date' => '2024-08-01',
                'birth_date' => '1985-05-15',
                'processing_status' => EmployeeCV::PROCESSING_STATUS_INNSENDT,
                'status' => 'submitted',
                'personal_info' => [
                    'name' => 'Ola Nordmann',
                    'mobile' => '91234567',
                    'address' => 'Kirkegata 10',
                    'postal_code' => '0153',
                    'postal_place' => 'Oslo',
                    'email' => 'ola.nordmann@example.com',
                    'employer_and_place' => 'Oslo Frikirke',
                    'position_size' => 100,
                    'bank_account' => '1234.56.78901',
                    'manager_name' => 'Leder Presteskap',
                    'manager_mobile' => '98765432',
                    'manager_email' => 'leder@oslofrikirke.no',
                    'congregation_name' => 'Oslo Vestre Frikirke',
                    'congregation_mobile' => '22334455',
                    'congregation_email' => 'post@oslofrikirke.no',
                ],
                'education' => [
                    [
                        'id' => (string) Str::uuid(),
                        'topic_and_school' => 'Cand. Theol. / Menighetsfakultetet',
                        'start_date' => '2005-08-15',
                        'end_date' => '2011-06-15',
                        'study_points' => '360',
                        'percentage' => 100,
                        'highereducation' => 'cand.theol.',
                        'relevance' => 1,
                    ],
                ],
                'work_experience' => [
                    [
                        'id' => (string) Str::uuid(),
                        'title_workplace' => 'Ungdomspastor / Kristiansand Frikirke',
                        'start_date' => '2011-08-01',
                        'end_date' => '2018-07-31',
                        'percentage' => '100',
                        'workplace_type' => 'freechurch',
                        'relevance' => 1,
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'title_workplace' => 'Pastor / Drammen Frikirke',
                        'start_date' => '2018-08-01',
                        'end_date' => '2024-07-31',
                        'percentage' => '100',
                        'workplace_type' => 'freechurch',
                        'relevance' => 1,
                    ],
                ],
                'last_viewed' => now()->subMinutes(15),
            ],
            [
                'job_title' => 'Menighet: Barne- og ungdomsarbeider',
                'work_start_date' => '2024-09-01',
                'birth_date' => '1996-11-20',
                'processing_status' => EmployeeCV::PROCESSING_STATUS_BEHANDLES,
                'status' => 'generated',
                'personal_info' => [
                    'name' => 'Kari Hansen',
                    'mobile' => '92345678',
                    'address' => 'Bryggeveien 4',
                    'postal_code' => '5003',
                    'postal_place' => 'Bergen',
                    'email' => 'kari.hansen@example.com',
                    'employer_and_place' => 'Bergen Frikirke',
                    'position_size' => 80,
                    'bank_account' => '2345.67.89012',
                    'manager_name' => 'Hovedpastor Bergen',
                    'manager_mobile' => '97654321',
                    'manager_email' => 'pastor@bergenfrikirke.no',
                    'congregation_name' => 'Bergen Frikirke',
                    'congregation_mobile' => '55112233',
                    'congregation_email' => 'post@bergenfrikirke.no',
                ],
                'education' => [
                    [
                        'id' => (string) Str::uuid(),
                        'topic_and_school' => 'Bachelor i Pedagogikk / NLA Høgskolen',
                        'start_date' => '2016-08-20',
                        'end_date' => '2019-06-15',
                        'study_points' => '180',
                        'percentage' => 100,
                        'highereducation' => 'bachelor',
                        'relevance' => 1,
                    ],
                ],
                'work_experience' => [
                    [
                        'id' => (string) Str::uuid(),
                        'title_workplace' => 'Miljøarbeider / Bergen Kommune',
                        'start_date' => '2019-08-01',
                        'end_date' => '2022-07-31',
                        'percentage' => '100',
                        'workplace_type' => 'normal',
                        'relevance' => 1,
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'title_workplace' => 'Ungdomsarbeider / FriBU Vest',
                        'start_date' => '2022-08-01',
                        'end_date' => '2024-08-31',
                        'percentage' => '60',
                        'workplace_type' => 'freechurch',
                        'relevance' => 1,
                    ],
                ],
                'last_viewed' => now()->subHours(2),
            ],
            [
                'job_title' => 'FriBU: Konsulent',
                'work_start_date' => '2024-01-01',
                'birth_date' => '1990-03-10',
                'processing_status' => EmployeeCV::PROCESSING_STATUS_GODKJENT,
                'status' => 'generated',
                'personal_info' => [
                    'name' => 'Per Olsen',
                    'mobile' => '93456789',
                    'address' => 'Storgata 15',
                    'postal_code' => '4612',
                    'postal_place' => 'Kristiansand',
                    'email' => 'per.olsen@example.com',
                    'employer_and_place' => 'FriBU Hovedkontor',
                    'position_size' => 100,
                    'bank_account' => '3456.78.90123',
                    'manager_name' => 'Daglig leder FriBU',
                    'manager_mobile' => '96543210',
                    'manager_email' => 'leder@fribu.no',
                    'congregation_name' => 'Kristiansand Frikirke',
                    'congregation_mobile' => '38112233',
                    'congregation_email' => 'post@kristiansandfrikirke.no',
                ],
                'education' => [
                    [
                        'id' => (string) Str::uuid(),
                        'topic_and_school' => 'Master i Ledelse og Menighetsutvikling / VID',
                        'start_date' => '2010-08-15',
                        'end_date' => '2015-06-15',
                        'study_points' => '300',
                        'percentage' => 100,
                        'highereducation' => 'master',
                        'relevance' => 1,
                    ],
                ],
                'work_experience' => [
                    [
                        'id' => (string) Str::uuid(),
                        'title_workplace' => 'Barne- og ungdomskonsulent / FriBU',
                        'start_date' => '2015-08-01',
                        'end_date' => '2023-12-31',
                        'percentage' => '100',
                        'workplace_type' => 'freechurch',
                        'relevance' => 1,
                    ],
                ],
                'last_viewed' => now()->subDays(1),
            ],
            [
                'job_title' => 'Hovedkontoret: Journalist',
                'work_start_date' => '2024-10-01',
                'birth_date' => '1992-07-25',
                'processing_status' => EmployeeCV::PROCESSING_STATUS_INNSENDT,
                'status' => 'submitted',
                'personal_info' => [
                    'name' => 'Astrid Lindgren',
                    'mobile' => '94567890',
                    'address' => 'Pilestredet 22',
                    'postal_code' => '0164',
                    'postal_place' => 'Oslo',
                    'email' => 'astrid.lindgren@example.com',
                    'employer_and_place' => 'Frikirkens Hovedkontor',
                    'position_size' => 100,
                    'bank_account' => '4567.89.01234',
                    'manager_name' => 'Kommunikasjonsleder',
                    'manager_mobile' => '95432109',
                    'manager_email' => 'kommunikasjon@frikirken.no',
                    'congregation_name' => 'Frikirkens Hovedkontor',
                    'congregation_mobile' => '22001100',
                    'congregation_email' => 'post@frikirken.no',
                ],
                'education' => [
                    [
                        'id' => (string) Str::uuid(),
                        'topic_and_school' => 'Bachelor i Journalistikk / OsloMet',
                        'start_date' => '2012-08-15',
                        'end_date' => '2015-06-15',
                        'study_points' => '180',
                        'percentage' => 100,
                        'highereducation' => 'bachelor',
                        'relevance' => 1,
                    ],
                ],
                'work_experience' => [
                    [
                        'id' => (string) Str::uuid(),
                        'title_workplace' => 'Journalist / Vårt Land',
                        'start_date' => '2015-08-01',
                        'end_date' => '2020-07-31',
                        'percentage' => '100',
                        'workplace_type' => 'other_christian',
                        'relevance' => 1,
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'title_workplace' => 'Informasjonskonsulent / Kirkens Nødhjelp',
                        'start_date' => '2020-08-01',
                        'end_date' => '2024-09-30',
                        'percentage' => '100',
                        'workplace_type' => 'other_christian',
                        'relevance' => 1,
                    ],
                ],
                'last_viewed' => now()->subHours(5),
            ],
            [
                'job_title' => 'Menighet: Hovedpastor',
                'work_start_date' => '2023-01-01',
                'birth_date' => '1978-02-14',
                'processing_status' => EmployeeCV::PROCESSING_STATUS_GODKJENT,
                'status' => 'generated',
                'personal_info' => [
                    'name' => 'Sigrid Undset',
                    'mobile' => '95678901',
                    'address' => 'Elvegata 8',
                    'postal_code' => '7013',
                    'postal_place' => 'Trondheim',
                    'email' => 'sigrid.undset@example.com',
                    'employer_and_place' => 'Trondheim Frikirke',
                    'position_size' => 100,
                    'bank_account' => '5678.90.12345',
                    'manager_name' => 'Eldsterådsleder',
                    'manager_mobile' => '94321098',
                    'manager_email' => 'eldsterad@trondheimfrikirke.no',
                    'congregation_name' => 'Trondheim Frikirke',
                    'congregation_mobile' => '73112233',
                    'congregation_email' => 'post@trondheimfrikirke.no',
                ],
                'education' => [
                    [
                        'id' => (string) Str::uuid(),
                        'topic_and_school' => 'Cand. Theol. / Det Teologiske Menighetsfakultet',
                        'start_date' => '1998-08-15',
                        'end_date' => '2004-06-15',
                        'study_points' => '360',
                        'percentage' => 100,
                        'highereducation' => 'cand.theol.',
                        'relevance' => 1,
                    ],
                ],
                'work_experience' => [
                    [
                        'id' => (string) Str::uuid(),
                        'title_workplace' => 'Pastor / Arendal Frikirke',
                        'start_date' => '2004-08-01',
                        'end_date' => '2014-07-31',
                        'percentage' => '100',
                        'workplace_type' => 'freechurch',
                        'relevance' => 1,
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'title_workplace' => 'Hovedpastor / Trondheim Frikirke',
                        'start_date' => '2014-08-01',
                        'end_date' => '2022-12-31',
                        'percentage' => '100',
                        'workplace_type' => 'freechurch',
                        'relevance' => 1,
                    ],
                ],
                'last_viewed' => now()->subDays(3),
            ],
        ];

        foreach ($testApplications as $appData) {
            EmployeeCV::create(array_merge($appData, [
                'id' => (string) Str::uuid(),
                'email_sent' => false,
            ]));
        }

        $this->command->info('Created ' . count($testApplications) . ' test employee CV applications.');
    }
}
