<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
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
     * Update profile data (tanpa foto)
     */
    public function updateData(Request $request)
    {
        try {

            Log::info('=== UPDATE PROFILE DATA DEBUG ===');
            Log::info('Method: ' . $request->method());
            Log::info('Content-Type: ' . $request->header('Content-Type'));
            Log::info('All Input: ', $request->all());
            Log::info('Raw Input: ' . $request->getContent());
            Log::info('Request Keys: ', array_keys($request->all()));


            Log::info('name field: ' . ($request->has('name') ? $request->name : 'NOT FOUND'));
            Log::info('email field: ' . ($request->has('email') ? $request->email : 'NOT FOUND'));
            Log::info('no_hp field: ' . ($request->has('no_hp') ? $request->no_hp : 'NOT FOUND'));
            Log::info('alamat field: ' . ($request->has('alamat') ? $request->alamat : 'NOT FOUND'));

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:2|max:255',
                'email' => 'required|email|unique:users,email,' . Auth::id(),
                'no_hp' => 'nullable|string|min:10|max:15',
                'alamat' => 'nullable|string|max:500',
            ], [
                'name.required' => 'Nama tidak boleh kosong',
                'name.min' => 'Nama minimal 2 karakter',
                'email.required' => 'Email tidak boleh kosong',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah digunakan',
                'no_hp.min' => 'Nomor HP minimal 10 digit',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed: ', $validator->errors()->toArray());
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
            $user->save();

            Log::info('Profile data updated successfully for user: ' . $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Data profil berhasil diperbarui',
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
            Log::error('Update profile data exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update foto profil
     */
    public function updatePhoto(Request $request)
    {
        try {
            Log::info('=== UPDATE PROFILE PHOTO DEBUG ===');
            Log::info('Method: ' . $request->method());
            Log::info('Has File foto: ' . ($request->hasFile('foto') ? 'YES' : 'NO'));

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                Log::info('Photo info: ' . $file->getClientOriginalName() . ' (' . $file->getSize() . ' bytes)');
            }

            $validator = Validator::make($request->all(), [
                'foto' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            ], [
                'foto.required' => 'Foto tidak boleh kosong',
                'foto.image' => 'File harus berupa gambar',
                'foto.mimes' => 'Format gambar harus jpeg, jpg, atau png',
                'foto.max' => 'Ukuran gambar maksimal 2MB',
            ]);

            if ($validator->fails()) {
                Log::error('Photo validation failed: ', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Foto tidak valid',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();


            if ($user->foto) {
                PhotoHelper::deletePhoto($user->foto);
                Log::info('Old photo deleted: ' . $user->foto);
            }


            $photoPath = PhotoHelper::uploadUserPhoto(
                $request->file('foto'),
                $user->id_karyawan
            );

            $user->foto = $photoPath;
            $user->save();

            Log::info('Photo uploaded successfully: ' . $photoPath);

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui',
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
            Log::error('Update photo exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload foto profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update lengkap (data + foto) - untuk backward compatibility
     */
    public function update(Request $request)
    {
        try {
            Log::info('=== UPDATE PROFILE COMPLETE DEBUG ===');
            Log::info('Method: ' . $request->method());
            Log::info('Content-Type: ' . $request->header('Content-Type'));
            Log::info('All Input: ', $request->all());
            Log::info('Has File foto: ' . ($request->hasFile('foto') ? 'YES' : 'NO'));


            if ($request->has('_method')) {
                Log::info('Method override: ' . $request->_method);
            }

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
                Log::error('Complete update validation failed: ', $validator->errors()->toArray());
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
                    Log::info('Photo updated in complete update: ' . $photoPath);

                } catch (\Exception $e) {
                    Log::error('Photo upload failed in complete update: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mengupload foto profil',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }

            $user->save();
            Log::info('Complete profile update successful for user: ' . $user->id);

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
            Log::error('Complete update exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}