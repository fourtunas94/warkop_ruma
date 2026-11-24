<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuApiController extends Controller
{
    private function isGamesCategory($kategoriId): bool
    {
        if (empty($kategoriId)) {
            return false;
        }
        $kat = Kategori::find($kategoriId);
        if (! $kat) {
            return false;
        }
        return str_contains(strtolower((string) $kat->nama_kategori), 'game');
    }

    public function index()
    {
        $menus = Menu::with('kategori')->orderBy('id', 'DESC')->get();
        return response()->json(['success' => true, 'menus' => $menus]);
    }

    public function store(Request $request)
    {
        $isGames = $this->isGamesCategory($request->input('kategori_id'));

        $validated = $request->validate([
            'nama_menu'   => 'required|min:2|max:100',
            'harga'       => $isGames ? 'nullable|integer|min:0' : 'required|integer|min:1',
            'kategori_id' => 'nullable|integer',
            'deskripsi'   => 'nullable|max:500',
            'stok'        => 'required|in:0,1',
            'is_active'   => 'required|in:0,1',
            'gambar'      => 'nullable|image|max:2048',
        ]);

        if ($isGames) {
            $validated['harga'] = 0;
        }

        if ($request->hasFile('gambar')) {
            $file      = $request->file('gambar');
            $uploadDir = public_path('uploads/menu');
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $safeName            = preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $newName             = time() . '_' . $safeName;
            $file->move($uploadDir, $newName);
            $validated['gambar'] = $newName;
        }

        $menu = Menu::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil ditambahkan.',
            'menu'    => $menu,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $menu    = Menu::findOrFail($id);
        $isGames = $this->isGamesCategory($request->input('kategori_id'));

        $validated = $request->validate([
            'nama_menu'   => 'required|min:2|max:100',
            'harga'       => $isGames ? 'nullable|integer|min:0' : 'required|integer|min:1',
            'kategori_id' => 'nullable|integer',
            'deskripsi'   => 'nullable|max:500',
            'stok'        => 'required|in:0,1',
            'is_active'   => 'required|in:0,1',
            'gambar'      => 'nullable|image|max:2048',
        ]);

        if ($isGames) {
            $validated['harga'] = 0;
        }

        if ($request->hasFile('gambar')) {
            $file      = $request->file('gambar');
            $uploadDir = public_path('uploads/menu');
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $safeName            = preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $newName             = time() . '_' . $safeName;
            $file->move($uploadDir, $newName);
            $validated['gambar'] = $newName;
        }

        $menu->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil diperbarui.',
            'menu'    => $menu,
        ]);
    }

    public function destroy(int $id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();
        return response()->json(['success' => true, 'message' => 'Menu berhasil dihapus.']);
    }

    public function toggleStatus(int $id)
    {
        $menu = Menu::findOrFail($id);
        $menu->update(['is_active' => $menu->is_active == 1 ? 0 : 1]);
        return response()->json(['success' => true, 'message' => 'Status menu diperbarui.']);
    }

    public function toggleStok(int $id)
    {
        $menu = Menu::findOrFail($id);
        $menu->update(['stok' => $menu->stok == 1 ? 0 : 1]);
        return response()->json(['success' => true, 'message' => 'Status stok diperbarui.']);
    }
}
