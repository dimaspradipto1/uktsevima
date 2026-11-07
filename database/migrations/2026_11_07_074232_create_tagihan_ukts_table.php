<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tagihan_ukts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institusi_id')->constrained('institusis')->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->foreignId('periode_akademik_id')->constrained('periode_akademiks')->cascadeOnDelete();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();

            $table->string('nomor_tagihan', 100);
            $table->unsignedBigInteger('nominal_tagihan');
            $table->string('status', 20)->default('belum_bayar'); // belum_bayar, dicicil, lunas, kadaluarsa
            $table->dateTime('diterbitkan_pada');
            $table->dateTime('tanggal_jatuh_tempo');
            $table->dateTime('dibayar_pada')->nullable();
            $table->timestamps();

            // Aturan Integritas ERD:
            // 1. Keunikan nomor tagihan per institusi
            $table->unique(['institusi_id', 'nomor_tagihan'], 'uq_tagihan_institusi_nomor');

            // 2. Keunikan Tagihan: 1 Mahasiswa hanya punya 1 tagihan per periode semester di institusi
            $table->unique(['institusi_id', 'mahasiswa_id', 'periode_akademik_id'], 'uq_tagihan_inst_mhs_periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_ukts');
    }
};
