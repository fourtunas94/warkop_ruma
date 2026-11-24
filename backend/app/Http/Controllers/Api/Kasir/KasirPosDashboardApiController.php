<?php
namespace App\Http\Controllers\Api\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\PesananSplit;
use App\Models\Menu;
use App\Models\Meja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirPosDashboardApiController extends Controller
{
    public function index()
    {
        $hari_ini      = date('Y-m-d');
        $total_pesanan = Pesanan::whereDate('created_at', $hari_ini)->count();
        $pendapatan    = Pesanan::whereDate('created_at', $hari_ini)->where('status', 'selesai')->sum('total_harga');
        $total_menu    = Menu::where('is_active', 1)->count();
        $total_meja    = Meja::count();

        // Get today's orders, active kitchen orders, and all unpaid (open bill) orders
        $queryOrders = Pesanan::with(['meja', 'items.menu.kategori', 'splits'])
            ->where(function ($q) use ($hari_ini) {
                $q->whereDate('created_at', $hari_ini)
                  ->orWhereIn('status_pesanan', ['menunggu', 'proses', 'siap'])
                  ->orWhere('status', '!=', 'selesai');
            })
            ->orderBy('id', 'DESC')
            ->limit(50)
            ->get();

        $pesananData = $queryOrders->map(function ($p) {
            $nomorMeja = $p->meja->nomor_meja ?? null;
            $splitsData = $p->splits->map(fn($s) => [
                'id'                => $s->id,
                'label'             => $s->label,
                'tipe'              => $s->tipe,
                'jumlah'            => (int) $s->jumlah,
                'jumlah_format'     => 'Rp ' . number_format($s->jumlah, 0, ',', '.'),
                'metode_pembayaran' => $s->metode_pembayaran,
                'metode_label'      => $this->formatMetode($s->metode_pembayaran),
                'status'            => $s->status,
                'items'             => $s->items_json ?? [],
                'paid_at'           => $s->paid_at ? $s->paid_at->format('H:i') : null,
            ])->values();
            $totalTerbayar = $p->status === 'selesai'
                ? (int) $p->total_harga
                : (int) $p->splits->where('status', 'lunas')->sum('jumlah');
            $sisaTagihan = max(0, (int) $p->total_harga - $totalTerbayar);

            // Hitung qty yang sudah dibayar per item (untuk mode Bayar Item / Pulang Duluan)
            $paidQtyByItemId = [];
            foreach ($p->splits->where('status', 'lunas') as $s) {
                if (is_array($s->items_json)) {
                    foreach ($s->items_json as $si) {
                        $itemId = (int) ($si['item_id'] ?? 0);
                        $qtyPaid = (int) ($si['qty'] ?? 0);
                        if ($itemId > 0 && $qtyPaid > 0) {
                            $paidQtyByItemId[$itemId] = ($paidQtyByItemId[$itemId] ?? 0) + $qtyPaid;
                        }
                    }
                }
            }

            $unpaidItems = $p->items->map(function ($i) use ($paidQtyByItemId) {
                $paid = $paidQtyByItemId[$i->id] ?? 0;
                $rem  = max(0, (int) $i->qty - $paid);
                return [
                    'item_id'       => $i->id,
                    'nama_menu'     => $i->nama_menu,
                    'harga'         => (int) $i->harga,
                    'qty_total'     => (int) $i->qty,
                    'qty_paid'      => $paid,
                    'qty_remaining' => $rem,
                ];
            })->values();

            return [
                'id'                => $p->id,
                'kode_pesanan'      => $p->kode_pesanan,
                'nomor_antrian'     => $p->nomor_antrian ?? '-',
                'nama_pemesan'      => $p->nama_pemesan ?? 'Tamu Warkop',
                'nomor_meja'        => $nomorMeja ?? '-',
                'meja_id'           => $p->meja_id,
                'total_harga'       => (int) $p->total_harga,
                'total_format'      => 'Rp ' . number_format($p->total_harga, 0, ',', '.'),
                'total_terbayar'    => $totalTerbayar,
                'sisa_tagihan'      => $sisaTagihan,
                'is_split_bill'     => $splitsData->isNotEmpty(),
                'has_partial_items' => $p->splits->where('tipe', 'bayar_item')->isNotEmpty(),
                'unpaid_items'      => $unpaidItems,
                'splits'            => $splitsData,
                'status'            => $p->status, // 'menunggu', 'selesai', 'batal' (status pembayaran)
                'status_pesanan'    => $p->status_pesanan ?? 'menunggu', // 'menunggu', 'proses', 'siap', 'selesai'
                'metode_pembayaran' => $p->metode_pembayaran ?? 'tunai',
                'metode_label'      => $this->formatMetode($p->metode_pembayaran),
                'catatan'           => $p->catatan,
                'potongan'          => (int) ($p->potongan ?? 0),
                'ppn'               => 0,
                'waktu'             => $p->created_at ? $p->created_at->format('H:i') : '-',
                'created_at'        => $p->created_at ? $p->created_at->toIso8601String() : null,
                'waktu_siap'        => $p->waktu_siap ? $p->waktu_siap->toIso8601String() : null,
                'channel'           => empty($nomorMeja) && empty($p->meja_id) ? 'takeaway' : 'dine_in',
                'items'             => $p->items->map(fn($i) => [
                    'id'              => $i->id,
                    'menu_id'         => $i->menu_id,
                    'nama_menu'       => $i->nama_menu,
                    'harga'           => (int) $i->harga,
                    'harga_format'    => 'Rp ' . number_format($i->harga, 0, ',', '.'),
                    'qty'             => (int) $i->qty,
                    'subtotal'        => (int) ($i->harga * $i->qty),
                    'subtotal_format' => 'Rp ' . number_format($i->harga * $i->qty, 0, ',', '.'),
                    'catatan'         => $i->catatan,
                    'kategori'        => $i->menu?->kategori?->nama_kategori ?? 'Kopi & Minuman',
                    'gambar'          => $i->menu?->gambar ? asset('storage/' . $i->menu->gambar) : null,
                ])->values(),
            ];
        });

        // Antrean aktif kasir: sembunyikan pesanan yang sudah lunas DAN sudah selesai diantar (cukup tampil di Riwayat)
        $activeOrders = $pesananData->filter(function ($p) {
            $isPaid = strtolower((string) ($p['status'] ?? '')) === 'selesai';
            $isDelivered = strtolower((string) ($p['status_pesanan'] ?? '')) === 'selesai';
            return !($isPaid && $isDelivered);
        })->values();

        // Filter counts (hanya pesanan aktif di antrean kasir)
        $baruMasukCount    = $activeOrders->filter(fn($p) => $p['status_pesanan'] === 'menunggu')->count();
        $sedangDibuatCount = $activeOrders->filter(fn($p) => $p['status_pesanan'] === 'proses')->count();
        $siapAntarCount    = $activeOrders->filter(fn($p) => $p['status_pesanan'] === 'siap')->count();
        $belumBayarCount   = $activeOrders->filter(fn($p) => $p['status'] !== 'selesai')->count();
        $semuaCount        = $activeOrders->count();

        return response()->json([
            'success'           => true,
            'total_pesanan'     => $total_pesanan,
            'pendapatan'        => $pendapatan,
            'pendapatan_format' => 'Rp ' . number_format($pendapatan, 0, ',', '.'),
            'total_menu'        => $total_menu,
            'total_meja'        => $total_meja,
            'counts'            => [
                'baru_masuk'    => $baruMasukCount,
                'sedang_dibuat' => $sedangDibuatCount,
                'siap_antar'    => $siapAntarCount,
                'belum_bayar'   => $belumBayarCount,
                'semua'         => $semuaCount,
            ],
            'pesanan_terbaru'   => $pesananData->take(10)->values(),
            'pesanans'          => $activeOrders,
        ]);
    }

    public function updateStatusPesanan(Request $request, int $id)
    {
        $pesanan = Pesanan::find($id);
        if (!$pesanan) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $statusBaru = $request->input('status_pesanan');
        if (!in_array($statusBaru, ['menunggu', 'proses', 'siap', 'selesai'])) {
            return response()->json(['success' => false, 'message' => 'Status tidak valid.'], 400);
        }

        $data = ['status_pesanan' => $statusBaru];
        if ($statusBaru === 'siap' && !$pesanan->waktu_siap) {
            $data['waktu_siap'] = now();
        }

        $pesanan->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Status pesanan berhasil diperbarui ke ' . $statusBaru,
            'pesanan' => $pesanan,
        ]);
    }

    public function createSplitBill(Request $request, int $id)
    {
        $pesanan = Pesanan::with('splits')->find($id);
        if (!$pesanan) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($pesanan->status === 'selesai') {
            return response()->json(['success' => false, 'message' => 'Pesanan sudah lunas, tidak dapat di-split.'], 400);
        }

        if ($pesanan->splits->where('status', 'lunas')->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Sebagian split bill sudah dibayar, tidak dapat mengubah pembagian.',
            ], 400);
        }

        $tipe = $request->input('tipe', 'rata');
        if (!in_array($tipe, ['rata', 'item', 'nominal'])) {
            $tipe = 'rata';
        }

        $splits = $request->input('splits', []);
        if (!is_array($splits) || count($splits) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Split bill minimal harus dibagi menjadi 2 bagian.',
            ], 422);
        }

        $totalSplit = 0;
        $cleanSplits = [];
        foreach ($splits as $idx => $item) {
            $jumlah = (int) ($item['jumlah'] ?? 0);
            if ($jumlah <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setiap bagian split bill harus memiliki nominal lebih dari Rp 0.',
                ], 422);
            }
            $metode = strtolower((string) ($item['metode_pembayaran'] ?? 'tunai'));
            if (!in_array($metode, ['tunai', 'qris', 'transfer'])) {
                $metode = 'tunai';
            }
            $label = trim((string) ($item['label'] ?? ('Bagian ' . ($idx + 1))));
            if ($label === '') {
                $label = 'Bagian ' . ($idx + 1);
            }
            $cleanSplits[] = [
                'label'             => substr($label, 0, 100),
                'tipe'              => $tipe,
                'jumlah'            => $jumlah,
                'metode_pembayaran' => $metode,
                'status'            => 'pending',
                'items_json'        => isset($item['items']) && is_array($item['items']) ? $item['items'] : null,
            ];
            $totalSplit += $jumlah;
        }

        if ($totalSplit !== (int) $pesanan->total_harga) {
            return response()->json([
                'success' => false,
                'message' => 'Total pembagian (Rp ' . number_format($totalSplit, 0, ',', '.') . ') harus sama dengan total tagihan (Rp ' . number_format($pesanan->total_harga, 0, ',', '.') . ').',
            ], 422);
        }

        $created = DB::transaction(function () use ($pesanan, $cleanSplits) {
            $pesanan->splits()->delete();
            $rows = [];
            foreach ($cleanSplits as $row) {
                $rows[] = $pesanan->splits()->create($row);
            }
            return $rows;
        });

        return response()->json([
            'success' => true,
            'message' => 'Split bill berhasil dibuat (' . count($created) . ' bagian).',
            'splits'  => $created,
        ]);
    }

    public function bayarSplit(Request $request, int $id, int $splitId)
    {
        $pesanan = Pesanan::with('splits')->find($id);
        if (!$pesanan) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $split = PesananSplit::where('pesanan_id', $id)->where('id', $splitId)->first();
        if (!$split) {
            return response()->json(['success' => false, 'message' => 'Bagian split bill tidak ditemukan.'], 404);
        }

        if ($split->status === 'lunas') {
            return response()->json([
                'success'  => true,
                'message'  => 'Bagian ini sudah dibayar sebelumnya.',
                'all_paid' => $pesanan->status === 'selesai',
            ]);
        }

        $metode = strtolower((string) ($request->input('metode_pembayaran') ?: $split->metode_pembayaran));
        if (!in_array($metode, ['tunai', 'qris', 'transfer'])) {
            $metode = $split->metode_pembayaran;
        }

        $allPaid = DB::transaction(function () use ($pesanan, $split, $metode) {
            $split->update([
                'metode_pembayaran' => $metode,
                'status'            => 'lunas',
                'paid_at'           => now(),
            ]);

            $unpaidCount = PesananSplit::where('pesanan_id', $pesanan->id)
                ->where('status', '!=', 'lunas')
                ->count();

            if ($unpaidCount === 0) {
                $pesanan->update([
                    'status'            => 'selesai',
                    'metode_pembayaran' => 'split',
                ]);
                return true;
            }
            return false;
        });

        return response()->json([
            'success'  => true,
            'message'  => $allPaid
                ? 'Semua bagian split bill sudah lunas! Pesanan ditandai LUNAS.'
                : 'Pembayaran ' . $split->label . ' berhasil dikonfirmasi.',
            'all_paid' => $allPaid,
        ]);
    }

    public function resetSplitBill(int $id)
    {
        $pesanan = Pesanan::with('splits')->find($id);
        if (!$pesanan) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($pesanan->splits->where('status', 'lunas')->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Split bill tidak dapat dibatalkan karena sebagian sudah dibayar.',
            ], 400);
        }

        $pesanan->splits()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Split bill berhasil dibatalkan.',
        ]);
    }

    public function bayarSebagianItem(Request $request, int $id)
    {
        $pesanan = Pesanan::with(['items', 'splits'])->find($id);
        if (!$pesanan) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($pesanan->status === 'selesai') {
            return response()->json(['success' => false, 'message' => 'Pesanan ini sudah lunas.'], 400);
        }

        // Cegah campur dengan skema split bill pending (rata/nominal/item)
        if ($pesanan->splits->where('status', '!=', 'lunas')->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan sudah memiliki skema split bill aktif. Reset split terlebih dahulu atau bayar per bagian.',
            ], 400);
        }

        $paidQtyByItemId = [];
        foreach ($pesanan->splits->where('status', 'lunas') as $s) {
            if (is_array($s->items_json)) {
                foreach ($s->items_json as $si) {
                    $itemId = (int) ($si['item_id'] ?? 0);
                    $qtyPaid = (int) ($si['qty'] ?? 0);
                    if ($itemId > 0 && $qtyPaid > 0) {
                        $paidQtyByItemId[$itemId] = ($paidQtyByItemId[$itemId] ?? 0) + $qtyPaid;
                    }
                }
            }
        }

        $itemsById = $pesanan->items->keyBy('id');
        $remQtyById = [];
        $remSubtotalAll = 0;
        foreach ($pesanan->items as $item) {
            $rem = max(0, (int) $item->qty - ($paidQtyByItemId[$item->id] ?? 0));
            $remQtyById[$item->id] = $rem;
            $remSubtotalAll += (int) $item->harga * $rem;
        }

        $rawSelected = $request->input('items', []);
        if (!is_array($rawSelected) || empty($rawSelected)) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih minimal 1 menu yang ingin dibayar.',
            ], 422);
        }

        $cleanSelected = [];
        $selQtyById = [];
        $selSubtotal = 0;

        foreach ($rawSelected as $row) {
            $itemId = (int) ($row['item_id'] ?? 0);
            $qty    = (int) ($row['qty'] ?? 0);
            if ($itemId <= 0 || $qty <= 0) continue;

            if (!isset($itemsById[$itemId])) {
                return response()->json(['success' => false, 'message' => 'Menu tidak ditemukan pada pesanan ini.'], 422);
            }

            $totalRequested = ($selQtyById[$itemId] ?? 0) + $qty;
            if ($totalRequested > ($remQtyById[$itemId] ?? 0)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah menu "' . $itemsById[$itemId]->nama_menu . '" melebihi sisa yang belum dibayar.',
                ], 422);
            }

            $selQtyById[$itemId] = $totalRequested;
        }

        if (empty($selQtyById)) {
            return response()->json(['success' => false, 'message' => 'Pilih minimal 1 menu yang valid.'], 422);
        }

        foreach ($selQtyById as $itemId => $qty) {
            $it = $itemsById[$itemId];
            $selSubtotal += (int) $it->harga * $qty;
            $cleanSelected[] = [
                'item_id'   => $itemId,
                'nama_menu' => $it->nama_menu,
                'harga'     => (int) $it->harga,
                'qty'       => $qty,
            ];
        }

        $totalTerbayar = (int) $pesanan->splits->where('status', 'lunas')->sum('jumlah');
        $sisaTagihan   = max(0, (int) $pesanan->total_harga - $totalTerbayar);

        // Cek apakah seluruh sisa item terbayar pada transaksi ini
        $allRemainingPaid = true;
        foreach ($remQtyById as $itemId => $rem) {
            if (($rem - ($selQtyById[$itemId] ?? 0)) > 0) {
                $allRemainingPaid = false;
                break;
            }
        }

        if ($allRemainingPaid) {
            $jumlah = $sisaTagihan;
        } else {
            $jumlah = $remSubtotalAll > 0
                ? (int) round(($sisaTagihan * $selSubtotal) / $remSubtotalAll)
                : 0;
            if ($sisaTagihan > 1) {
                $jumlah = max(1, min($sisaTagihan - 1, $jumlah));
            }
        }

        $metode = strtolower((string) $request->input('metode_pembayaran', 'tunai'));
        if (!in_array($metode, ['tunai', 'qris', 'transfer'], true)) {
            $metode = 'tunai';
        }

        $label = trim((string) $request->input('label', ''));
        if ($label === '') {
            $label = 'Bayar Item #' . ($pesanan->splits->count() + 1);
        }
        $label = substr($label, 0, 100);

        $allPaid = DB::transaction(function () use ($pesanan, $label, $jumlah, $metode, $cleanSelected, $allRemainingPaid) {
            $pesanan->splits()->create([
                'label'             => $label,
                'tipe'              => 'bayar_item',
                'jumlah'            => $jumlah,
                'metode_pembayaran' => $metode,
                'status'            => 'lunas',
                'items_json'        => $cleanSelected,
                'paid_at'           => now(),
            ]);

            $totalPaidNow = (int) PesananSplit::where('pesanan_id', $pesanan->id)
                ->where('status', 'lunas')
                ->sum('jumlah');

            if ($allRemainingPaid || $totalPaidNow >= (int) $pesanan->total_harga) {
                $pesanan->update([
                    'status'            => 'selesai',
                    'metode_pembayaran' => 'split',
                ]);
                return true;
            }
            return false;
        });

        return response()->json([
            'success'  => true,
            'message'  => $allPaid
                ? "Pembayaran {$label} (Rp " . number_format($jumlah, 0, ',', '.') . ") berhasil! Seluruh tagihan kini LUNAS."
                : "Pembayaran {$label} (Rp " . number_format($jumlah, 0, ',', '.') . ") berhasil dicatat. Sisa menu tetap terbuka.",
            'all_paid' => $allPaid,
            'jumlah'   => $jumlah,
        ]);
    }

    public function mergeBill(Request $request, int $id)
    {
        $primary = Pesanan::with(['items', 'splits'])->find($id);
        if (!$primary) {
            return response()->json(['success' => false, 'message' => 'Pesanan utama tidak ditemukan.'], 404);
        }

        if ($primary->status === 'selesai') {
            return response()->json(['success' => false, 'message' => 'Pesanan utama sudah lunas, tidak dapat digabung.'], 400);
        }

        if ($primary->splits->where('status', 'lunas')->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan utama sudah memiliki sebagian split bill yang dibayar.',
            ], 400);
        }

        $rawIds = $request->input('merge_ids', []);
        if (!is_array($rawIds)) {
            return response()->json(['success' => false, 'message' => 'Daftar pesanan yang digabung tidak valid.'], 422);
        }

        $mergeIds = array_values(array_unique(array_filter(
            array_map('intval', $rawIds),
            fn($otherId) => $otherId > 0 && $otherId !== $id
        )));

        if (empty($mergeIds)) {
            return response()->json(['success' => false, 'message' => 'Pilih minimal 1 pesanan lain untuk digabungkan.'], 422);
        }

        $others = Pesanan::with(['items', 'splits'])->whereIn('id', $mergeIds)->get();
        if ($others->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Pesanan yang dipilih tidak ditemukan.'], 404);
        }

        foreach ($others as $other) {
            if ($other->status === 'selesai') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan ' . $other->kode_pesanan . ' sudah lunas, tidak dapat digabung.',
                ], 400);
            }
            if ($other->splits->where('status', 'lunas')->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan ' . $other->kode_pesanan . ' sudah memiliki sebagian split bill yang dibayar.',
                ], 400);
            }
        }

        $result = DB::transaction(function () use ($primary, $others) {
            // Hapus split bill pending pada pesanan utama karena total akan berubah
            $primary->splits()->delete();

            // Gabungkan nama pemesan unik
            $names = [];
            $addName = function (?string $raw) use (&$names) {
                if (!$raw) return;
                foreach (explode('&', $raw) as $part) {
                    $clean = trim($part);
                    if ($clean !== '' && !in_array($clean, $names, true)) {
                        $names[] = $clean;
                    }
                }
            };
            $addName($primary->nama_pemesan);

            $notes = [];
            if (!empty(trim((string) $primary->catatan))) {
                $notes[] = trim((string) $primary->catatan);
            }

            $totalPotongan = (int) ($primary->potongan ?? 0);

            foreach ($others as $other) {
                $addName($other->nama_pemesan);
                if (!empty(trim((string) $other->catatan))) {
                    $notes[] = trim((string) $other->catatan);
                }
                $totalPotongan += (int) ($other->potongan ?? 0);

                // Pindahkan semua item ke pesanan utama
                PesananItem::where('pesanan_id', $other->id)->update([
                    'pesanan_id' => $primary->id,
                ]);
            }

            // Hitung ulang subtotal dan total_harga (tanpa PPN / PB1)
            $subtotal = PesananItem::where('pesanan_id', $primary->id)
                ->get()
                ->sum(fn($i) => (int) $i->harga * (int) $i->qty);

            $totalSetelahDiskon = max(0, $subtotal - $totalPotongan);
            $ppn = 0;
            $totalHarga = $totalSetelahDiskon;

            $mergedName = substr(implode(' & ', $names), 0, 100);
            $mergedNote = !empty($notes) ? implode(' | ', array_unique($notes)) : null;

            $primary->update([
                'nama_pemesan' => $mergedName !== '' ? $mergedName : $primary->nama_pemesan,
                'catatan'      => $mergedNote,
                'potongan'     => $totalPotongan,
                'ppn'          => $ppn,
                'total_harga'  => $totalHarga,
            ]);

            // Hapus pesanan lama yang sudah digabung agar tidak dobel
            Pesanan::whereIn('id', $others->pluck('id')->all())->delete();

            return [
                'merged_count' => $others->count(),
                'total_harga'  => $totalHarga,
                'nama_pemesan' => $primary->nama_pemesan,
            ];
        });

        return response()->json([
            'success'      => true,
            'message'      => 'Berhasil menggabungkan ' . ($result['merged_count'] + 1) . ' pesanan menjadi 1 tagihan (' . $result['nama_pemesan'] . ').',
            'pesanan_id'   => $primary->id,
            'total_harga'  => $result['total_harga'],
        ]);
    }

    private function formatMetode(?string $metode): string
    {
        return match (strtolower($metode ?? '')) {
            'tunai'    => 'Tunai / Kasir',
            'qris'     => 'QRIS / E-Wallet',
            'transfer' => 'Transfer Bank / VA',
            'split'    => 'Split Bill',
            'gratis'   => 'Gratis (Games)',
            default    => ucfirst($metode ?? 'Tunai'),
        };
    }
}

