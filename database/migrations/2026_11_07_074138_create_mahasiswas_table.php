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
        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institusi_id')->constrained('institusis')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('nomor_induk_mahasiswa', 50);
            $table->string('nama_lengkap', 150);
            $table->timestamps();

            // Aturan Integritas ERD: Unique (institusi_id, nomor_induk_mahasiswa)
            $table->unique(['institusi_id', 'nomor_induk_mahasiswa'], 'uq_mahasiswa_institusi_nim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswas');
    }
};
