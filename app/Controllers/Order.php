<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MejaModel;
use App\Models\MenuModel;
use App\Models\KategoriModel;
use App\Models\PesananModel;
use App\Models\PesananItemModel;

class Order extends BaseController
{
    protected MejaModel        $mejaModel;
    protected MenuModel        $menuModel;
    protected KategoriModel    $kategoriModel;
    protected PesananModel     $pesananModel;
    protected PesananItemModel $itemModel;

    public function __construct()
    {
        $this->mejaModel     = new MejaModel();
        $this->menuModel     = new MenuModel();
        $this->kategoriModel = new KategoriModel();
        $this->pesananModel  = new PesananModel();
        $this->itemModel     = new PesananItemModel();
    }

    public function menu(string $kode)
    {
        $meja = $this->mejaModel->findByKode($kode);

        if (!$meja) {
            return view('order/invalid');
        }

        $kategoris = $this->kategoriModel->getAktif();
        $menus     = $this->menuModel->getAktif();

        $menuByKategori = [];
        foreach ($kategoris as $k) {
            $menuByKategori[$k['id']] = [
                'nama'  => $k['nama_kategori'],
                'items' => [],
            ];
        }
        foreach ($menus as $m) {
            $kid = $m['kategori_id'] ?? 0;
            if (isset($menuByKategori[$kid])) {
                $menuByKategori[$kid]['items'][] = $m;
            }
        }

        $data = [
            'meja'           => $meja,
            'menuByKategori' => $menuByKategori,
        ];

        return view('order/menu', $data);
    }

    public function checkout(string $kode)
    {
        $meja = $this->mejaModel->findByKode($kode);

        if (!$meja) {
            return view('order/invalid');
        }

        return view('order/checkout', ['meja' => $meja]);
    }

    public function submit(string $kode)
    {
        $meja = $this->mejaModel->findByKode($kode);

        if (!$meja) {
            return $this->response->setJSON(['success' => false, 'message' => 'Meja tidak valid.']);
        }

        $rules = [
            'nama_pemesan' => 'required|min_length[2]|max_length[100]',
            'items'        => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'errors' => $this->validator->getErrors()]);
        }

        $items = json_decode($this->request->getPost('items'), true);

        if (empty($items)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Keranjang kosong.']);
        }

        $totalHarga = 0;
        foreach ($items as $item) {
            $totalHarga += (int) $item['harga'] * (int) $item['qty'];
        }

        $kodeP = $this->pesananModel->generateKode();

        $pesananId = $this->pesananModel->insert([
            'kode_pesanan'  => $kodeP,
            'meja_id'       => $meja['id'],
            'nama_pemesan'  => $this->request->getPost('nama_pemesan'),
            'total_harga'   => $totalHarga,
            'catatan'       => $this->request->getPost('catatan'),
            'status'        => 'pending',
        ]);

        if (!$pesananId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal menyimpan pesanan.']);
        }

        foreach ($items as $item) {
            $this->itemModel->insert([
                'pesanan_id' => $pesananId,
                'menu_id'    => $item['id'] ?? null,
                'nama_menu'  => $item['nama_menu'],
                'harga'      => (int) $item['harga'],
                'qty'        => (int) $item['qty'],
                'catatan'    => $item['catatan'] ?? null,
            ]);
        }

        return $this->response->setJSON([
            'success'      => true,
            'kode_pesanan' => $kodeP,
            'redirect'     => base_url('order/sukses/' . $kodeP),
        ]);
    }

    public function sukses(string $kode)
    {
        $pesanan = $this->pesananModel->where('kode_pesanan', $kode)->first();

        if (!$pesanan) {
            return redirect()->to('/');
        }

        $items = $this->itemModel->getByPesanan($pesanan['id']);
        $meja  = $this->mejaModel->find($pesanan['meja_id']);

        return view('order/sukses', [
            'pesanan' => $pesanan,
            'items'   => $items,
            'meja'    => $meja,
        ]);
    }
}