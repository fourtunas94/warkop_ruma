<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function index()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(session()->get('role') . '/dashboard');
        }
        
        return view('auth/login');
    }

    public function login()
    {
        $session = session();
        $model = new UserModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $model->where('username', $username)->first();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                
                $sessionData = [
                    'id'           => $user['id'],
                    'nama_lengkap' => $user['nama_lengkap'],
                    'username'     => $user['username'],
                    'role'         => $user['role'],
                    'isLoggedIn'   => true,
                ];
                $session->set($sessionData);

                return redirect()->to($user['role'] . '/dashboard');
                
            } else {
                $session->setFlashdata('msg', 'Password salah.');
                return redirect()->to('/auth');
            }
        } else {
            $session->setFlashdata('msg', 'Username tidak ditemukan.');
            return redirect()->to('/auth');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth');
    }
}