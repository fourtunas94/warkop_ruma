<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MejaModel;

class Meja extends BaseController
{
    protected MejaModel $mejaModel;

    public function __construct()
    {
        $this->mejaModel = new MejaModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Meja & QR Code — ARZANA',
            'mejas' => $this->mejaModel->orderBy('id', 'ASC')->findAll(),
        ];

        return view('admin/meja/index', $data);
    }

    public function store()
    {
        $rules = [
            'nomor_meja' => 'required|max_length[10]|is_unique[mejas.nomor_meja]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $saved = $this->mejaModel->insert([
            'nomor_meja' => strtoupper($this->request->getPost('nomor_meja')),
            'kode_unik'  => md5(uniqid(rand(), true)),
            'is_active'  => 1,
        ]);

        if (!$saved) {
            return redirect()->back()->with('error', 'Gagal menambahkan meja.');
        }

        return redirect()->to(base_url('admin/meja'))->with('success', 'Meja berhasil ditambahkan.');
    }

    public function delete(int $id)
    {
        $meja = $this->mejaModel->find($id);

        if (!$meja) {
            return redirect()->to(base_url('admin/meja'))->with('error', 'Meja tidak ditemukan.');
        }

        $this->mejaModel->delete($id);

        return redirect()->to(base_url('admin/meja'))->with('success', 'Meja berhasil dihapus.');
    }

    public function toggleStatus(int $id)
    {
        $meja = $this->mejaModel->find($id);

        if (!$meja) {
            return redirect()->to(base_url('admin/meja'))->with('error', 'Meja tidak ditemukan.');
        }

        $this->mejaModel->update($id, [
            'is_active' => $meja['is_active'] == 1 ? 0 : 1,
        ]);

        return redirect()->to(base_url('admin/meja'))->with('success', 'Status meja diperbarui.');
    }
}