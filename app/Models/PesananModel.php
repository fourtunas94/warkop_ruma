<?php

namespace App\Models;

use CodeIgniter\Model;

class PesananModel extends Model
{
    protected $table         = 'pesanans';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['kode_pesanan', 'meja_id', 'nama_pemesan', 'total_harga', 'status', 'catatan', 'metode_pembayaran', 'kode_promo', 'potongan'];
    
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

    public function generateKode(): string
    {
        return 'ORD-' . strtoupper(substr(uniqid(), -6));
    }
}