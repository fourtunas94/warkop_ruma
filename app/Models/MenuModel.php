<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table            = 'menus';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['kategori_id', 'nama_menu', 'deskripsi', 'harga', 'stok', 'gambar', 'is_active'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nama_menu'   => 'required|min_length[2]|max_length[100]',
        'harga'       => 'required|integer|greater_than[0]',
        'kategori_id' => 'permit_empty|integer',
        'is_active'   => 'required|in_list[0,1]',
        'stok'        => 'required|in_list[0,1]',
    ];

    protected $validationMessages = [
        'nama_menu' => [
            'required'   => 'Nama menu wajib diisi.',
            'min_length' => 'Nama menu minimal 2 karakter.',
        ],
        'harga' => [
            'required'     => 'Harga wajib diisi.',
            'integer'      => 'Harga harus berupa angka.',
            'greater_than' => 'Harga harus lebih dari 0.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $beforeUpdate   = [];

    public function getWithKategori(): array
    {
        return $this->select('menus.*, kategoris.nama_kategori')
                    ->join('kategoris', 'kategoris.id = menus.kategori_id', 'left')
                    ->orderBy('menus.id', 'ASC')
                    ->findAll();
    }

    public function getAktif(): array
    {
        return $this->select('menus.*, kategoris.nama_kategori')
                    ->join('kategoris', 'kategoris.id = menus.kategori_id', 'left')
                    ->where('menus.is_active', 1)
                    ->orderBy('kategoris.id', 'ASC')
                    ->orderBy('menus.nama_menu', 'ASC')
                    ->findAll();
    }
}