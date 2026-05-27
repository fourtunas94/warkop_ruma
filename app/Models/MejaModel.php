<?php

namespace App\Models;

use CodeIgniter\Model;

class MejaModel extends Model
{
    protected $table         = 'mejas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['nomor_meja', 'kode_unik', 'is_active'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nomor_meja' => 'required|max_length[10]|is_unique[mejas.nomor_meja,id,{id}]',
    ];

    protected $validationMessages = [
        'nomor_meja' => [
            'required'  => 'Nomor meja wajib diisi.',
            'is_unique' => 'Nomor meja sudah digunakan.',
        ],
    ];

    public function findByKode(string $kode): ?array
    {
        return $this->where('kode_unik', $kode)->where('is_active', 1)->first();
    }
}