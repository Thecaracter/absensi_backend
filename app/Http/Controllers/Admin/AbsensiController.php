<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    /**
     * Halaman Daftar Absensi - Tampil Hari Ini Saja
     */
    public function index(Request $request)
    {
        // Default hanya hari ini, tidak bisa diubah
        $tanggal = today()->format('Y-m-d');

        $query = Attendance::with(['user', 'shift'])
            ->whereDate('tanggal_absen', $tanggal);

        // Filter shift
        if ($request->shift_id) {
            $query->where('shift_id', $request->shift_id);
        }

        // Filter status absensi
        if ($request->status_absen) {
            $query->where('status_absen', $request->status_absen);
        }

        // Filter karyawan
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter status approval
        if ($request->status_approval) {
            if ($request->status_approval === 'menunggu_masuk') {
                $query->where('status_masuk', 'menunggu');
            } elseif ($request->status_approval === 'menunggu_keluar') {
                $query->where('status_keluar', 'menunggu');
            }
        }

        $absensi = $query->orderBy('created_at', 'desc')->paginate(20);

        // Auto-calculate late status untuk semua absensi hari ini
        $this->autoCalculateLateStatus($tanggal);

        // Data untuk filter
        $shifts = Shift::aktif()->get();
        $karyawan = User::karyawan()->aktif()->get();

        // Statistik absensi hari ini
        $statsToday = [
            'total' => Attendance::whereDate('tanggal_absen', $tanggal)->count(),
            'hadir' => Attendance::whereDate('tanggal_absen', $tanggal)->hadir()->count(),
            'terlambat' => Attendance::whereDate('tanggal_absen', $tanggal)->terlambat()->count(),
            'tidak_hadir' => Attendance::whereDate('tanggal_absen', $tanggal)->where('status_absen', 'tidak_hadir')->count(),
            'menunggu_approval' => Attendance::whereDate('tanggal_absen', $tanggal)->menungguApproval()->count(),
            'izin' => Attendance::whereDate('tanggal_absen', $tanggal)->where('status_absen', 'izin')->count(),
        ];

        return view('admin.absensi', compact(
            'absensi',
            'shifts',
            'karyawan',
            'tanggal',
            'statsToday'
        ));
    }

    /**
     * Auto calculate late status berdasarkan toleransi shift
     */
    private function autoCalculateLateStatus($tanggal)
    {
        // Get all attendances today that have jam_masuk and status is not izin
        $attendances = Attendance::with('shift')
            ->whereDate('tanggal_absen', $tanggal)
            ->whereNotNull('jam_masuk')
            ->whereNotIn('status_absen', ['izin'])
            ->get();

        foreach ($attendances as $attendance) {
            if (!$attendance->shift)
                continue;

            $jamMasukActual = Carbon::parse($attendance->jam_masuk);
            $jamMasukScheduled = Carbon::parse($attendance->tanggal_absen->format('Y-m-d') . ' ' . $attendance->shift->jam_masuk->format('H:i:s'));
            $toleransiMenit = $attendance->shift->toleransi_menit;

            // Hitung berapa menit terlambat
            $menitTerlambat = 0;
            $statusAbsen = 'hadir';

            if ($jamMasukActual->gt($jamMasukScheduled->addMinutes($toleransiMenit))) {
                $menitTerlambat = $jamMasukActual->diffInMinutes($jamMasukScheduled);
                $statusAbsen = 'terlambat';
            }

            // Update hanya jika ada perubahan
            if ($attendance->status_absen !== $statusAbsen || $attendance->menit_terlambat !== $menitTerlambat) {
                $attendance->update([
                    'status_absen' => $statusAbsen,
                    'menit_terlambat' => $menitTerlambat,
                ]);
            }
        }
    }

    /**
     * Detail Absensi (untuk halaman penuh jika diperlukan)
     */
    public function show(Attendance $attendance)
    {
        $attendance->load(['user', 'shift']);
        return view('admin.absensi.show', compact('attendance'));
    }

    /**
     * Get attendance detail as JSON for AJAX modal
     */
    public function getAttendanceJson(Attendance $attendance)
    {
        $attendance->load(['user', 'shift']);

        return response()->json([
            'id' => $attendance->id,
            'user' => [
                'id' => $attendance->user->id,
                'name' => $attendance->user->name,
                'id_karyawan' => $attendance->user->id_karyawan,
            ],
            'shift' => [
                'id' => $attendance->shift->id,
                'nama' => $attendance->shift->nama,
                'jam_masuk' => $attendance->shift->jam_masuk->format('H:i'),
                'jam_keluar' => $attendance->shift->jam_keluar->format('H:i'),
                'toleransi_menit' => $attendance->shift->toleransi_menit,
            ],
            'tanggal_absen' => $attendance->tanggal_absen->format('d/m/Y'),
            'jam_masuk' => $attendance->jam_masuk ? Carbon::parse($attendance->jam_masuk)->format('H:i') : null,
            'jam_keluar' => $attendance->jam_keluar ? Carbon::parse($attendance->jam_keluar)->format('H:i') : null,
            'status_absen' => $attendance->status_absen,
            'status_absen_text' => $attendance->getStatusAbsenText(),
            'status_masuk' => $attendance->status_masuk,
            'status_keluar' => $attendance->status_keluar,
            'menit_terlambat' => $attendance->menit_terlambat,
            'menit_lembur' => $attendance->menit_lembur,
            'catatan_admin' => $attendance->catatan_admin,
            'foto_masuk_url' => $attendance->foto_masuk_url,
            'foto_keluar_url' => $attendance->foto_keluar_url,
        ]);
    }

    /**
     * Approve Absen Masuk - dengan auto calculate terlambat
     */
    public function approveMasuk(Request $request, Attendance $attendance)
    {
        $request->validate([
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        // Auto calculate late status saat approve
        if ($attendance->jam_masuk && $attendance->shift) {
            $jamMasukActual = Carbon::parse($attendance->jam_masuk);
            $jamMasukScheduled = Carbon::parse($attendance->tanggal_absen->format('Y-m-d') . ' ' . $attendance->shift->jam_masuk->format('H:i:s'));
            $toleransiMenit = $attendance->shift->toleransi_menit;

            $menitTerlambat = 0;
            $statusAbsen = 'hadir';

            if ($jamMasukActual->gt($jamMasukScheduled->addMinutes($toleransiMenit))) {
                $menitTerlambat = $jamMasukActual->diffInMinutes($jamMasukScheduled);
                $statusAbsen = 'terlambat';
            }

            $attendance->update([
                'status_masuk' => 'disetujui',
                'status_absen' => $statusAbsen,
                'menit_terlambat' => $menitTerlambat,
                'catatan_admin' => $request->catatan_admin,
            ]);
        } else {
            $attendance->update([
                'status_masuk' => 'disetujui',
                'catatan_admin' => $request->catatan_admin,
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Absen masuk berhasil disetujui!' .
                    ($attendance->status_absen === 'terlambat' ? ' (Terlambat ' . $attendance->menit_terlambat . ' menit)' : '')
            ]);
        }

        return redirect()->back()->with('success', 'Absen masuk berhasil disetujui!');
    }

    /**
     * Reject Absen Masuk
     */
    public function rejectMasuk(Request $request, Attendance $attendance)
    {
        $request->validate([
            'catatan_admin' => 'required|string|max:500',
        ]);

        $attendance->update([
            'status_masuk' => 'ditolak',
            'catatan_admin' => $request->catatan_admin,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Absen masuk berhasil ditolak!'
            ]);
        }

        return redirect()->back()->with('success', 'Absen masuk berhasil ditolak!');
    }

    /**
     * Approve Absen Keluar
     */
    public function approveKeluar(Request $request, Attendance $attendance)
    {
        $request->validate([
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $attendance->update([
            'status_keluar' => 'disetujui',
            'catatan_admin' => $request->catatan_admin,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Absen keluar berhasil disetujui!'
            ]);
        }

        return redirect()->back()->with('success', 'Absen keluar berhasil disetujui!');
    }

    /**
     * Reject Absen Keluar
     */
    public function rejectKeluar(Request $request, Attendance $attendance)
    {
        $request->validate([
            'catatan_admin' => 'required|string|max:500',
        ]);

        $attendance->update([
            'status_keluar' => 'ditolak',
            'catatan_admin' => $request->catatan_admin,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Absen keluar berhasil ditolak!'
            ]);
        }

        return redirect()->back()->with('success', 'Absen keluar berhasil ditolak!');
    }

    /**
     * Update Status Absensi Manual
     */
    public function updateStatus(Request $request, Attendance $attendance)
    {
        $request->validate([
            'status_absen' => 'required|in:hadir,terlambat,tidak_hadir,izin',
            'menit_terlambat' => 'nullable|integer|min:0',
            'menit_lembur' => 'nullable|integer|min:0',
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $attendance->update([
            'status_absen' => $request->status_absen,
            'menit_terlambat' => $request->menit_terlambat ?? 0,
            'menit_lembur' => $request->menit_lembur ?? 0,
            'catatan_admin' => $request->catatan_admin,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status absensi berhasil diperbarui!'
            ]);
        }

        return redirect()->back()->with('success', 'Status absensi berhasil diperbarui!');
    }

    /**
     * Force recalculate late status untuk hari ini
     */
    public function recalculateLateStatus()
    {
        $tanggal = today()->format('Y-m-d');
        $this->autoCalculateLateStatus($tanggal);

        return response()->json([
            'success' => true,
            'message' => 'Status keterlambatan berhasil diperbarui untuk hari ini!'
        ]);
    }

    /**
     * Export Absensi ke Excel/CSV
     */
    public function export(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'format' => 'required|in:csv,excel',
        ]);

        $absensi = Attendance::with(['user', 'shift'])
            ->whereBetween('tanggal_absen', [$request->tanggal_mulai, $request->tanggal_selesai])
            ->orderBy('tanggal_absen', 'desc')
            ->get();

        if ($request->format === 'csv') {
            return $this->exportToCsv($absensi, $request->tanggal_mulai, $request->tanggal_selesai);
        } else {
            return $this->exportToExcel($absensi, $request->tanggal_mulai, $request->tanggal_selesai);
        }
    }

    /**
     * Export ke CSV
     */
    private function exportToCsv($absensi, $tanggalMulai, $tanggalSelesai)
    {
        $filename = 'absensi_' . $tanggalMulai . '_' . $tanggalSelesai . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($absensi) {
            $file = fopen('php://output', 'w');

            // Header CSV
            fputcsv($file, [
                'Tanggal',
                'ID Karyawan',
                'Nama Karyawan',
                'Shift',
                'Jam Masuk Scheduled',
                'Jam Masuk Actual',
                'Jam Keluar Scheduled',
                'Jam Keluar Actual',
                'Status Absen',
                'Menit Terlambat',
                'Toleransi Shift (Menit)',
                'Menit Lembur',
                'Status Masuk',
                'Status Keluar',
                'Catatan Admin'
            ]);

            // Data
            foreach ($absensi as $data) {
                fputcsv($file, [
                    $data->tanggal_absen->format('Y-m-d'),
                    $data->user->id_karyawan,
                    $data->user->name,
                    $data->shift->nama,
                    $data->shift->jam_masuk->format('H:i:s'),
                    $data->jam_masuk ? Carbon::parse($data->jam_masuk)->format('H:i:s') : '',
                    $data->shift->jam_keluar->format('H:i:s'),
                    $data->jam_keluar ? Carbon::parse($data->jam_keluar)->format('H:i:s') : '',
                    $data->getStatusAbsenText(),
                    $data->menit_terlambat,
                    $data->shift->toleransi_menit,
                    $data->menit_lembur,
                    $data->status_masuk,
                    $data->status_keluar,
                    $data->catatan_admin
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export ke Excel (placeholder - implementasi bisa pakai PhpSpreadsheet)
     */
    private function exportToExcel($absensi, $tanggalMulai, $tanggalSelesai)
    {
        // Untuk sementara redirect ke CSV
        // Implementasi Excel bisa ditambahkan nanti dengan PhpSpreadsheet
        return $this->exportToCsv($absensi, $tanggalMulai, $tanggalSelesai);
    }

    /**
     * Bulk Action - Approve/Reject Multiple
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve_masuk,reject_masuk,approve_keluar,reject_keluar,recalculate_late',
            'attendance_ids' => 'required|array',
            'attendance_ids.*' => 'exists:attendances,id',
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $attendances = Attendance::whereIn('id', $request->attendance_ids);
        $count = $attendances->count();

        if ($count === 0) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih!');
        }

        switch ($request->action) {
            case 'approve_masuk':
                // Auto calculate late status for each attendance
                foreach ($attendances->get() as $attendance) {
                    if ($attendance->jam_masuk && $attendance->shift) {
                        $jamMasukActual = Carbon::parse($attendance->jam_masuk);
                        $jamMasukScheduled = Carbon::parse($attendance->tanggal_absen->format('Y-m-d') . ' ' . $attendance->shift->jam_masuk->format('H:i:s'));
                        $toleransiMenit = $attendance->shift->toleransi_menit;

                        $menitTerlambat = 0;
                        $statusAbsen = 'hadir';

                        if ($jamMasukActual->gt($jamMasukScheduled->addMinutes($toleransiMenit))) {
                            $menitTerlambat = $jamMasukActual->diffInMinutes($jamMasukScheduled);
                            $statusAbsen = 'terlambat';
                        }

                        $attendance->update([
                            'status_masuk' => 'disetujui',
                            'status_absen' => $statusAbsen,
                            'menit_terlambat' => $menitTerlambat,
                            'catatan_admin' => $request->catatan_admin
                        ]);
                    } else {
                        $attendance->update([
                            'status_masuk' => 'disetujui',
                            'catatan_admin' => $request->catatan_admin
                        ]);
                    }
                }
                $message = "$count absen masuk berhasil disetujui dengan auto calculate keterlambatan!";
                break;

            case 'reject_masuk':
                if (empty($request->catatan_admin)) {
                    return redirect()->back()->with('error', 'Catatan admin wajib diisi untuk reject!');
                }
                $attendances->update([
                    'status_masuk' => 'ditolak',
                    'catatan_admin' => $request->catatan_admin
                ]);
                $message = "$count absen masuk berhasil ditolak!";
                break;

            case 'approve_keluar':
                $attendances->update([
                    'status_keluar' => 'disetujui',
                    'catatan_admin' => $request->catatan_admin
                ]);
                $message = "$count absen keluar berhasil disetujui!";
                break;

            case 'reject_keluar':
                if (empty($request->catatan_admin)) {
                    return redirect()->back()->with('error', 'Catatan admin wajib diisi untuk reject!');
                }
                $attendances->update([
                    'status_keluar' => 'ditolak',
                    'catatan_admin' => $request->catatan_admin
                ]);
                $message = "$count absen keluar berhasil ditolak!";
                break;

            case 'recalculate_late':
                // Recalculate late status for selected attendances
                foreach ($attendances->get() as $attendance) {
                    if ($attendance->jam_masuk && $attendance->shift && $attendance->status_absen !== 'izin') {
                        $jamMasukActual = Carbon::parse($attendance->jam_masuk);
                        $jamMasukScheduled = Carbon::parse($attendance->tanggal_absen->format('Y-m-d') . ' ' . $attendance->shift->jam_masuk->format('H:i:s'));
                        $toleransiMenit = $attendance->shift->toleransi_menit;

                        $menitTerlambat = 0;
                        $statusAbsen = 'hadir';

                        if ($jamMasukActual->gt($jamMasukScheduled->addMinutes($toleransiMenit))) {
                            $menitTerlambat = $jamMasukActual->diffInMinutes($jamMasukScheduled);
                            $statusAbsen = 'terlambat';
                        }

                        $attendance->update([
                            'status_absen' => $statusAbsen,
                            'menit_terlambat' => $menitTerlambat,
                        ]);
                    }
                }
                $message = "$count status keterlambatan berhasil di-recalculate!";
                break;
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Create manual attendance (jika diperlukan)
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:shifts,id',
            'tanggal_absen' => 'required|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'status_absen' => 'required|in:hadir,terlambat,tidak_hadir,izin',
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        // Cek apakah sudah ada absensi untuk user dan tanggal tersebut
        $existingAttendance = Attendance::where('user_id', $request->user_id)
            ->where('tanggal_absen', $request->tanggal_absen)
            ->first();

        if ($existingAttendance) {
            return redirect()->back()->with('error', 'Absensi untuk karyawan ini pada tanggal tersebut sudah ada!');
        }

        // Auto calculate late status jika ada jam_masuk
        $menitTerlambat = 0;
        $statusAbsen = $request->status_absen;

        if ($request->jam_masuk && $statusAbsen !== 'izin') {
            $shift = Shift::find($request->shift_id);
            if ($shift) {
                $jamMasukActual = Carbon::parse($request->tanggal_absen . ' ' . $request->jam_masuk);
                $jamMasukScheduled = Carbon::parse($request->tanggal_absen . ' ' . $shift->jam_masuk->format('H:i:s'));
                $toleransiMenit = $shift->toleransi_menit;

                if ($jamMasukActual->gt($jamMasukScheduled->addMinutes($toleransiMenit))) {
                    $menitTerlambat = $jamMasukActual->diffInMinutes($jamMasukScheduled);
                    $statusAbsen = 'terlambat';
                } else {
                    $statusAbsen = 'hadir';
                }
            }
        }

        $attendance = Attendance::create([
            'user_id' => $request->user_id,
            'shift_id' => $request->shift_id,
            'tanggal_absen' => $request->tanggal_absen,
            'jam_masuk' => $request->jam_masuk,
            'jam_keluar' => $request->jam_keluar,
            'status_absen' => $statusAbsen,
            'menit_terlambat' => $menitTerlambat,
            'status_masuk' => 'disetujui',
            'status_keluar' => $request->jam_keluar ? 'disetujui' : 'menunggu',
            'catatan_admin' => $request->catatan_admin,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Absensi manual berhasil ditambahkan!' .
                    ($statusAbsen === 'terlambat' ? ' (Terlambat ' . $menitTerlambat . ' menit)' : ''),
                'data' => $attendance
            ]);
        }

        return redirect()->back()->with('success', 'Absensi manual berhasil ditambahkan!');
    }
}