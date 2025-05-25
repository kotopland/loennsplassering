<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

class PositionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Position::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->jobTitle . ' ' . $this->faker->randomLetter, // Ensure unique names for testing
            'ladder' => $this->faker->randomElement(['A', 'B', 'C', 'D', 'E', 'F']),
            'group' => $this->faker->numberBetween(1, 3),
            'description' => $this->faker->sentence,
        ];
    }
}
