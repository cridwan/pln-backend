<?php

namespace Database\Factories\Transaction;

use App\Models\GlobalUnit;
use App\Models\Transaction\ConsMat;
use App\Models\Transaction\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ConsMatFactory extends Factory
{
    protected $model = ConsMat::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'merk' => '',
            'qty' => rand(1000, 10000),
            'global_unit_uuid' => '9e53ff2d-a9e6-43dd-ad91-0584a82d6d56',
            'project_uuid' => ''
        ];
    }
}
