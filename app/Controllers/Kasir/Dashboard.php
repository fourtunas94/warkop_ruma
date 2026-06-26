<?php

namespace App\Controllers\Kasir;

use App\Controllers\BaseController;
use App\Models\PesananModel;
use App\Models\PesananItemModel;
use App\Models\MejaModel;

class Dashboard extends BaseController
{
    protected PesananModel     $pesananModel;
    protected PesananItemModel $itemModel;
    protected MejaModel        $mejaModel;

    public function __construct()
    {
        $this->pesananModel = new PesananModel();
        $this->itemModel    = new PesananItemModel();
        $this->mejaModel    = new MejaModel();
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

        $riwayatHariIni = $this->pesananModel
            ->where('DATE(created_at)', $hari_ini)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        foreach ($riwayatHariIni as &$r) {
            $meja = $this->mejaModel->find($r['meja_id']);
            $r['nomor_meja'] = $meja['nomor_meja'] ?? '-';
            $r['items'] = $this->itemModel->getByPesanan($r['id']);
        }
        unset($r);

        $data = [
            'title'              => 'Antrean Pesanan — ARZANA',
            'pesanan_pending'    => $pending,
            'riwayat_hari_ini'   => $riwayatHariIni,
            'total_pending'      => count($pending),
            'total_selesai'      => $this->pesananModel->getSelesaiHariIni(),
            'pendapatan'         => $pendapatan,
            'last_id'            => $last_id,
            'last_payment_time'  => date('Y-m-d H:i:s'),
        ];

        return view('kasir/dashboard', $data);
    }

    public function selesai(int $id)
    {
        $pesanan = $this->pesananModel->find($id);

        if (!$pesanan) {
            return redirect()->to(base_url('kasir/dashboard'))
                ->with('error', 'Pesanan tidak ditemukan.');
        }

        if (($pesanan['metode_pembayaran'] ?? '') !== 'tunai') {
            return redirect()->to(base_url('kasir/dashboard'))
                ->with('error', 'Tombol selesai hanya untuk pembayaran tunai.');
        }

        $this->pesananModel->update($id, [
            'status'     => 'selesai',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('kasir/dashboard'))
            ->with('success', 'Pesanan #' . $pesanan['kode_pesanan'] . ' ditandai selesai.');
    }

    public function cek_baru($last_id)
    {
        try {
            $semuaPending = $this->pesananModel->getPending();

            $pesananBaru = [];
            $new_last_id = (int) $last_id;

            foreach ($semuaPending as $p) {
                if ($p['id'] > $last_id) {
                    $p['items'] = $this->itemModel->getByPesanan($p['id']);

                    $p['waktu'] = date('H:i', strtotime($p['created_at']));
                    $p['total_format'] = number_format($p['total_harga'], 0, ',', '.');
                    $p['status_pembayaran'] = $p['status'];
                    $p['metode_label'] = $this->formatMetodePembayaran($p['metode_pembayaran'] ?? '-');

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

            return $this->response->setJSON([
                'ada' => false
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'ada'   => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cek_pembayaran_berhasil()
    {
        try {
            $lastTime = $this->request->getGet('last_time');

            if (empty($lastTime)) {
                $lastTime = date('Y-m-d H:i:s');
            }

            $hari_ini = date('Y-m-d');

            $pesananLunas = $this->pesananModel
                ->where('DATE(updated_at)', $hari_ini)
                ->where('updated_at >', $lastTime)
                ->where('status', 'selesai')
                ->where('metode_pembayaran !=', 'tunai')
                ->orderBy('updated_at', 'ASC')
                ->findAll();

            $data = [];
            $newLastTime = $lastTime;

            foreach ($pesananLunas as $p) {
                $meja = $this->mejaModel->find($p['meja_id']);

                $p['nomor_meja'] = $meja['nomor_meja'] ?? '-';
                $p['items'] = $this->itemModel->getByPesanan($p['id']);
                $p['waktu'] = date('H:i', strtotime($p['updated_at']));
                $p['created_waktu'] = date('H:i', strtotime($p['created_at']));
                $p['total_format'] = number_format($p['total_harga'], 0, ',', '.');
                $p['status_pembayaran'] = $p['status'];
                $p['metode_label'] = $this->formatMetodePembayaran($p['metode_pembayaran'] ?? '-');

                $data[] = $p;

                if (strtotime($p['updated_at']) > strtotime($newLastTime)) {
                    $newLastTime = $p['updated_at'];
                }
            }

            if (!empty($data)) {
                return $this->response->setJSON([
                    'ada'       => true,
                    'data'      => $data,
                    'last_time' => $newLastTime,
                ]);
            }

            return $this->response->setJSON([
                'ada'       => false,
                'last_time' => $lastTime,
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'ada'   => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function formatMetodePembayaran(string $metode): string
    {
        return match ($metode) {
            'tunai'    => 'Tunai',
            'qris'     => 'QRIS / E-Wallet',
            'transfer' => 'Transfer Bank',
            default    => ucfirst($metode),
        };
    }
}