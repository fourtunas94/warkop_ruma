<?php

namespace App\Controllers\Kasir;

use App\Controllers\BaseController;
use App\Models\PesananItemModel;

class Riwayat extends BaseController
{
    protected PesananItemModel $itemModel;

    public function __construct()
    {
        $this->itemModel = new PesananItemModel();
    }

    public function index()
    {
        $db = \Config\Database::connect();
        $request = \Config\Services::request();
        
        $startDate = $request->getGet('start_date');
        $endDate = $request->getGet('end_date');
        
        $builder = $db->table('pesanans')
            ->select('pesanans.*, mejas.nomor_meja')
            ->join('mejas', 'mejas.id = pesanans.meja_id', 'left')
            ->where('pesanans.status', 'selesai');

        if (!empty($startDate)) {
            $builder->where('DATE(pesanans.updated_at) >=', $startDate);
        }

        if (!empty($endDate)) {
            $builder->where('DATE(pesanans.updated_at) <=', $endDate);
        }

        $riwayat = $builder->orderBy('pesanans.updated_at', 'DESC')->get()->getResultArray();

        $totalPendapatan = 0;
        $totalPesanan = count($riwayat);

        foreach ($riwayat as &$r) {
            $r['items'] = $this->itemModel->getByPesanan($r['id']);
            $totalPendapatan += $r['total_harga'];
        }
        unset($r);

        $data = [
            'title'            => 'Riwayat Transaksi — ARZANA',
            'riwayat'          => $riwayat,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'total_pesanan'    => $totalPesanan,
            'total_pendapatan' => $totalPendapatan
        ];

        return view('kasir/riwayat', $data);
    }
}