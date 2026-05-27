<?php

namespace App\Models;

use CodeIgniter\Model;

class PesananItemModel extends Model
{
    protected $table         = 'pesanan_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['pesanan_id', 'menu_id', 'nama_menu', 'harga', 'qty', 'catatan'];

    public function getByPesanan(int $pesananId): array
    {
        return $this->where('pesanan_id', $pesananId)->findAll();
    }
}