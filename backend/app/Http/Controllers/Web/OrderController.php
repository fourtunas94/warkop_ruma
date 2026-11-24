<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function menu(string $kode)
    {
        $meja = Meja::where('kode_unik', $kode)->first();

        if (! $meja) {
            return view('order.invalid');
        }

        $kategoris = Kategori::where('is_active', 1)
            ->orWhereNull('is_active')
            ->get();

        $menus = Menu::where('is_active', 1)
            ->orWhereNull('is_active')
            ->get();

        $menuByKategori = [];

        foreach ($kategoris as $k) {
            $kid = is_array($k) ? $k['id'] : $k->id;
            $menuByKategori[$kid] = [
                'nama'  => is_array($k) ? ($k['nama_kategori'] ?? '-') : ($k->nama_kategori ?? '-'),
                'items' => [],
            ];
        }

        foreach ($menus as $m) {
            $kid = is_array($m) ? ($m['kategori_id'] ?? 0) : ($m->kategori_id ?? 0);

            if (isset($menuByKategori[$kid])) {
                $menuByKategori[$kid]['items'][] = $m;
            }
        }

        $promos = Promo::where('is_active', 1)
            ->orWhereNull('is_active')
            ->limit(5)
            ->get();

        return view('order.menu', [
            'meja'           => $meja,
            'menuByKategori' => $menuByKategori,
            'promos'         => $promos,
        ]);
    }

    public function checkout(string $kode)
    {
        $meja = Meja::where('kode_unik', $kode)->first();

        if (! $meja) {
            return view('order.invalid');
        }

        return view('order.checkout', [
            'meja' => $meja,
        ]);
    }

    public function cekPromo(Request $request, string $kodeUnik)
    {
        $meja = Meja::where('kode_unik', $kodeUnik)->first();

        if (! $meja) {
            return response()->json([
                'success' => false,
                'message' => 'Meja tidak valid.',
            ]);
        }

        $kodePromo = strtoupper(trim((string) $request->input('kode_promo', '')));
        $subtotal  = (int) $request->input('subtotal', 0);

        if ($kodePromo === '') {
            return response()->json([
                'success' => false,
                'message' => 'Masukkan kode promo terlebih dahulu.',
            ]);
        }

        $promo = Promo::whereRaw('UPPER(kode) = ?', [$kodePromo])
            ->where(function ($q) {
                $q->where('is_active', 1)->orWhereNull('is_active');
            })
            ->first();

        if (! $promo) {
            return response()->json([
                'success' => false,
                'message' => 'Kode promo tidak ditemukan atau tidak aktif.',
            ]);
        }

        $minBelanja = (int) ($promo->minimal_belanja ?? 0);
        if ($minBelanja > 0 && $subtotal < $minBelanja) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal belanja untuk promo ini Rp ' . number_format($minBelanja, 0, ',', '.'),
            ]);
        }

        $potongan = $this->hitungPotonganPromo($promo, $subtotal);

        return response()->json([
            'success'  => true,
            'potongan' => $potongan,
            'message'  => 'Promo berhasil diterapkan.',
        ]);
    }

    public function submit(Request $request, string $kode)
    {
        $meja = Meja::where('kode_unik', $kode)->first();

        if (! $meja) {
            return response()->json([
                'success' => false,
                'message' => 'Meja tidak valid.',
            ]);
        }

        $validated = $request->validate([
            'nama_pemesan' => 'required|string|min:2|max:100',
            'items'        => 'required|string',
        ]);

        $items = json_decode($validated['items'], true);

        if (empty($items) || ! is_array($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong.',
            ]);
        }

        $metodePembayaran = strtolower(trim((string) $request->input('payment_method', 'tunai')));
        if (! in_array($metodePembayaran, ['tunai', 'qris'], true)) {
            $metodePembayaran = 'tunai';
        }

        $kodePromo   = strtoupper(trim((string) $request->input('kode_promo', '')));
        $namaPemesan = trim((string) $validated['nama_pemesan']);
        $catatan     = trim((string) $request->input('catatan', ''));

        $cleanItems = [];
        $subTotal   = 0;

        foreach ($items as $item) {
            $menuId = (int) ($item['id'] ?? 0);
            $qty    = (int) ($item['qty'] ?? 0);

            if ($menuId <= 0 || $qty <= 0) {
                continue;
            }

            $menu = Menu::find($menuId);

            if (! $menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salah satu menu tidak ditemukan.',
                ]);
            }

            if (isset($menu->is_active) && (int) $menu->is_active !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu "' . ($menu->nama_menu ?? '-') . '" sedang tidak aktif.',
                ]);
            }

            if (isset($menu->stok) && (int) $menu->stok <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu "' . ($menu->nama_menu ?? '-') . '" sedang habis.',
                ]);
            }

            $harga = max(0, (int) ($menu->harga ?? 0));

            $cleanItems[] = [
                'id'        => $menuId,
                'nama_menu' => $menu->nama_menu ?? '-',
                'harga'     => $harga,
                'qty'       => $qty,
                'catatan'   => $item['catatan'] ?? null,
            ];

            $subTotal += $harga * $qty;
        }

        if (empty($cleanItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang tidak valid.',
            ]);
        }

        $potongan = 0;
        $promoObj = null;

        if ($kodePromo !== '') {
            $promoObj = Promo::whereRaw('UPPER(kode) = ?', [$kodePromo])
                ->where(function ($q) {
                    $q->where('is_active', 1)->orWhereNull('is_active');
                })
                ->first();

            if (! $promoObj) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode promo tidak valid.',
                ]);
            }

            $minBelanja = (int) ($promoObj->minimal_belanja ?? 0);
            if ($minBelanja > 0 && $subTotal < $minBelanja) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimal belanja promo belum terpenuhi.',
                ]);
            }

            $potongan = $this->hitungPotonganPromo($promoObj, $subTotal);
        }

        $totalSetelahDiskon = max(0, $subTotal - $potongan);
        $ppn                = 0;
        $totalHarga         = $totalSetelahDiskon;

        $kodeP        = $this->generateKodePesanan();
        $nomorAntrian = $this->generateNomorAntrian();
        $statusAwal   = ($totalHarga <= 0) ? 'selesai' : 'pending';
        if ($totalHarga <= 0) {
            $metodePembayaran = 'gratis';
        }
        $mejaId       = is_array($meja) ? $meja['id'] : $meja->id;

        DB::beginTransaction();

        try {
            $pesanan = Pesanan::create([
                'kode_pesanan'      => $kodeP,
                'nomor_antrian'     => $nomorAntrian,
                'meja_id'           => $mejaId,
                'nama_pemesan'      => $namaPemesan,
                'total_harga'       => $totalHarga,
                'catatan'           => $catatan !== '' ? $catatan : null,
                'status'            => $statusAwal,
                'status_pesanan'    => 'menunggu',
                'waktu_siap'        => null,
                'metode_pembayaran' => $metodePembayaran,
                'kode_promo'        => $kodePromo !== '' ? $kodePromo : null,
                'potongan'          => $potongan,
                'ppn'               => $ppn,
            ]);

            foreach ($cleanItems as $item) {
                PesananItem::create([
                    'pesanan_id' => $pesanan->id,
                    'menu_id'    => $item['id'],
                    'nama_menu'  => $item['nama_menu'],
                    'harga'      => $item['harga'],
                    'qty'        => $item['qty'],
                    'catatan'    => $item['catatan'],
                ]);
            }

            if ($promoObj && isset($promoObj->jumlah_digunakan)) {
                $promoObj->increment('jumlah_digunakan');
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'redirect' => url('order/sukses/' . $kodeP),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sukses(string $kode)
    {
        $pesanan = Pesanan::where('kode_pesanan', $kode)->first();

        if (! $pesanan) {
            return redirect('/');
        }

        $items = PesananItem::where('pesanan_id', $pesanan->id)->get();
        $meja  = Meja::find($pesanan->meja_id);

        return view('order.sukses', [
            'pesanan' => $pesanan,
            'items'   => $items,
            'meja'    => $meja,
        ]);
    }

    private function hitungPotonganPromo($promo, int $subtotal): int
    {
        $tipe  = strtolower((string) ($promo->tipe_diskon ?? 'nominal'));
        $nilai = (int) ($promo->nilai_diskon ?? 0);
        $maks  = (int) ($promo->maksimal_diskon ?? 0);

        if ($nilai <= 0 || $subtotal <= 0) {
            return 0;
        }

        if ($tipe === 'persen') {
            $diskon = (int) floor($subtotal * ($nilai / 100));
            if ($maks > 0 && $diskon > $maks) {
                $diskon = $maks;
            }
            return min($subtotal, max(0, $diskon));
        }

        return min($subtotal, max(0, $nilai));
    }

    private function generateKodePesanan(): string
    {
        do {
            $kode = 'ORD-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        } while (Pesanan::where('kode_pesanan', $kode)->exists());

        return $kode;
    }

    private function generateNomorAntrian(): string
    {
        $countToday = Pesanan::whereDate('created_at', today())->count() + 1;
        return str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);
    }
}