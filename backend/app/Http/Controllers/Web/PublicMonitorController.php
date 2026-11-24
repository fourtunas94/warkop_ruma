<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;

class PublicMonitorController extends Controller
{
    public function index()
    {
        return view('public_monitor.index');
    }

    public function data()
    {
        $rows = Pesanan::with('meja')
            ->whereIn('status_pesanan', ['menunggu', 'siap'])
            ->whereDate('created_at', date('Y-m-d'))
            ->orderBy('id', 'ASC')
            ->get();

        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'id'             => (int) $row->id,
                'nomor_antrian'  => $row->nomor_antrian ?? '-',
                'nomor_meja'     => $row->meja->nomor_meja ?? '-',
                'status'         => $row->status ?? 'pending',
                'status_pesanan' => $row->status_pesanan ?? 'menunggu',
                'waktu'          => $row->created_at ? $row->created_at->format('H:i') : '-',
                'waktu_siap'     => $row->waktu_siap ? $row->waktu_siap->format('H:i') : null,
                'label'          => $this->statusLabel($row),
                'tone'           => $this->statusTone($row),
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
            'time'    => date('H:i:s'),
        ]);
    }

    private function statusLabel($row): string
    {
        if ($row->status_pesanan === 'siap') {
            return 'Siap Diantarkan';
        }
        if ($row->metode_pembayaran !== 'tunai' && $row->status !== 'selesai') {
            return 'Menunggu Pembayaran';
        }
        return 'Sedang Disiapkan';
    }

    private function statusTone($row): string
    {
        if ($row->status_pesanan === 'siap') {
            return 'ready';
        }
        if ($row->metode_pembayaran !== 'tunai' && $row->status !== 'selesai') {
            return 'payment';
        }
        return 'waiting';
    }
}
