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
        Schema::create('transaksi_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institusi_id')->constrained('institusis')->cascadeOnDelete();
            $table->foreignId('tagihan_ukt_id')->constrained('tagihan_ukts')->cascadeOnDelete();

            $table->string('gateway_transaction_id', 100)->nullable();
            $table->unsignedBigInteger('nominal_rupiah');
            $table->string('metode_pembayaran', 50)->nullable(); // VA, QRIS, Retail, Transfer, Tunai
            $table->string('status', 20)->default('menunggu'); // menunggu, berhasil, gagal, kedaluwarsa
            $table->dateTime('dibayar_pada')->nullable();
            $table->timestamps();

            // Aturan Integritas ERD: gateway_transaction_id unik per institusi
            $table->unique(['institusi_id', 'gateway_transaction_id'], 'uq_transaksi_institusi_gateway');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_pembayarans');
    }
};
