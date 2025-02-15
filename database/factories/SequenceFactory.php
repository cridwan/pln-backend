<?php

namespace Database\Factories;

use App\Models\InspectionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sequence>
 */
class SequenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'link' => $this->faker->url(),
            'inspection_type_uuid' => (InspectionType::inRandomOrder()->first())->uuid
        ];
    }
}
