<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index()
    {
        if (session()->has('user_id')) {
            return redirect(session('role') . '/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        $user = User::where('username', $username)->first();

        if ($user && Hash::check($password, $user->password)) {
            session([
                'user_id'      => $user->id,
                'nama_lengkap' => $user->nama_lengkap,
                'username'     => $user->username,
                'role'         => $user->role,
            ]);
            return redirect($user->role . '/dashboard');
        }

        return back()->with('msg', 'Username atau password salah.');
    }

    public function logout()
    {
        session()->flush();
        return redirect('/auth');
    }
}
