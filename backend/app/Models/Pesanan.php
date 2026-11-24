<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesanan extends Model
{
    protected $table = 'pesanans';

    protected $fillable = [
        'kode_pesanan',
        'nomor_antrian',
        'meja_id',
        'nama_pemesan',
        'total_harga',
        'status',
        'status_pesanan',
        'waktu_siap',
        'catatan',
        'metode_pembayaran',
        'kode_promo',
        'potongan',
        'ppn',
    ];

    protected $casts = [
        'waktu_siap' => 'datetime',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function meja(): BelongsTo
    {
        return $this->belongsTo(Meja::class, 'meja_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PesananItem::class, 'pesanan_id');
    }

    public function splits(): HasMany
    {
        return $this->hasMany(PesananSplit::class, 'pesanan_id')->orderBy('id', 'ASC');
    }

    // =========================================================================
    // Static Helpers
    // =========================================================================

    /**
     * Generate a unique order code in the format ORD-YYMMDD-XXXXXX.
     */
    public static function generateKode(): string
    {
        do {
            $kode = 'ORD-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $exists = static::where('kode_pesanan', $kode)->exists();
        } while ($exists);

        return $kode;
    }

    /**
     * Generate the next sequential queue number (e.g. A001, A002) for today.
     * Retries if the generated number was already taken due to a race condition.
     */
    public static function generateNomorAntrian(): string
    {
        $today = date('Y-m-d');

        $last = static::select('nomor_antrian')
            ->whereDate('created_at', $today)
            ->whereNotNull('nomor_antrian')
            ->orderBy('id', 'DESC')
            ->first();

        $nextNumber = 1;
        if ($last && !empty($last->nomor_antrian)) {
            $number = (int) preg_replace('/[^0-9]/', '', $last->nomor_antrian);
            $nextNumber = $number + 1;
        }

        do {
            $nomor = 'A' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            $exists = static::whereDate('created_at', $today)
                ->where('nomor_antrian', $nomor)
                ->exists();
            $nextNumber++;
        } while ($exists);

        return $nomor;
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Scope to orders currently waiting to be processed.
     */
    public function scopePending($query)
    {
        return $query->where('status_pesanan', 'menunggu');
    }

    /**
     * Scope for the public monitor screen: shows today's pending and ready orders.
     */
    public function scopeMonitorPublic($query)
    {
        return $query->whereIn('status_pesanan', ['menunggu', 'siap'])
            ->whereDate('created_at', date('Y-m-d'));
    }
}
