<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Users extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        
        $users = $db->table('users')
                    ->orderBy('role', 'ASC')
                    ->orderBy('nama_lengkap', 'ASC')
                    ->get()
                    ->getResultArray();

        $data = [
            'title' => 'Manajemen Akun — ARZANA',
            'users' => $users
        ];

        return view('admin/users', $data);
    }

    public function store()
    {
        $db = \Config\Database::connect();
        
        $password = (string)$this->request->getPost('password');
        
        $data = [
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'username'     => $this->request->getPost('username'),
            'password'     => password_hash($password, PASSWORD_DEFAULT),
            'role'         => $this->request->getPost('role')
        ];

        $db->table('users')->insert($data);
        
        return redirect()->to(base_url('admin/users'))->with('success', 'Akun baru berhasil ditambahkan.');
    }

    public function delete($id)
    {
        $db = \Config\Database::connect();
        
        $db->table('users')->where('id', $id)->delete();
        
        return redirect()->to(base_url('admin/users'))->with('success', 'Akun berhasil dihapus.');
    }
}