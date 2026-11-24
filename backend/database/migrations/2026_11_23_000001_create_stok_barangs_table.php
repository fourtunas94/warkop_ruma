<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_barangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_barang');
            $table->string('kategori')->default('Bahan Baku');
            $table->double('stok')->default(0);
            $table->string('satuan')->default('pcs');
            $table->double('minimum_stok')->default(5);
            $table->double('harga_beli_terakhir')->default(0);
            $table->string('supplier')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('pembelian_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stok_barang_id')->constrained('stok_barangs')->onDelete('cascade');
            $table->double('jumlah');
            $table->double('total_biaya')->default(0);
            $table->string('supplier')->nullable();
            $table->date('tanggal_pembelian');
            $table->string('foto_nota')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian_barangs');
        Schema::dropIfExists('stok_barangs');
    }
};
