<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MenuModel;
use App\Models\KategoriModel;

class Menu extends BaseController
{
    protected MenuModel $menuModel;
    protected KategoriModel $kategoriModel;

    public function __construct()
    {
        $this->menuModel     = new MenuModel();
        $this->kategoriModel = new KategoriModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Kelola Menu — ARZANA',
            'menus' => $this->menuModel->getWithKategori(),
        ];

        return view('admin/menu/index', $data);
    }

    public function create()
    {
        $data = [
            'title'     => 'Tambah Menu — ARZANA',
            'kategoris' => $this->kategoriModel->getAktif(),
        ];

        return view('admin/menu/form', $data);
    }

    public function store()
    {
        $rules = [
            'nama_menu'   => 'required|min_length[2]|max_length[100]',
            'harga'       => 'required|integer|greater_than[0]',
            'kategori_id' => 'permit_empty|integer',
            'deskripsi'   => 'permit_empty|max_length[500]',
            'stok'        => 'required|in_list[0,1]',
            'is_active'   => 'required|in_list[0,1]',
            'gambar'      => 'permit_empty|uploaded[gambar]|max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $gambar = $this->request->getFile('gambar');
        $namaGambar = null;

        if ($gambar && $gambar->isValid() && !$gambar->hasMoved()) {
            $namaGambar = $gambar->getRandomName();
            $gambar->move(FCPATH . 'uploads/menu', $namaGambar);
        }

        $saved = $this->menuModel->insert([
            'kategori_id' => $this->request->getPost('kategori_id') ?: null,
            'nama_menu'   => $this->request->getPost('nama_menu'),
            'deskripsi'   => $this->request->getPost('deskripsi'),
            'harga'       => $this->request->getPost('harga'),
            'stok'        => $this->request->getPost('stok'),
            'gambar'      => $namaGambar,
            'is_active'   => $this->request->getPost('is_active'),
        ]);

        if (!$saved) {
            return redirect()->back()->withInput()->with('errors', $this->menuModel->errors());
        }

        return redirect()->to(base_url('admin/menu'))->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $menu = $this->menuModel->find($id);

        if (!$menu) {
            return redirect()->to(base_url('admin/menu'))->with('error', 'Menu tidak ditemukan.');
        }

        $data = [
            'title'     => 'Edit Menu — ARZANA',
            'menu'      => $menu,
            'kategoris' => $this->kategoriModel->getAktif(),
        ];

        return view('admin/menu/form', $data);
    }

    public function update(int $id)
    {
        $menu = $this->menuModel->find($id);

        if (!$menu) {
            return redirect()->to(base_url('admin/menu'))->with('error', 'Menu tidak ditemukan.');
        }

        $rules = [
            'nama_menu'   => 'required|min_length[2]|max_length[100]',
            'harga'       => 'required|integer|greater_than[0]',
            'kategori_id' => 'permit_empty|integer',
            'deskripsi'   => 'permit_empty|max_length[500]',
            'stok'        => 'required|in_list[0,1]',
            'is_active'   => 'required|in_list[0,1]',
            'gambar'      => 'permit_empty|max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $gambar = $this->request->getFile('gambar');
        $namaGambar = $menu['gambar'];

        if ($gambar && $gambar->isValid() && !$gambar->hasMoved()) {
            if ($namaGambar && file_exists(FCPATH . 'uploads/menu/' . $namaGambar)) {
                unlink(FCPATH . 'uploads/menu/' . $namaGambar);
            }
            $namaGambar = $gambar->getRandomName();
            $gambar->move(FCPATH . 'uploads/menu', $namaGambar);
        }

        $updated = $this->menuModel->update($id, [
            'kategori_id' => $this->request->getPost('kategori_id') ?: null,
            'nama_menu'   => $this->request->getPost('nama_menu'),
            'deskripsi'   => $this->request->getPost('deskripsi'),
            'harga'       => $this->request->getPost('harga'),
            'stok'        => $this->request->getPost('stok'),
            'gambar'      => $namaGambar,
            'is_active'   => $this->request->getPost('is_active'),
        ]);

        if (!$updated) {
            return redirect()->back()->withInput()->with('errors', $this->menuModel->errors());
        }

        return redirect()->to(base_url('admin/menu'))->with('success', 'Menu berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $menu = $this->menuModel->find($id);

        if (!$menu) {
            return redirect()->to(base_url('admin/menu'))->with('error', 'Menu tidak ditemukan.');
        }

        if ($menu['gambar'] && file_exists(FCPATH . 'uploads/menu/' . $menu['gambar'])) {
            unlink(FCPATH . 'uploads/menu/' . $menu['gambar']);
        }

        $this->menuModel->delete($id);

        return redirect()->to(base_url('admin/menu'))->with('success', 'Menu berhasil dihapus.');
    }

    public function toggleStatus(int $id)
    {
        $menu = $this->menuModel->find($id);

        if (!$menu) {
            return redirect()->to(base_url('admin/menu'))->with('error', 'Menu tidak ditemukan.');
        }

        $this->menuModel->update($id, [
            'is_active' => $menu['is_active'] == 1 ? 0 : 1,
        ]);

        return redirect()->to(base_url('admin/menu'))->with('success', 'Status menu diperbarui.');
    }

    public function toggleStok(int $id)
    {
        $menu = $this->menuModel->find($id);

        if (!$menu) {
            return redirect()->to(base_url('admin/menu'))->with('error', 'Menu tidak ditemukan.');
        }

        $this->menuModel->update($id, [
            'stok' => $menu['stok'] == 1 ? 0 : 1,
        ]);

        return redirect()->to(base_url('admin/menu'))->with('success', 'Status stok diperbarui.');
    }
}