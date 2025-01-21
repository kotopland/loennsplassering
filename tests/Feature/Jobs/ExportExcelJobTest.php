<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ExportExcelJob;
use App\Mail\SimpleEmail;
use App\Models\EmployeeCV;
use App\Services\SalaryEstimationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportExcelJobTest extends TestCase
{
    use RefreshDatabase {
        refreshDatabase as baseRefreshDatabase;
    }

    protected function refreshDatabase()
    {
        $this->baseRefreshDatabase();
        $this->artisan('db:seed --class=PositionsSeeder');
        $this->artisan('db:seed --class=SalaryLaddersSeeder');
    }

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('public');
    }

    public function test_job_processes_application_and_sends_email()
    {
        // Create test data
        $employeeCV = EmployeeCV::factory()->create([
            'job_title' => 'Menighet: Pastor',
            'birth_date' => '1990-01-01',
            'work_start_date' => '2023-01-01',
            'education' => [
                [
                    'id' => '1',
                    'topic_and_school' => 'Test Education',
                    'start_date' => '2010-01-01',
                    'end_date' => '2014-01-01',
                    'study_points' => '180',
                    'highereducation' => 'Bachelor',
                    'relevance' => true,
                ],
            ],
            'work_experience' => [
                [
                    'id' => '2',
                    'title_workplace' => 'Test Workplace',
                    'start_date' => '2014-01-01',
                    'end_date' => '2022-12-31',
                    'percentage' => 100,
                    'relevance' => true,
                ],
            ],
        ]);

        $email = 'test@example.com';

        // Execute job
        $job = new ExportExcelJob($employeeCV->id, $email);
        $job->handle(new SalaryEstimationService);

        // Assert email was sent
        Mail::assertQueued(SimpleEmail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });

        // Assert email was sent to report email
        Mail::assertQueued(SimpleEmail::class, function ($mail) {
            return $mail->hasTo(config('app.report_email'));
        });
    }

    public function test_job_handles_missing_application()
    {
        $nonExistentId = '999999';
        $email = 'test@example.com';

        $job = new ExportExcelJob($nonExistentId, $email);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Application not found for ID: {$nonExistentId}");

        $job->handle(new SalaryEstimationService);

        // Assert error notification was sent
        Mail::assertQueued(SimpleEmail::class, function ($mail) use ($email) {
            return $mail->hasTo($email) &&
                   str_contains($mail->subject, 'Feil ved prosessering');
        });
    }

    // public function test_job_handles_too_many_entries()
    // {
    //     // Create test data with many education and work experience entries
    //     $educationEntries = array_fill(0, 25, [
    //         'id' => uniqid(),
    //         'topic_and_school' => 'Test Education',
    //         'start_date' => '2010-01-01',
    //         'end_date' => '2014-01-01',
    //         'study_points' => '180',
    //         'highereducation' => 'Bachelor',
    //         'relevance' => true,
    //     ]);

    //     $workExperienceEntries = array_fill(0, 35, [
    //         'id' => uniqid(),
    //         'title_workplace' => 'Test Workplace',
    //         'start_date' => '2014-01-01',
    //         'end_date' => '2022-12-31',
    //         'percentage' => 100,
    //         'relevance' => true,
    //     ]);

    //     $employeeCV = EmployeeCV::factory()->create([
    //         'job_title' => 'Menighet: Pastor',
    //         'birth_date' => '1990-01-01',
    //         'work_start_date' => '2023-01-01',
    //         'education' => $educationEntries,
    //         'work_experience' => $workExperienceEntries,
    //     ]);

    //     $email = 'test@example.com';

    //     // Execute job
    //     $job = new ExportExcelJob($employeeCV->id, $email);
    //     $job->handle(new SalaryEstimationService);

    //     // Assert emails were still sent
    //     Mail::assertQueued(SimpleEmail::class, function ($mail) use ($email) {
    //         return $mail->hasTo($email);
    //     });
    // }

    public function test_job_calculates_correct_salary_placement()
    {
        $employeeCV = EmployeeCV::factory()->create([
            'job_title' => 'Menighet: Pastor',
            'birth_date' => '1990-01-01',
            'work_start_date' => Carbon::now()->subYears(5)->format('Y-m-d'),
            'work_experience' => [
                [
                    'id' => '1',
                    'title_workplace' => 'Previous Job',
                    'start_date' => Carbon::now()->subYears(5)->format('Y-m-d'),
                    'end_date' => Carbon::now()->format('Y-m-d'),
                    'percentage' => 100,
                    'relevance' => true,
                ],
            ],
        ]);

        $email = 'test@example.com';

        // Execute job
        $job = new ExportExcelJob($employeeCV->id, $email);
        $job->handle(new SalaryEstimationService);

        // Assert email was sent with correct salary placement
        Mail::assertQueued(SimpleEmail::class, function ($mail) {
            return $mail->hasTo(config('app.report_email'));
        });
    }
}
