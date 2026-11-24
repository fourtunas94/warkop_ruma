<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $table = 'promos';

    protected $fillable = [
        'kode',
        'nama_promo',
        'deskripsi',
        'tipe_diskon',
        'nilai_diskon',
        'maksimal_diskon',
        'minimal_belanja',
        'kuota',
        'jumlah_digunakan',
        'berlaku_mulai',
        'berlaku_sampai',
        'is_active',
    ];

    protected $casts = [
        'berlaku_mulai'  => 'datetime',
        'berlaku_sampai' => 'datetime',
    ];

    // =========================================================================
    // Static Helpers
    // =========================================================================

    /**
     * Find a promo by its code (case-insensitive, trimmed).
     */
    public static function findByKode(string $kode): ?self
    {
        return static::where('kode', strtoupper(trim($kode)))->first();
    }

    /**
     * Calculate the discount amount for a given subtotal.
     *
     * Supports two discount types:
     * - 'persen'  : percentage discount, optionally capped by maksimal_diskon
     * - 'nominal' : flat amount, capped at the subtotal
     */
    public function hitungPotongan(int $subtotal): int
    {
        $tipe  = $this->tipe_diskon;
        $nilai = (int) $this->nilai_diskon;

        if ($subtotal <= 0 || $nilai <= 0) {
            return 0;
        }

        if ($tipe === 'persen') {
            $potongan = (int) floor($subtotal * ($nilai / 100));
            $maks = (int) $this->maksimal_diskon;
            if ($maks > 0 && $potongan > $maks) {
                $potongan = $maks;
            }
            return min($potongan, $subtotal);
        }

        // nominal
        return min($nilai, $subtotal);
    }

    /**
     * Validate a promo code against the current subtotal.
     * Returns a result array with 'success', 'message', and on success 'potongan' and 'promo'.
     *
     * @return array{success: bool, message: string, potongan?: int, promo?: Promo}
     */
    public static function validasiPromo(string $kode, int $subtotal): array
    {
        $kode = strtoupper(trim($kode));

        if ($kode === '') {
            return ['success' => false, 'message' => 'Kode promo wajib diisi.'];
        }

        $promo = static::findByKode($kode);
        if (!$promo) {
            return ['success' => false, 'message' => 'Kode promo tidak ditemukan.'];
        }

        if ((int) $promo->is_active !== 1) {
            return ['success' => false, 'message' => 'Kode promo sudah tidak aktif.'];
        }

        $now = time();

        if ($promo->berlaku_mulai && $promo->berlaku_mulai->timestamp > $now) {
            return ['success' => false, 'message' => 'Kode promo belum bisa digunakan.'];
        }

        if ($promo->berlaku_sampai && $promo->berlaku_sampai->timestamp < $now) {
            return ['success' => false, 'message' => 'Kode promo sudah kedaluwarsa.'];
        }

        $kuota = (int) $promo->kuota;
        if ($kuota > 0 && (int) $promo->jumlah_digunakan >= $kuota) {
            return ['success' => false, 'message' => 'Kuota promo sudah habis.'];
        }

        $minimal = (int) $promo->minimal_belanja;
        if ($minimal > 0 && $subtotal < $minimal) {
            return [
                'success' => false,
                'message' => 'Minimum belanja Rp ' . number_format($minimal, 0, ',', '.') . ' untuk promo ini.',
            ];
        }

        $potongan = $promo->hitungPotongan($subtotal);

        return [
            'success'  => true,
            'potongan' => $potongan,
            'message'  => 'Promo berhasil digunakan.',
            'promo'    => $promo,
        ];
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Scope for the public promo banner: active promos, most recent first.
     */
    public function scopeAktifPublic($query, int $limit = 5)
    {
        return $query->where('is_active', 1)->latest()->limit($limit);
    }
}
