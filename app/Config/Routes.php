<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Auth::index');
$routes->get('/login', 'Auth::index');
$routes->post('/auth/login', 'Auth::login');
$routes->get('/logout', 'Auth::logout');

$routes->get('order/(:segment)',             'Order::menu/$1');
$routes->get('order/checkout/(:segment)',    'Order::checkout/$1');
$routes->post('order/cek-promo/(:segment)',  'Order::cekPromo/$1');
$routes->post('order/submit/(:segment)',     'Order::submit/$1');
$routes->get('order/sukses/(:segment)',      'Order::sukses/$1');
$routes->post('order/midtrans-notification', 'Order::midtransNotification');

$routes->group('admin', ['filter' => 'role:admin'], function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');

    // Kategori
    $routes->get('kategori',                'Admin\Kategori::index');
    $routes->get('kategori/create',         'Admin\Kategori::create');
    $routes->post('kategori/store',         'Admin\Kategori::store');
    $routes->get('kategori/edit/(:num)',    'Admin\Kategori::edit/$1');
    $routes->post('kategori/update/(:num)', 'Admin\Kategori::update/$1');
    $routes->post('kategori/delete/(:num)', 'Admin\Kategori::delete/$1');
    $routes->post('kategori/toggle/(:num)', 'Admin\Kategori::toggleStatus/$1');

    // Menu
    $routes->get('menu',                     'Admin\Menu::index');
    $routes->get('menu/create',              'Admin\Menu::create');
    $routes->post('menu/store',              'Admin\Menu::store');
    $routes->get('menu/edit/(:num)',         'Admin\Menu::edit/$1');
    $routes->post('menu/update/(:num)',      'Admin\Menu::update/$1');
    $routes->post('menu/delete/(:num)',      'Admin\Menu::delete/$1');
    $routes->post('menu/toggle/(:num)',      'Admin\Menu::toggleStatus/$1');
    $routes->post('menu/toggle-stok/(:num)', 'Admin\Menu::toggleStok/$1');

    // Meja
    $routes->get('meja',                'Admin\Meja::index');
    $routes->post('meja/store',         'Admin\Meja::store');
    $routes->post('meja/delete/(:num)', 'Admin\Meja::delete/$1');
    $routes->post('meja/toggle/(:num)', 'Admin\Meja::toggleStatus/$1');

    // Users
    $routes->get('users',                'Admin\Users::index');
    $routes->post('users/store',         'Admin\Users::store');
    $routes->post('users/delete/(:num)', 'Admin\Users::delete/$1');

    // Riwayat
    $routes->get('riwayat', 'Admin\Riwayat::index');

    // Promo
    $routes->get('promo',                 'Admin\Promo::index');
    $routes->post('promo/store',          'Admin\Promo::store');
    $routes->post('promo/update/(:num)',  'Admin\Promo::update/$1');
    $routes->post('promo/toggle/(:num)',  'Admin\Promo::toggle/$1');
    $routes->post('promo/delete/(:num)',  'Admin\Promo::delete/$1');

    // Pengaturan
    $routes->get('pengaturan',         'Admin\Pengaturan::index');
    $routes->post('pengaturan/update', 'Admin\Pengaturan::update');
});

$routes->group('kasir', ['filter' => 'role:kasir'], function ($routes) {
    $routes->get('dashboard',                    'Kasir\Dashboard::index');
    $routes->post('selesai/(:num)',              'Kasir\Dashboard::selesai/$1');
    $routes->get('cek_baru/(:num)',              'Kasir\Dashboard::cek_baru/$1');
    $routes->get('cek_pembayaran_berhasil',      'Kasir\Dashboard::cek_pembayaran_berhasil');
    $routes->get('riwayat',                      'Kasir\Riwayat::index');
});