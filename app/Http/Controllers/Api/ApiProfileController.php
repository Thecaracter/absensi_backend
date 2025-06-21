<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Helpers\PhotoHelper;

class ApiProfileController extends Controller
{
    /**
     * Get user profile
     */
    public function show()
    {
        try {
            $user = Auth::user();

            return response()->json([
                'success' => true,
                'message' => 'Data profil berhasil diambil',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'no_hp' => $user->no_hp,
                    'alamat' => $user->alamat,
                    'id_karyawan' => $user->id_karyawan,
                    'tanggal_masuk' => $user->tanggal_masuk,
                    'foto_url' => $user->foto_url,
                    'role' => $user->role,
                    'status' => $user->status,
                    'shift' => $user->shift ? [
                        'id' => $user->shift->id,
                        'nama' => $user->shift->nama
                    ] : null,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:2|max:255',
                'email' => 'required|email|unique:users,email,' . Auth::id(),
                'no_hp' => 'nullable|string|min:10|max:15',
                'alamat' => 'nullable|string|max:500',
                'foto' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            ], [
                'name.required' => 'Nama tidak boleh kosong',
                'name.min' => 'Nama minimal 2 karakter',
                'email.required' => 'Email tidak boleh kosong',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah digunakan',
                'no_hp.min' => 'Nomor HP minimal 10 digit',
                'foto.image' => 'File harus berupa gambar',
                'foto.mimes' => 'Format gambar harus jpeg, jpg, atau png',
                'foto.max' => 'Ukuran gambar maksimal 2MB',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();


            $user->name = $request->name;
            $user->email = $request->email;
            $user->no_hp = $request->no_hp;
            $user->alamat = $request->alamat;


            if ($request->hasFile('foto')) {
                try {

                    if ($user->foto) {
                        PhotoHelper::deletePhoto($user->foto);
                    }


                    $photoPath = PhotoHelper::uploadUserPhoto(
                        $request->file('foto'),
                        $user->id_karyawan
                    );

                    $user->foto = $photoPath;

                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mengupload foto profil',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }


            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'no_hp' => $user->no_hp,
                    'alamat' => $user->alamat,
                    'id_karyawan' => $user->id_karyawan,
                    'tanggal_masuk' => $user->tanggal_masuk,
                    'foto_url' => $user->foto_url,
                    'role' => $user->role,
                    'status' => $user->status,
                    'updated_at' => $user->updated_at,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}