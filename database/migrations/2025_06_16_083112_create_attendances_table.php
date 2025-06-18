<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shift_id');
            $table->date('tanggal_absen');


            $table->time('jam_masuk')->nullable();
            $table->string('foto_masuk')->nullable();
            $table->decimal('latitude_masuk', 10, 8)->nullable();
            $table->decimal('longitude_masuk', 11, 8)->nullable();
            $table->enum('status_masuk', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');


            $table->time('jam_keluar')->nullable();
            $table->string('foto_keluar')->nullable();
            $table->decimal('latitude_keluar', 10, 8)->nullable();
            $table->decimal('longitude_keluar', 11, 8)->nullable();
            $table->enum('status_keluar', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');


            $table->enum('status_absen', ['hadir', 'terlambat', 'tidak_hadir', 'izin'])->default('tidak_hadir');
            $table->integer('menit_terlambat')->default(0);
            $table->integer('menit_lembur')->default(0);
            $table->text('catatan_admin')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');


            $table->unique(['user_id', 'tanggal_absen']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};