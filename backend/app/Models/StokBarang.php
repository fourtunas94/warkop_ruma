<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokBarang extends Model
{
    use HasFactory;

    protected $table = 'stok_barangs';

    protected $fillable = [
        'nama_barang',
        'kategori',
        'stok',
        'satuan',
        'minimum_stok',
        'harga_beli_terakhir',
        'supplier',
        'catatan',
    ];

    public function pembelians()
    {
        return $this->hasMany(PembelianBarang::class, 'stok_barang_id');
    }
}
