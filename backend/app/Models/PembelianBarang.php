<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianBarang extends Model
{
    use HasFactory;

    protected $table = 'pembelian_barangs';

    protected $fillable = [
        'stok_barang_id',
        'jumlah',
        'total_biaya',
        'supplier',
        'tanggal_pembelian',
        'foto_nota',
        'catatan',
    ];

    public function barang()
    {
        return $this->belongsTo(StokBarang::class, 'stok_barang_id');
    }
}
