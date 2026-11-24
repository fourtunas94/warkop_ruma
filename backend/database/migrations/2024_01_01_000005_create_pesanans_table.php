<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pesanan', 20)->unique();
            $table->string('nomor_antrian', 10)->nullable();
            $table->foreignId('meja_id')->constrained('mejas')->cascadeOnDelete();
            $table->string('nama_pemesan', 100);
            $table->integer('total_harga')->default(0);
            $table->enum('status', ['pending', 'selesai'])->default('pending');
            $table->enum('status_pesanan', ['menunggu', 'siap', 'selesai'])->default('menunggu');
            $table->timestamp('waktu_siap')->nullable();
            $table->text('catatan')->nullable();
            $table->string('metode_pembayaran', 50)->default('tunai');
            $table->string('kode_promo', 50)->nullable();
            $table->integer('potongan')->default(0);
            $table->integer('ppn')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanans');
    }
};
