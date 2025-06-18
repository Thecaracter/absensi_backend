<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Shift;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Dashboard Admin - Statistik & Overview
     */
    public function dashboard()
    {

        $stats = [
            'total_karyawan' => User::karyawan()->aktif()->count(),
            'total_shift' => Shift::aktif()->count(),
            'absensi_hari_ini' => Attendance::hariIni()->count(),
            'izin_menunggu' => LeaveRequest::menunggu()->count(),
            'karyawan_hadir_hari_ini' => Attendance::hariIni()->hadir()->count(),
            'karyawan_terlambat_hari_ini' => Attendance::hariIni()->terlambat()->count(),
            'total_absensi_bulan_ini' => Attendance::bulanIni()->count(),
            'total_izin_bulan_ini' => LeaveRequest::bulanIni()->count(),
        ];


        $absensiHariIni = Attendance::hariIni()
            ->with(['user', 'shift'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();


        $izinMenunggu = LeaveRequest::menunggu()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();


        $absensiPerShift = Attendance::hariIni()
            ->with('shift')
            ->get()
            ->groupBy('shift.nama')
            ->map(function ($group) {
                return [
                    'total' => $group->count(),
                    'hadir' => $group->where('status_absen', 'hadir')->count(),
                    'terlambat' => $group->where('status_absen', 'terlambat')->count(),
                    'tidak_hadir' => $group->where('status_absen', 'tidak_hadir')->count(),
                ];
            });


        $topKaryawan = User::karyawan()
            ->with('shift')
            ->withCount([
                'attendances' => function ($query) {
                    $query->bulanIni()->hadir();
                }
            ])
            ->orderBy('attendances_count', 'desc')
            ->take(5)
            ->get();


        $absensi7Hari = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = now()->subDays($i);
            $absensi7Hari[] = [
                'tanggal' => $tanggal->format('Y-m-d'),
                'hari' => $tanggal->format('D'),
                'hadir' => Attendance::whereDate('tanggal_absen', $tanggal)->hadir()->count(),
                'terlambat' => Attendance::whereDate('tanggal_absen', $tanggal)->terlambat()->count(),
                'tidak_hadir' => Attendance::whereDate('tanggal_absen', $tanggal)->where('status_absen', 'tidak_hadir')->count(),
            ];
        }

        return view('admin.dashboard', compact(
            'stats',
            'absensiHariIni',
            'izinMenunggu',
            'absensiPerShift',
            'topKaryawan',
            'absensi7Hari'
        ));
    }
}