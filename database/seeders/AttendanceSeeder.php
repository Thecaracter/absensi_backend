<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Shift;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Attendance Seeder...');

        // Clear existing attendance data (optional - comment out if you want to keep existing data)
        // Attendance::truncate();

        // Get all active employees (karyawan)
        $employees = User::where('role', 'karyawan')
            ->where('status', 'aktif')
            ->get();

        if ($employees->isEmpty()) {
            $this->command->warn('No active employees found. Please run UserSeeder first.');
            return;
        }

        // Get all active shifts
        $shifts = Shift::where('aktif', true)->get();

        if ($shifts->isEmpty()) {
            $this->command->warn('No active shifts found. Please run ShiftSeeder first.');
            return;
        }

        // Generate attendance for the last 30 days (excluding weekends for more realism)
        $startDate = now()->subDays(30);
        $endDate = now()->subDay(); // Yesterday

        $this->command->info("Generating attendance from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        $attendanceData = [];
        $totalRecords = 0;

        foreach ($employees as $employee) {
            // Assign random shift if employee doesn't have one
            $employeeShift = $employee->shift ?? $shifts->random();

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // Skip weekends (Saturday = 6, Sunday = 0)
                if (in_array($date->dayOfWeek, [0, 6])) {
                    continue;
                }

                // Random chance to skip some days (sick leave, etc.) - 15% chance
                if (rand(1, 100) <= 15) {
                    $attendanceData[] = $this->createAbsentRecord($employee, $employeeShift, $date->copy(), 'tidak_hadir');
                    $totalRecords++;
                    continue;
                }

                // Random chance for leave/izin - 5% chance
                if (rand(1, 100) <= 5) {
                    $attendanceData[] = $this->createAbsentRecord($employee, $employeeShift, $date->copy(), 'izin');
                    $totalRecords++;
                    continue;
                }

                // Generate normal attendance with various scenarios
                $attendanceData[] = $this->createNormalAttendanceRecord($employee, $employeeShift, $date->copy());
                $totalRecords++;

                // Insert in batches of 50 for performance
                if (count($attendanceData) >= 50) {
                    Attendance::insert($attendanceData);
                    $attendanceData = [];
                    $this->command->info("Inserted batch of 50 records...");
                }
            }
        }

        // Insert remaining records
        if (!empty($attendanceData)) {
            Attendance::insert($attendanceData);
        }

        $this->command->info("Successfully created {$totalRecords} attendance records!");

        // Display statistics
        $this->displayStatistics();
    }

    /**
     * Create base attendance record structure with all required fields
     */
    private function createBaseRecord($employee, $shift, $date)
    {
        return [
            'user_id' => $employee->id,
            'shift_id' => $shift->id,
            'tanggal_absen' => $date->format('Y-m-d'),
            'jam_masuk' => null,
            'foto_masuk' => null,
            'latitude_masuk' => null,
            'longitude_masuk' => null,
            'status_masuk' => 'menunggu',
            'jam_keluar' => null,
            'foto_keluar' => null,
            'latitude_keluar' => null,
            'longitude_keluar' => null,
            'status_keluar' => 'menunggu',
            'status_absen' => 'menunggu',
            'menit_terlambat' => 0,
            'menit_lembur' => 0,
            'catatan_admin' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create normal attendance record with realistic scenarios
     */
    private function createNormalAttendanceRecord($employee, $shift, $date)
    {
        $shiftStartTime = Carbon::parse($shift->jam_masuk);
        $shiftEndTime = Carbon::parse($shift->jam_keluar);
        $tolerance = $shift->toleransi_menit;

        // Create base record
        $attendance = $this->createBaseRecord($employee, $shift, $date);

        // Determine if employee is late (30% chance)
        $isLate = rand(1, 100) <= 30;

        if ($isLate) {
            // Late between 1 to 60 minutes
            $lateMinutes = rand(1, 60);
            $actualStartTime = $shiftStartTime->copy()->addMinutes($tolerance + $lateMinutes);

            $attendance['jam_masuk'] = $actualStartTime->format('H:i:s');
            $attendance['status_absen'] = 'terlambat';
            $attendance['menit_terlambat'] = $lateMinutes;
        } else {
            // On time or early (within tolerance)
            $earlyMinutes = rand(-15, $tolerance); // Can be early up to 15 minutes
            $actualStartTime = $shiftStartTime->copy()->addMinutes($earlyMinutes);

            $attendance['jam_masuk'] = $actualStartTime->format('H:i:s');
            $attendance['status_absen'] = 'hadir';
            $attendance['menit_terlambat'] = 0;
        }

        // Generate check-out time (90% chance they check out)
        if (rand(1, 100) <= 90) {
            // Check out time: shift end time + random overtime (0-120 minutes)
            $overtimeMinutes = $this->getRandomOvertimeMinutes();
            $actualEndTime = $shiftEndTime->copy()->addMinutes($overtimeMinutes);

            $attendance['jam_keluar'] = $actualEndTime->format('H:i:s');
            $attendance['menit_lembur'] = max(0, $overtimeMinutes);
        }

        // Add location data (simulate office location)
        $locationData = $this->generateLocationData();
        $attendance['latitude_masuk'] = $locationData['latitude_masuk'];
        $attendance['longitude_masuk'] = $locationData['longitude_masuk'];
        $attendance['latitude_keluar'] = $locationData['latitude_keluar'];
        $attendance['longitude_keluar'] = $locationData['longitude_keluar'];

        // Add photo paths (simulate taking photos)
        $photoData = $this->generatePhotoData($employee, $date);
        $attendance['foto_masuk'] = $photoData['foto_masuk'];
        $attendance['foto_keluar'] = $photoData['foto_keluar'];

        // Set approval status (most are approved, some pending)
        $approvalData = $this->generateApprovalStatus();
        $attendance['status_masuk'] = $approvalData['status_masuk'];
        $attendance['status_keluar'] = $approvalData['status_keluar'];
        $attendance['catatan_admin'] = $approvalData['catatan_admin'] ?? null;

        return $attendance;
    }

    /**
     * Create attendance record for absent/leave days
     */
    private function createAbsentRecord($employee, $shift, $date, $status)
    {
        $attendance = $this->createBaseRecord($employee, $shift, $date);
        $attendance['status_absen'] = $status;

        return $attendance;
    }

    /**
     * Generate realistic overtime minutes
     */
    private function getRandomOvertimeMinutes()
    {
        $rand = rand(1, 100);

        if ($rand <= 60) {
            return 0; // No overtime - 60% chance
        } elseif ($rand <= 85) {
            return rand(15, 60); // Light overtime - 25% chance
        } else {
            return rand(60, 120); // Heavy overtime - 15% chance
        }
    }

    /**
     * Generate location data (simulate office location)
     */
    private function generateLocationData()
    {
        // Simulate main office location in Jakarta
        $baseLatitude = -6.2088;
        $baseLongitude = 106.8456;

        // Add small random variation (within 100 meters)
        $latVariation = (rand(-100, 100) / 100000);
        $lonVariation = (rand(-100, 100) / 100000);

        return [
            'latitude_masuk' => round($baseLatitude + $latVariation, 8),
            'longitude_masuk' => round($baseLongitude + $lonVariation, 8),
            'latitude_keluar' => round($baseLatitude + (rand(-100, 100) / 100000), 8),
            'longitude_keluar' => round($baseLongitude + (rand(-100, 100) / 100000), 8),
        ];
    }

    /**
     * Generate photo data (simulate file paths)
     */
    private function generatePhotoData($employee, $date)
    {
        $employeeId = $employee->id_karyawan;
        $dateStr = $date->format('Y-m-d');

        // 80% chance they take photos
        if (rand(1, 100) <= 80) {
            return [
                'foto_masuk' => "storage/attendance/{$employeeId}/{$dateStr}/checkin.jpg",
                'foto_keluar' => "storage/attendance/{$employeeId}/{$dateStr}/checkout.jpg",
            ];
        }

        return [
            'foto_masuk' => null,
            'foto_keluar' => null,
        ];
    }

    /**
     * Generate approval status
     */
    private function generateApprovalStatus()
    {
        $rand = rand(1, 100);

        if ($rand <= 85) {
            // 85% approved
            return [
                'status_masuk' => 'disetujui',
                'status_keluar' => 'disetujui',
                'catatan_admin' => null,
            ];
        } elseif ($rand <= 95) {
            // 10% pending
            return [
                'status_masuk' => 'menunggu',
                'status_keluar' => 'menunggu',
                'catatan_admin' => null,
            ];
        } else {
            // 5% rejected
            return [
                'status_masuk' => 'ditolak',
                'status_keluar' => 'ditolak',
                'catatan_admin' => 'Lokasi tidak sesuai dengan kantor',
            ];
        }
    }

    /**
     * Display attendance statistics
     */
    private function displayStatistics()
    {
        $this->command->info("\n=== Attendance Statistics ===");

        $totalAttendance = Attendance::count();

        if ($totalAttendance == 0) {
            $this->command->info("No attendance records found.");
            return;
        }

        $hadirCount = Attendance::where('status_absen', 'hadir')->count();
        $terlambatCount = Attendance::where('status_absen', 'terlambat')->count();
        $tidakHadirCount = Attendance::where('status_absen', 'tidak_hadir')->count();
        $izinCount = Attendance::where('status_absen', 'izin')->count();
        $menungguCount = Attendance::where('status_absen', 'menunggu')->count();

        $this->command->table([
            'Status',
            'Count',
            'Percentage'
        ], [
            ['Hadir', $hadirCount, round(($hadirCount / $totalAttendance) * 100, 2) . '%'],
            ['Terlambat', $terlambatCount, round(($terlambatCount / $totalAttendance) * 100, 2) . '%'],
            ['Tidak Hadir', $tidakHadirCount, round(($tidakHadirCount / $totalAttendance) * 100, 2) . '%'],
            ['Izin', $izinCount, round(($izinCount / $totalAttendance) * 100, 2) . '%'],
            ['Menunggu', $menungguCount, round(($menungguCount / $totalAttendance) * 100, 2) . '%'],
            ['TOTAL', $totalAttendance, '100%'],
        ]);

        // Approval statistics
        $approvedCount = Attendance::where('status_masuk', 'disetujui')->count();
        $pendingCount = Attendance::where('status_masuk', 'menunggu')->count();
        $rejectedCount = Attendance::where('status_masuk', 'ditolak')->count();

        $this->command->info("\n=== Approval Statistics ===");
        $this->command->table([
            'Approval Status',
            'Count',
            'Percentage'
        ], [
            ['Disetujui', $approvedCount, round(($approvedCount / $totalAttendance) * 100, 2) . '%'],
            ['Menunggu', $pendingCount, round(($pendingCount / $totalAttendance) * 100, 2) . '%'],
            ['Ditolak', $rejectedCount, round(($rejectedCount / $totalAttendance) * 100, 2) . '%'],
        ]);

        // Average lateness
        $avgLateness = Attendance::where('status_absen', 'terlambat')
            ->where('menit_terlambat', '>', 0)
            ->avg('menit_terlambat');

        $this->command->info("\nAverage lateness: " . round($avgLateness ?? 0, 2) . " minutes");

        // Employees with attendance
        $employeesWithAttendance = Attendance::distinct('user_id')->count('user_id');
        $this->command->info("Employees with attendance records: {$employeesWithAttendance}");
    }
}