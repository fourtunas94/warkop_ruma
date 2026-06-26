<?php
helper('app_setting');

$storeName        = app_setting('store_name', 'ARZANA');
$storeLogo        = app_setting('store_logo', '');
$storeFavicon     = app_setting('store_favicon', '');
$storeDescription = app_setting('store_description', 'Pesan makanan lebih mudah dari meja Anda.');
$storeWhatsapp    = app_setting('store_whatsapp', '');

$logoSrc = ! empty($storeLogo)
    ? base_url($storeLogo)
    : base_url('assets/img/logo_k.png');

$faviconPath = ! empty($storeFavicon)
    ? $storeFavicon
    : (! empty($storeLogo) ? $storeLogo : 'assets/img/logo_k.png');

$faviconSrc = base_url($faviconPath);

$nomorMeja = $meja['nomor_meja'] ?? '-';
$kodeMeja  = $meja['kode_unik'] ?? '';

$whatsappUrl = '';
if (! empty($storeWhatsapp)) {
    $waNumber = preg_replace('/[^0-9]/', '', $storeWhatsapp);
    $waText   = 'Halo, saya pelanggan meja ' . $nomorMeja . ' ingin bertanya.';
    $whatsappUrl = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($waText);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Meja <?= esc($nomorMeja) ?> — <?= esc($storeName) ?></title>

    <link rel="icon" type="image/png" href="<?= esc($faviconSrc) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --bg: #020617;
            --bg-soft: #07111f;
            --card: rgba(255, 255, 255, .055);
            --card-strong: rgba(255, 255, 255, .08);
            --border: rgba(255, 255, 255, .10);
            --text: #ffffff;
            --muted: #94a3b8;
            --muted-2: #64748b;
            --sky: #0ea5e9;
            --cyan: #22d3ee;
            --purple: #7c3aed;
            --rose: #f43f5e;
            --emerald: #22c55e;
            --amber: #f59e0b;
            --gradient: linear-gradient(135deg, #0ea5e9, #7c3aed);
            --shadow: 0 20px 60px rgba(0, 0, 0, .38);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, .22), transparent 24rem),
                radial-gradient(circle at top right, rgba(124, 58, 237, .20), transparent 24rem),
                linear-gradient(135deg, #020617 0%, #07111f 48%, #020617 100%);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
            padding-bottom: 120px;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(to bottom, rgba(0,0,0,.9), rgba(0,0,0,.18));
            z-index: 0;
        }

        button,
        a {
            -webkit-tap-highlight-color: transparent;
        }

        .app {
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .header {
            position: sticky;
            top: 0;
            z-index: 30;
            padding: 16px 16px 12px;
            background: rgba(2, 6, 23, .76);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .logo-box {
            width: 46px;
            height: 46px;
            border-radius: 18px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.10);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 0 35px rgba(14,165,233,.18);
        }

        .logo-box img {
            width: 38px;
            height: 38px;
            object-fit: contain;
        }

        .logo-fallback {
            display: none;
            width: 38px;
            height: 38px;
            border-radius: 14px;
            background: var(--gradient);
            align-items: center;
            justify-content: center;
            color: white;
        }

        .brand-info {
            min-width: 0;
        }

        .brand-info h1 {
            font-size: 17px;
            font-weight: 800;
            line-height: 1.2;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 230px;
        }

        .brand-info p {
            font-size: 12px;
            color: var(--muted);
            margin-top: 3px;
        }

        .brand-info p span {
            color: #fde047;
            font-weight: 800;
        }

        .header-action {
            width: 42px;
            height: 42px;
            border-radius: 16px;
            background: rgba(255,255,255,.055);
            border: 1px solid rgba(255,255,255,.10);
            color: var(--muted);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            flex-shrink: 0;
            transition: .2s ease;
        }

        .header-action:hover {
            background: rgba(255,255,255,.10);
            color: #fff;
        }

        .hero {
            padding: 18px 16px 16px;
        }

        .hero-card {
            position: relative;
            overflow: hidden;
            border-radius: 28px;
            background: rgba(255,255,255,.045);
            border: 1px solid rgba(255,255,255,.10);
            box-shadow: var(--shadow);
            padding: 18px;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            right: -60px;
            top: -70px;
            width: 180px;
            height: 180px;
            background: rgba(14,165,233,.22);
            filter: blur(38px);
            border-radius: 50%;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            left: -80px;
            bottom: -80px;
            width: 200px;
            height: 200px;
            background: rgba(124,58,237,.18);
            filter: blur(42px);
            border-radius: 50%;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 11px;
            border-radius: 999px;
            background: rgba(34,197,94,.10);
            border: 1px solid rgba(34,197,94,.20);
            color: #86efac;
            font-size: 11px;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 5px rgba(34,197,94,.10);
        }

        .hero-title {
            font-size: 24px;
            line-height: 1.16;
            font-weight: 900;
            letter-spacing: -.04em;
            color: #fff;
        }

        .hero-desc {
            font-size: 13px;
            line-height: 1.6;
            color: var(--muted);
            margin-top: 8px;
        }

        .hero-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 16px;
        }

        .meta-card {
            border-radius: 20px;
            background: rgba(255,255,255,.045);
            border: 1px solid rgba(255,255,255,.08);
            padding: 12px;
        }

        .meta-card span {
            display: block;
            font-size: 11px;
            color: var(--muted-2);
            margin-bottom: 4px;
        }

        .meta-card strong {
            font-size: 13px;
            color: #fff;
        }

        .section-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 12px;
            padding: 4px 16px 12px;
        }

        .section-head h2 {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: -.02em;
        }

        .section-head p {
            font-size: 12px;
            color: var(--muted-2);
            margin-top: 2px;
        }

        .cat-list {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding: 0 16px 18px;
        }

        .cat-list::-webkit-scrollbar {
            display: none;
        }

        .category-pill {
            border: 1px solid rgba(255,255,255,.10);
            background: rgba(255,255,255,.055);
            color: #cbd5e1;
            padding: 10px 15px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
            cursor: pointer;
            transition: .2s ease;
        }

        .category-pill.active {
            background: #ffffff;
            color: #020617;
            border-color: #ffffff;
            box-shadow: 0 10px 30px rgba(255,255,255,.12);
        }

        .category-pill:not(.active):hover {
            background: rgba(255,255,255,.09);
            color: #fff;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            padding: 0 16px;
        }

        .menu-card {
            position: relative;
            overflow: hidden;
            background: rgba(255,255,255,.055);
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 24px;
            min-height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 18px 46px rgba(0,0,0,.22);
        }

        .img-wrap {
            position: relative;
            width: 100%;
            aspect-ratio: 1.08 / .82;
            background: rgba(255,255,255,.04);
            overflow: hidden;
        }

        .img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .img-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, transparent 40%, rgba(2,6,23,.82));
            pointer-events: none;
        }

        .no-img {
            width: 100%;
            height: 100%;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .stock-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 2;
            font-size: 10px;
            font-weight: 900;
            padding: 5px 9px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.12);
            backdrop-filter: blur(10px);
        }

        .stock-ready {
            background: rgba(34,197,94,.16);
            color: #86efac;
        }

        .stock-empty {
            background: rgba(244,63,94,.16);
            color: #fda4af;
        }

        .menu-content {
            padding: 13px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1;
        }

        .item-title {
            font-size: 13px;
            line-height: 1.35;
            font-weight: 850;
            color: #fff;
            min-height: 36px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .item-desc {
            font-size: 11px;
            line-height: 1.45;
            color: var(--muted-2);
            min-height: 32px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .item-footer {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .item-price {
            font-size: 12px;
            font-weight: 900;
            color: #f8fafc;
            white-space: nowrap;
        }

        .action-container {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 7px;
            min-width: 34px;
        }

        .icon-btn {
            width: 31px;
            height: 31px;
            border: 0;
            border-radius: 999px;
            color: #fff;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform .15s ease, opacity .15s ease;
        }

        .icon-btn:active {
            transform: scale(.92);
        }

        .btn-plus {
            background: var(--gradient);
            box-shadow: 0 10px 24px rgba(14,165,233,.25);
        }

        .btn-minus {
            background: rgba(244,63,94,.92);
            box-shadow: 0 10px 24px rgba(244,63,94,.18);
        }

        .item-qty {
            min-width: 18px;
            text-align: center;
            font-size: 13px;
            font-weight: 900;
            color: #fff;
        }

        .btn-habis {
            border: 1px solid rgba(255,255,255,.10);
            background: rgba(255,255,255,.055);
            color: #64748b;
            padding: 7px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 900;
        }

        .empty-state {
            grid-column: 1 / -1;
            padding: 38px 18px;
            text-align: center;
            border-radius: 24px;
            background: rgba(255,255,255,.045);
            border: 1px solid rgba(255,255,255,.10);
        }

        .empty-state i {
            font-size: 28px;
            color: #475569;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 13px;
            font-weight: 800;
            color: #fff;
        }

        .empty-state span {
            display: block;
            font-size: 12px;
            color: var(--muted-2);
            margin-top: 4px;
        }

        .bottom-bar {
            position: fixed;
            left: 50%;
            bottom: 18px;
            transform: translateX(-50%) translateY(150%);
            width: calc(100% - 32px);
            max-width: 488px;
            border-radius: 24px;
            background: linear-gradient(135deg, #0ea5e9, #7c3aed);
            box-shadow: 0 20px 50px rgba(14,165,233,.25), 0 10px 34px rgba(0,0,0,.50);
            border: 1px solid rgba(255,255,255,.18);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            cursor: pointer;
            z-index: 40;
            transition: transform .28s ease;
        }

        .bottom-bar.show {
            transform: translateX(-50%) translateY(0);
        }

        .cart-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .cart-bubble {
            width: 43px;
            height: 43px;
            border-radius: 18px;
            background: rgba(255,255,255,.16);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            flex-shrink: 0;
        }

        .cart-bubble i {
            color: #fff;
            font-size: 18px;
        }

        .cart-badge {
            position: absolute;
            top: -7px;
            right: -7px;
            min-width: 20px;
            height: 20px;
            border-radius: 999px;
            background: #fff;
            color: #0ea5e9;
            border: 2px solid rgba(14,165,233,.50);
            font-size: 10px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
        }

        .cart-text h4 {
            font-size: 13px;
            font-weight: 900;
            color: #fff;
            white-space: nowrap;
        }

        .cart-text p {
            font-size: 12px;
            font-weight: 700;
            color: rgba(255,255,255,.86);
            margin-top: 3px;
        }

        .cart-cta {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 900;
            color: #fff;
            white-space: nowrap;
        }

        .toast {
            position: fixed;
            left: 50%;
            bottom: 100px;
            transform: translateX(-50%) translateY(20px);
            opacity: 0;
            pointer-events: none;
            z-index: 45;
            width: calc(100% - 32px);
            max-width: 488px;
            border-radius: 18px;
            background: rgba(15,23,42,.94);
            border: 1px solid rgba(255,255,255,.12);
            box-shadow: 0 18px 48px rgba(0,0,0,.35);
            padding: 12px 14px;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            transition: .22s ease;
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        @media (max-width: 380px) {
            .menu-grid {
                gap: 12px;
                padding-inline: 12px;
            }

            .header,
            .hero,
            .section-head,
            .cat-list {
                padding-left: 12px;
                padding-right: 12px;
            }

            .brand-info h1 {
                max-width: 190px;
            }

            .hero-title {
                font-size: 22px;
            }

            .item-title {
                font-size: 12.5px;
            }

            .item-desc {
                display: none;
            }

            .item-price {
                font-size: 11px;
            }

            .icon-btn {
                width: 29px;
                height: 29px;
            }
        }
    </style>
</head>

<body>

<div class="app">

    <header class="header">
        <div class="header-top">
            <div class="brand">
                <div class="logo-box">
                    <img src="<?= esc($logoSrc) ?>"
                         alt="Logo <?= esc($storeName) ?>"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                    <div class="logo-fallback">
                        <i class="fa-solid fa-store"></i>
                    </div>
                </div>

                <div class="brand-info">
                    <h1><?= esc($storeName) ?></h1>
                    <p>Meja <span><?= esc($nomorMeja) ?></span></p>
                </div>
            </div>

            <?php if (! empty($whatsappUrl)): ?>
                <a href="<?= esc($whatsappUrl) ?>"
                   target="_blank"
                   rel="noopener"
                   class="header-action"
                   title="Hubungi admin">
                    <i class="fa-brands fa-whatsapp"></i>
                </a>
            <?php else: ?>
                <button type="button" class="header-action" title="Bantuan">
                    <i class="fa-solid fa-headset"></i>
                </button>
            <?php endif; ?>
        </div>
    </header>

    <section class="hero">
        <div class="hero-card">
            <div class="hero-content">
                <div class="badge">
                    <span class="badge-dot"></span>
                    Scan QR berhasil
                </div>

                <h2 class="hero-title">
                    Pilih menu favoritmu dari meja <?= esc($nomorMeja) ?>
                </h2>

                <p class="hero-desc">
                    <?= esc($storeDescription ?: 'Tambahkan menu ke keranjang, lalu lanjut checkout untuk mengirim pesanan ke kasir.') ?>
                </p>

                <div class="hero-meta">
                    <div class="meta-card">
                        <span>Meja</span>
                        <strong><?= esc($nomorMeja) ?></strong>
                    </div>

                    <div class="meta-card">
                        <span>Status</span>
                        <strong>Siap pesan</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-head">
        <div>
            <h2>Kategori</h2>
            <p>Pilih kategori menu</p>
        </div>
    </div>

    <div class="cat-list" id="cat-list">
        <button type="button"
                data-filter="semua"
                id="btn-kat-semua"
                class="category-pill active">
            Semua
        </button>

        <?php foreach ($menuByKategori as $kid => $kat): ?>
            <?php if (! empty($kat['items'])): ?>
                <button type="button"
                        data-filter="<?= esc($kid) ?>"
                        id="btn-kat-<?= esc($kid) ?>"
                        class="category-pill">
                    <?= esc($kat['nama']) ?>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <main class="menu-grid" id="menu-grid">
        <?php $hasMenu = false; ?>

        <?php foreach ($menuByKategori as $kid => $kat): ?>
            <?php foreach (($kat['items'] ?? []) as $item): ?>
                <?php
                    $hasMenu = true;
                    $itemId = (int) ($item['id'] ?? 0);
                    $stok   = (int) ($item['stok'] ?? 0);
                ?>

                <div class="menu-card menu-item" data-kategori="<?= esc($kid) ?>">
                    <div class="img-wrap">
                        <?php if (! empty($item['gambar'])): ?>
                            <img src="<?= esc(base_url('uploads/menu/' . $item['gambar'])) ?>" alt="<?= esc($item['nama_menu'] ?? 'Menu') ?>">
                        <?php else: ?>
                            <div class="no-img">
                                <i class="fa-solid fa-image"></i>
                            </div>
                        <?php endif; ?>

                        <div class="img-overlay"></div>

                        <div class="stock-badge <?= $stok ? 'stock-ready' : 'stock-empty' ?>">
                            <?= $stok ? 'Tersedia' : 'Habis' ?>
                        </div>
                    </div>

                    <div class="menu-content">
                        <div>
                            <h3 class="item-title"><?= esc($item['nama_menu'] ?? '-') ?></h3>

                            <?php if (! empty($item['deskripsi'])): ?>
                                <p class="item-desc"><?= esc($item['deskripsi']) ?></p>
                            <?php else: ?>
                                <p class="item-desc">Menu pilihan untuk meja Anda.</p>
                            <?php endif; ?>
                        </div>

                        <div class="item-footer">
                            <p class="item-price">
                                Rp <?= number_format($item['harga'] ?? 0, 0, ',', '.') ?>
                            </p>

                            <div class="action-container"
                                 data-id="<?= esc($itemId) ?>"
                                 data-nama="<?= esc($item['nama_menu'] ?? '-') ?>"
                                 data-harga="<?= esc((int) ($item['harga'] ?? 0)) ?>"
                                 data-stok="<?= esc($stok) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <?php if (! $hasMenu): ?>
            <div class="empty-state">
                <i class="fa-solid fa-bowl-food"></i>
                <p>Belum ada menu tersedia</p>
                <span>Silakan hubungi kasir atau coba beberapa saat lagi.</span>
            </div>
        <?php endif; ?>
    </main>

</div>

<div id="bottom-bar" class="bottom-bar" onclick="goCheckout()">
    <div class="cart-left">
        <div class="cart-bubble">
            <i class="fa-solid fa-cart-shopping"></i>
            <div id="cart-badge" class="cart-badge">0</div>
        </div>

        <div class="cart-text">
            <h4>Keranjang (<span id="cart-title-qty">0</span>)</h4>
            <p id="cart-total">Rp 0</p>
        </div>
    </div>

    <div class="cart-cta">
        Checkout
        <i class="fa-solid fa-arrow-right"></i>
    </div>
</div>

<div id="toast" class="toast"></div>

<script>
    const KODE_MEJA = <?= json_encode($kodeMeja) ?>;
    const CHECKOUT_URL = <?= json_encode(base_url('order/checkout/' . $kodeMeja)) ?>;

    let cart = JSON.parse(localStorage.getItem('arzana_cart_' + KODE_MEJA) || '[]');

    function rupiah(value) {
        return 'Rp ' + Number(value || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function showToast(message) {
        const toast = document.getElementById('toast');

        if (!toast) {
            return;
        }

        toast.textContent = message;
        toast.classList.add('show');

        clearTimeout(window.__toastTimer);
        window.__toastTimer = setTimeout(() => {
            toast.classList.remove('show');
        }, 1600);
    }

    function saveCart() {
        localStorage.setItem('arzana_cart_' + KODE_MEJA, JSON.stringify(cart));
        updateAllUI();
    }

    function getMenuData(id) {
        const container = document.querySelector(`.action-container[data-id="${id}"]`);

        if (!container) {
            return null;
        }

        return {
            id: parseInt(container.dataset.id),
            nama_menu: container.dataset.nama,
            harga: parseInt(container.dataset.harga || '0'),
            stok: parseInt(container.dataset.stok || '0')
        };
    }

    function addToCart(id) {
        const data = getMenuData(id);

        if (!data || !data.stok) {
            return;
        }

        const existing = cart.find(i => i.id === data.id);

        if (existing) {
            existing.qty++;
        } else {
            cart.push({
                id: data.id,
                nama_menu: data.nama_menu,
                harga: data.harga,
                qty: 1,
                catatan: ''
            });
        }

        saveCart();
        showToast(data.nama_menu + ' ditambahkan ke keranjang.');
    }

    function increaseQty(id) {
        const item = cart.find(i => i.id === parseInt(id));

        if (item) {
            item.qty++;
            saveCart();
        }
    }

    function decreaseQty(id) {
        const idx = cart.findIndex(i => i.id === parseInt(id));

        if (idx > -1) {
            if (cart[idx].qty > 1) {
                cart[idx].qty--;
            } else {
                cart.splice(idx, 1);
            }

            saveCart();
        }
    }

    function updateAllUI() {
        document.querySelectorAll('.action-container').forEach(container => {
            const id = parseInt(container.dataset.id);
            const stok = parseInt(container.dataset.stok || '0');
            const item = cart.find(i => i.id === id);

            if (!stok) {
                container.innerHTML = `<button disabled class="btn-habis">Habis</button>`;
                return;
            }

            if (item) {
                container.innerHTML = `
                    <button type="button" onclick="event.stopPropagation(); decreaseQty(${id})" class="icon-btn btn-minus" aria-label="Kurangi">
                        <i class="fa-solid fa-minus"></i>
                    </button>
                    <span class="item-qty">${item.qty}</span>
                    <button type="button" onclick="event.stopPropagation(); increaseQty(${id})" class="icon-btn btn-plus" aria-label="Tambah">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                `;
            } else {
                container.innerHTML = `
                    <button type="button" onclick="event.stopPropagation(); addToCart(${id})" class="icon-btn btn-plus" aria-label="Tambah ke keranjang">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                `;
            }
        });

        updateBottomBar();
    }

    function updateBottomBar() {
        const bottomBar = document.getElementById('bottom-bar');
        const totalQty = cart.reduce((sum, item) => sum + Number(item.qty || 0), 0);
        const totalPrice = cart.reduce((sum, item) => sum + (Number(item.harga || 0) * Number(item.qty || 0)), 0);

        if (totalQty > 0) {
            document.getElementById('cart-badge').textContent = totalQty;
            document.getElementById('cart-title-qty').textContent = totalQty;
            document.getElementById('cart-total').textContent = rupiah(totalPrice);

            bottomBar.classList.add('show');
        } else {
            bottomBar.classList.remove('show');
        }
    }

    function filterKategori(id) {
        document.querySelectorAll('.category-pill').forEach(el => {
            el.classList.remove('active');
        });

        const btnId = id === 'semua' ? 'btn-kat-semua' : 'btn-kat-' + id;
        const activeBtn = document.getElementById(btnId);

        if (activeBtn) {
            activeBtn.classList.add('active');
        }

        document.querySelectorAll('.menu-item').forEach(item => {
            if (id === 'semua' || item.dataset.kategori == id) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function goCheckout() {
        if (cart.length === 0) {
            return;
        }

        window.location.href = CHECKOUT_URL;
    }

    document.querySelectorAll('.category-pill').forEach(button => {
        button.addEventListener('click', () => {
            filterKategori(button.dataset.filter);
        });
    });

    updateAllUI();
</script>

</body>
</html>