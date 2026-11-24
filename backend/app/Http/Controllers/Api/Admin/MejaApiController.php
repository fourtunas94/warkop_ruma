<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meja;
use Illuminate\Http\Request;

class MejaApiController extends Controller
{
    public function index()
{
    $mejas = Meja::orderBy('id', 'ASC')->get()->map(function ($m) {
        // Mengambil base URL dari APP_URL di file .env
        $baseUrl = rtrim(config('app.url'), '/');
        $qrUrl = $baseUrl . '/order/' . $m->kode_unik;

        return array_merge($m->toArray(), ['qr_url' => $qrUrl]);
    });

    return response()->json(['success' => true, 'mejas' => $mejas]);
}

    public function store(Request $request)
    {
        $request->validate([
            'nomor_meja' => 'required|max:10|unique:mejas,nomor_meja',
        ]);

        $meja = Meja::create([
            'nomor_meja' => strtoupper($request->input('nomor_meja')),
            'kode_unik'  => md5(uniqid(rand(), true)),
            'is_active'  => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Meja berhasil ditambahkan.',
            'meja'    => $meja,
        ], 201);
    }

    public function destroy(int $id)
    {
        $meja = Meja::findOrFail($id);
        $meja->delete();
        return response()->json(['success' => true, 'message' => 'Meja berhasil dihapus.']);
    }

    public function toggleStatus(int $id)
    {
        $meja = Meja::findOrFail($id);
        $meja->update(['is_active' => $meja->is_active == 1 ? 0 : 1]);
        return response()->json(['success' => true, 'message' => 'Status meja diperbarui.']);
    }
}
