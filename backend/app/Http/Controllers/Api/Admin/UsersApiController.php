<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsersApiController extends Controller
{
    public function index()
    {
        $users = User::orderBy('role')
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'username', 'role', 'created_at']);

        return response()->json(['success' => true, 'users' => $users]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|max:100',
            'username'     => 'required|max:50|unique:users,username',
            'password'     => 'required|min:6',
            'role'         => 'required|in:admin,kasir',
        ]);

        $user = User::create([
            'nama_lengkap' => $request->input('nama_lengkap'),
            'username'     => $request->input('username'),
            'password'     => bcrypt($request->input('password')),
            'role'         => $request->input('role'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil ditambahkan.',
            'user'    => $user,
        ], 201);
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['success' => true, 'message' => 'Akun berhasil dihapus.']);
    }
}
