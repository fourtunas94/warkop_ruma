<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\PesananItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RiwayatApiController extends Controller
{
    public function index(Request $request)
    {
        $periode   = $request->query('periode'); // 'harian', 'mingguan', 'bulanan', 'kustom'
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $now = Carbon::now();

        if ($periode === 'harian') {
            $startDate = $now->toDateString();
            $endDate   = $now->toDateString();
            $periodeLabel = 'Harian (' . $now->translatedFormat('d F Y') . ')';
        } elseif ($periode === 'mingguan') {
            $startDate = $now->copy()->subDays(6)->toDateString();
            $endDate   = $now->toDateString();
            $periodeLabel = 'Mingguan (' . Carbon::parse($startDate)->format('d M') . ' - ' . $now->format('d M Y') . ')';
        } elseif ($periode === 'bulanan') {
            $startDate = $now->copy()->startOfMonth()->toDateString();
            $endDate   = $now->toDateString();
            $periodeLabel = 'Bulanan (' . $now->translatedFormat('F Y') . ')';
        } else {
            if ($startDate && $endDate) {
                if ($startDate === $endDate) {
                    $periodeLabel = Carbon::parse($startDate)->format('d F Y');
                } else {
                    $periodeLabel = Carbon::parse($startDate)->format('d M Y') . ' - ' . Carbon::parse($endDate)->format('d M Y');
                }
            } elseif ($startDate) {
                $endDate = $startDate;
                $periodeLabel = Carbon::parse($startDate)->format('d F Y');
            } else {
                $startDate = $now->copy()->subDays(6)->toDateString();
                $endDate   = $now->toDateString();
                $periodeLabel = '7 Hari Terakhir';
            }
        }

        $query = Pesanan::with(['meja', 'items'])->where('status', 'selesai');
        if ($startDate) {
            $query->whereDate('updated_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('updated_at', '<=', $endDate);
        }
        $riwayat = $query->orderBy('updated_at', 'DESC')->get();

        $pesananIds = $riwayat->pluck('id');

        // Menu yang paling banyak dipesan (Best Sellers)
        $topMenus = PesananItem::whereIn('pesanan_id', $pesananIds)
            ->selectRaw('nama_menu, sum(qty) as total_qty, sum(qty * harga) as total_nominal, count(distinct pesanan_id) as total_pesanan')
            ->groupBy('nama_menu')
            ->orderByDesc('total_qty')
            ->get()
            ->values()
            ->map(function ($item, $idx) {
                return [
                    'ranking'        => $idx + 1,
                    'nama_menu'      => $item->nama_menu,
                    'total_qty'      => (int) $item->total_qty,
                    'total_nominal'  => (int) $item->total_nominal,
                    'nominal_format' => 'Rp ' . number_format($item->total_nominal, 0, ',', '.'),
                    'total_pesanan'  => (int) $item->total_pesanan,
                ];
            });

        $totalPendapatan = (int) $riwayat->sum('total_harga');
        $totalPpn        = (int) $riwayat->sum('ppn');
        $totalPotongan   = (int) $riwayat->sum('potongan');
        $totalPesanan    = $riwayat->count();

        $metodeBreakdown = [
            'tunai'    => (int) $riwayat->filter(fn($p) => strtolower($p->metode_pembayaran ?? '') === 'tunai')->sum('total_harga'),
            'qris'     => (int) $riwayat->filter(fn($p) => strtolower($p->metode_pembayaran ?? '') === 'qris')->sum('total_harga'),
            'transfer' => (int) $riwayat->filter(fn($p) => in_array(strtolower($p->metode_pembayaran ?? ''), ['transfer', 'va']))->sum('total_harga'),
            'split'    => (int) $riwayat->filter(fn($p) => strtolower($p->metode_pembayaran ?? '') === 'split')->sum('total_harga'),
        ];

        $riwayatFormatted = $riwayat->map(fn($r) => [
            'id'                => $r->id,
            'kode_pesanan'      => $r->kode_pesanan,
            'nomor_antrian'     => $r->nomor_antrian,
            'nomor_meja'        => $r->meja->nomor_meja ?? '-',
            'nama_pemesan'      => $r->nama_pemesan,
            'total_harga'       => $r->total_harga,
            'total_format'      => 'Rp ' . number_format($r->total_harga, 0, ',', '.'),
            'metode_pembayaran' => $r->metode_pembayaran,
            'kode_promo'        => $r->kode_promo,
            'potongan'          => $r->potongan,
            'ppn'               => $r->ppn,
            'status'            => $r->status,
            'status_pesanan'    => $r->status_pesanan,
            'catatan'           => $r->catatan,
            'updated_at'        => $r->updated_at,
            'created_at'        => $r->created_at,
            'items'             => $r->items->map(fn($i) => [
                'nama_menu' => $i->nama_menu,
                'harga'     => $i->harga,
                'qty'       => $i->qty,
                'catatan'   => $i->catatan,
            ]),
        ])->values();

        return response()->json([
            'success'          => true,
            'periode'          => [
                'type'       => $periode ?? 'kustom',
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'label'      => $periodeLabel,
            ],
            'summary'          => [
                'total_pesanan'     => $totalPesanan,
                'total_pendapatan'  => $totalPendapatan,
                'pendapatan_format' => 'Rp ' . number_format($totalPendapatan, 0, ',', '.'),
                'total_ppn'         => $totalPpn,
                'ppn_format'        => 'Rp ' . number_format($totalPpn, 0, ',', '.'),
                'total_potongan'    => $totalPotongan,
                'potongan_format'   => 'Rp ' . number_format($totalPotongan, 0, ',', '.'),
                'metode'            => $metodeBreakdown,
            ],
            'top_menus'        => $topMenus,
            'riwayat'          => $riwayatFormatted,
            'total_pesanan'    => $totalPesanan,
            'total_pendapatan' => $totalPendapatan,
        ]);
    }
}
