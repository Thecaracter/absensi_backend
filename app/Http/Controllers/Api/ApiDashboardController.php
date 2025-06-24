<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ApiDashboardController extends Controller
{
    /**
     * GET DASHBOARD HOME - RINGKASAN SEMUA DATA
     */
    public function home()
    {
        try {
            $user = Auth::user();
            $today = today();
            $currentMonth = now();


            $todayAttendance = Attendance::where('user_id', $user->id)
                ->where('tanggal_absen', $today)
                ->with('shift')
                ->first();

            $attendanceToday = [
                'sudah_check_in' => $todayAttendance && $todayAttendance->jam_masuk ? true : false,
                'sudah_check_out' => $todayAttendance && $todayAttendance->jam_keluar ? true : false,
                'jam_masuk' => $todayAttendance && $todayAttendance->jam_masuk ?
                    Carbon::parse($todayAttendance->jam_masuk)->format('H:i') : null,
                'jam_keluar' => $todayAttendance && $todayAttendance->jam_keluar ?
                    Carbon::parse($todayAttendance->jam_keluar)->format('H:i') : null,
                'status_absen' => $todayAttendance ? $todayAttendance->status_absen : null,
                'menit_terlambat' => $todayAttendance ? $todayAttendance->menit_terlambat : 0,
                'shift' => $todayAttendance && $todayAttendance->shift ? [
                    'nama' => $todayAttendance->shift->nama,
                    'jam_masuk' => $todayAttendance->shift->jam_masuk->format('H:i'),
                    'jam_keluar' => $todayAttendance->shift->jam_keluar->format('H:i'),
                ] : null
            ];


            $attendanceStats = [
                'total_hari_kerja' => Attendance::where('user_id', $user->id)
                    ->whereMonth('tanggal_absen', $currentMonth->month)
                    ->whereYear('tanggal_absen', $currentMonth->year)
                    ->count(),
                'total_hadir' => Attendance::where('user_id', $user->id)
                    ->whereMonth('tanggal_absen', $currentMonth->month)
                    ->whereYear('tanggal_absen', $currentMonth->year)
                    ->whereIn('status_absen', ['hadir', 'terlambat'])
                    ->count(),
                'total_terlambat' => Attendance::where('user_id', $user->id)
                    ->whereMonth('tanggal_absen', $currentMonth->month)
                    ->whereYear('tanggal_absen', $currentMonth->year)
                    ->where('status_absen', 'terlambat')
                    ->count(),
            ];

            $attendanceStats['tingkat_kehadiran'] = $attendanceStats['total_hari_kerja'] > 0 ?
                round(($attendanceStats['total_hadir'] / $attendanceStats['total_hari_kerja']) * 100, 1) : 0;


            $leaveStats = [
                'total_pengajuan_bulan_ini' => LeaveRequest::where('user_id', $user->id)
                    ->whereMonth('created_at', $currentMonth->month)
                    ->whereYear('created_at', $currentMonth->year)
                    ->count(),
                'menunggu_approval' => LeaveRequest::where('user_id', $user->id)
                    ->where('status', 'menunggu')
                    ->count(),
                'total_hari_izin_tahun_ini' => LeaveRequest::where('user_id', $user->id)
                    ->whereYear('created_at', $currentMonth->year)
                    ->where('status', 'disetujui')
                    ->sum('total_hari'),
                'kuota_cuti' => 12,
            ];

            $leaveStats['sisa_kuota'] = max(0, $leaveStats['kuota_cuti'] - $leaveStats['total_hari_izin_tahun_ini']);


            $recentAttendances = Attendance::where('user_id', $user->id)
                ->whereBetween('tanggal_absen', [
                    $today->copy()->subDays(6),
                    $today
                ])
                ->orderBy('tanggal_absen', 'asc')
                ->get()
                ->map(function ($attendance) {
                    return [
                        'tanggal' => $attendance->tanggal_absen->format('Y-m-d'),
                        'hari' => $attendance->tanggal_absen->format('D'),
                        'status' => $attendance->status_absen,
                        'jam_masuk' => $attendance->jam_masuk ? Carbon::parse($attendance->jam_masuk)->format('H:i') : null,
                        'jam_keluar' => $attendance->jam_keluar ? Carbon::parse($attendance->jam_keluar)->format('H:i') : null,
                        'terlambat' => $attendance->menit_terlambat > 0,
                    ];
                });


            $notifications = [];


            if (!$attendanceToday['sudah_check_in'] && now()->format('H') >= 8) {
                $notifications[] = [
                    'type' => 'warning',
                    'message' => 'Anda belum melakukan check-in hari ini',
                    'action' => 'check_in'
                ];
            }


            if ($attendanceToday['sudah_check_in'] && !$attendanceToday['sudah_check_out'] && now()->format('H') >= 17) {
                $notifications[] = [
                    'type' => 'info',
                    'message' => 'Jangan lupa check-out sebelum pulang',
                    'action' => 'check_out'
                ];
            }


            if ($leaveStats['menunggu_approval'] > 0) {
                $notifications[] = [
                    'type' => 'info',
                    'message' => "Ada {$leaveStats['menunggu_approval']} pengajuan izin menunggu approval",
                    'action' => 'view_leaves'
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Dashboard data berhasil diambil',
                'data' => [
                    'user' => [
                        'name' => $user->name,
                        'id_karyawan' => $user->id_karyawan,
                        'foto_url' => $user->foto_url,
                    ],
                    'absensi_hari_ini' => $attendanceToday,
                    'statistik_absensi' => $attendanceStats,
                    'statistik_izin' => $leaveStats,
                    'riwayat_7_hari' => $recentAttendances,
                    'notifikasi' => $notifications,
                    'quick_actions' => [
                        'can_check_in' => !$attendanceToday['sudah_check_in'],
                        'can_check_out' => $attendanceToday['sudah_check_in'] && !$attendanceToday['sudah_check_out'],
                        'can_request_leave' => true,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * GET SUMMARY STATISTICS - RINGKASAN ANGKA UNTUK CARDS
     */
    public function summary()
    {
        try {
            $user = Auth::user();
            $currentMonth = now();


            $thisMonth = [
                'hadir' => Attendance::where('user_id', $user->id)
                    ->whereMonth('tanggal_absen', $currentMonth->month)
                    ->whereYear('tanggal_absen', $currentMonth->year)
                    ->whereIn('status_absen', ['hadir', 'terlambat'])
                    ->count(),
                'terlambat' => Attendance::where('user_id', $user->id)
                    ->whereMonth('tanggal_absen', $currentMonth->month)
                    ->whereYear('tanggal_absen', $currentMonth->year)
                    ->where('status_absen', 'terlambat')
                    ->count(),
                'izin_disetujui' => LeaveRequest::where('user_id', $user->id)
                    ->whereMonth('created_at', $currentMonth->month)
                    ->whereYear('created_at', $currentMonth->year)
                    ->where('status', 'disetujui')
                    ->sum('total_hari'),
                'izin_menunggu' => LeaveRequest::where('user_id', $user->id)
                    ->where('status', 'menunggu')
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Summary statistics berhasil diambil',
                'data' => $thisMonth
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET QUICK ACTIONS - STATUS AKSI YANG BISA DILAKUKAN
     */
    public function quickActions()
    {
        try {
            $user = Auth::user();
            $today = today();

            $attendance = Attendance::where('user_id', $user->id)
                ->where('tanggal_absen', $today)
                ->first();

            $actions = [
                'check_in' => [
                    'available' => !$attendance || !$attendance->jam_masuk,
                    'text' => 'Check In',
                    'icon' => 'login',
                    'color' => 'success'
                ],
                'check_out' => [
                    'available' => $attendance && $attendance->jam_masuk && !$attendance->jam_keluar,
                    'text' => 'Check Out',
                    'icon' => 'logout',
                    'color' => 'warning'
                ],
                'request_leave' => [
                    'available' => true,
                    'text' => 'Ajukan Izin',
                    'icon' => 'calendar',
                    'color' => 'info'
                ],
                'view_attendance' => [
                    'available' => true,
                    'text' => 'Riwayat Absensi',
                    'icon' => 'history',
                    'color' => 'secondary'
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Quick actions berhasil diambil',
                'data' => $actions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}