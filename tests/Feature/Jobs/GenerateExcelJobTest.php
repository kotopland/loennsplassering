<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Jobs\GenerateExcelJob;
use App\Jobs\NotifyAdminOfSubmissionJob;
use App\Jobs\ProcessUserSubmissionJob;
use App\Models\EmployeeCV;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Seed necessary data for salary calculation and position details
    $this->seed('PositionsSeeder');
    $this->seed('SalaryLaddersSeeder');

    // Fake storage and job bus
    // Storage::fake('public');




    Bus::fake();
});

test('generate excel job creates file, updates application, and dispatches notification job', function () {
    // 1. Arrange
    // Create an EmployeeCV instance using its factory
    $application = EmployeeCV::factory()->create([
        'status' => 'submitted',
        'personal_info' => [
            'name' => 'Pest Test User',
            'email' => 'pest@example.com',
            'mobile' => '12345678',
            'address' => '123 Pest Street',
            'postal_code' => '5432',
            'postal_place' => 'Pestville',
            'bank_account' => '12345678901',
            'position_size' => '100',
            'employer_and_place' => 'Pest Employer',
            'manager_name' => 'Pest Manager',
            'manager_mobile' => '87654321',
            'manager_email' => 'manager.pest@example.com',
            'congregation_name' => 'Pest Congregation',
            'congregation_mobile' => '11223344',
            'congregation_email' => 'congregation.pest@example.com',
        ],
    ]);

    // 2. Act
    // Dispatch and handle the job
    $job = new GenerateExcelJob($application->id, true);
    app()->call([$job, 'handle']);

    // 3. Assert
    // Refresh model from DB to get the latest state
    $application->refresh();

    // Assert that the generated file path is set and the file exists
    expect($application->generated_file_path)->not->toBeNull();
    Storage::disk('public')->assertExists($application->generated_file_path);
    // Assert that the application status was updated to 'generated'
    expect($application->status)->toBe('generated');

    // Assert that the ProcessUserSubmissionJob was dispatched with correct parameters
    Bus::assertDispatched(ProcessUserSubmissionJob::class, function ($job) use ($application) {
        return $job->applicationId === $application->id;
    });

    // Assert that the NotifyAdminOfSubmissionJob was also dispatched
    Bus::assertDispatched(NotifyAdminOfSubmissionJob::class);
});
