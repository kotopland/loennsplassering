<?php

namespace Database\Factories;

use App\Models\EmployeeCV;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmployeeCVFactory extends Factory
{
    protected $model = EmployeeCV::class;

    public function definition()
    {
        $birthDate = $this->faker->dateTimeBetween(now()->subYears(70), now()->subYears(18));
        $workStartDate = $this->faker->dateTimeBetween(now()->subMonths(6), now()->addMonths(6));

        $educationData = [];
        for ($i = 0; $i < $this->faker->numberBetween(1, 5); $i++) {
            $minEduAgeDate = Carbon::parse($birthDate)->copy()->addYears(16);
            // Education can start some time in the past up to near current date
            $eduStartDate = Carbon::instance($this->faker->dateTimeBetween($minEduAgeDate, now()->subDays(1)));
            // Education duration 1-5 years. End date can be in the future.
            $eduEndDate = Carbon::instance($this->faker->dateTimeBetween($eduStartDate, $eduStartDate->copy()->addYears($this->faker->numberBetween(1, 5))));

            if ($eduStartDate->gt($eduEndDate)) {
                // This case should be rare. If it happens, ensure end_date is after start_date.
                // For education, let's assume a minimum duration like 6 months if Faker messes up.
                $eduEndDate = $eduStartDate->copy()->addMonths($this->faker->numberBetween(6, 12));
            }

            $educationData[] = [
                "topic_and_school" => $this->faker->catchPhrase() . " / " . $this->faker->company() . " " . $this->faker->randomElement(['University', 'College', 'School']),
                "start_date" => $eduStartDate->format('Y-m-d'),
                "end_date" => $eduEndDate->format('Y-m-d'),
                "study_points" => $this->faker->randomElement(['bestått', (string)$this->faker->numberBetween(10, 180)]),
                "percentage" => $this->faker->numberBetween(50, 100),
                "highereducation" => $this->faker->optional(0.7)->randomElement(['bachelor', 'master', 'cand.theol.']),
                "relevance" => $this->faker->boolean(75) ? 1 : 0,
                "id" => (string) Str::uuid(),
            ];
        }

        $workExperienceData = [];
        for ($i = 0; $i < $this->faker->numberBetween(1, 7); $i++) {
            $minWorkAgeDate = Carbon::parse($birthDate)->copy()->addYears(18);
            // Work experience can start some time in the past up to near current date
            $workExpStartDate = Carbon::instance($this->faker->dateTimeBetween($minWorkAgeDate, now()->subDays(1)));
            // Work duration 0-10 years. End date can be in the future.
            $workExpEndDate = Carbon::instance($this->faker->dateTimeBetween($workExpStartDate, $workExpStartDate->copy()->addYears($this->faker->numberBetween(0, 10))));

            if ($workExpStartDate->gt($workExpEndDate)) {
                // This case should be rare. If it happens, ensure end_date is at least same as start_date or slightly after.
                $workExpEndDate = $workExpStartDate->copy()->addMonths($this->faker->numberBetween(0, 3)); // Can be 0 months for same day start/end
            }
            // Ensure end date is not before start date, especially for short durations
            // This also handles the case where the above fix might result in a very short period.
            if ($workExpEndDate->lt($workExpStartDate->copy()->addMonth())) {
                $workExpEndDate = $workExpStartDate->copy()->addMonths($this->faker->numberBetween(1, 12));
            }

            $workExperienceData[] = [
                "id" => (string) Str::uuid(),
                "title_workplace" => $this->faker->jobTitle() . " / " . $this->faker->company(),
                "percentage" => (string) $this->faker->randomElement([0, 10, 20, 50, 80, 100]),
                "start_date" => $workExpStartDate->format('Y-m-d'),
                "end_date" => $workExpEndDate->format('Y-m-d'),
                "workplace_type" => $this->faker->optional(0.8)->randomElement(['normal', 'freechurch', 'other_christian']),
                "relevance" => $this->faker->boolean(60) ? 1 : 0,
            ];
        }

        return [
            'id' => (string) Str::uuid(),
            'job_title' => $this->faker->randomElement(['Menighet: Pastor', 'Menighet: Menighetsarbeider']),
            'work_start_date' => $workStartDate,
            'birth_date' => $birthDate,
            'education' => $educationData,
            'work_experience' => $workExperienceData,
            'email_sent' => false,
            'last_viewed' => now(),
            'personal_info' => $this->faker->boolean(25) ? [
                'name' => $this->faker->name(),
                'mobile' => $this->faker->phoneNumber(),
                'address' => $this->faker->streetAddress(),
                'postal_code' => $this->faker->postcode(),
                'postal_place' => $this->faker->city(),
                'email' => $this->faker->safeEmail(),
                'employer_and_place' => $this->faker->company(),
                'position_size' => $this->faker->randomElement([20, 50, 80, 100]),
                'bank_account' => $this->faker->iban('NO'), // Added bank_account
                'manager_name' => $this->faker->name(),
                'manager_mobile' => $this->faker->phoneNumber(),
                'manager_email' => $this->faker->safeEmail(),
                'congregation_name' => $this->faker->name(),
                'congregation_mobile' => $this->faker->phoneNumber(),
                'congregation_email' => $this->faker->safeEmail(),
            ] : null,
            // Add education_adjusted and work_experience_adjusted if they are actual db columns
            // 'education_adjusted' => [],
            // 'work_experience_adjusted' => [],
        ];
    }
}
