<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesananSplit extends Model
{
    protected $table = 'pesanan_splits';

    protected $fillable = [
        'pesanan_id',
        'label',
        'tipe',
        'jumlah',
        'metode_pembayaran',
        'status',
        'items_json',
        'paid_at',
    ];

    protected $casts = [
        'jumlah'     => 'integer',
        'items_json' => 'array',
        'paid_at'    => 'datetime',
    ];

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }
}
