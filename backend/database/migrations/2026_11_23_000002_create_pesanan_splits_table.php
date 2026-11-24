<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanans')->cascadeOnDelete();
            $table->string('label', 100);
            $table->string('tipe', 20)->default('rata'); // rata, item, nominal
            $table->integer('jumlah');
            $table->string('metode_pembayaran', 50)->default('tunai');
            $table->string('status', 20)->default('pending'); // pending, lunas
            $table->json('items_json')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_splits');
    }
};
