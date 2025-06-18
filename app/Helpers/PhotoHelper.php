<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class PhotoHelper
{
    /**
     * Upload foto user ke public directory
     */
    public static function uploadUserPhoto(UploadedFile $file, $idKaryawan)
    {
        $uploadPath = public_path('uploads/users');


        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }


        $fileName = time() . '_' . $idKaryawan . '.' . $file->getClientOriginalExtension();


        $file->move($uploadPath, $fileName);

        return 'uploads/users/' . $fileName;
    }

    /**
     * Upload foto absensi ke public directory
     */
    public static function uploadAttendancePhoto(UploadedFile $file, $userId, $type = 'masuk')
    {
        $uploadPath = public_path('uploads/attendance');


        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }


        $fileName = date('Y-m-d') . '_' . $userId . '_' . $type . '_' . time() . '.' . $file->getClientOriginalExtension();


        $file->move($uploadPath, $fileName);

        return 'uploads/attendance/' . $fileName;
    }

    /**
     * Upload lampiran izin ke public directory
     */
    public static function uploadLeaveAttachment(UploadedFile $file, $userId)
    {
        $uploadPath = public_path('uploads/leave');


        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true);
        }


        $fileName = date('Y-m-d') . '_' . $userId . '_' . time() . '.' . $file->getClientOriginalExtension();


        $file->move($uploadPath, $fileName);

        return 'uploads/leave/' . $fileName;
    }

    /**
     * Hapus foto dari public directory
     */
    public static function deletePhoto($filePath)
    {
        if ($filePath && File::exists(public_path($filePath))) {
            File::delete(public_path($filePath));
            return true;
        }
        return false;
    }

    /**
     * Get URL foto dari path
     */
    public static function getPhotoUrl($filePath, $defaultPath = 'images/default-avatar.png')
    {
        if ($filePath && File::exists(public_path($filePath))) {
            return asset($filePath);
        }
        return asset($defaultPath);
    }
}