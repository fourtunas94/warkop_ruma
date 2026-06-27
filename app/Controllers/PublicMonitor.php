<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PesananModel;

class PublicMonitor extends BaseController
{
    protected PesananModel $pesananModel;

    public function __construct()
    {
        $this->pesananModel = new PesananModel();
    }

    public function index()
    {
        return view('public_monitor/index');
    }

    public function data()
    {
        $rows = $this->pesananModel->getMonitorPublic();

        $data = [];

        foreach ($rows as $row) {
            $data[] = [
                'id'             => (int) ($row['id'] ?? 0),
                'nomor_antrian'  => $row['nomor_antrian'] ?? '-',
                'nomor_meja'     => $row['nomor_meja'] ?? '-',
                'status'         => $row['status'] ?? 'pending',
                'status_pesanan' => $row['status_pesanan'] ?? 'menunggu',
                'waktu'          => ! empty($row['created_at']) ? date('H:i', strtotime($row['created_at'])) : '-',
                'waktu_siap'     => ! empty($row['waktu_siap']) ? date('H:i', strtotime($row['waktu_siap'])) : null,
                'label'          => $this->statusLabel($row),
                'tone'           => $this->statusTone($row),
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $data,
            'time'    => date('H:i:s'),
        ]);
    }

    private function statusLabel(array $row): string
    {
        $statusPesanan = $row['status_pesanan'] ?? 'menunggu';
        $metode        = $row['metode_pembayaran'] ?? '';
        $statusBayar   = $row['status'] ?? 'pending';

        if ($statusPesanan === 'siap') {
            return 'Siap Diantarkan';
        }

        if ($metode !== 'tunai' && $statusBayar !== 'selesai') {
            return 'Menunggu Pembayaran';
        }

        return 'Sedang Disiapkan';
    }

    private function statusTone(array $row): string
    {
        $statusPesanan = $row['status_pesanan'] ?? 'menunggu';
        $metode        = $row['metode_pembayaran'] ?? '';
        $statusBayar   = $row['status'] ?? 'pending';

        if ($statusPesanan === 'siap') {
            return 'ready';
        }

        if ($metode !== 'tunai' && $statusBayar !== 'selesai') {
            return 'payment';
        }

        return 'waiting';
    }
}