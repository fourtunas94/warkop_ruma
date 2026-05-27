<?php

namespace App\Controllers\Kasir;

use App\Controllers\BaseController;
use App\Models\PesananModel;
use App\Models\PesananItemModel;

class Dashboard extends BaseController
{
    protected PesananModel     $pesananModel;
    protected PesananItemModel $itemModel;

    public function __construct()
    {
        $this->pesananModel = new PesananModel();
        $this->itemModel    = new PesananItemModel();
    }

    public function index()
    {
        $pending = $this->pesananModel->getPending();
        $last_id = 0;

        foreach ($pending as &$p) {
            $p['items'] = $this->itemModel->getByPesanan($p['id']);
            if ($p['id'] > $last_id) {
                $last_id = $p['id'];
            }
        }
        unset($p);

        $hari_ini = date('Y-m-d');
        $pendapatan_row = $this->pesananModel->selectSum('total_harga')
                                             ->where('DATE(updated_at)', $hari_ini)
                                             ->where('status', 'selesai')
                                             ->first();
        $pendapatan = $pendapatan_row['total_harga'] ?? 0;

        $data = [
            'title'           => 'Antrean Pesanan — ARZANA',
            'pesanan_pending' => $pending,
            'total_pending'   => count($pending),
            'total_selesai'   => $this->pesananModel->getSelesaiHariIni(),
            'pendapatan'      => $pendapatan,
            'last_id'         => $last_id
        ];

        return view('kasir/dashboard', $data);
    }

    public function selesai(int $id)
    {
        $pesanan = $this->pesananModel->find($id);

        if (!$pesanan) {
            return redirect()->to(base_url('kasir/dashboard'))->with('error', 'Pesanan tidak ditemukan.');
        }

        $this->pesananModel->update($id, ['status' => 'selesai']);
        return redirect()->to(base_url('kasir/dashboard'))->with('success', 'Pesanan #' . $pesanan['kode_pesanan'] . ' ditandai selesai.');
    }

    public function cek_baru($last_id)
    {
        try {
            $semuaPending = $this->pesananModel->getPending();
            
            $pesananBaru = [];
            $new_last_id = (int)$last_id;

            foreach ($semuaPending as $p) {
                if ($p['id'] > $last_id) {
                    $p['items'] = $this->itemModel->getByPesanan($p['id']);
                    
                    $p['waktu'] = date('H:i', strtotime($p['created_at']));
                    $p['total_format'] = number_format($p['total_harga'], 0, ',', '.');
                    
                    $pesananBaru[] = $p;

                    if ($p['id'] > $new_last_id) {
                        $new_last_id = $p['id'];
                    }
                }
            }

            if (!empty($pesananBaru)) {
                return $this->response->setJSON([
                    'ada'     => true,
                    'data'    => $pesananBaru,
                    'last_id' => $new_last_id
                ]);
            }

            return $this->response->setJSON(['ada' => false]);

        } catch (\Exception $e) {
           return $this->response->setJSON(['ada' => false, 'error' => $e->getMessage()]);
        }
    }
}