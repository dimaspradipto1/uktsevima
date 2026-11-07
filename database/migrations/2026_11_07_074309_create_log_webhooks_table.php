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
        Schema::create('log_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institusi_id')->nullable()->constrained('institusis')->nullOnDelete();
            $table->foreignId('transaksi_pembayaran_id')->nullable()->constrained('transaksi_pembayarans')->nullOnDelete();

            $table->string('kode_institusi_diterima', 50)->nullable();
            $table->string('gateway_event_id', 100)->nullable();
            $table->string('jenis_event', 50)->nullable(); // payment.success, payment.expired, dll
            $table->text('signature_diterima')->nullable();
            $table->boolean('signature_valid')->default(false);
            $table->text('payload_asli')->nullable(); // Raw payload untuk verifikasi HMAC
            $table->json('payload_json')->nullable();
            $table->string('status_pemprosesan', 30)->default('diterima'); // diterima, diproses, ditolak, gagal, duplikat
            $table->smallInteger('kode_respons_http')->nullable(); // 200, 400, 401, 500
            $table->string('alamat_ip', 45)->nullable();
            $table->text('pesan_kesalahan')->nullable();
            $table->dateTime('diterima_pada')->nullable();
            $table->dateTime('diproses_pada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_webhooks');
    }
};
