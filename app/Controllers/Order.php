<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MejaModel;
use App\Models\MenuModel;
use App\Models\KategoriModel;
use App\Models\PesananModel;
use App\Models\PesananItemModel;
use App\Models\PromoModel;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class Order extends BaseController
{
    protected MejaModel        $mejaModel;
    protected MenuModel        $menuModel;
    protected KategoriModel    $kategoriModel;
    protected PesananModel     $pesananModel;
    protected PesananItemModel $itemModel;
    protected PromoModel       $promoModel;

    public function __construct()
    {
        $this->mejaModel     = new MejaModel();
        $this->menuModel     = new MenuModel();
        $this->kategoriModel = new KategoriModel();
        $this->pesananModel  = new PesananModel();
        $this->itemModel     = new PesananItemModel();
        $this->promoModel    = new PromoModel();
    }

    private function setupMidtrans(): void
    {
        MidtransConfig::$serverKey    = trim((string) env('MIDTRANS_SERVER_KEY'));
        MidtransConfig::$isProduction = filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);
        MidtransConfig::$isSanitized  = true;
        MidtransConfig::$is3ds        = true;

        MidtransConfig::$overrideNotifUrl = base_url('order/midtrans-notification');
    }

    public function menu(string $kode)
    {
        $meja = $this->mejaModel->findByKode($kode);

        if (! $meja) {
            return view('order/invalid');
        }

        $kategoris = $this->kategoriModel->getAktif();
        $menus     = $this->menuModel->getAktif();

        $menuByKategori = [];

        foreach ($kategoris as $k) {
            $menuByKategori[$k['id']] = [
                'nama'  => $k['nama_kategori'],
                'items' => [],
            ];
        }

        foreach ($menus as $m) {
            $kid = $m['kategori_id'] ?? 0;

            if (isset($menuByKategori[$kid])) {
                $menuByKategori[$kid]['items'][] = $m;
            }
        }

        $promoModel = new PromoModel();

        return view('order/menu', [
            'meja'           => $meja,
            'menuByKategori' => $menuByKategori,
            'promos'         => $promoModel->getAktifPublic(5),
        ]);
    }

    public function checkout(string $kode)
    {
        $meja = $this->mejaModel->findByKode($kode);

        if (! $meja) {
            return view('order/invalid');
        }

        return view('order/checkout', [
            'meja' => $meja,
        ]);
    }

    public function cekPromo(string $kodeUnik)
    {
        $meja = $this->mejaModel->findByKode($kodeUnik);

        if (! $meja) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Meja tidak valid.',
            ]);
        }

        $kodePromo = strtoupper(trim((string) $this->request->getPost('kode_promo')));
        $subtotal  = (int) $this->request->getPost('subtotal');

        $result = $this->promoModel->validasiPromo($kodePromo, $subtotal);

        return $this->response->setJSON($result);
    }

    public function submit(string $kode)
    {
        $meja = $this->mejaModel->findByKode($kode);

        if (! $meja) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Meja tidak valid.',
            ]);
        }

        $rules = [
            'nama_pemesan'   => 'required|min_length[2]|max_length[100]',
            'items'          => 'required',
            'payment_method' => 'required|in_list[tunai,qris,transfer]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ]);
        }

        $items = json_decode((string) $this->request->getPost('items'), true);

        if (empty($items) || ! is_array($items)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Keranjang kosong.',
            ]);
        }

        $metodePembayaran = (string) $this->request->getPost('payment_method');
        $kodePromo        = strtoupper(trim((string) $this->request->getPost('kode_promo')));
        $namaPemesan      = trim((string) $this->request->getPost('nama_pemesan'));
        $catatan          = trim((string) $this->request->getPost('catatan'));

        /*
         * Penting:
         * Harga tidak boleh percaya dari JavaScript/localStorage.
         * Server ambil ulang harga dari database menu.
         */
        $cleanItems = [];
        $subTotal   = 0;

        foreach ($items as $item) {
            $menuId = (int) ($item['id'] ?? 0);
            $qty    = (int) ($item['qty'] ?? 0);

            if ($menuId <= 0 || $qty <= 0) {
                continue;
            }

            $menu = $this->menuModel->find($menuId);

            if (! $menu) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Salah satu menu tidak ditemukan.',
                ]);
            }

            if ((int) ($menu['is_active'] ?? 1) !== 1) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Menu "' . ($menu['nama_menu'] ?? '-') . '" sedang tidak aktif.',
                ]);
            }

            if ((int) ($menu['stok'] ?? 1) !== 1) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Menu "' . ($menu['nama_menu'] ?? '-') . '" sedang habis.',
                ]);
            }

            $harga = (int) ($menu['harga'] ?? 0);

            if ($harga < 0) {
                $harga = 0;
            }

            $cleanItems[] = [
                'id'        => $menuId,
                'nama_menu' => $menu['nama_menu'] ?? '-',
                'harga'     => $harga,
                'qty'       => $qty,
                'catatan'   => $item['catatan'] ?? null,
            ];

            $subTotal += $harga * $qty;
        }

        if (empty($cleanItems) || $subTotal <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Keranjang tidak valid.',
            ]);
        }

        /*
         * Promo dihitung ulang di server.
         * Jangan percaya nilai potongan dari JavaScript.
         */
        $potongan = 0;
        $promoId  = null;

        if ($kodePromo !== '') {
            $hasilPromo = $this->promoModel->validasiPromo($kodePromo, $subTotal);

            if (! ($hasilPromo['success'] ?? false)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $hasilPromo['message'] ?? 'Kode promo tidak valid.',
                ]);
            }

            $potongan = (int) ($hasilPromo['potongan'] ?? 0);

            $promo = $this->promoModel->findByKode($kodePromo);
            if ($promo) {
                $promoId = (int) $promo['id'];
            }
        }

        $totalSetelahDiskon = max(0, $subTotal - $potongan);

        /*
         * PPN 10% dari total setelah diskon.
         */
        $ppn        = (int) floor($totalSetelahDiskon * 0.10);
        $totalHarga = $totalSetelahDiskon + $ppn;

        $kodeP         = $this->pesananModel->generateKode();
        $nomorAntrian = $this->pesananModel->generateNomorAntrian();

        $db = db_connect();
        $db->transStart();

        $pesananId = $this->pesananModel->insert([
            'kode_pesanan'      => $kodeP,
            'nomor_antrian'     => $nomorAntrian,
            'meja_id'           => $meja['id'],
            'nama_pemesan'      => $namaPemesan,
            'total_harga'       => $totalHarga,
            'catatan'           => $catatan !== '' ? $catatan : null,
            'status'            => 'pending',
            'status_pesanan'    => 'menunggu',
            'waktu_siap'        => null,
            'metode_pembayaran' => $metodePembayaran,
            'kode_promo'        => $kodePromo !== '' ? $kodePromo : null,
            'potongan'          => $potongan,
            'ppn'               => $ppn,
        ]);

        if (! $pesananId) {
            $db->transRollback();

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan pesanan.',
            ]);
        }

        foreach ($cleanItems as $item) {
            $this->itemModel->insert([
                'pesanan_id' => $pesananId,
                'menu_id'    => $item['id'],
                'nama_menu'  => $item['nama_menu'],
                'harga'      => $item['harga'],
                'qty'        => $item['qty'],
                'catatan'    => $item['catatan'],
            ]);
        }

        if ($promoId !== null) {
            $this->promoModel->tambahJumlahDigunakan($promoId);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi.',
            ]);
        }

        /*
         * Pembayaran tunai:
         * status tetap pending.
         * Nanti kasir klik tombol Selesai.
         */
        if ($metodePembayaran === 'tunai') {
            return $this->response->setJSON([
                'success'  => true,
                'redirect' => base_url('order/sukses/' . $kodeP),
            ]);
        }

        /*
         * Jika total 0 karena promo penuh:
         * langsung dianggap selesai.
         */
        if ($totalHarga <= 0) {
            $this->pesananModel
                ->where('kode_pesanan', $kodeP)
                ->set([
                    'status'     => 'selesai',
                    'updated_at' => date('Y-m-d H:i:s'),
                ])
                ->update();

            return $this->response->setJSON([
                'success'  => true,
                'redirect' => base_url('order/sukses/' . $kodeP),
            ]);
        }

        /*
         * Pembayaran non-tunai:
         * QRIS / Transfer Bank menggunakan Midtrans Snap Redirect.
         */
        $serverKey = trim((string) env('MIDTRANS_SERVER_KEY'));

        if (empty($serverKey)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Konfigurasi Midtrans belum lengkap. Isi MIDTRANS_SERVER_KEY di file .env.',
            ]);
        }

        $this->setupMidtrans();

        $enabledPayments = [];

        if ($metodePembayaran === 'qris') {
            $enabledPayments = [
                'other_qris',
                'gopay',
                'shopeepay',
            ];
        }

        if ($metodePembayaran === 'transfer') {
            $enabledPayments = [
                'bca_va',
                'bni_va',
                'bri_va',
                'permata_va',
                'other_va',
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id'     => $kodeP,
                'gross_amount' => $totalHarga,
            ],
            'customer_details' => [
                'first_name' => $namaPemesan,
                'email'      => 'customer@arzana.com',
                'phone'      => '081234567890',
            ],
            'item_details' => [
                [
                    'id'       => 'ORDER-' . $kodeP,
                    'price'    => $totalHarga,
                    'quantity' => 1,
                    'name'     => 'Pesanan Arzana - Meja ' . $meja['nomor_meja'],
                ],
            ],
            'enabled_payments' => $enabledPayments,
            'callbacks' => [
                'finish' => base_url('order/sukses/' . $kodeP),
            ],
            'expiry' => [
                'start_time' => date('Y-m-d H:i:s O'),
                'unit'       => 'minute',
                'duration'   => 60,
            ],
        ];

        try {
            $transaction = Snap::createTransaction($params);

            $redirectUrl = $transaction->redirect_url ?? null;

            if (! $redirectUrl) {
                log_message('error', 'MIDTRANS REDIRECT URL EMPTY: ' . json_encode($transaction));

                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal membuat URL pembayaran Midtrans.',
                    'debug'   => $transaction,
                ]);
            }

            return $this->response->setJSON([
                'success'  => true,
                'redirect' => $redirectUrl,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'MIDTRANS CREATE TRANSACTION ERROR: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Midtrans Error: ' . $e->getMessage(),
            ]);
        }
    }

    public function sukses(string $kode)
    {
        $pesanan = $this->pesananModel
            ->where('kode_pesanan', $kode)
            ->first();

        if (! $pesanan) {
            return redirect()->to('/');
        }

        $items = $this->itemModel->getByPesanan($pesanan['id']);
        $meja  = $this->mejaModel->find($pesanan['meja_id']);

        return view('order/sukses', [
            'pesanan' => $pesanan,
            'items'   => $items,
            'meja'    => $meja,
        ]);
    }

    public function midtransNotification()
    {
        $serverKey = trim((string) env('MIDTRANS_SERVER_KEY'));

        if (empty($serverKey)) {
            log_message('error', 'MIDTRANS NOTIFICATION ERROR: SERVER KEY EMPTY');

            return $this->response
                ->setStatusCode(500)
                ->setBody('SERVER KEY EMPTY');
        }

        $rawBody = $this->request->getBody();
        $notif   = json_decode($rawBody, true);

        log_message('info', 'MIDTRANS NOTIFICATION RAW: ' . $rawBody);

        if (empty($notif) || ! is_array($notif)) {
            return $this->response
                ->setStatusCode(400)
                ->setBody('INVALID NOTIFICATION');
        }

        $orderId           = $notif['order_id'] ?? null;
        $statusCode        = $notif['status_code'] ?? null;
        $grossAmount       = $notif['gross_amount'] ?? null;
        $signatureKey      = $notif['signature_key'] ?? null;
        $transactionStatus = $notif['transaction_status'] ?? null;
        $fraudStatus       = $notif['fraud_status'] ?? null;
        $paymentType       = $notif['payment_type'] ?? null;

        if (empty($orderId) || empty($statusCode) || empty($grossAmount) || empty($signatureKey)) {
            log_message('error', 'MIDTRANS NOTIFICATION ERROR: REQUIRED FIELD EMPTY');

            return $this->response
                ->setStatusCode(400)
                ->setBody('REQUIRED FIELD EMPTY');
        }

        $calculatedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (! hash_equals($calculatedSignature, $signatureKey)) {
            log_message('error', 'MIDTRANS INVALID SIGNATURE FOR ORDER: ' . $orderId);

            return $this->response
                ->setStatusCode(403)
                ->setBody('INVALID SIGNATURE');
        }

        $pesanan = $this->pesananModel
            ->where('kode_pesanan', $orderId)
            ->first();

        if (! $pesanan) {
            log_message('error', 'MIDTRANS ORDER NOT FOUND: ' . $orderId);

            return $this->response
                ->setStatusCode(404)
                ->setBody('ORDER NOT FOUND');
        }

        if ($transactionStatus === 'settlement') {
            $this->pesananModel
                ->where('kode_pesanan', $orderId)
                ->set([
                    'status'     => 'selesai',
                    'updated_at' => date('Y-m-d H:i:s'),
                ])
                ->update();
        } elseif ($transactionStatus === 'capture') {
            if ($paymentType === 'credit_card') {
                if ($fraudStatus === 'accept') {
                    $this->pesananModel
                        ->where('kode_pesanan', $orderId)
                        ->set([
                            'status'     => 'selesai',
                            'updated_at' => date('Y-m-d H:i:s'),
                        ])
                        ->update();
                }
            } else {
                $this->pesananModel
                    ->where('kode_pesanan', $orderId)
                    ->set([
                        'status'     => 'selesai',
                        'updated_at' => date('Y-m-d H:i:s'),
                    ])
                    ->update();
            }
        } elseif ($transactionStatus === 'pending') {
            $this->pesananModel
                ->where('kode_pesanan', $orderId)
                ->set([
                    'status'     => 'pending',
                    'updated_at' => date('Y-m-d H:i:s'),
                ])
                ->update();
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel', 'failure'], true)) {
            $this->pesananModel
                ->where('kode_pesanan', $orderId)
                ->set([
                    'status'     => 'pending',
                    'updated_at' => date('Y-m-d H:i:s'),
                ])
                ->update();
        }

        log_message('info', 'MIDTRANS ORDER UPDATED: ' . json_encode([
            'order_id'           => $orderId,
            'transaction_status' => $transactionStatus,
            'payment_type'       => $paymentType,
            'fraud_status'       => $fraudStatus,
        ]));

        return $this->response
            ->setStatusCode(200)
            ->setBody('OK');
    }
}