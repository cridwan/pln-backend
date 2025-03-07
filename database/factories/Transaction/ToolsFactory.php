<?php

namespace Database\Factories\Transaction;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction\Tools>
 */
class ToolsFactory extends Factory
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
            'qty' => rand(100, 1000),
            'section' => $this->faker->postcode,
            'global_unit_uuid' => '9e53ff2d-a9e6-43dd-ad91-0584a82d6d56',
        ];
    }
}
