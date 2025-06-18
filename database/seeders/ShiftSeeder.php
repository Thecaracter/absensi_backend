<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            [
                'nama' => 'Shift Pagi',
                'jam_masuk' => '08:00:00',
                'jam_keluar' => '17:00:00',
                'toleransi_menit' => 15,
                'aktif' => true,
                'keterangan' => 'Shift pagi - 08:00 sampai 17:00 WIB'
            ],
            [
                'nama' => 'Shift Siang',
                'jam_masuk' => '14:00:00',
                'jam_keluar' => '23:00:00',
                'toleransi_menit' => 15,
                'aktif' => true,
                'keterangan' => 'Shift siang - 14:00 sampai 23:00 WIB'
            ],
            [
                'nama' => 'Shift Malam',
                'jam_masuk' => '23:00:00',
                'jam_keluar' => '08:00:00',
                'toleransi_menit' => 15,
                'aktif' => true,
                'keterangan' => 'Shift malam - 23:00 sampai 08:00 WIB (hari berikutnya)'
            ]
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }
    }
}