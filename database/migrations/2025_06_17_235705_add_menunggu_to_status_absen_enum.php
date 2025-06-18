<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'menunggu' to status_absen ENUM - more logical for scheduled but not yet attended
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status_absen ENUM(
            'menunggu',
            'hadir', 
            'terlambat', 
            'tidak_hadir', 
            'izin'
        ) NOT NULL DEFAULT 'menunggu'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update existing 'menunggu' records to 'tidak_hadir' before reverting
        DB::statement("UPDATE attendances SET status_absen = 'tidak_hadir' WHERE status_absen = 'menunggu'");

        // Revert to original ENUM values
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status_absen ENUM(
            'hadir', 
            'terlambat', 
            'tidak_hadir', 
            'izin'
        ) NOT NULL DEFAULT 'tidak_hadir'");
    }
};