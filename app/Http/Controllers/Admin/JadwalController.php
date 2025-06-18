<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shift;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JadwalController extends Controller
{
    /**
     * Halaman Jadwal Lengkap (Monthly + Shift Management Only)
     */
    public function index(Request $request)
    {
        // Determine view type (monthly or shift only)
        $view = $request->view ?? 'monthly';

        // Handle month parameter for monthly view
        $month = $request->month ?? now()->format('Y-m');
        $monthDate = Carbon::parse($month);

        // Filter by shift
        $shiftId = $request->shift_id;

        // Get employees
        $query = User::where('role', 'karyawan')->where('status', 'aktif')->with('shift');
        if ($shiftId) {
            $query->where('shift_id', $shiftId);
        }
        $karyawan = $query->orderBy('name')->get();

        // Generate calendar weeks for monthly view
        $weeks = [];
        if ($view === 'monthly') {
            $startOfMonth = $monthDate->copy()->startOfMonth()->startOfWeek();
            $endOfMonth = $monthDate->copy()->endOfMonth()->endOfWeek();
            $currentDate = $startOfMonth->copy();

            while ($currentDate <= $endOfMonth) {
                $week_days = [];
                for ($i = 0; $i < 7; $i++) {
                    $week_days[] = [
                        'date' => $currentDate->copy(),
                        'is_current_month' => $currentDate->month === $monthDate->month,
                        'is_today' => $currentDate->isToday(),
                        'is_weekend' => $currentDate->isWeekend(),
                    ];
                    $currentDate->addDay();
                }
                $weeks[] = $week_days;
            }
        }

        // Get attendances for monthly view
        $startDate = $monthDate->copy()->startOfMonth();
        $endDate = $monthDate->copy()->endOfMonth();

        $attendances = Attendance::with(['user', 'shift'])
            ->whereBetween('tanggal_absen', [$startDate, $endDate])
            ->when($shiftId, function ($q) use ($shiftId) {
                return $q->where('shift_id', $shiftId);
            })
            ->get();

        // Debug log
        Log::info("Attendance Query Debug: Start={$startDate}, End={$endDate}, Count={$attendances->count()}");

        // Prepare shift summary for each day (monthly view)
        $daily_shift_summary = [];
        if ($view === 'monthly') {
            foreach ($weeks as $week) {
                foreach ($week as $day) {
                    $dayKey = $day['date']->format('Y-m-d');

                    // Filter attendances for this specific day
                    $dayAttendances = $attendances->filter(function ($attendance) use ($dayKey) {
                        return $attendance->tanggal_absen->format('Y-m-d') === $dayKey;
                    });

                    $summary = [
                        'total' => $dayAttendances->count(),
                        'shifts' => []
                    ];

                    // Group by shift for this day
                    $shiftGroups = $dayAttendances->groupBy('shift_id');
                    foreach ($shiftGroups as $shiftId => $shiftAttendances) {
                        $firstAttendance = $shiftAttendances->first();
                        if ($firstAttendance && $firstAttendance->shift) {
                            $shift = $firstAttendance->shift;
                            $summary['shifts'][] = [
                                'id' => $shiftId,
                                'nama' => $shift->nama,
                                'count' => $shiftAttendances->count(),
                                'jam_masuk' => $shift->jam_masuk,
                                'jam_keluar' => $shift->jam_keluar,
                            ];
                        }
                    }

                    $daily_shift_summary[$dayKey] = $summary;
                }
            }
        }

        // Also create monthly_attendances for backward compatibility
        $monthly_attendances = collect();
        if ($view === 'monthly') {
            foreach ($daily_shift_summary as $dayKey => $summary) {
                $dayAttendances = $attendances->filter(function ($attendance) use ($dayKey) {
                    return $attendance->tanggal_absen->format('Y-m-d') === $dayKey;
                });

                $monthly_attendances->put($dayKey, [
                    'total' => $summary['total'],
                    'hadir' => $dayAttendances->where('status_absen', 'hadir')->count(),
                    'terlambat' => $dayAttendances->where('status_absen', 'terlambat')->count(),
                    'tidak_hadir' => $dayAttendances->where('status_absen', 'tidak_hadir')->count(),
                    'izin' => $dayAttendances->where('status_absen', 'izin')->count(),
                ]);
            }
        }

        // Get all shifts for filter and management
        $shifts_aktif = Shift::where('aktif', true)->orderBy('nama')->get();
        $shifts_all = Shift::orderBy('nama')->get();

        // Get specific date attendances for detail view
        $selected_date = $request->selected_date;
        $date_attendances = [];
        if ($selected_date) {
            $date_attendances = Attendance::with(['user', 'shift'])
                ->whereDate('tanggal_absen', $selected_date)
                ->when($shiftId, function ($q) use ($shiftId) {
                    return $q->where('shift_id', $shiftId);
                })
                ->get();
        }

        // Handle AJAX request for day details
        if ($request->ajax()) {
            return response()->json([
                'date_attendances' => $date_attendances
            ]);
        }

        return view('admin.jadwal', compact(
            'view',
            'karyawan',
            'shifts_aktif',
            'shifts_all',
            'weeks',
            'attendances',
            'daily_shift_summary',
            'monthly_attendances',
            'date_attendances',
            'month',
            'monthDate',
            'selected_date'
        ));
    }

    /**
     * Get detailed schedule for specific date (for modal)
     */
    public function getDateScheduleDetail(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->date;

        // Get all attendances for this date
        $attendances = Attendance::with(['user', 'shift'])
            ->whereDate('tanggal_absen', $date)
            ->get();

        // Group by shift
        $shiftGroups = $attendances->groupBy('shift_id');

        $shifts = [];
        foreach ($shiftGroups as $shiftId => $shiftAttendances) {
            $firstAttendance = $shiftAttendances->first();
            $shift = $firstAttendance->shift;

            $employees = $shiftAttendances->map(function ($attendance) {
                return [
                    'id' => $attendance->user->id,
                    'name' => $attendance->user->name,
                    'id_karyawan' => $attendance->user->id_karyawan,
                    'status_absen' => $attendance->status_absen,
                    'jam_masuk' => $attendance->jam_masuk ? Carbon::parse($attendance->jam_masuk)->format('H:i') : null,
                    'jam_keluar' => $attendance->jam_keluar ? Carbon::parse($attendance->jam_keluar)->format('H:i') : null,
                    'status_masuk' => $attendance->status_masuk,
                    'status_keluar' => $attendance->status_keluar,
                ];
            });

            $shifts[] = [
                'id' => $shiftId,
                'nama' => $shift->nama ?? 'No Shift',
                'jam_masuk' => $shift ? $shift->jam_masuk->format('H:i') : null,
                'jam_keluar' => $shift ? $shift->jam_keluar->format('H:i') : null,
                'count' => $shiftAttendances->count(),
                'employees' => $employees
            ];
        }

        return response()->json([
            'success' => true,
            'date' => Carbon::parse($date)->format('d F Y'),
            'date_formatted' => Carbon::parse($date)->format('l, d F Y'),
            'total_employees' => $attendances->count(),
            'shifts' => $shifts
        ]);
    }

    /**
     * AUTO GENERATE MONTHLY SCHEDULE WITH RANDOM BALANCED DISTRIBUTION
     */
    public function autoGenerateMonthlySchedule(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'overwrite' => 'nullable|in:on,off,true,false,1,0',
            'exclude_weekends' => 'nullable|in:on,off,true,false,1,0',
            'min_shift_ratio' => 'numeric|min:0.1|max:1.0',
        ]);

        $month = Carbon::parse($request->month);
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        // Convert checkbox values to boolean
        $overwrite = in_array($request->get('overwrite'), ['on', 'true', '1', true], true);
        $excludeWeekends = in_array($request->get('exclude_weekends', 'on'), ['on', 'true', '1', true], true);
        $minShiftRatio = $request->get('min_shift_ratio', 0.8);

        // Get active employees and shifts
        $employees = User::where('role', 'karyawan')->where('status', 'aktif')->get();
        $activeShifts = Shift::where('aktif', true)->get();

        if ($employees->isEmpty() || $activeShifts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada karyawan aktif atau shift yang tersedia!'
            ], 422);
        }

        // Check for existing schedules
        if (!$overwrite) {
            $existingCount = Attendance::whereBetween('tanggal_absen', [$startDate, $endDate])->count();
            if ($existingCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sudah ada jadwal untuk bulan ini. Aktifkan "Timpa Jadwal Lama" untuk mengganti.',
                    'existing_count' => $existingCount
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            // Delete existing schedules if overwrite is enabled
            if ($overwrite) {
                Attendance::whereBetween('tanggal_absen', [$startDate, $endDate])
                    ->whereNull('jam_masuk')
                    ->whereNull('jam_keluar')
                    ->delete();
            }

            $generatedSchedules = [];
            $shiftStats = [];

            // Initialize shift statistics
            foreach ($activeShifts as $shift) {
                $shiftStats[$shift->id] = [
                    'name' => $shift->nama,
                    'count' => 0,
                    'target' => 0
                ];
            }

            // Generate schedule for each day
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                // Skip weekends if requested
                if ($excludeWeekends && $currentDate->isWeekend()) {
                    $currentDate->addDay();
                    continue;
                }

                $dailySchedules = $this->generateBalancedDailySchedule(
                    $employees,
                    $activeShifts,
                    $currentDate->copy(),
                    $minShiftRatio
                );

                foreach ($dailySchedules as $schedule) {
                    $attendance = Attendance::create([
                        'user_id' => $schedule['user_id'],
                        'shift_id' => $schedule['shift_id'],
                        'tanggal_absen' => $schedule['date'],
                        'status_absen' => 'menunggu', // Use the new status from migration
                        'status_masuk' => 'menunggu',
                        'status_keluar' => 'menunggu',
                        'jam_masuk' => null,
                        'jam_keluar' => null,
                        'catatan_admin' => 'Auto-generated: ' . now()->format('Y-m-d H:i:s'),
                    ]);

                    $generatedSchedules[] = $attendance;
                    $shiftStats[$schedule['shift_id']]['count']++;
                }

                $currentDate->addDay();
            }

            // Calculate final statistics
            $totalSchedules = count($generatedSchedules);
            foreach ($shiftStats as $shiftId => &$stats) {
                $stats['percentage'] = $totalSchedules > 0 ? round(($stats['count'] / $totalSchedules) * 100, 1) : 0;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal bulanan berhasil di-generate!',
                'data' => [
                    'total_schedules' => $totalSchedules,
                    'date_range' => [
                        'start' => $startDate->format('Y-m-d'),
                        'end' => $endDate->format('Y-m-d')
                    ],
                    'shift_distribution' => $shiftStats,
                    'settings' => [
                        'exclude_weekends' => $excludeWeekends,
                        'min_shift_ratio' => $minShiftRatio,
                        'overwrite' => $overwrite
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Gagal generate jadwal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate balanced daily schedule with randomization
     */
    private function generateBalancedDailySchedule($employees, $shifts, $date, $minRatio = 0.8)
    {
        $dailySchedules = [];

        // Calculate target employees per shift
        $totalEmployees = $employees->count();
        $totalShifts = $shifts->count();
        $baseEmployeesPerShift = floor($totalEmployees / $totalShifts);
        $remainder = $totalEmployees % $totalShifts;

        // Randomize employees order
        $shuffledEmployees = $employees->shuffle();

        // Randomize shifts order
        $shuffledShifts = $shifts->shuffle();

        $employeeIndex = 0;

        foreach ($shuffledShifts as $index => $shift) {
            // Calculate employees for this shift
            $employeesForShift = $baseEmployeesPerShift;
            if ($index < $remainder) {
                $employeesForShift++;
            }

            // Apply minimum ratio
            $minEmployees = max(1, floor($totalEmployees * $minRatio / $totalShifts));
            $employeesForShift = max($employeesForShift, $minEmployees);

            // Assign employees to this shift
            for ($i = 0; $i < $employeesForShift && $employeeIndex < $totalEmployees; $i++) {
                $employee = $shuffledEmployees[$employeeIndex];

                $dailySchedules[] = [
                    'user_id' => $employee->id,
                    'shift_id' => $shift->id,
                    'date' => $date->format('Y-m-d'),
                    'employee_name' => $employee->name,
                    'shift_name' => $shift->nama
                ];

                $employeeIndex++;
            }
        }

        return $dailySchedules;
    }

    /**
     * Clear all schedules for a month
     */
    public function clearMonthlySchedule(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'clear_attended' => 'nullable|in:on,off,true,false,1,0'
        ]);

        $month = Carbon::parse($request->month);
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        // Convert checkbox value to boolean
        $clearAttended = in_array($request->get('clear_attended'), ['on', 'true', '1', true], true);

        try {
            DB::beginTransaction();

            $query = Attendance::whereBetween('tanggal_absen', [$startDate, $endDate]);

            if (!$clearAttended) {
                $query->whereNull('jam_masuk')->whereNull('jam_keluar');
            }

            $deletedCount = $query->count();
            $query->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deletedCount} jadwal.",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jadwal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status attendance yang ada dari pending ke tidak_hadir (status default)
     */
    public function updatePendingStatus()
    {
        try {
            // Update any menunggu status to tidak_hadir for past dates
            $updated = Attendance::where('status_absen', 'menunggu')
                ->where('tanggal_absen', '<', today())
                ->whereNull('jam_masuk')
                ->whereNull('jam_keluar')
                ->update([
                    'status_absen' => 'tidak_hadir',
                    'catatan_admin' => 'Auto-updated: Overdue - ' . now()->format('Y-m-d H:i:s')
                ]);

            return response()->json([
                'success' => true,
                'message' => "Berhasil update $updated records dari menunggu ke tidak_hadir",
                'updated_count' => $updated
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tambah Shift Baru
     */
    public function storeShift(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:shifts,nama',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i',
            'toleransi_menit' => 'required|integer|min:0|max:60',
            'keterangan' => 'nullable|string|max:500',
        ]);

        Shift::create([
            'nama' => $request->nama,
            'jam_masuk' => $request->jam_masuk,
            'jam_keluar' => $request->jam_keluar,
            'toleransi_menit' => $request->toleransi_menit,
            'aktif' => true,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin.jadwal.index', ['view' => 'shift'])
            ->with('success', 'Shift berhasil ditambahkan!');
    }

    /**
     * Update Shift
     */
    public function updateShift(Request $request, Shift $shift)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:shifts,nama,' . $shift->id,
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i',
            'toleransi_menit' => 'required|integer|min:0|max:60',
            'aktif' => 'required|boolean',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $shift->update($request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Shift berhasil diperbarui!'
            ]);
        }

        return redirect()->route('admin.jadwal.index', ['view' => 'shift'])
            ->with('success', 'Shift berhasil diperbarui!');
    }

    /**
     * Toggle Shift Status
     */
    public function toggleShiftStatus(Shift $shift)
    {
        $shift->update(['aktif' => !$shift->aktif]);

        return redirect()->route('admin.jadwal.index', ['view' => 'shift'])
            ->with('success', 'Status shift berhasil diubah!');
    }

    /**
     * Hapus Shift
     */
    public function destroyShift(Shift $shift)
    {
        // Check attendance usage
        $attendanceCount = Attendance::where('shift_id', $shift->id)->count();

        if ($attendanceCount > 0) {
            return redirect()->route('admin.jadwal.index', ['view' => 'shift'])
                ->with('error', "Tidak dapat menghapus shift ini karena masih digunakan di $attendanceCount jadwal!");
        }

        $shift->delete();

        return redirect()->route('admin.jadwal.index', ['view' => 'shift'])
            ->with('success', 'Shift berhasil dihapus!');
    }

    /**
     * Get shift data as JSON for edit modal
     */
    public function getShiftJson(Shift $shift)
    {
        return response()->json([
            'id' => $shift->id,
            'nama' => $shift->nama,
            'jam_masuk' => $shift->jam_masuk->format('H:i'),
            'jam_keluar' => $shift->jam_keluar->format('H:i'),
            'toleransi_menit' => $shift->toleransi_menit,
            'aktif' => $shift->aktif,
            'keterangan' => $shift->keterangan,
        ]);
    }

    /**
     * Create manual schedule entry
     */
    public function createSchedule(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'shift_id' => 'required|exists:shifts,id',
            'catatan' => 'nullable|string|max:500',
        ]);

        $existing = Attendance::where('user_id', $request->user_id)
            ->where('tanggal_absen', $request->tanggal)
            ->first();

        if ($existing) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal untuk karyawan ini pada tanggal tersebut sudah ada!'
                ], 422);
            }
            return redirect()->back()
                ->with('error', 'Jadwal untuk karyawan ini pada tanggal tersebut sudah ada!');
        }

        $attendance = Attendance::create([
            'user_id' => $request->user_id,
            'shift_id' => $request->shift_id,
            'tanggal_absen' => $request->tanggal,
            'status_absen' => 'menunggu', // Use the new status from migration
            'status_masuk' => 'menunggu',
            'status_keluar' => 'menunggu',
            'jam_masuk' => null,
            'jam_keluar' => null,
            'catatan_admin' => $request->catatan,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil dibuat!',
                'data' => $attendance
            ]);
        }

        return redirect()->back()->with('success', 'Jadwal berhasil dibuat!');
    }

    /**
     * Export Jadwal
     */
    public function exportJadwal(Request $request)
    {
        $request->validate([
            'periode' => 'required|in:minggu,bulan',
            'tanggal' => 'required|date',
            'format' => 'required|in:csv,excel,pdf',
            'shift_id' => 'nullable|exists:shifts,id',
        ]);

        if ($request->periode === 'minggu') {
            $date = Carbon::parse($request->tanggal);
            $startDate = $date->startOfWeek();
            $endDate = $date->endOfWeek();
        } else {
            $date = Carbon::parse($request->tanggal);
            $startDate = $date->startOfMonth();
            $endDate = $date->endOfMonth();
        }

        $attendances = Attendance::with(['user', 'shift'])
            ->whereBetween('tanggal_absen', [$startDate, $endDate])
            ->when($request->shift_id, function ($q) use ($request) {
                return $q->where('shift_id', $request->shift_id);
            })
            ->orderBy('tanggal_absen')
            ->get();

        return $this->exportScheduleToCsv($attendances, $startDate, $endDate, $request->periode);
    }

    private function exportScheduleToCsv($attendances, $startDate, $endDate, $periode)
    {
        $filename = 'jadwal_' . $periode . '_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($attendances) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Tanggal', 'Nama Karyawan', 'Shift', 'Jam Masuk', 'Jam Keluar', 'Status', 'Catatan']);

            foreach ($attendances as $attendance) {
                fputcsv($file, [
                    $attendance->tanggal_absen,
                    $attendance->user->name,
                    $attendance->shift->nama,
                    $attendance->jam_masuk ?? '-',
                    $attendance->jam_keluar ?? '-',
                    $attendance->status_absen,
                    $attendance->catatan_admin ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}