<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiAuthController extends Controller
{
    /**
     * LOGIN KARYAWAN
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }


            $user = User::where('email', $request->email)
                ->where('role', 'karyawan')
                ->where('status', 'aktif')
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah'
                ], 401);
            }


            $user->tokens()->delete();


            $token = $user->createToken('mobile-app')->plainTextToken;


            $latestAttendance = $user->attendances()->with('shift')->latest('tanggal_absen')->first();

            $userData = [
                'id' => $user->id,
                'id_karyawan' => $user->id_karyawan,
                'name' => $user->name,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'alamat' => $user->alamat,
                'tanggal_masuk' => $user->tanggal_masuk ? $user->tanggal_masuk->format('Y-m-d') : null,
                'foto_url' => $user->foto_url,
                'shift' => null
            ];


            if ($latestAttendance && $latestAttendance->shift) {
                $userData['shift'] = [
                    'id' => $latestAttendance->shift->id,
                    'nama' => $latestAttendance->shift->nama,
                    'jam_masuk' => $latestAttendance->shift->jam_masuk->format('H:i'),
                    'jam_keluar' => $latestAttendance->shift->jam_keluar->format('H:i'),
                    'toleransi_menit' => $latestAttendance->shift->toleransi_menit
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => $userData,
                    'token' => $token,
                    'token_type' => 'Bearer'
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
     * LOGOUT KARYAWAN
     */
    public function logout(Request $request)
    {
        try {

            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil'
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
     * GET USER INFO (ME)
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();


            $latestAttendance = $user->attendances()->with('shift')->latest('tanggal_absen')->first();

            $userData = [
                'id' => $user->id,
                'id_karyawan' => $user->id_karyawan,
                'name' => $user->name,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'alamat' => $user->alamat,
                'tanggal_masuk' => $user->tanggal_masuk ? $user->tanggal_masuk->format('Y-m-d') : null,
                'foto_url' => $user->foto_url,
                'shift' => null
            ];


            if ($latestAttendance && $latestAttendance->shift) {
                $userData['shift'] = [
                    'id' => $latestAttendance->shift->id,
                    'nama' => $latestAttendance->shift->nama,
                    'jam_masuk' => $latestAttendance->shift->jam_masuk->format('H:i'),
                    'jam_keluar' => $latestAttendance->shift->jam_keluar->format('H:i'),
                    'toleransi_menit' => $latestAttendance->shift->toleransi_menit
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Data user berhasil diambil',
                'data' => $userData
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
     * REFRESH TOKEN
     */
    public function refreshToken(Request $request)
    {
        try {
            $user = $request->user();


            $request->user()->currentAccessToken()->delete();


            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Token berhasil diperbarui',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer'
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
}