<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meja extends Model
{
    protected $table = 'mejas';

    protected $fillable = [
        'nomor_meja',
        'kode_unik',
        'is_active',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function pesanans(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'meja_id');
    }

    // =========================================================================
    // Static Helpers
    // =========================================================================

    /**
     * Find an active table by its unique QR code.
     */
    public static function findByKode(string $kode): ?self
    {
        return static::where('kode_unik', $kode)
            ->where('is_active', 1)
            ->first();
    }
}
