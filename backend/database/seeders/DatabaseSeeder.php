<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Menu;
use App\Models\Meja;
use App\Models\Setting;
use App\Models\Promo;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);

        // Default Settings
        $defaultSettings = [
            'store_name'        => 'WARKOP RUMA',
            'store_logo'        => 'assets/img/logo_k.jpeg',
            'store_favicon'     => 'assets/img/logo_k.jpeg',
            'store_description' => 'Sistem Pemesanan QR Code untuk Restoran dan Kafe.',
            'store_whatsapp'    => '081234567890',
            'store_address'     => 'Jl. Kuliner No. 123',
            'footer_text'       => 'MI 5B',
        ];
        foreach ($defaultSettings as $key => $value) {
            Setting::firstOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        }

        // Sample Meja
        Meja::firstOrCreate(['nomor_meja' => '01'], [
            'kode_unik' => 'meja01',
            'is_active' => 1,
        ]);
        Meja::firstOrCreate(['nomor_meja' => '02'], [
            'kode_unik' => 'meja02',
            'is_active' => 1,
        ]);
        Meja::firstOrCreate(['nomor_meja' => '03'], [
            'kode_unik' => 'meja03',
            'is_active' => 1,
        ]);

        // Sample Kategori
        $katMakanan = Kategori::firstOrCreate(['slug' => 'makanan'], [
            'nama_kategori' => 'Makanan',
            'deskripsi'     => 'Aneka makanan lezat dan gurih',
            'is_active'     => 1,
        ]);
        $katMinuman = Kategori::firstOrCreate(['slug' => 'minuman'], [
            'nama_kategori' => 'Minuman',
            'deskripsi'     => 'Aneka minuman segar',
            'is_active'     => 1,
        ]);
        $katSnack = Kategori::firstOrCreate(['slug' => 'snack'], [
            'nama_kategori' => 'Snack',
            'deskripsi'     => 'Aneka camilan lezat',
            'is_active'     => 1,
        ]);
        $katGames = Kategori::firstOrCreate(['slug' => 'Games'], [
            'nama_kategori' => 'Games',
            'deskripsi'     => 'Aneka permainan menarik',
            'is_active'     => 1,
        ]);
        // Sample Menu
        Menu::firstOrCreate(['nama_menu' => 'Mie Pedas Level 1'], [
            'kategori_id' => $katMakanan->id,
            'deskripsi'   => 'Mie gurih pedas manis dengan taburan ayam cincang dan pangsit goreng.',
            'harga'       => 15000,
            'stok'        => 1,
            'gambar'      => '1779824383_f2b907ed917805ebcec7.png',
            'is_active'   => 1,
        ]);
        Menu::firstOrCreate(['nama_menu' => 'Mie Pedas Level 3'], [
            'kategori_id' => $katMakanan->id,
            'deskripsi'   => 'Mie super pedas ekstra cabai dengan taburan ayam cincang.',
            'harga'       => 16000,
            'stok'        => 1,
            'gambar'      => '1779905623_ce768947b078c303bb18.png',
            'is_active'   => 1,
        ]);
        Menu::firstOrCreate(['nama_menu' => 'Es Teh Manis'], [
            'kategori_id' => $katMinuman->id,
            'deskripsi'   => 'Teh melati manis segar dingin.',
            'harga'       => 5000,
            'stok'        => 1,
            'gambar'      => '1779905676_7505f47189e869a04eaf.png',
            'is_active'   => 1,
        ]);
        Menu::firstOrCreate(['nama_menu' => 'Es Jeruk Segar'], [
            'kategori_id' => $katMinuman->id,
            'deskripsi'   => 'Perasan jeruk asli dingin dan menyegarkan.',
            'harga'       => 7000,
            'stok'        => 1,
            'gambar'      => '1779906509_7da220b5ffd44ca718ae.png',
            'is_active'   => 1,
        ]);

        // Sample Promo
        Promo::firstOrCreate(['kode' => 'DISKON10'], [
            'nama_promo'      => 'Diskon 10% Spesial',
            'deskripsi'       => 'Potongan 10% untuk semua menu tanpa minimal belanja',
            'tipe_diskon'     => 'persen',
            'nilai_diskon'    => 10,
            'maksimal_diskon' => 10000,
            'minimal_belanja' => 0,
            'kuota'           => 100,
            'is_active'       => 1,
        ]); 
    }
}
