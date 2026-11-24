<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('nama_promo', 150);
            $table->string('deskripsi', 255)->nullable();
            $table->string('tipe_diskon', 20)->default('nominal');
            $table->integer('nilai_diskon')->default(0);
            $table->integer('maksimal_diskon')->default(0);
            $table->integer('minimal_belanja')->default(0);
            $table->integer('kuota')->default(0);
            $table->integer('jumlah_digunakan')->default(0);
            $table->timestamp('berlaku_mulai')->nullable();
            $table->timestamp('berlaku_sampai')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};
