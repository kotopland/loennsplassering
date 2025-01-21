<?php

namespace Database\Factories;

use App\Models\EmployeeCV;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeCVFactory extends Factory
{
    protected $model = EmployeeCV::class;

    public function definition()
    {
        return [
            'job_title' => $this->faker->randomElement(['Menighet: Pastor', 'Menighet: Menighetsarbeider']),
            'birth_date' => $this->faker->date(),
            'work_start_date' => $this->faker->date(),
            'education' => [],
            'work_experience' => [],
        ];
    }
}
