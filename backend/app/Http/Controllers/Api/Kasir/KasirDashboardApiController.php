<?php
namespace App\Http\Controllers\Api\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Meja;
use Illuminate\Http\Request;

class KasirDashboardApiController extends Controller
{
    public function index()
    {
        $pending = Pesanan::with(['meja', 'items'])
            ->where('status_pesanan', 'menunggu')
            ->orderBy('id', 'ASC')
            ->get();

        $lastId = 0;
        $pendingData = $pending->map(function ($p) use (&$lastId) {
            if ($p->id > $lastId) {
                $lastId = $p->id;
            }
            return [
                'id'                 => $p->id,
                'kode_pesanan'       => $p->kode_pesanan,
                'nomor_antrian'      => $p->nomor_antrian,
                'nomor_meja'         => $p->meja->nomor_meja ?? '-',
                'nama_pemesan'       => $p->nama_pemesan,
                'total_harga'        => $p->total_harga,
                'total_format'       => 'Rp ' . number_format($p->total_harga, 0, ',', '.'),
                'status'             => $p->status,
                'status_pesanan'     => $p->status_pesanan,
                'metode_pembayaran'  => $p->metode_pembayaran,
                'metode_label'       => $this->formatMetode($p->metode_pembayaran),
                'catatan'            => $p->catatan,
                'waktu'              => $p->created_at ? $p->created_at->format('H:i') : '-',
                'created_at'         => $p->created_at,
                'items'              => $p->items->map(fn($i) => [
                    'nama_menu' => $i->nama_menu,
                    'harga'     => $i->harga,
                    'qty'       => $i->qty,
                    'catatan'   => $i->catatan,
                ]),
            ];
        });

        $hari_ini      = date('Y-m-d');
        $pendapatan    = Pesanan::whereDate('updated_at', $hari_ini)->where('status', 'selesai')->sum('total_harga');
        $total_selesai = Pesanan::where('status_pesanan', 'siap')->whereDate('waktu_siap', $hari_ini)->count();

        return response()->json([
            'success'              => true,
            'pesanan_pending'      => $pendingData,
            'total_pending'        => $pendingData->count(),
            'total_selesai'        => $total_selesai,
            'pendapatan'           => $pendapatan,
            'last_id'              => $lastId,
            'last_payment_time'    => now()->toDateTimeString(),
        ]);
    }

    public function telahBayar(Request $request, int $id)
    {
        $pesanan = Pesanan::find($id);
        if (!$pesanan) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($pesanan->status === 'selesai') {
            return response()->json(['success' => true, 'message' => 'Pembayaran sudah tercatat sebelumnya.']);
        }

        $metode = strtolower((string) $request->input('metode_pembayaran', ''));
        $updateData = ['status' => 'selesai'];
        if (in_array($metode, ['tunai', 'qris', 'transfer'], true)) {
            $updateData['metode_pembayaran'] = $metode;
        }

        $pesanan->update($updateData);
        $metodeLabel = $this->formatMetode($pesanan->metode_pembayaran ?? 'tunai');

        return response()->json([
            'success' => true,
            'message' => "Pembayaran {$metodeLabel} berhasil dikonfirmasi.",
        ]);
    }

    public function siap(int $id)
    {
        $pesanan = Pesanan::find($id);
        if (!$pesanan) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if (!in_array($pesanan->status_pesanan, ['menunggu', 'proses'], true)) {
            return response()->json(['success' => true, 'message' => 'Pesanan sudah siap atau selesai.']);
        }

        Pesanan::where('id', $id)->update([
            'status_pesanan' => 'siap',
            'waktu_siap'     => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Pesanan siap diantarkan.']);
    }

    public function cekBaru(int $lastId)
    {
        $semua = Pesanan::with(['meja', 'items'])->where('status_pesanan', 'menunggu')->get();
        $baru  = $semua->filter(fn($p) => $p->id > $lastId);
        $newLastId = $lastId;

        if ($baru->isNotEmpty()) {
            $data = $baru->map(function ($p) use (&$newLastId) {
                if ($p->id > $newLastId) {
                    $newLastId = $p->id;
                }
                return [
                    'id'                => $p->id,
                    'kode_pesanan'      => $p->kode_pesanan,
                    'nomor_antrian'     => $p->nomor_antrian,
                    'nomor_meja'        => $p->meja->nomor_meja ?? '-',
                    'nama_pemesan'      => $p->nama_pemesan,
                    'total_format'      => 'Rp ' . number_format($p->total_harga, 0, ',', '.'),
                    'waktu'             => $p->created_at ? $p->created_at->format('H:i') : date('H:i'),
                    'status_pembayaran' => $p->status,
                    'metode_label'      => $this->formatMetode($p->metode_pembayaran),
                    'items'             => $p->items->map(fn($i) => [
                        'nama_menu' => $i->nama_menu,
                        'qty'       => $i->qty,
                        'catatan'   => $i->catatan,
                    ]),
                ];
            })->values();

            return response()->json(['ada' => true, 'data' => $data, 'last_id' => $newLastId]);
        }

        return response()->json(['ada' => false, 'last_id' => $newLastId]);
    }

    public function cekPembayaranBerhasil(Request $request)
    {
        $lastTime = $request->query('last_time', now()->toDateTimeString());
        $hari_ini = date('Y-m-d');

        $lunas = Pesanan::with(['meja', 'items'])
            ->whereDate('updated_at', $hari_ini)
            ->where('updated_at', '>', $lastTime)
            ->where('status', 'selesai')
            ->where('metode_pembayaran', '!=', 'tunai')
            ->orderBy('updated_at', 'ASC')
            ->get();

        if ($lunas->isNotEmpty()) {
            $newLastTime = $lastTime;
            $data = $lunas->map(function ($p) use (&$newLastTime) {
                if ($p->updated_at && strtotime($p->updated_at) > strtotime($newLastTime)) {
                    $newLastTime = $p->updated_at->toDateTimeString();
                }
                return [
                    'id'           => $p->id,
                    'nomor_antrian' => $p->nomor_antrian,
                    'nomor_meja'   => $p->meja->nomor_meja ?? '-',
                    'total_format' => 'Rp ' . number_format($p->total_harga, 0, ',', '.'),
                    'waktu'        => $p->updated_at ? $p->updated_at->format('H:i') : date('H:i'),
                    'metode_label' => $this->formatMetode($p->metode_pembayaran),
                    'items'        => $p->items->map(fn($i) => [
                        'nama_menu' => $i->nama_menu,
                        'qty'       => $i->qty,
                    ]),
                ];
            })->values();

            return response()->json(['ada' => true, 'data' => $data, 'last_time' => $newLastTime]);
        }

        return response()->json(['ada' => false, 'last_time' => $lastTime]);
    }

    private function formatMetode(string $metode): string
    {
        return match ($metode) {
            'tunai'    => 'Tunai / COD',
            'qris'     => 'QRIS / E-Wallet',
            'transfer' => 'Transfer Bank / VA',
            default    => ucfirst($metode),
        };
    }
}
