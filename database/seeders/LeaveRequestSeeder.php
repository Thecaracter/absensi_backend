<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;

class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        $karyawan = User::where('role', 'karyawan')->get();
        $admin = User::where('role', 'admin')->first();

        $jenisIzin = ['sakit', 'cuti_tahunan', 'keperluan_pribadi', 'darurat', 'lainnya'];
        $status = ['menunggu', 'disetujui', 'ditolak'];

        $alasan = [
            'sakit' => [
                'Demam tinggi dan flu',
                'Sakit perut',
                'Pusing dan tidak enak badan',
                'Kontrol dokter'
            ],
            'cuti_tahunan' => [
                'Liburan keluarga',
                'Mudik lebaran',
                'Liburan akhir tahun',
                'Cuti pribadi'
            ],
            'keperluan_pribadi' => [
                'Acara keluarga',
                'Mengurus dokumen penting',
                'Keperluan mendesak',
                'Acara pernikahan'
            ],
            'darurat' => [
                'Keluarga sakit',
                'Kecelakaan',
                'Bencana alam',
                'Keperluan mendadak'
            ],
            'lainnya' => [
                'Training eksternal',
                'Seminar',
                'Keperluan khusus',
                'Lain-lain'
            ]
        ];

        foreach ($karyawan as $kar) {
            // Buat 2-4 izin random per karyawan
            $jumlahIzin = rand(2, 4);

            for ($i = 0; $i < $jumlahIzin; $i++) {
                $jenis = $jenisIzin[array_rand($jenisIzin)];
                $statusIzin = $status[array_rand($status)];

                // Random tanggal dalam 3 bulan terakhir atau bulan depan
                $tanggalMulai = Carbon::now()->addDays(rand(-90, 30));
                $totalHari = rand(1, 5);
                $tanggalSelesai = $tanggalMulai->copy()->addDays($totalHari - 1);

                LeaveRequest::create([
                    'user_id' => $kar->id,
                    'jenis_izin' => $jenis,
                    'tanggal_mulai' => $tanggalMulai,
                    'tanggal_selesai' => $tanggalSelesai,
                    'total_hari' => $totalHari,
                    'alasan' => $alasan[$jenis][array_rand($alasan[$jenis])],
                    'status' => $statusIzin,
                    'disetujui_oleh' => $statusIzin !== 'menunggu' ? $admin->id : null,
                    'catatan_admin' => $statusIzin === 'ditolak' ? 'Alasan tidak memadai' : null,
                    'tanggal_persetujuan' => $statusIzin !== 'menunggu' ? now() : null,
                ]);
            }
        }
    }
}