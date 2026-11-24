<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\StokBarang;
use App\Models\PembelianBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class StokApiController extends Controller
{
    public function index()
    {
        $items = StokBarang::orderBy('nama_barang', 'asc')->get();

        $totalItems = $items->count();
        $stokMenipisCount = $items->filter(function ($item) {
            return $item->stok <= $item->minimum_stok;
        })->count();

        $totalPengeluaran = PembelianBarang::sum('total_biaya');

        return response()->json([
            'success' => true,
            'data' => $items,
            'summary' => [
                'total_items' => $totalItems,
                'stok_menipis' => $stokMenipisCount,
                'total_pengeluaran' => (double) $totalPengeluaran,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'stok' => 'nullable|numeric|min:0',
            'satuan' => 'required|string|max:50',
            'minimum_stok' => 'required|numeric|min:0',
            'harga_beli_terakhir' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
        ]);

        $item = StokBarang::create([
            'nama_barang' => $request->nama_barang,
            'kategori' => $request->kategori ?? 'Bahan Baku',
            'stok' => $request->stok ?? 0,
            'satuan' => $request->satuan,
            'minimum_stok' => $request->minimum_stok,
            'harga_beli_terakhir' => $request->harga_beli_terakhir ?? 0,
            'supplier' => $request->supplier,
            'catatan' => $request->catatan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Barang stok berhasil ditambahkan.',
            'data' => $item,
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = StokBarang::findOrFail($id);

        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'stok' => 'nullable|numeric|min:0',
            'satuan' => 'required|string|max:50',
            'minimum_stok' => 'required|numeric|min:0',
            'harga_beli_terakhir' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
        ]);

        $item->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data barang stok berhasil diperbarui.',
            'data' => $item,
        ]);
    }

    public function destroy($id)
    {
        $item = StokBarang::findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Barang stok berhasil dihapus.',
        ]);
    }

    public function catatPembelian(Request $request)
    {
        $request->validate([
            'stok_barang_id' => 'required|exists:stok_barangs,id',
            'jumlah' => 'required|numeric|min:0.01',
            'total_biaya' => 'required|numeric|min:0',
            'tanggal_pembelian' => 'required|date',
            'supplier' => 'nullable|string|max:255',
            'foto_nota' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'catatan' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $barang = StokBarang::lockForUpdate()->findOrFail($request->stok_barang_id);

            $fotoPath = null;
            if ($request->hasFile('foto_nota')) {
                $file = $request->file('foto_nota');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $targetDir = public_path('uploads/stok_pembelian');
                if (!File::exists($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true);
                }
                $file->move($targetDir, $filename);
                $fotoPath = 'uploads/stok_pembelian/' . $filename;
            }

            $pembelian = PembelianBarang::create([
                'stok_barang_id' => $barang->id,
                'jumlah' => (double) $request->jumlah,
                'total_biaya' => (double) $request->total_biaya,
                'supplier' => $request->supplier ?? $barang->supplier,
                'tanggal_pembelian' => $request->tanggal_pembelian,
                'foto_nota' => $fotoPath,
                'catatan' => $request->catatan,
            ]);

            // Update current stock and latest purchase price
            $barang->stok += (double) $request->jumlah;
            if ($request->jumlah > 0) {
                $barang->harga_beli_terakhir = (double) $request->total_biaya / (double) $request->jumlah;
            }
            if ($request->supplier) {
                $barang->supplier = $request->supplier;
            }
            $barang->save();

            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil dicatat dan stok bertambah.',
                'data' => $pembelian->load('barang'),
            ]);
        });
    }

    public function riwayatPembelian()
    {
        $riwayat = PembelianBarang::with('barang')
            ->orderBy('tanggal_pembelian', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) {
                $fotoUrl = $item->foto_nota ? url($item->foto_nota) : null;
                return [
                    'id' => $item->id,
                    'stok_barang_id' => $item->stok_barang_id,
                    'nama_barang' => $item->barang?->nama_barang ?? 'Barang Dihapus',
                    'satuan' => $item->barang?->satuan ?? 'pcs',
                    'jumlah' => $item->jumlah,
                    'total_biaya' => $item->total_biaya,
                    'supplier' => $item->supplier ?? '-',
                    'tanggal_pembelian' => $item->tanggal_pembelian,
                    'foto_nota' => $item->foto_nota,
                    'foto_nota_url' => $fotoUrl,
                    'catatan' => $item->catatan ?? '',
                    'created_at' => $item->created_at?->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $riwayat,
        ]);
    }
}
