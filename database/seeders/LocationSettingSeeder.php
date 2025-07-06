<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            [
                'key' => 'enable_location_validation',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Aktifkan Validasi Lokasi',
                'description' => 'Mengaktifkan atau menonaktifkan validasi lokasi untuk absensi',
                'is_editable' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'default_radius_meters',
                'value' => '800',
                'type' => 'integer',
                'label' => 'Radius Default (meter)',
                'description' => 'Radius default dalam meter untuk validasi lokasi',
                'is_editable' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'allow_manual_location',
                'value' => '0',
                'type' => 'boolean',
                'label' => 'Izinkan Input Lokasi Manual',
                'description' => 'Mengizinkan pengguna memasukkan lokasi secara manual',
                'is_editable' => true,
                'sort_order' => 3,
            ],
            [
                'key' => 'strict_mode',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Mode Ketat',
                'description' => 'Mengaktifkan mode ketat untuk validasi lokasi',
                'is_editable' => true,
                'sort_order' => 4,
            ],
            [
                'key' => 'auto_tracking_enabled',
                'value' => '1',
                'type' => 'boolean',
                'label' => 'Pelacakan Otomatis',
                'description' => 'Mengaktifkan pelacakan lokasi secara otomatis',
                'is_editable' => true,
                'sort_order' => 5,
            ],
            [
                'key' => 'location_update_interval_seconds',
                'value' => '15',
                'type' => 'integer',
                'label' => 'Interval Update Lokasi (detik)',
                'description' => 'Interval pembaruan lokasi dalam detik di Flutter',
                'is_editable' => true,
                'sort_order' => 6,
            ],
            [
                'key' => 'accuracy_threshold_meters',
                'value' => '50',
                'type' => 'integer',
                'label' => 'Ambang Batas Akurasi GPS (meter)',
                'description' => 'Ambang batas akurasi GPS minimal yang diterima',
                'is_editable' => true,
                'sort_order' => 7,
            ],
        ];

        foreach ($settings as $setting) {
            $setting['created_at'] = now();
            $setting['updated_at'] = now();
            DB::table('location_settings')->insert($setting);
        }
    }
}