<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Jalankan database seeding.
     */
    public function run(): void
    {
        // Create Admin
        User::create([
            'id_karyawan' => 'ADM001',
            'name' => 'Administrator',
            'email' => 'admin@gmail.com',
            'no_hp' => '081234567890',
            'role' => 'admin',
            'status' => 'aktif',
            'tanggal_masuk' => '2024-01-01',
            'alamat' => 'Kantor Pusat',
            'password' => Hash::make('admin123'),
            'email_verified_at' => now(),
        ]);

        echo "✅ Admin berhasil dibuat! (Email: admin@gmail.com, Password: admin123)\n";

        // Extended employee data for better shift coverage
        $karyawan = [
            // Existing employees
            [
                'id_karyawan' => 'EMP001',
                'name' => 'Budi Santoso',
                'email' => 'budi@gmail.com',
                'no_hp' => '081234567891',
                'tanggal_masuk' => '2024-01-15',
                'alamat' => 'Jl. Merdeka No. 123, Jakarta Pusat',
            ],
            [
                'id_karyawan' => 'EMP002',
                'name' => 'Siti Nurhaliza',
                'email' => 'siti@gmail.com',
                'no_hp' => '081234567892',
                'tanggal_masuk' => '2024-01-20',
                'alamat' => 'Jl. Sudirman No. 456, Jakarta Selatan',
            ],
            [
                'id_karyawan' => 'EMP003',
                'name' => 'Ahmad Wijaya',
                'email' => 'ahmad@gmail.com',
                'no_hp' => '081234567893',
                'tanggal_masuk' => '2024-02-01',
                'alamat' => 'Jl. Gatot Subroto No. 789, Jakarta Barat',
            ],
            [
                'id_karyawan' => 'EMP004',
                'name' => 'Dewi Lestari',
                'email' => 'dewi@gmail.com',
                'no_hp' => '081234567894',
                'tanggal_masuk' => '2024-02-10',
                'alamat' => 'Jl. Thamrin No. 321, Jakarta Pusat',
            ],
            [
                'id_karyawan' => 'EMP005',
                'name' => 'Rizki Pratama',
                'email' => 'rizki@gmail.com',
                'no_hp' => '081234567895',
                'tanggal_masuk' => '2024-02-15',
                'alamat' => 'Jl. Kuningan No. 654, Jakarta Selatan',
            ],
            [
                'id_karyawan' => 'EMP006',
                'name' => 'Maya Sari',
                'email' => 'maya@gmail.com',
                'no_hp' => '081234567896',
                'tanggal_masuk' => '2024-02-20',
                'alamat' => 'Jl. Casablanca No. 987, Jakarta Timur',
            ],

            // Additional employees for better shift coverage
            [
                'id_karyawan' => 'EMP007',
                'name' => 'Eko Prasetyo',
                'email' => 'eko@gmail.com',
                'no_hp' => '081234567897',
                'tanggal_masuk' => '2024-03-01',
                'alamat' => 'Jl. Kemang No. 111, Jakarta Selatan',
            ],
            [
                'id_karyawan' => 'EMP008',
                'name' => 'Rini Susanti',
                'email' => 'rini@gmail.com',
                'no_hp' => '081234567898',
                'tanggal_masuk' => '2024-03-05',
                'alamat' => 'Jl. Menteng No. 222, Jakarta Pusat',
            ],
            [
                'id_karyawan' => 'EMP009',
                'name' => 'Doni Hermawan',
                'email' => 'doni@gmail.com',
                'no_hp' => '081234567899',
                'tanggal_masuk' => '2024-03-10',
                'alamat' => 'Jl. Senayan No. 333, Jakarta Selatan',
            ],
            [
                'id_karyawan' => 'EMP010',
                'name' => 'Lina Marlina',
                'email' => 'lina@gmail.com',
                'no_hp' => '081234567810',
                'tanggal_masuk' => '2024-03-15',
                'alamat' => 'Jl. Pancoran No. 444, Jakarta Selatan',
            ],
            [
                'id_karyawan' => 'EMP011',
                'name' => 'Agus Setiawan',
                'email' => 'agus@gmail.com',
                'no_hp' => '081234567811',
                'tanggal_masuk' => '2024-03-20',
                'alamat' => 'Jl. Tebet No. 555, Jakarta Selatan',
            ],
            [
                'id_karyawan' => 'EMP012',
                'name' => 'Fitri Rahayu',
                'email' => 'fitri@gmail.com',
                'no_hp' => '081234567812',
                'tanggal_masuk' => '2024-03-25',
                'alamat' => 'Jl. Cibubur No. 666, Jakarta Timur',
            ],
            [
                'id_karyawan' => 'EMP013',
                'name' => 'Hendro Wijaya',
                'email' => 'hendro@gmail.com',
                'no_hp' => '081234567813',
                'tanggal_masuk' => '2024-04-01',
                'alamat' => 'Jl. Kalibata No. 777, Jakarta Selatan',
            ],
            [
                'id_karyawan' => 'EMP014',
                'name' => 'Sari Indah',
                'email' => 'sari@gmail.com',
                'no_hp' => '081234567814',
                'tanggal_masuk' => '2024-04-05',
                'alamat' => 'Jl. Pasar Minggu No. 888, Jakarta Selatan',
            ],
            [
                'id_karyawan' => 'EMP015',
                'name' => 'Wahyu Nugroho',
                'email' => 'wahyu@gmail.com',
                'no_hp' => '081234567815',
                'tanggal_masuk' => '2024-04-10',
                'alamat' => 'Jl. Duren Sawit No. 999, Jakarta Timur',
            ],
            [
                'id_karyawan' => 'EMP016',
                'name' => 'Indira Putri',
                'email' => 'indira@gmail.com',
                'no_hp' => '081234567816',
                'tanggal_masuk' => '2024-04-15',
                'alamat' => 'Jl. Kelapa Gading No. 1010, Jakarta Utara',
            ],
            [
                'id_karyawan' => 'EMP017',
                'name' => 'Farid Rahman',
                'email' => 'farid@gmail.com',
                'no_hp' => '081234567817',
                'tanggal_masuk' => '2024-04-20',
                'alamat' => 'Jl. Sunter No. 1111, Jakarta Utara',
            ],
            [
                'id_karyawan' => 'EMP018',
                'name' => 'Nita Sari',
                'email' => 'nita@gmail.com',
                'no_hp' => '081234567818',
                'tanggal_masuk' => '2024-04-25',
                'alamat' => 'Jl. Pluit No. 1212, Jakarta Utara',
            ],
            [
                'id_karyawan' => 'EMP019',
                'name' => 'Bayu Anggoro',
                'email' => 'bayu@gmail.com',
                'no_hp' => '081234567819',
                'tanggal_masuk' => '2024-05-01',
                'alamat' => 'Jl. Grogol No. 1313, Jakarta Barat',
            ],
            [
                'id_karyawan' => 'EMP020',
                'name' => 'Mega Wulandari',
                'email' => 'mega@gmail.com',
                'no_hp' => '081234567820',
                'tanggal_masuk' => '2024-05-05',
                'alamat' => 'Jl. Kebon Jeruk No. 1414, Jakarta Barat',
            ],
            [
                'id_karyawan' => 'EMP021',
                'name' => 'Ivan Kurniawan',
                'email' => 'ivan@gmail.com',
                'no_hp' => '081234567821',
                'tanggal_masuk' => '2024-05-10',
                'alamat' => 'Jl. Slipi No. 1515, Jakarta Barat',
            ],
            [
                'id_karyawan' => 'EMP022',
                'name' => 'Diana Sari',
                'email' => 'diana@gmail.com',
                'no_hp' => '081234567822',
                'tanggal_masuk' => '2024-05-15',
                'alamat' => 'Jl. Palmerah No. 1616, Jakarta Barat',
            ],
            [
                'id_karyawan' => 'EMP023',
                'name' => 'Rizal Maulana',
                'email' => 'rizal@gmail.com',
                'no_hp' => '081234567823',
                'tanggal_masuk' => '2024-05-20',
                'alamat' => 'Jl. Cengkareng No. 1717, Jakarta Barat',
            ],
            [
                'id_karyawan' => 'EMP024',
                'name' => 'Yuli Astuti',
                'email' => 'yuli@gmail.com',
                'no_hp' => '081234567824',
                'tanggal_masuk' => '2024-05-25',
                'alamat' => 'Jl. Tanah Abang No. 1818, Jakarta Pusat',
            ],
            [
                'id_karyawan' => 'EMP025',
                'name' => 'Gunawan Susilo',
                'email' => 'gunawan@gmail.com',
                'no_hp' => '081234567825',
                'tanggal_masuk' => '2024-06-01',
                'alamat' => 'Jl. Gambir No. 1919, Jakarta Pusat',
            ],
            [
                'id_karyawan' => 'EMP026',
                'name' => 'Ratna Dewi',
                'email' => 'ratna@gmail.com',
                'no_hp' => '081234567826',
                'tanggal_masuk' => '2024-06-05',
                'alamat' => 'Jl. Matraman No. 2020, Jakarta Timur',
            ],
            [
                'id_karyawan' => 'EMP027',
                'name' => 'Hendra Saputra',
                'email' => 'hendra@gmail.com',
                'no_hp' => '081234567827',
                'tanggal_masuk' => '2024-06-10',
                'alamat' => 'Jl. Jatinegara No. 2121, Jakarta Timur',
            ],
            [
                'id_karyawan' => 'EMP028',
                'name' => 'Wulan Sari',
                'email' => 'wulan@gmail.com',
                'no_hp' => '081234567828',
                'tanggal_masuk' => '2024-06-15',
                'alamat' => 'Jl. Rawamangun No. 2222, Jakarta Timur',
            ],
            [
                'id_karyawan' => 'EMP029',
                'name' => 'Andi Prasetyo',
                'email' => 'andi@gmail.com',
                'no_hp' => '081234567829',
                'tanggal_masuk' => '2024-06-20',
                'alamat' => 'Jl. Pademangan No. 2323, Jakarta Utara',
            ],
            [
                'id_karyawan' => 'EMP030',
                'name' => 'Siska Amelia',
                'email' => 'siska@gmail.com',
                'no_hp' => '081234567830',
                'tanggal_masuk' => '2024-06-25',
                'alamat' => 'Jl. Ancol No. 2424, Jakarta Utara',
            ],
        ];

        // Create all employees
        foreach ($karyawan as $kar) {
            User::create([
                'id_karyawan' => $kar['id_karyawan'],
                'name' => $kar['name'],
                'email' => $kar['email'],
                'no_hp' => $kar['no_hp'],
                'role' => 'karyawan',
                'status' => 'aktif',
                'tanggal_masuk' => $kar['tanggal_masuk'],
                'alamat' => $kar['alamat'],
                'password' => Hash::make('karyawan123'),
                'email_verified_at' => now(),
            ]);
        }

        $totalKaryawan = count($karyawan);
        echo "✅ {$totalKaryawan} Karyawan berhasil dibuat!\n";
        echo "📝 Password semua karyawan: karyawan123\n";
        echo "🔄 Shift akan di-assign otomatis via auto generate jadwal\n";
        echo "📊 Dengan {$totalKaryawan} karyawan, sistem dapat:\n";
        echo "   - Mengisi 3 shift dengan ~10 karyawan per shift\n";
        echo "   - Memberikan coverage penuh untuk semua hari\n";
        echo "   - Memungkinkan rotasi shift yang fleksibel\n";
        echo "   - Menghindari jadwal kosong\n";
    }
}