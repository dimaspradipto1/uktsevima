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
        Schema::create('periode_akademiks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institusi_id')->constrained('institusis')->cascadeOnDelete();
            $table->string('tahun_akademik', 9);
            $table->string('semester', 20);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->timestamps();

            // Aturan Integritas ERD: Unique (institusi_id, tahun_akademik, semester)
            $table->unique(['institusi_id', 'tahun_akademik', 'semester'], 'uq_periode_institusi_tahun_smt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode_akademiks');
    }
};
