<?php
helper('app_setting');

$storeName        = app_setting('store_name', 'ARZANA');
$storeLogo        = app_setting('store_logo', '');
$storeFavicon     = app_setting('store_favicon', '');
$storeWhatsapp    = app_setting('store_whatsapp', '');

$logoSrc = ! empty($storeLogo)
    ? base_url($storeLogo)
    : base_url('assets/img/logo_k.png');

$faviconPath = ! empty($storeFavicon)
    ? $storeFavicon
    : (! empty($storeLogo) ? $storeLogo : 'assets/img/logo_k.png');

$faviconSrc = base_url($faviconPath);

$whatsappUrl = '';
if (! empty($storeWhatsapp)) {
    $waNumber = preg_replace('/[^0-9]/', '', $storeWhatsapp);
    $waText   = 'Halo, saya mengalami kendala QR Code tidak valid.';
    $whatsappUrl = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($waText);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Tidak Valid — <?= esc($storeName) ?></title>

    <link rel="icon" type="image/png" href="<?= esc($faviconSrc) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --bg: #020617;
            --card: rgba(255, 255, 255, .055);
            --border: rgba(255, 255, 255, .10);
            --muted: #94a3b8;
            --rose: #f43f5e;
            --sky: #0ea5e9;
            --purple: #7c3aed;
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
                radial-gradient(circle at top left, rgba(244, 63, 94, .22), transparent 24rem),
                radial-gradient(circle at top right, rgba(124, 58, 237, .18), transparent 24rem),
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
            max-width: 420px;
            position: relative;
            z-index: 1;
        }

        .card {
            position: relative;
            overflow: hidden;
            border-radius: 32px;
            background: rgba(255,255,255,.045);
            border: 1px solid rgba(255,255,255,.10);
            box-shadow: var(--shadow);
            padding: 28px 22px;
            text-align: center;
        }

        .card::before {
            content: '';
            position: absolute;
            right: -70px;
            top: -80px;
            width: 190px;
            height: 190px;
            background: rgba(244,63,94,.22);
            filter: blur(42px);
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

        .content {
            position: relative;
            z-index: 1;
        }

        .logo {
            width: 64px;
            height: 64px;
            border-radius: 24px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.10);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0 auto 18px;
        }

        .logo img {
            width: 52px;
            height: 52px;
            object-fit: contain;
        }

        .logo-fallback {
            display: none;
            width: 52px;
            height: 52px;
            border-radius: 20px;
            background: var(--gradient);
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .icon {
            width: 74px;
            height: 74px;
            border-radius: 28px;
            background: rgba(244,63,94,.12);
            border: 1px solid rgba(244,63,94,.24);
            color: #fda4af;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            font-size: 30px;
            box-shadow: 0 0 35px rgba(244,63,94,.16);
        }

        h1 {
            font-size: 24px;
            line-height: 1.2;
            font-weight: 900;
            letter-spacing: -.04em;
            margin-bottom: 9px;
        }

        p {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.65;
            margin-bottom: 22px;
        }

        .actions {
            display: grid;
            gap: 10px;
        }

        .btn {
            text-decoration: none;
            width: 100%;
            border-radius: 20px;
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

        .btn:hover {
            opacity: .94;
        }
    </style>
</head>

<body>

<div class="wrap">
    <div class="card">
        <div class="content">
            <div class="logo">
                <img src="<?= esc($logoSrc) ?>"
                     alt="Logo <?= esc($storeName) ?>"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">

                <div class="logo-fallback">
                    <i class="fa-solid fa-store"></i>
                </div>
            </div>

            <div class="icon">
                <i class="fa-solid fa-qrcode"></i>
            </div>

            <h1>QR Code Tidak Valid</h1>

            <p>
                Meja tidak ditemukan, QR sudah tidak aktif, atau link yang kamu buka tidak sesuai.
                Silakan hubungi kasir untuk mendapatkan QR Code yang benar.
            </p>

            <div class="actions">
                <?php if (! empty($whatsappUrl)): ?>
                    <a href="<?= esc($whatsappUrl) ?>" target="_blank" rel="noopener" class="btn btn-primary">
                        <i class="fa-brands fa-whatsapp"></i>
                        Hubungi Admin
                    </a>
                <?php endif; ?>

                <a href="<?= base_url() ?>" class="btn btn-secondary">
                    <i class="fa-solid fa-house"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>