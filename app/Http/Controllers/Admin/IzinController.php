<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class IzinController extends Controller
{
    /**
     * Halaman Daftar Izin dengan Pemisahan Pending
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending'); // Default ke pending

        $query = LeaveRequest::with(['user', 'approver']);

        // Filter berdasarkan tab
        if ($tab === 'pending') {
            $query->where('status', 'menunggu');
        } elseif ($tab === 'processed') {
            $query->whereIn('status', ['disetujui', 'ditolak']);
        }

        // Filter pencarian
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', function ($userQuery) use ($request) {
                    $userQuery->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('id_karyawan', 'like', '%' . $request->search . '%');
                })
                    ->orWhere('alasan', 'like', '%' . $request->search . '%');
            });
        }

        // Filter jenis izin
        if ($request->jenis_izin) {
            $query->where('jenis_izin', $request->jenis_izin);
        }

        // Filter status (untuk tab processed)
        if ($request->status && $tab === 'processed') {
            $query->where('status', $request->status);
        }

        // Filter karyawan
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter periode
        if ($request->periode) {
            $periode = Carbon::parse($request->periode);
            $query->whereMonth('tanggal_mulai', $periode->month)
                ->whereYear('tanggal_mulai', $periode->year);
        }

        // Filter tanggal pengajuan
        if ($request->tanggal_mulai && $request->tanggal_selesai) {
            $query->whereBetween('tanggal_mulai', [$request->tanggal_mulai, $request->tanggal_selesai]);
        }

        $izin = $query->orderBy('created_at', 'desc')->paginate(20);

        // Data untuk filter
        $karyawan = User::karyawan()->aktif()->get();

        // Statistik izin
        $stats = [
            'total' => LeaveRequest::count(),
            'menunggu' => LeaveRequest::where('status', 'menunggu')->count(),
            'disetujui' => LeaveRequest::where('status', 'disetujui')->count(),
            'ditolak' => LeaveRequest::where('status', 'ditolak')->count(),
            'bulan_ini' => LeaveRequest::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        // Statistik per jenis izin bulan ini
        $jenisIzinStats = LeaveRequest::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('jenis_izin, COUNT(*) as total')
            ->groupBy('jenis_izin')
            ->get()
            ->pluck('total', 'jenis_izin');

        return view('admin.izin', compact(
            'izin',
            'karyawan',
            'stats',
            'jenisIzinStats',
            'tab'
        ));
    }

    /**
     * Detail Izin (untuk halaman penuh jika diperlukan)
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['user', 'approver']);
        return view('admin.izin.show', compact('leaveRequest'));
    }

    /**
     * Get leave request detail as JSON for AJAX modal
     */
    public function getLeaveRequestJson(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['user', 'approver']);

        return response()->json([
            'id' => $leaveRequest->id,
            'user' => [
                'id' => $leaveRequest->user->id,
                'name' => $leaveRequest->user->name,
                'id_karyawan' => $leaveRequest->user->id_karyawan,
                'foto_url' => $leaveRequest->user->foto_url ?? asset('images/default-avatar.png'),
            ],
            'approver' => $leaveRequest->approver ? [
                'id' => $leaveRequest->approver->id,
                'name' => $leaveRequest->approver->name,
            ] : null,
            'jenis_izin' => $leaveRequest->jenis_izin,
            'jenis_izin_text' => $leaveRequest->getJenisIzinText(),
            'tanggal_mulai' => $leaveRequest->tanggal_mulai->format('d/m/Y'),
            'tanggal_selesai' => $leaveRequest->tanggal_selesai->format('d/m/Y'),
            'total_hari' => $leaveRequest->total_hari,
            'durasi_text' => $leaveRequest->getDurasiText(),
            'alasan' => $leaveRequest->alasan,
            'status' => $leaveRequest->status,
            'status_text' => $leaveRequest->getStatusText(),
            'status_badge_class' => $leaveRequest->getStatusBadgeClass(),
            'catatan_admin' => $leaveRequest->catatan_admin,
            'tanggal_persetujuan' => $leaveRequest->tanggal_persetujuan ? $leaveRequest->tanggal_persetujuan->format('d/m/Y H:i') : null,
            'created_at' => $leaveRequest->created_at->format('d/m/Y H:i'),
            'lampiran_url' => $leaveRequest->lampiran_url,
            'is_menunggu' => $leaveRequest->status === 'menunggu',
            'bisa_diedit' => $leaveRequest->status === 'menunggu',
            'bisa_dibatalkan' => $leaveRequest->status === 'menunggu',
        ]);
    }

    /**
     * Create new leave request
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'jenis_izin' => 'required|in:sakit,cuti_tahunan,keperluan_pribadi,darurat,lainnya',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:1000',
            'status' => 'nullable|in:menunggu,disetujui,ditolak',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Check for overlapping leave requests
        $overlapping = LeaveRequest::where('user_id', $request->user_id)
            ->where('status', '!=', 'ditolak')
            ->where(function ($query) use ($request) {
                $query->whereBetween('tanggal_mulai', [$request->tanggal_mulai, $request->tanggal_selesai])
                    ->orWhereBetween('tanggal_selesai', [$request->tanggal_mulai, $request->tanggal_selesai])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('tanggal_mulai', '<=', $request->tanggal_mulai)
                            ->where('tanggal_selesai', '>=', $request->tanggal_selesai);
                    });
            })
            ->exists();

        if ($overlapping) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terdapat izin yang overlap dengan tanggal yang dipilih!'
                ], 422);
            }
            return redirect()->back()->with('error', 'Terdapat izin yang overlap dengan tanggal yang dipilih!');
        }

        $leaveData = $request->only(['user_id', 'jenis_izin', 'tanggal_mulai', 'tanggal_selesai', 'alasan']);
        $leaveData['status'] = $request->status ?? 'menunggu';

        // Handle file upload
        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/leave_attachments'), $fileName);
            $leaveData['lampiran'] = 'uploads/leave_attachments/' . $fileName;
        }

        // Jika langsung disetujui, tambahkan data approval
        if ($leaveData['status'] === 'disetujui') {
            $leaveData['disetujui_oleh'] = auth()->id();
            $leaveData['tanggal_persetujuan'] = now();
        }

        $leaveRequest = LeaveRequest::create($leaveData);

        // Jika langsung disetujui, buat jadwal otomatis
        if ($leaveRequest->status === 'disetujui') {
            $this->createAttendanceForLeave($leaveRequest);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan izin berhasil dibuat!',
                'data' => $leaveRequest
            ]);
        }

        return redirect()->back()->with('success', 'Pengajuan izin berhasil dibuat!');
    }

    /**
     * Update leave request
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'menunggu') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Izin ini tidak dapat diedit karena sudah diproses!'
                ], 422);
            }
            return redirect()->back()->with('error', 'Izin ini tidak dapat diedit karena sudah diproses!');
        }

        $request->validate([
            'jenis_izin' => 'required|in:sakit,cuti_tahunan,keperluan_pribadi,darurat,lainnya',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:1000',
            'status' => 'nullable|in:menunggu,disetujui,ditolak',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $updateData = $request->only(['jenis_izin', 'tanggal_mulai', 'tanggal_selesai', 'alasan']);

        // Cek jika status diubah
        $oldStatus = $leaveRequest->status;
        if ($request->status) {
            $updateData['status'] = $request->status;

            // Jika status berubah menjadi disetujui
            if ($request->status === 'disetujui' && $oldStatus !== 'disetujui') {
                $updateData['disetujui_oleh'] = auth()->id();
                $updateData['tanggal_persetujuan'] = now();
            }
        }

        // Handle file upload
        if ($request->hasFile('lampiran')) {
            // Delete old file
            if ($leaveRequest->lampiran && file_exists(public_path($leaveRequest->lampiran))) {
                unlink(public_path($leaveRequest->lampiran));
            }

            $file = $request->file('lampiran');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/leave_attachments'), $fileName);
            $updateData['lampiran'] = 'uploads/leave_attachments/' . $fileName;
        }

        $leaveRequest->update($updateData);

        // Jika status berubah menjadi disetujui, buat jadwal otomatis
        if (isset($updateData['status']) && $updateData['status'] === 'disetujui' && $oldStatus !== 'disetujui') {
            $this->createAttendanceForLeave($leaveRequest);
        }

        // Jika status berubah dari disetujui ke yang lain, hapus jadwal otomatis
        if (isset($updateData['status']) && $updateData['status'] !== 'disetujui' && $oldStatus === 'disetujui') {
            $this->deleteAttendanceForLeave($leaveRequest);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengajuan izin berhasil diperbarui!'
            ]);
        }

        return redirect()->back()->with('success', 'Pengajuan izin berhasil diperbarui!');
    }

    /**
     * Delete leave request
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        // Hapus jadwal otomatis jika ada
        if ($leaveRequest->status === 'disetujui') {
            $this->deleteAttendanceForLeave($leaveRequest);
        }

        // Delete attachment file if exists
        if ($leaveRequest->lampiran && file_exists(public_path($leaveRequest->lampiran))) {
            unlink(public_path($leaveRequest->lampiran));
        }

        $leaveRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan izin berhasil dihapus!'
        ]);
    }

    /**
     * Approve Izin - Otomatis Buat Jadwal
     */
    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'menunggu') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Izin ini sudah diproses sebelumnya!'
                ], 422);
            }
            return redirect()->back()->with('error', 'Izin ini sudah diproses sebelumnya!');
        }

        $request->validate([
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $leaveRequest->update([
            'status' => 'disetujui',
            'disetujui_oleh' => auth()->id(),
            'catatan_admin' => $request->catatan_admin,
            'tanggal_persetujuan' => now(),
        ]);

        // Auto create attendance records dengan status izin untuk tanggal izin
        $this->createAttendanceForLeave($leaveRequest);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Izin berhasil disetujui dan jadwal otomatis telah dibuat!'
            ]);
        }

        return redirect()->back()->with('success', 'Izin berhasil disetujui dan jadwal otomatis telah dibuat!');
    }

    /**
     * Reject Izin
     */
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'menunggu') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Izin ini sudah diproses sebelumnya!'
                ], 422);
            }
            return redirect()->back()->with('error', 'Izin ini sudah diproses sebelumnya!');
        }

        $request->validate([
            'catatan_admin' => 'required|string|max:500',
        ]);

        $leaveRequest->update([
            'status' => 'ditolak',
            'disetujui_oleh' => auth()->id(),
            'catatan_admin' => $request->catatan_admin,
            'tanggal_persetujuan' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Izin berhasil ditolak!'
            ]);
        }

        return redirect()->back()->with('success', 'Izin berhasil ditolak!');
    }

    /**
     * Batalkan Persetujuan Izin (revert) - Hapus Jadwal Otomatis
     */
    public function cancel(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status === 'menunggu') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Izin masih menunggu persetujuan!'
                ], 422);
            }
            return redirect()->back()->with('error', 'Izin masih menunggu persetujuan!');
        }

        $request->validate([
            'alasan_pembatalan' => 'required|string|max:500',
        ]);

        // Hapus attendance records yang dibuat otomatis jika disetujui
        if ($leaveRequest->status === 'disetujui') {
            $this->deleteAttendanceForLeave($leaveRequest);
        }

        $leaveRequest->update([
            'status' => 'menunggu',
            'disetujui_oleh' => null,
            'catatan_admin' => 'DIBATALKAN: ' . $request->alasan_pembatalan,
            'tanggal_persetujuan' => null,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Persetujuan izin berhasil dibatalkan dan jadwal otomatis telah dihapus!'
            ]);
        }

        return redirect()->back()->with('success', 'Persetujuan izin berhasil dibatalkan dan jadwal otomatis telah dihapus!');
    }

    /**
     * Bulk Action - Approve/Reject Multiple dengan Jadwal Otomatis
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'leave_request_ids' => 'required|array',
            'leave_request_ids.*' => 'exists:leave_requests,id',
            'catatan_admin' => 'nullable|string|max:500',
        ]);

        $leaveRequests = LeaveRequest::whereIn('id', $request->leave_request_ids)
            ->where('status', 'menunggu');

        $count = $leaveRequests->count();

        if ($count === 0) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada izin yang bisa diproses!'
                ], 422);
            }
            return redirect()->back()->with('error', 'Tidak ada izin yang bisa diproses!');
        }

        $updateData = [
            'disetujui_oleh' => auth()->id(),
            'catatan_admin' => $request->catatan_admin,
            'tanggal_persetujuan' => now(),
        ];

        if ($request->action === 'approve') {
            $updateData['status'] = 'disetujui';

            // Get all leave requests before updating
            $leaveRequestList = $leaveRequests->get();

            // Update status
            $leaveRequests->update($updateData);

            // Create attendance for each approved leave
            foreach ($leaveRequestList as $leave) {
                $this->createAttendanceForLeave($leave);
            }

            $message = "$count izin berhasil disetujui dan jadwal otomatis telah dibuat!";
        } else {
            if (empty($request->catatan_admin)) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Catatan admin wajib diisi untuk reject!'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Catatan admin wajib diisi untuk reject!');
            }

            $updateData['status'] = 'ditolak';
            $leaveRequests->update($updateData);
            $message = "$count izin berhasil ditolak!";
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
     * Export Izin ke Excel/CSV
     */
    public function export(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status' => 'nullable|in:menunggu,disetujui,ditolak',
            'jenis_izin' => 'nullable|in:sakit,cuti_tahunan,keperluan_pribadi,darurat,lainnya',
        ]);

        $query = LeaveRequest::with(['user', 'approver']);

        if ($request->tanggal_mulai && $request->tanggal_selesai) {
            $query->whereBetween('created_at', [$request->tanggal_mulai, $request->tanggal_selesai]);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->jenis_izin) {
            $query->where('jenis_izin', $request->jenis_izin);
        }

        $izin = $query->orderBy('created_at', 'desc')->get();

        return $this->exportToCsv($izin, $request->tanggal_mulai ?? 'semua', $request->tanggal_selesai ?? 'semua');
    }

    /**
     * Export ke CSV
     */
    private function exportToCsv($izin, $tanggalMulai, $tanggalSelesai)
    {
        $filename = 'izin_' . $tanggalMulai . '_' . $tanggalSelesai . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($izin) {
            $file = fopen('php://output', 'w');

            // Header CSV
            fputcsv($file, [
                'Tanggal Pengajuan',
                'ID Karyawan',
                'Nama Karyawan',
                'Jenis Izin',
                'Tanggal Mulai',
                'Tanggal Selesai',
                'Total Hari',
                'Alasan',
                'Status',
                'Disetujui Oleh',
                'Tanggal Persetujuan',
                'Catatan Admin',
                'Jadwal Otomatis'
            ]);

            // Data
            foreach ($izin as $data) {
                $jadwalOtomatis = 'Tidak';
                if ($data->status === 'disetujui') {
                    // Cek apakah ada jadwal otomatis
                    $attendanceCount = Attendance::where('user_id', $data->user_id)
                        ->whereBetween('tanggal_absen', [$data->tanggal_mulai, $data->tanggal_selesai])
                        ->where('status_absen', 'izin')
                        ->where('catatan_admin', 'like', 'Auto: Izin%')
                        ->count();

                    if ($attendanceCount > 0) {
                        $jadwalOtomatis = 'Ya (' . $attendanceCount . ' hari)';
                    }
                }

                fputcsv($file, [
                    $data->created_at->format('Y-m-d H:i:s'),
                    $data->user->id_karyawan,
                    $data->user->name,
                    $data->getJenisIzinText(),
                    $data->tanggal_mulai->format('Y-m-d'),
                    $data->tanggal_selesai->format('Y-m-d'),
                    $data->total_hari,
                    $data->alasan,
                    $data->getStatusText(),
                    $data->approver ? $data->approver->name : '',
                    $data->tanggal_persetujuan ? $data->tanggal_persetujuan->format('Y-m-d H:i:s') : '',
                    $data->catatan_admin,
                    $jadwalOtomatis
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Auto create attendance dengan status izin - Integrasi Jadwal
     */
    private function createAttendanceForLeave(LeaveRequest $leaveRequest)
    {
        $startDate = $leaveRequest->tanggal_mulai;
        $endDate = $leaveRequest->tanggal_selesai;
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            // Skip weekend jika diperlukan (opsional)
            if ($currentDate->isWeekend()) {
                $currentDate->addDay();
                continue;
            }

            Attendance::updateOrCreate([
                'user_id' => $leaveRequest->user_id,
                'tanggal_absen' => $currentDate,
            ], [
                'shift_id' => $leaveRequest->user->shift_id ?? 1, // Default shift
                'status_absen' => 'izin',
                'status_masuk' => 'disetujui',
                'status_keluar' => 'disetujui',
                'jam_masuk' => null,
                'jam_keluar' => null,
                'terlambat_menit' => 0,
                'pulang_cepat_menit' => 0,
                'catatan_admin' => 'Auto: Izin ' . $leaveRequest->getJenisIzinText() . ' (ID: ' . $leaveRequest->id . ')',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $currentDate->addDay();
        }
    }

    /**
     * Delete attendance untuk izin yang dibatalkan - Cleanup Jadwal
     */
    private function deleteAttendanceForLeave(LeaveRequest $leaveRequest)
    {
        Attendance::where('user_id', $leaveRequest->user_id)
            ->whereBetween('tanggal_absen', [$leaveRequest->tanggal_mulai, $leaveRequest->tanggal_selesai])
            ->where('status_absen', 'izin')
            ->where('catatan_admin', 'like', 'Auto: Izin%')
            ->where('catatan_admin', 'like', '%ID: ' . $leaveRequest->id . '%')
            ->delete();
    }
}