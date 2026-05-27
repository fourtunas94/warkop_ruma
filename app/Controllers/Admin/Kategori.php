<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KategoriModel;

class Kategori extends BaseController
{
    protected KategoriModel $kategoriModel;

    public function __construct()
    {
        $this->kategoriModel = new KategoriModel();
    }

    public function index()
    {
        $data = [
            'title'     => 'Kelola Kategori — ARZANA',
            'kategoris' => $this->kategoriModel->orderBy('id', 'ASC')->findAll(),
        ];

        return view('admin/kategori/index', $data);
    }

    public function create()
    {
        $data = ['title' => 'Tambah Kategori — ARZANA'];

        return view('admin/kategori/form', $data);
    }

    public function store()
    {
        $rules = [
            'nama_kategori' => 'required|min_length[2]|max_length[100]',
            'deskripsi'     => 'permit_empty|max_length[500]',
            'is_active'     => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $saved = $this->kategoriModel->insert([
            'nama_kategori' => $this->request->getPost('nama_kategori'),
            'slug'          => url_title($this->request->getPost('nama_kategori'), '-', true),
            'deskripsi'     => $this->request->getPost('deskripsi'),
            'is_active'     => $this->request->getPost('is_active'),
        ]);

        if (!$saved) {
            return redirect()->back()->withInput()->with('errors', $this->kategoriModel->errors());
        }

        return redirect()->to(base_url('admin/kategori'))->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $kategori = $this->kategoriModel->find($id);

        if (!$kategori) {
            return redirect()->to(base_url('admin/kategori'))->with('error', 'Kategori tidak ditemukan.');
        }

        $data = [
            'title'    => 'Edit Kategori — ARZANA',
            'kategori' => $kategori,
        ];

        return view('admin/kategori/form', $data);
    }

    public function update(int $id)
    {
        $kategori = $this->kategoriModel->find($id);

        if (!$kategori) {
            return redirect()->to(base_url('admin/kategori'))->with('error', 'Kategori tidak ditemukan.');
        }

        $rules = [
            'nama_kategori' => 'required|min_length[2]|max_length[100]',
            'deskripsi'     => 'permit_empty|max_length[500]',
            'is_active'     => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updated = $this->kategoriModel->update($id, [
            'nama_kategori' => $this->request->getPost('nama_kategori'),
            'slug'          => url_title($this->request->getPost('nama_kategori'), '-', true),
            'deskripsi'     => $this->request->getPost('deskripsi'),
            'is_active'     => $this->request->getPost('is_active'),
        ]);

        if (!$updated) {
            return redirect()->back()->withInput()->with('errors', $this->kategoriModel->errors());
        }

        return redirect()->to(base_url('admin/kategori'))->with('success', 'Kategori berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $kategori = $this->kategoriModel->find($id);

        if (!$kategori) {
            return redirect()->to(base_url('admin/kategori'))->with('error', 'Kategori tidak ditemukan.');
        }

        $this->kategoriModel->delete($id);

        return redirect()->to(base_url('admin/kategori'))->with('success', 'Kategori berhasil dihapus.');
    }

    public function toggleStatus(int $id)
    {
        $kategori = $this->kategoriModel->find($id);

        if (!$kategori) {
            return redirect()->to(base_url('admin/kategori'))->with('error', 'Kategori tidak ditemukan.');
        }

        $this->kategoriModel->update($id, [
            'is_active' => $kategori['is_active'] == 1 ? 0 : 1,
        ]);

        return redirect()->to(base_url('admin/kategori'))->with('success', 'Status kategori diperbarui.');
    }
}