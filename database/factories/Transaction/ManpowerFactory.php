<?php

namespace Database\Factories\Transaction;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction\Manpower>
 */
class ManpowerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'qty' => rand(100, 10000),
            'type' => 'Orang',
        ];
    }
}
