<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Location::create([
            "name" => 'PRIOK',
            "slug" => "priok",
            "lat" => -6.139100,   // Koordinat Latitude
            "lon" => 106.866802,  // Koordinat Longitude,
            "color" => "#D84040"
        ]);
    }
}
