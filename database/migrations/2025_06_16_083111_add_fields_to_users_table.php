<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('id_karyawan')->unique()->after('id');
            $table->string('no_hp')->nullable()->after('email');
            $table->enum('role', ['admin', 'karyawan'])->default('karyawan')->after('no_hp');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('role');
            $table->unsignedBigInteger('shift_id')->nullable()->after('status');
            $table->date('tanggal_masuk')->nullable()->after('shift_id');
            $table->text('alamat')->nullable()->after('tanggal_masuk');
            $table->string('foto')->nullable()->after('alamat');

            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn([
                'id_karyawan',
                'no_hp',
                'role',
                'status',
                'shift_id',
                'tanggal_masuk',
                'alamat',
                'foto'
            ]);
        });
    }
};