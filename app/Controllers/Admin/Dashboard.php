<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PesananModel;
use App\Models\MenuModel;
use App\Models\MejaModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $pesananModel = new PesananModel();
        $menuModel    = new MenuModel();
        $mejaModel    = new MejaModel();
        
        $hari_ini = date('Y-m-d');
        
        $total_pesanan = $pesananModel->where('DATE(created_at)', $hari_ini)->countAllResults();
        
        $pendapatan_row = $pesananModel->selectSum('total_harga')
                                       ->where('DATE(created_at)', $hari_ini)
                                       ->where('status', 'selesai')
                                       ->first();
        $pendapatan = $pendapatan_row['total_harga'] ?? 0;
        
        $total_menu = $menuModel->where('is_active', 1)->countAllResults();
        
        $total_meja = $mejaModel->countAllResults();
        
        $db = \Config\Database::connect();
        $pesanan_terbaru = $db->table('pesanans') 
                              ->select('pesanans.*, mejas.nomor_meja') 
                              ->join('mejas', 'mejas.id = pesanans.meja_id', 'left') 
                              ->orderBy('pesanans.created_at', 'DESC') 
                              ->limit(5)
                              ->get()
                              ->getResultArray();

        $data = [
            'title'           => 'Dashboard Admin — ARZANA',
            'total_pesanan'   => $total_pesanan,
            'pendapatan'      => $pendapatan,
            'total_menu'      => $total_menu,
            'total_meja'      => $total_meja,
            'pesanan_terbaru' => $pesanan_terbaru
        ];
        
        return view('admin/dashboard', $data);
    }
}