<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KategoriApiController extends Controller
{
    public function index()
    {
        $kategoris = Kategori::orderBy('id', 'ASC')->get()->map(function($k) {
            return [
                'id'            => $k->id,
                'nama'          => $k->nama_kategori,
                'nama_kategori' => $k->nama_kategori,
                'slug'          => $k->slug,
                'deskripsi'     => $k->deskripsi,
                'is_active'     => $k->is_active,
                'status'        => $k->is_active ? 'aktif' : 'nonaktif',
                'created_at'    => $k->created_at,
                'updated_at'    => $k->updated_at,
            ];
        });

        return response()->json([
            'success'   => true,
            'data'      => $kategoris,
            'kategoris' => $kategoris,
        ]);
    }

    public function store(Request $request)
    {
        $nama = $request->input('nama') ?? $request->input('nama_kategori');
        $desc = $request->input('deskripsi');
        $isActive = $request->has('is_active') ? $request->input('is_active') : 1;

        if (!$nama) {
            return response()->json(['success' => false, 'message' => 'Nama kategori wajib diisi.'], 422);
        }

        $kategori = Kategori::create([
            'nama_kategori' => $nama,
            'slug'          => Str::slug($nama),
            'deskripsi'     => $desc,
            'is_active'     => $isActive ? 1 : 0,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Kategori berhasil ditambahkan.',
            'kategori' => $kategori,
            'data'     => $kategori,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $kategori = Kategori::findOrFail($id);
        $nama = $request->input('nama') ?? $request->input('nama_kategori') ?? $kategori->nama_kategori;
        $desc = $request->input('deskripsi') ?? $kategori->deskripsi;
        $isActive = $request->has('is_active') ? $request->input('is_active') : $kategori->is_active;

        $kategori->update([
            'nama_kategori' => $nama,
            'slug'          => Str::slug($nama),
            'deskripsi'     => $desc,
            'is_active'     => $isActive ? 1 : 0,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Kategori berhasil diperbarui.',
            'kategori' => $kategori,
            'data'     => $kategori,
        ]);
    }

    public function destroy(int $id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();

        return response()->json(['success' => true, 'message' => 'Kategori berhasil dihapus.']);
    }

    public function toggleStatus(int $id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->update(['is_active' => $kategori->is_active == 1 ? 0 : 1]);

        return response()->json([
            'success'  => true,
            'message'  => 'Status kategori diperbarui.',
            'kategori' => $kategori,
        ]);
    }
}
