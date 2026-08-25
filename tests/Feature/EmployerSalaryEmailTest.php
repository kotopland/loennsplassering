<?php

namespace Tests\Feature;

use App\Mail\SimpleEmail;
use App\Models\EmployeeCV;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

uses(RefreshDatabase::class);

test('admin can view employer email modal with calculated fields on index', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $employeeCV = EmployeeCV::factory()->create([
        'job_title' => 'Menighet: Pastor',
        'work_start_date' => '2024-08-01',
        'birth_date' => '1985-05-15',
        'education' => [
            [
                'id' => 'edu-1',
                'topic_and_school' => 'Cand. Theol. / MF',
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
                'id' => 'work-1',
                'title_workplace' => 'Pastor / Oslo Frikirke',
                'start_date' => '2011-08-01',
                'end_date' => '2024-07-31',
                'percentage' => '100',
                'workplace_type' => 'freechurch',
                'relevance' => 1,
            ],
        ],
        'personal_info' => [
            'name' => 'Ola Nordmann',
            'employer_and_place' => 'Oslo Frikirke',
            'manager_name' => 'Leder Navn',
            'manager_email' => 'leder@oslofrikirke.no',
            'congregation_email' => 'post@oslofrikirke.no',
            'postal_code' => '0153',
        ],
        'status' => 'generated',
        'processing_status' => 'behandles',
    ]);

    $response = $this->actingAs($user)->get(route('admin.employee-cv.index'));

    $response->assertStatus(200);
    $response->assertSee('Stige:');
    $response->assertSee('Kompetansetillegg:');
    $response->assertSee('Ansvarstillegg*: 0');
    $response->assertSee('Lønnsplassering inkl tillegg:');
    $response->assertSee('Ansiennitet fra:');
    $response->assertSee('Neste ansiennitetsopprykk:');
    $response->assertSee('leder@oslofrikirke.no');
    $response->assertSee('Vi minner om at arbeidsgiver er ansvarlig for å:');
});

test('admin can send employer placement email successfully', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $employeeCV = EmployeeCV::factory()->create([
        'job_title' => 'Menighet: Pastor',
        'work_start_date' => '2024-08-01',
        'birth_date' => '1985-05-15',
        'personal_info' => [
            'name' => 'Ola Nordmann',
            'employer_and_place' => 'Oslo Frikirke',
            'manager_email' => 'leder@oslofrikirke.no',
        ],
    ]);

    $response = $this->actingAs($user)->post(route('admin.employee-cv.send-employer-email', $employeeCV->id), [
        'recipient_email' => 'leder@oslofrikirke.no',
        'subject' => 'Lønnsplassering for Ola Nordmann - Menighet: Pastor',
        'email_body' => "Stige: A 1\nKompetansetillegg: 7\nAnsvarstillegg*: 0\nLønnsplassering inkl tillegg: 25\nAnsiennitet fra: 01.08.2011\nNeste ansiennitetsopprykk: 01.08.2025\n\nVi minner om at arbeidsgiver er ansvarlig for å:\n...",
    ]);

    $response->assertRedirect(route('admin.employee-cv.index'));
    $response->assertSessionHas('success');

    Mail::assertQueued(SimpleEmail::class, function ($mail) {
        return $mail->hasTo('leder@oslofrikirke.no') &&
               $mail->subject === 'Lønnsplassering for Ola Nordmann - Menighet: Pastor';
    });
});
