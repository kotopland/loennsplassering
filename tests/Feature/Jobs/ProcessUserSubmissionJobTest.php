<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ProcessUserSubmissionJob;
use App\Mail\SimpleEmail;
use App\Models\EmployeeCV;
use App\Services\ExcelGenerationService;
use App\Services\SalaryEstimationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

class ProcessUserSubmissionJobTest extends TestCase
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
        Log::shouldReceive('channel->info')->andReturnNull();
        Log::shouldReceive('channel->error')->andReturnNull();
        Log::shouldReceive('channel->warning')->andReturnNull();
        Log::shouldReceive('info')->andReturnNull();
        Log::shouldReceive('error')->andReturnNull();
        Log::shouldReceive('warning')->andReturnNull();
    }

    public function test_job_processes_application_and_sends_email()
    {
        // Create test data
        $employeeCV = EmployeeCV::factory()->create([
            'personal_info' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'mobile' => '12345678',
                'address' => 'Test Address',
                'postal_code' => '1234',
                'postal_place' => 'Test Place',
                'bank_account' => '12345678901',
                'position_size' => '100',
                'employer_and_place' => 'Test Employer',
                'manager_name' => 'Test Manager',
                'manager_mobile' => '12345678',
                'manager_email' => 'manager@example.com',
                'congregation_name' => 'Test Congregation',
                'congregation_mobile' => '12345678',
                'congregation_email' => 'congregation@example.com',
                'birth_date' => '1990-01-01',
            ],
        ]);

        // Execute job
        $job = new ProcessUserSubmissionJob($employeeCV->id);
        $job->handle(new ExcelGenerationService(new SalaryEstimationService()));

        // Assert email was sent
        Mail::assertQueued(SimpleEmail::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    public function test_job_handles_missing_application()
    {
        $nonExistentId = '999999';

        $job = new ProcessUserSubmissionJob($nonExistentId);

        try {
            $job->handle(new ExcelGenerationService(new SalaryEstimationService()));
            $this->fail('Exception was not thrown');
        } catch (\Exception $e) {
            $this->assertEquals("Application not found for ID: {$nonExistentId}", $e->getMessage());
        }

        Log::shouldHaveReceived('error');
    }
}
