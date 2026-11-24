<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesananItem extends Model
{
    protected $table = 'pesanan_items';

    /**
     * Order items are immutable snapshots — no created_at / updated_at needed.
     */
    public $timestamps = false;

    protected $fillable = [
        'pesanan_id',
        'menu_id',
        'nama_menu',
        'harga',
        'qty',
        'catatan',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Get the line-item subtotal (harga × qty).
     */
    public function getSubtotalAttribute(): int
    {
        return (int) $this->harga * (int) $this->qty;
    }
}
