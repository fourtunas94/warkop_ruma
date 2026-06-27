<?php

namespace App\Models;

use CodeIgniter\Model;

class PesananModel extends Model
{
    protected $table            = 'pesanans';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
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

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getWithMeja(): array
    {
        return $this->select('pesanans.*, mejas.nomor_meja')
            ->join('mejas', 'mejas.id = pesanans.meja_id', 'left')
            ->orderBy('pesanans.id', 'DESC')
            ->findAll();
    }

    public function getPending(): array
    {
        return $this->select('pesanans.*, mejas.nomor_meja')
            ->join('mejas', 'mejas.id = pesanans.meja_id', 'left')
            ->where('pesanans.status_pesanan', 'menunggu')
            ->orderBy('pesanans.id', 'ASC')
            ->findAll();
    }

    public function getAntreanKasir(): array
    {
        return $this->select('pesanans.*, mejas.nomor_meja')
            ->join('mejas', 'mejas.id = pesanans.meja_id', 'left')
            ->where('pesanans.status_pesanan', 'menunggu')
            ->orderBy('pesanans.id', 'ASC')
            ->findAll();
    }

    public function getMonitorPublic(): array
    {
        return $this->select('pesanans.*, mejas.nomor_meja')
            ->join('mejas', 'mejas.id = pesanans.meja_id', 'left')
            ->whereIn('pesanans.status_pesanan', ['menunggu', 'siap'])
            ->where('DATE(pesanans.created_at)', date('Y-m-d'))
            ->orderBy('pesanans.id', 'ASC')
            ->findAll();
    }

    public function getSelesaiHariIni(): int
    {
        return $this->where('status_pesanan', 'siap')
            ->where('DATE(waktu_siap)', date('Y-m-d'))
            ->countAllResults();
    }

    public function getPendapatanHariIni(): int
    {
        $row = $this->selectSum('total_harga')
            ->where('status', 'selesai')
            ->where('DATE(updated_at)', date('Y-m-d'))
            ->first();

        return (int) ($row['total_harga'] ?? 0);
    }

    public function getRiwayatByTanggal(?string $startDate = null, ?string $endDate = null): array
    {
        $builder = $this->select('pesanans.*, mejas.nomor_meja')
            ->join('mejas', 'mejas.id = pesanans.meja_id', 'left')
            ->where('pesanans.status', 'selesai');

        if (! empty($startDate)) {
            $builder->where('DATE(pesanans.updated_at) >=', $startDate);
        }

        if (! empty($endDate)) {
            $builder->where('DATE(pesanans.updated_at) <=', $endDate);
        }

        return $builder
            ->orderBy('pesanans.updated_at', 'DESC')
            ->findAll();
    }

    public function generateKode(): string
    {
        do {
            $kode = 'ORD-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $exists = $this->where('kode_pesanan', $kode)->first();
        } while ($exists);

        return $kode;
    }

    public function generateNomorAntrian(): string
    {
        $today = date('Y-m-d');

        $last = $this->select('nomor_antrian')
            ->where('DATE(created_at)', $today)
            ->where('nomor_antrian IS NOT NULL', null, false)
            ->orderBy('id', 'DESC')
            ->first();

        $nextNumber = 1;

        if ($last && ! empty($last['nomor_antrian'])) {
            $number = (int) preg_replace('/[^0-9]/', '', $last['nomor_antrian']);
            $nextNumber = $number + 1;
        }

        do {
            $nomor = 'A' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);

            $exists = $this->where('DATE(created_at)', $today)
                ->where('nomor_antrian', $nomor)
                ->first();

            $nextNumber++;
        } while ($exists);

        return $nomor;
    }
}