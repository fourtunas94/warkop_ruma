<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;

class PromoApiController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'promos'  => Promo::orderBy('id', 'DESC')->get(),
        ]);
    }

    public function store(Request $request)
    {
        return $this->savePromo($request);
    }

    public function update(Request $request, int $id)
    {
        return $this->savePromo($request, $id);
    }

    public function toggle(int $id)
    {
        $promo = Promo::findOrFail($id);
        $promo->update(['is_active' => $promo->is_active == 1 ? 0 : 1]);
        return response()->json(['success' => true, 'message' => 'Status promo diperbarui.']);
    }

    public function destroy(int $id)
    {
        Promo::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Promo berhasil dihapus.']);
    }

    private function savePromo(Request $request, ?int $id = null)
    {
        $kode   = strtoupper(trim($request->input('kode', '')));
        $errors = [];

        if ($kode === '') {
            $errors[] = 'Kode promo wajib diisi.';
        }
        if ($kode !== '' && !preg_match('/^[A-Z0-9_-]+$/', $kode)) {
            $errors[] = 'Kode promo hanya boleh huruf, angka, strip, dan underscore.';
        }

        $kodeCheck = Promo::where('kode', $kode);
        if ($id) {
            $kodeCheck->where('id', '!=', $id);
        }
        if ($kode !== '' && $kodeCheck->exists()) {
            $errors[] = 'Kode promo sudah digunakan.';
        }

        $tipeDiskon  = trim($request->input('tipe_diskon', ''));
        $nilaiDiskon = (int) $request->input('nilai_diskon', 0);

        if (!in_array($tipeDiskon, ['persen', 'nominal'])) {
            $errors[] = 'Tipe diskon tidak valid.';
        }
        if ($tipeDiskon === 'persen' && ($nilaiDiskon < 1 || $nilaiDiskon > 100)) {
            $errors[] = 'Diskon persen harus 1-100.';
        }
        if ($tipeDiskon === 'nominal' && $nilaiDiskon < 1) {
            $errors[] = 'Diskon nominal harus > 0.';
        }

        if (!empty($errors)) {
            return response()->json(['success' => false, 'errors' => $errors], 422);
        }

        $data = [
            'kode'            => $kode,
            'nama_promo'      => trim($request->input('nama_promo', '')),
            'deskripsi'       => trim($request->input('deskripsi', '')) ?: null,
            'tipe_diskon'     => $tipeDiskon,
            'nilai_diskon'    => $nilaiDiskon,
            'maksimal_diskon' => (int) $request->input('maksimal_diskon', 0),
            'minimal_belanja' => (int) $request->input('minimal_belanja', 0),
            'kuota'           => (int) $request->input('kuota', 0),
            'berlaku_mulai'   => $request->input('berlaku_mulai') ?: null,
            'berlaku_sampai'  => $request->input('berlaku_sampai') ?: null,
            'is_active'       => $request->input('is_active') === '1' ? 1 : 0,
        ];

        if ($id) {
            $promo = Promo::findOrFail($id);
            $promo->update($data);
            return response()->json([
                'success' => true,
                'message' => 'Promo berhasil diperbarui.',
                'promo'   => $promo,
            ]);
        }

        $promo = Promo::create($data);
        return response()->json([
            'success' => true,
            'message' => 'Promo berhasil ditambahkan.',
            'promo'   => $promo,
        ], 201);
    }
}
