<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->locale(),
            'slug' => $this->faker->slug(),
            'lat' => $this->faker->latitude(),
            'lon' => $this->faker->longitude(),
            'color' => $this->faker->hexColor()
        ];
    }
}
