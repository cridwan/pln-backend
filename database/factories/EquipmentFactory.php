<?php

namespace Database\Factories;

use App\Models\ScopeStandart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipment>
 */
class EquipmentFactory extends Factory
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
            'scope_standart_uuid' => (ScopeStandart::inRandomOrder()->first())->uuid,
        ];
    }
}
