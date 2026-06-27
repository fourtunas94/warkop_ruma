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
        'meja_id',
        'nama_pemesan',
        'total_harga',
        'status',
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
            ->where('pesanans.status', 'pending')
            ->orderBy('pesanans.id', 'ASC')
            ->findAll();
    }

    public function getSelesaiHariIni(): int
    {
        return $this->where('status', 'selesai')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->countAllResults();
    }

    public function getPendapatanHariIni(): int
    {
        $row = $this->selectSum('total_harga')
            ->where('status', 'selesai')
            ->where('DATE(created_at)', date('Y-m-d'))
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
}