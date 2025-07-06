<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeLocationSeeder extends Seeder
{
    public function run()
    {
        DB::table('office_locations')->insert([
            [
                'name' => 'Kantor Capek banget',
                'address' => 'Jl. Raya Kantor No. 123, Malang, Jawa Timur',
                'latitude' => -6.434361833673113,
                'longitude' => 106.92662450674572,
                'radius_meters' => 800,
                'description' => 'Area radius 800 meter dari kantor pusat',
                'type' => 'main',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kantor Cabang Malang Kota',
                'address' => 'Jl. Ijen No. 456, Malang Kota',
                'latitude' => -7.9666,
                'longitude' => 112.6326,
                'radius_meters' => 500,
                'description' => 'Area radius 500 meter dari kantor cabang Malang Kota',
                'type' => 'branch',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kantor Cabang Banyuwangi',
                'address' => 'Jl. Ahmad Yani No. 789, Banyuwangi',
                'latitude' => -8.2191,
                'longitude' => 114.3691,
                'radius_meters' => 600,
                'description' => 'Area radius 600 meter dari kantor cabang Banyuwangi',
                'type' => 'branch',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}