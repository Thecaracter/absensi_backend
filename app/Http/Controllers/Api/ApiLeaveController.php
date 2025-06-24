<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ApiLeaveController extends Controller
{
    /**
     * GET DAFTAR PENGAJUAN IZIN
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            $limit = $request->limit ?? 20;
            $page = $request->page ?? 1;
            $status = $request->status;
            $tahun = $request->tahun ?? now()->year;

            $query = LeaveRequest::where('user_id', $user->id)
                ->with('approver')
                ->whereYear('tanggal_mulai', $tahun)
                ->orderBy('created_at', 'desc');


            if ($status) {
                $query->where('status', $status);
            }

            $leaveRequests = $query->paginate($limit, ['*'], 'page', $page);

            $leaveData = collect($leaveRequests->items())->map(function ($leave) {
                return [
                    'id' => $leave->id,
                    'jenis_izin' => $leave->jenis_izin,
                    'jenis_izin_text' => $leave->getJenisIzinText(),
                    'tanggal_mulai' => $leave->tanggal_mulai->format('Y-m-d'),
                    'tanggal_selesai' => $leave->tanggal_selesai->format('Y-m-d'),
                    'tanggal_mulai_formatted' => $leave->tanggal_mulai->format('d/m/Y'),
                    'tanggal_selesai_formatted' => $leave->tanggal_selesai->format('d/m/Y'),
                    'total_hari' => $leave->total_hari,
                    'durasi_text' => $leave->getDurasiText(),
                    'alasan' => $leave->alasan,
                    'status' => $leave->status,
                    'status_text' => $leave->getStatusText(),
                    'lampiran_url' => $leave->lampiran_url,
                    'tanggal_pengajuan' => $leave->created_at->format('Y-m-d H:i:s'),
                    'tanggal_pengajuan_formatted' => $leave->created_at->format('d/m/Y H:i'),
                    'disetujui_oleh' => $leave->approver ? $leave->approver->name : null,
                    'tanggal_persetujuan' => $leave->tanggal_persetujuan ? $leave->tanggal_persetujuan->format('Y-m-d H:i:s') : null,
                    'tanggal_persetujuan_formatted' => $leave->tanggal_persetujuan ? $leave->tanggal_persetujuan->format('d/m/Y H:i') : null,
                    'catatan_admin' => $leave->catatan_admin,
                    'bisa_diedit' => $leave->bisaDiedit(),
                    'bisa_dibatalkan' => $leave->bisaDibatalkan(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Daftar pengajuan izin berhasil diambil',
                'data' => $leaveData,
                'pagination' => [
                    'current_page' => $leaveRequests->currentPage(),
                    'last_page' => $leaveRequests->lastPage(),
                    'per_page' => $leaveRequests->perPage(),
                    'total' => $leaveRequests->total(),
                    'from' => $leaveRequests->firstItem(),
                    'to' => $leaveRequests->lastItem(),
                ]
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
     * BUAT PENGAJUAN IZIN BARU
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'jenis_izin' => 'required|in:sakit,cuti_tahunan,keperluan_pribadi,darurat,lainnya',
                'tanggal_mulai' => 'required|date|after_or_equal:today',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'alasan' => 'required|string|max:1000',
                'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();


            $overlapping = LeaveRequest::where('user_id', $user->id)
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
                return response()->json([
                    'success' => false,
                    'message' => 'Terdapat izin yang overlap dengan tanggal yang dipilih!'
                ], 422);
            }

            $leaveData = [
                'user_id' => $user->id,
                'jenis_izin' => $request->jenis_izin,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'alasan' => $request->alasan,
                'status' => 'menunggu',
            ];


            if ($request->hasFile('lampiran')) {
                $lampiranPath = $this->uploadLampiran($request->file('lampiran'), $user->id);
                $leaveData['lampiran'] = $lampiranPath;
            }

            $leaveRequest = LeaveRequest::create($leaveData);


            $responseData = [
                'id' => $leaveRequest->id,
                'jenis_izin' => $leaveRequest->jenis_izin,
                'jenis_izin_text' => $leaveRequest->getJenisIzinText(),
                'tanggal_mulai' => $leaveRequest->tanggal_mulai->format('Y-m-d'),
                'tanggal_selesai' => $leaveRequest->tanggal_selesai->format('Y-m-d'),
                'total_hari' => $leaveRequest->total_hari,
                'alasan' => $leaveRequest->alasan,
                'status' => $leaveRequest->status,
                'lampiran_url' => $leaveRequest->lampiran_url,
                'tanggal_pengajuan' => $leaveRequest->created_at->format('Y-m-d H:i:s'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan izin berhasil dibuat',
                'data' => $responseData
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
     * GET DETAIL PENGAJUAN IZIN
     */
    public function show($id)
    {
        try {
            $user = Auth::user();

            $leaveRequest = LeaveRequest::where('id', $id)
                ->where('user_id', $user->id)
                ->with('approver')
                ->first();

            if (!$leaveRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan izin tidak ditemukan'
                ], 404);
            }

            $leaveData = [
                'id' => $leaveRequest->id,
                'jenis_izin' => $leaveRequest->jenis_izin,
                'jenis_izin_text' => $leaveRequest->getJenisIzinText(),
                'tanggal_mulai' => $leaveRequest->tanggal_mulai->format('Y-m-d'),
                'tanggal_selesai' => $leaveRequest->tanggal_selesai->format('Y-m-d'),
                'tanggal_mulai_formatted' => $leaveRequest->tanggal_mulai->format('d/m/Y'),
                'tanggal_selesai_formatted' => $leaveRequest->tanggal_selesai->format('d/m/Y'),
                'total_hari' => $leaveRequest->total_hari,
                'durasi_text' => $leaveRequest->getDurasiText(),
                'alasan' => $leaveRequest->alasan,
                'status' => $leaveRequest->status,
                'status_text' => $leaveRequest->getStatusText(),
                'lampiran_url' => $leaveRequest->lampiran_url,
                'tanggal_pengajuan' => $leaveRequest->created_at->format('Y-m-d H:i:s'),
                'tanggal_pengajuan_formatted' => $leaveRequest->created_at->format('d/m/Y H:i'),
                'disetujui_oleh' => $leaveRequest->approver ? $leaveRequest->approver->name : null,
                'tanggal_persetujuan' => $leaveRequest->tanggal_persetujuan ? $leaveRequest->tanggal_persetujuan->format('Y-m-d H:i:s') : null,
                'tanggal_persetujuan_formatted' => $leaveRequest->tanggal_persetujuan ? $leaveRequest->tanggal_persetujuan->format('d/m/Y H:i') : null,
                'catatan_admin' => $leaveRequest->catatan_admin,
                'bisa_diedit' => $leaveRequest->bisaDiedit(),
                'bisa_dibatalkan' => $leaveRequest->bisaDibatalkan(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Detail pengajuan izin berhasil diambil',
                'data' => $leaveData
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
     * UPDATE PENGAJUAN IZIN (hanya yang masih menunggu)
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();

            $leaveRequest = LeaveRequest::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$leaveRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan izin tidak ditemukan'
                ], 404);
            }

            if ($leaveRequest->status !== 'menunggu') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan izin ini tidak dapat diedit karena sudah diproses'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'jenis_izin' => 'required|in:sakit,cuti_tahunan,keperluan_pribadi,darurat,lainnya',
                'tanggal_mulai' => 'required|date|after_or_equal:today',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'alasan' => 'required|string|max:1000',
                'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }


            $overlapping = LeaveRequest::where('user_id', $user->id)
                ->where('id', '!=', $id)
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
                return response()->json([
                    'success' => false,
                    'message' => 'Terdapat izin yang overlap dengan tanggal yang dipilih!'
                ], 422);
            }

            $updateData = [
                'jenis_izin' => $request->jenis_izin,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'alasan' => $request->alasan,
            ];


            if ($request->hasFile('lampiran')) {

                if ($leaveRequest->lampiran && File::exists(public_path($leaveRequest->lampiran))) {
                    File::delete(public_path($leaveRequest->lampiran));
                }

                $lampiranPath = $this->uploadLampiran($request->file('lampiran'), $user->id);
                $updateData['lampiran'] = $lampiranPath;
            }

            $leaveRequest->update($updateData);


            $responseData = [
                'id' => $leaveRequest->id,
                'jenis_izin' => $leaveRequest->jenis_izin,
                'jenis_izin_text' => $leaveRequest->getJenisIzinText(),
                'tanggal_mulai' => $leaveRequest->tanggal_mulai->format('Y-m-d'),
                'tanggal_selesai' => $leaveRequest->tanggal_selesai->format('Y-m-d'),
                'total_hari' => $leaveRequest->total_hari,
                'alasan' => $leaveRequest->alasan,
                'status' => $leaveRequest->status,
                'lampiran_url' => $leaveRequest->lampiran_url,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan izin berhasil diperbarui',
                'data' => $responseData
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
     * HAPUS PENGAJUAN IZIN (hanya yang masih menunggu)
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();

            $leaveRequest = LeaveRequest::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$leaveRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan izin tidak ditemukan'
                ], 404);
            }

            if ($leaveRequest->status !== 'menunggu') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengajuan izin ini tidak dapat dihapus karena sudah diproses'
                ], 422);
            }


            if ($leaveRequest->lampiran && File::exists(public_path($leaveRequest->lampiran))) {
                File::delete(public_path($leaveRequest->lampiran));
            }

            $leaveRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan izin berhasil dihapus'
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
     * GET STATISTIK IZIN KARYAWAN
     */
    public function stats()
    {
        try {
            $user = Auth::user();
            $currentYear = now()->year;

            $stats = [
                'total_pengajuan' => LeaveRequest::where('user_id', $user->id)
                    ->whereYear('created_at', $currentYear)
                    ->count(),
                'menunggu' => LeaveRequest::where('user_id', $user->id)
                    ->whereYear('created_at', $currentYear)
                    ->where('status', 'menunggu')
                    ->count(),
                'disetujui' => LeaveRequest::where('user_id', $user->id)
                    ->whereYear('created_at', $currentYear)
                    ->where('status', 'disetujui')
                    ->count(),
                'ditolak' => LeaveRequest::where('user_id', $user->id)
                    ->whereYear('created_at', $currentYear)
                    ->where('status', 'ditolak')
                    ->count(),
                'total_hari_izin' => LeaveRequest::where('user_id', $user->id)
                    ->whereYear('created_at', $currentYear)
                    ->where('status', 'disetujui')
                    ->sum('total_hari'),
            ];


            $kuotaCuti = 12;
            $stats['kuota_cuti'] = $kuotaCuti;
            $stats['sisa_kuota'] = max(0, $kuotaCuti - $stats['total_hari_izin']);

            return response()->json([
                'success' => true,
                'message' => 'Statistik izin berhasil diambil',
                'data' => $stats
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
     * UPLOAD LAMPIRAN HELPER
     */
    private function uploadLampiran($file, $userId)
    {
        $uploadPath = public_path('uploads/leave_attachments');

        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }

        $fileName = date('Y-m-d') . '_' . $userId . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($uploadPath, $fileName);

        return 'uploads/leave_attachments/' . $fileName;
    }
}