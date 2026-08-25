<?php

namespace Tests\Feature;

use App\Models\EmployeeCV;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('admin can view employee cv index with processing status and arbeidssted', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $employeeCV = EmployeeCV::factory()->create([
        'job_title' => 'Menighet:Prest',
        'work_start_date' => '2024-01-01',
        'birth_date' => '1990-01-01',
        'education' => [['topic_and_school' => 'Teologi', 'start_date' => '2010-01-01', 'end_date' => '2015-01-01', 'study_points' => 300, 'percentage' => 100, 'relevance' => 'full']],
        'work_experience' => [['title_workplace' => 'Pastor', 'start_date' => '2015-01-01', 'end_date' => '2020-01-01', 'percentage' => 100, 'relevance' => 'full']],
        'personal_info' => [
            'name' => 'Ola Nordmann',
            'employer_and_place' => 'Oslo Menighet',
            'postal_code' => '0123',
        ],
        'status' => 'submitted',
        'processing_status' => 'innsendt',
    ]);

    $response = $this->actingAs($user)->get(route('admin.employee-cv.index'));

    $response->assertStatus(200);
    $response->assertSee('Stilling tittel');
    $response->assertSee('Arbeidssted');
    $response->assertSee('Behandlingsstatus');
    $response->assertSee('Oslo Menighet');
    $response->assertSee('Ola Nordmann');
});

test('admin can update processing status to behandles or godkjent', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $employeeCV = EmployeeCV::factory()->create([
        'job_title' => 'Menighet:Prest',
        'work_start_date' => '2024-01-01',
        'birth_date' => '1990-01-01',
        'education' => [['topic_and_school' => 'Teologi', 'start_date' => '2010-01-01', 'end_date' => '2015-01-01', 'study_points' => 300, 'percentage' => 100, 'relevance' => 'full']],
        'work_experience' => [['title_workplace' => 'Pastor', 'start_date' => '2015-01-01', 'end_date' => '2020-01-01', 'percentage' => 100, 'relevance' => 'full']],
        'processing_status' => 'innsendt',
    ]);

    $response = $this->actingAs($user)->post(route('admin.employee-cv.update-processing-status', $employeeCV->id), [
        'processing_status' => 'behandles',
    ]);

    $response->assertRedirect(route('admin.employee-cv.index'));
    $response->assertSessionHas('success', 'Behandlingsstatus er oppdatert!');

    expect($employeeCV->fresh()->processing_status)->toBe('behandles');
    expect($employeeCV->fresh()->status)->toBe('generated');

    $response2 = $this->actingAs($user)->post(route('admin.employee-cv.update-processing-status', $employeeCV->id), [
        'processing_status' => 'godkjent',
    ]);

    $response2->assertRedirect(route('admin.employee-cv.index'));
    expect($employeeCV->fresh()->processing_status)->toBe('godkjent');
    expect($employeeCV->fresh()->status)->toBe('generated');
});

test('submitting candidate form sets processing status to innsendt', function () {
    $employeeCV = EmployeeCV::factory()->create([
        'job_title' => 'Menighet:Prest',
        'work_start_date' => '2024-01-01',
        'birth_date' => '1990-01-01',
        'education' => [['topic_and_school' => 'Teologi', 'start_date' => '2010-01-01', 'end_date' => '2015-01-01', 'study_points' => 300, 'percentage' => 100, 'relevance' => 'full']],
        'work_experience' => [['title_workplace' => 'Pastor', 'start_date' => '2015-01-01', 'end_date' => '2020-01-01', 'percentage' => 100, 'relevance' => 'full']],
        'status' => null,
    ]);

    \Illuminate\Support\Facades\Queue::fake();

    $response = $this->withSession(['applicationId' => $employeeCV->id])
        ->post(route('submit-for-processing'), [
            'name' => 'Kari Nordmann',
            'mobile' => '12345678',
            'address' => 'Testveien 1',
            'postal_code' => '1234',
            'postal_place' => 'Oslo',
            'email' => 'kari@example.com',
            'employer_and_place' => 'Frikirken Hovedkontor',
            'position_size' => 100,
            'manager_name' => 'Leder Navn',
            'manager_mobile' => '87654321',
            'manager_email' => 'leder@example.com',
            'congregation_name' => 'Oslo Frikirke',
            'congregation_mobile' => '99887766',
            'congregation_email' => 'menighet@example.com',
        ]);

    $response->assertRedirect(route('welcome'));
    expect($employeeCV->fresh()->processing_status)->toBe('innsendt');
    expect($employeeCV->fresh()->status)->toBe('submitted');
});

test('admin can view system documentation page', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('admin.docs.show'));

    $response->assertStatus(200);
    $response->assertSee('Systemdokumentasjon: Frikirkens Lønnsberegner');
    $response->assertSee('Beregningslogikk for Lønnsplassering');
    $response->assertSee('Funksjon: E-post til Arbeidsgiver');
});
