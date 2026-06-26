<?php
helper('app_setting');

$storeName        = app_setting('store_name', 'ARZANA');
$storeLogo        = app_setting('store_logo', '');
$storeFavicon     = app_setting('store_favicon', '');
$storeDescription = app_setting('store_description', 'Terima kasih sudah memesan.');
$storeWhatsapp    = app_setting('store_whatsapp', '');

$logoSrc = ! empty($storeLogo)
    ? base_url($storeLogo)
    : base_url('assets/img/logo_k.png');

$faviconPath = ! empty($storeFavicon)
    ? $storeFavicon
    : (! empty($storeLogo) ? $storeLogo : 'assets/img/logo_k.png');

$faviconSrc = base_url($faviconPath);

$metode = isset($pesanan['metode_pembayaran'])
    ? strtolower((string) $pesanan['metode_pembayaran'])
    : 'tunai';

$status = strtolower((string) ($pesanan['status'] ?? 'pending'));
$isSelesai = $status === 'selesai';

$metodeLabel = match ($metode) {
    'tunai'    => 'Tunai / COD',
    'qris'     => 'QRIS / E-Wallet',
    'transfer' => 'Transfer Bank / VA',
    default    => ucfirst($metode),
};

$metodeIcon = match ($metode) {
    'tunai'    => 'fa-money-bill-wave',
    'qris'     => 'fa-qrcode',
    'transfer' => 'fa-building-columns',
    default    => 'fa-credit-card',
};

$nomorMeja = $meja['nomor_meja'] ?? '-';
$kodeMeja  = $meja['kode_unik'] ?? '';

$whatsappUrl = '';
if (! empty($storeWhatsapp)) {
    $waNumber = preg_replace('/[^0-9]/', '', $storeWhatsapp);
    $waText   = 'Halo, saya ingin bertanya tentang pesanan ' . ($pesanan['kode_pesanan'] ?? '') . ' meja ' . $nomorMeja . '.';
    $whatsappUrl = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($waText);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan — <?= esc($storeName) ?></title>

    <link rel="icon" type="image/png" href="<?= esc($faviconSrc) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --bg: #020617;
            --card: rgba(255, 255, 255, .055);
            --border: rgba(255, 255, 255, .10);
            --muted: #94a3b8;
            --muted-2: #64748b;
            --sky: #0ea5e9;
            --purple: #7c3aed;
            --emerald: #22c55e;
            --amber: #f59e0b;
            --rose: #f43f5e;
            --gradient: linear-gradient(135deg, #0ea5e9, #7c3aed);
            --shadow: 0 20px 60px rgba(0, 0, 0, .38);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(14, 165, 233, .22), transparent 24rem),
                radial-gradient(circle at top right, rgba(124, 58, 237, .20), transparent 24rem),
                linear-gradient(135deg, #020617 0%, #07111f 48%, #020617 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
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

        .wrap {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        .card {
            position: relative;
            overflow: hidden;
            border-radius: 34px;
            background: rgba(255,255,255,.045);
            border: 1px solid rgba(255,255,255,.10);
            box-shadow: var(--shadow);
        }

        .card::before {
            content: '';
            position: absolute;
            right: -70px;
            top: -80px;
            width: 200px;
            height: 200px;
            background: <?= $isSelesai ? 'rgba(34,197,94,.20)' : 'rgba(245,158,11,.20)' ?>;
            filter: blur(44px);
            border-radius: 50%;
        }

        .card::after {
            content: '';
            position: absolute;
            left: -90px;
            bottom: -90px;
            width: 220px;
            height: 220px;
            background: rgba(124,58,237,.16);
            filter: blur(44px);
            border-radius: 50%;
        }

        .status-header {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 30px 22px 24px;
            border-bottom: 1px solid rgba(255,255,255,.10);
        }

        .logo {
            width: 58px;
            height: 58px;
            border-radius: 22px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.10);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0 auto 18px;
        }

        .logo img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        .logo-fallback {
            display: none;
            width: 48px;
            height: 48px;
            border-radius: 18px;
            background: var(--gradient);
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .icon-circle {
            width: 76px;
            height: 76px;
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 30px;
        }

        .icon-success {
            background: rgba(34,197,94,.12);
            color: #86efac;
            border: 1px solid rgba(34,197,94,.24);
            box-shadow: 0 0 35px rgba(34,197,94,.16);
        }

        .icon-pending {
            background: rgba(245,158,11,.12);
            color: #fcd34d;
            border: 1px solid rgba(245,158,11,.24);
            box-shadow: 0 0 35px rgba(245,158,11,.16);
        }

        .status-title {
            font-size: 25px;
            line-height: 1.2;
            font-weight: 900;
            letter-spacing: -.04em;
            margin-bottom: 8px;
        }

        .status-desc {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.65;
        }

        .card-body {
            position: relative;
            z-index: 1;
            padding: 22px;
        }

        .instruction-box {
            border-radius: 24px;
            padding: 16px;
            margin-bottom: 18px;
        }

        .instruction-success {
            background: rgba(34,197,94,.08);
            border: 1px dashed rgba(34,197,94,.24);
        }

        .instruction-pending {
            background: rgba(245,158,11,.08);
            border: 1px dashed rgba(245,158,11,.26);
        }

        .instruction-header {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 9px;
        }

        .instruction-header i {
            width: 36px;
            height: 36px;
            border-radius: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .instruction-success .instruction-header i {
            color: #86efac;
            background: rgba(34,197,94,.12);
        }

        .instruction-pending .instruction-header i {
            color: #fcd34d;
            background: rgba(245,158,11,.12);
        }

        .instruction-header h4 {
            font-size: 13px;
            font-weight: 900;
            color: #fff;
        }

        .instruction-box p {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.6;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }

        .info-box {
            border-radius: 22px;
            background: rgba(2,6,23,.46);
            border: 1px solid rgba(255,255,255,.10);
            padding: 14px;
        }

        .info-label {
            display: block;
            font-size: 10px;
            font-weight: 900;
            color: var(--muted-2);
            text-transform: uppercase;
            letter-spacing: .12em;
            margin-bottom: 6px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 900;
            color: #fff;
            word-break: break-word;
        }

        .payment-box {
            border-radius: 22px;
            background: rgba(2,6,23,.46);
            border: 1px solid rgba(255,255,255,.10);
            padding: 14px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .payment-icon {
            width: 42px;
            height: 42px;
            border-radius: 17px;
            background: rgba(14,165,233,.10);
            border: 1px solid rgba(14,165,233,.20);
            color: #7dd3fc;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .payment-box p {
            font-size: 10px;
            font-weight: 900;
            color: var(--muted-2);
            text-transform: uppercase;
            letter-spacing: .12em;
            margin-bottom: 4px;
        }

        .payment-box strong {
            font-size: 14px;
            font-weight: 900;
            color: #fff;
        }

        .summary-title {
            font-size: 11px;
            font-weight: 900;
            color: var(--muted-2);
            text-transform: uppercase;
            letter-spacing: .12em;
            margin-bottom: 12px;
        }

        .summary-card {
            border-radius: 24px;
            background: rgba(2,6,23,.40);
            border: 1px solid rgba(255,255,255,.10);
            padding: 15px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            font-size: 13px;
            padding: 10px 0;
            border-bottom: 1px dashed rgba(255,255,255,.10);
        }

        .summary-item:first-child {
            padding-top: 0;
        }

        .summary-item .name {
            color: var(--muted);
            line-height: 1.45;
        }

        .summary-item .name span {
            color: #7dd3fc;
            font-weight: 900;
            margin-left: 4px;
        }

        .summary-item .price {
            font-weight: 900;
            color: #fff;
            white-space: nowrap;
        }

        .summary-promo {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-size: 13px;
            padding-top: 13px;
            margin-top: 4px;
            color: #86efac;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding-top: 15px;
            margin-top: 12px;
            border-top: 1px solid rgba(255,255,255,.10);
        }

        .summary-total span {
            font-size: 13px;
            font-weight: 900;
            color: var(--muted);
        }

        .summary-total strong {
            font-size: 20px;
            font-weight: 900;
            color: #fff;
        }

        .actions {
            display: grid;
            gap: 10px;
            margin-top: 18px;
        }

        .btn {
            text-decoration: none;
            width: 100%;
            border-radius: 21px;
            padding: 14px 16px;
            font-size: 13px;
            font-weight: 900;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            transition: .2s ease;
        }

        .btn-primary {
            background: var(--gradient);
            color: #fff;
            box-shadow: 0 20px 50px rgba(14,165,233,.22);
        }

        .btn-secondary {
            background: rgba(255,255,255,.05);
            color: #cbd5e1;
            border: 1px solid rgba(255,255,255,.10);
        }

        .btn-wa {
            background: rgba(34,197,94,.10);
            color: #86efac;
            border: 1px solid rgba(34,197,94,.22);
        }

        .btn:hover {
            opacity: .94;
        }

        @media (max-width: 380px) {
            .card-body {
                padding: 18px;
            }

            .status-header {
                padding: 26px 18px 22px;
            }

            .status-title {
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

<div class="wrap">
    <div class="card">

        <div class="status-header">
            <div class="logo">
                <img src="<?= esc($logoSrc) ?>"
                     alt="Logo <?= esc($storeName) ?>"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                <div class="logo-fallback">
                    <i class="fa-solid fa-store"></i>
                </div>
            </div>

            <?php if ($isSelesai): ?>
                <div class="icon-circle icon-success">
                    <i class="fa-solid fa-check"></i>
                </div>

                <h1 class="status-title">Pesanan Diproses!</h1>

                <p class="status-desc">
                    Pembayaran berhasil. Pesanan kamu sudah bisa segera diproses oleh kasir atau dapur.
                </p>
            <?php else: ?>
                <div class="icon-circle icon-pending">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <h1 class="status-title">Menunggu Pembayaran</h1>

                <p class="status-desc">
                    Pesanan berhasil dibuat, tetapi pembayaran masih menunggu konfirmasi.
                </p>
            <?php endif; ?>
        </div>

        <div class="card-body">

            <?php if ($isSelesai): ?>
                <div class="instruction-box instruction-success">
                    <div class="instruction-header">
                        <i class="fa-solid fa-circle-check"></i>
                        <h4>Pembayaran Berhasil</h4>
                    </div>
                    <p>
                        Terima kasih. Silakan tunggu, pesanan kamu akan segera disiapkan.
                    </p>
                </div>
            <?php else: ?>
                <div class="instruction-box instruction-pending">
                    <?php if ($metode === 'tunai'): ?>
                        <div class="instruction-header">
                            <i class="fa-solid fa-cash-register"></i>
                            <h4>Bayar ke Kasir</h4>
                        </div>
                        <p>
                            Tunjukkan halaman ini atau sebutkan kode pesanan ke kasir.
                            Pesanan akan diproses setelah pembayaran tunai diterima.
                        </p>
                    <?php else: ?>
                        <div class="instruction-header">
                            <i class="fa-solid fa-rotate"></i>
                            <h4>Menunggu Midtrans</h4>
                        </div>
                        <p>
                            Jika pembayaran sudah dilakukan melalui Midtrans, sistem akan memverifikasi otomatis.
                            Halaman ini bisa kamu refresh beberapa saat lagi.
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="info-grid">
                <div class="info-box">
                    <span class="info-label">Kode Pesanan</span>
                    <strong class="info-value"><?= esc($pesanan['kode_pesanan'] ?? '-') ?></strong>
                </div>

                <div class="info-box">
                    <span class="info-label">Meja</span>
                    <strong class="info-value"><?= esc($nomorMeja) ?></strong>
                </div>
            </div>

            <div class="payment-box">
                <div class="payment-icon">
                    <i class="fa-solid <?= esc($metodeIcon) ?>"></i>
                </div>

                <div>
                    <p>Atas Nama</p>
                    <strong><?= esc($pesanan['nama_pemesan'] ?? '-') ?></strong>
                    <p style="margin-top: 6px;">Metode Pembayaran</p>
                    <strong><?= esc($metodeLabel) ?></strong>
                </div>
            </div>

            <p class="summary-title">Ringkasan Pesanan</p>

            <div class="summary-card">
                <?php foreach (($items ?? []) as $item): ?>
                    <div class="summary-item">
                        <span class="name">
                            <?= esc($item['nama_menu'] ?? '-') ?>
                            <span>x<?= esc($item['qty'] ?? 0) ?></span>
                        </span>

                        <span class="price">
                            Rp <?= number_format(($item['harga'] ?? 0) * ($item['qty'] ?? 0), 0, ',', '.') ?>
                        </span>
                    </div>
                <?php endforeach; ?>

                <?php if (isset($pesanan['potongan']) && (int) $pesanan['potongan'] > 0): ?>
                    <div class="summary-promo">
                        <span style="font-weight: 800;">
                            Potongan <?= ! empty($pesanan['kode_promo']) ? '(' . esc($pesanan['kode_promo']) . ')' : '' ?>
                        </span>

                        <span style="font-weight: 900;">
                            -Rp <?= number_format((int) $pesanan['potongan'], 0, ',', '.') ?>
                        </span>
                    </div>
                <?php endif; ?>

                <div class="summary-total">
                    <span>Total Pembayaran</span>
                    <strong>Rp <?= number_format($pesanan['total_harga'] ?? 0, 0, ',', '.') ?></strong>
                </div>
            </div>

            <div class="actions">
                <a href="<?= base_url('order/' . $kodeMeja) ?>" class="btn btn-primary">
                    <i class="fa-solid fa-utensils"></i>
                    Kembali ke Menu
                </a>

                <?php if (! empty($whatsappUrl)): ?>
                    <a href="<?= esc($whatsappUrl) ?>" target="_blank" rel="noopener" class="btn btn-wa">
                        <i class="fa-brands fa-whatsapp"></i>
                        Hubungi Admin
                    </a>
                <?php endif; ?>

                <a href="javascript:location.reload()" class="btn btn-secondary">
                    <i class="fa-solid fa-rotate-right"></i>
                    Refresh Status
                </a>
            </div>

        </div>
    </div>
</div>

</body>
</html>