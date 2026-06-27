<?php

namespace App\Models;

use CodeIgniter\Model;

class PromoModel extends Model
{
    protected $table            = 'promos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
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

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findByKode(string $kode): ?array
    {
        return $this->where('kode', strtoupper(trim($kode)))->first();
    }

    public function hitungPotongan(array $promo, int $subtotal): int
    {
        $tipe  = $promo['tipe_diskon'] ?? 'nominal';
        $nilai = (int) ($promo['nilai_diskon'] ?? 0);

        if ($subtotal <= 0 || $nilai <= 0) {
            return 0;
        }

        if ($tipe === 'persen') {
            $potongan = (int) floor($subtotal * ($nilai / 100));

            $maksimalDiskon = (int) ($promo['maksimal_diskon'] ?? 0);

            if ($maksimalDiskon > 0 && $potongan > $maksimalDiskon) {
                $potongan = $maksimalDiskon;
            }

            return min($potongan, $subtotal);
        }

        return min($nilai, $subtotal);
    }

    public function validasiPromo(string $kode, int $subtotal): array
    {
        $kode = strtoupper(trim($kode));

        if ($kode === '') {
            return [
                'success' => false,
                'message' => 'Kode promo wajib diisi.',
            ];
        }

        $promo = $this->findByKode($kode);

        if (! $promo) {
            return [
                'success' => false,
                'message' => 'Kode promo tidak ditemukan.',
            ];
        }

        if ((int) $promo['is_active'] !== 1) {
            return [
                'success' => false,
                'message' => 'Kode promo sudah tidak aktif.',
            ];
        }

        $now = time();

        if (! empty($promo['berlaku_mulai']) && strtotime($promo['berlaku_mulai']) > $now) {
            return [
                'success' => false,
                'message' => 'Kode promo belum bisa digunakan.',
            ];
        }

        if (! empty($promo['berlaku_sampai']) && strtotime($promo['berlaku_sampai']) < $now) {
            return [
                'success' => false,
                'message' => 'Kode promo sudah kedaluwarsa.',
            ];
        }

        $minimalBelanja = (int) ($promo['minimal_belanja'] ?? 0);

        if ($minimalBelanja > 0 && $subtotal < $minimalBelanja) {
            return [
                'success' => false,
                'message' => 'Minimal belanja untuk promo ini adalah Rp ' . number_format($minimalBelanja, 0, ',', '.'),
            ];
        }

        $kuota = (int) ($promo['kuota'] ?? 0);
        $jumlahDigunakan = (int) ($promo['jumlah_digunakan'] ?? 0);

        if ($kuota > 0 && $jumlahDigunakan >= $kuota) {
            return [
                'success' => false,
                'message' => 'Kuota promo sudah habis.',
            ];
        }

        $potongan = $this->hitungPotongan($promo, $subtotal);

        if ($potongan <= 0) {
            return [
                'success' => false,
                'message' => 'Promo tidak menghasilkan potongan.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Kode promo berhasil digunakan.',
            'promo'   => [
                'id'              => (int) $promo['id'],
                'kode'            => $promo['kode'],
                'nama_promo'      => $promo['nama_promo'],
                'tipe_diskon'     => $promo['tipe_diskon'],
                'nilai_diskon'    => (int) $promo['nilai_diskon'],
                'maksimal_diskon' => (int) $promo['maksimal_diskon'],
                'minimal_belanja' => (int) $promo['minimal_belanja'],
            ],
            'potongan'             => $potongan,
            'total_setelah_diskon' => max(0, $subtotal - $potongan),
        ];
    }

    public function tambahJumlahDigunakan(int $id): bool
    {
        return (bool) $this->set('jumlah_digunakan', 'jumlah_digunakan + 1', false)
            ->where('id', $id)
            ->update();
    }

    public function getAktifPublic(int $limit = 5): array
    {
        $now = date('Y-m-d H:i:s');

        return $this->where('is_active', 1)
            ->groupStart()
                ->where('berlaku_mulai', null)
                ->orWhere('berlaku_mulai <=', $now)
            ->groupEnd()
            ->groupStart()
                ->where('berlaku_sampai', null)
                ->orWhere('berlaku_sampai >=', $now)
            ->groupEnd()
            ->groupStart()
                ->where('kuota', 0)
                ->orWhere('jumlah_digunakan < kuota')
            ->groupEnd()
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}