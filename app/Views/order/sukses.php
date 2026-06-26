<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan — ARZANA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #0a0e17;
            --card-dark: #131824;
            --text-light: #ffffff;
            --text-dim: #8a94a6;
            --btn-blue: #0066ff;
            --gold: #e2b938;
            --emerald: #10b981;
            --border-color: rgba(255, 255, 255, 0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text-light); -webkit-font-smoothing: antialiased; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 30px 20px; }
        .container { width: 100%; max-width: 400px; }
        .card { background-color: var(--card-dark); border-radius: 20px; overflow: hidden; border: 1px solid var(--border-color); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4); }
        
        /* Header Status */
        .status-header { padding: 30px 20px; text-align: center; border-bottom: 1px solid var(--border-color); }
        .status-header.success { background-color: rgba(16, 185, 129, 0.05); }
        .status-header.pending { background-color: rgba(226, 185, 56, 0.05); }
        
        .icon-circle { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 28px; }
        .success .icon-circle { background-color: rgba(16, 185, 129, 0.1); color: var(--emerald); border: 1px solid rgba(16, 185, 129, 0.2); box-shadow: 0 0 20px rgba(16, 185, 129, 0.15); }
        .pending .icon-circle { background-color: rgba(226, 185, 56, 0.1); color: var(--gold); border: 1px solid rgba(226, 185, 56, 0.2); box-shadow: 0 0 20px rgba(226, 185, 56, 0.15); }
        
        .status-title { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .status-desc { font-size: 13px; color: var(--text-dim); }
        
        /* Konten */
        .card-body { padding: 24px 20px; }
        .instruction-box { background-color: rgba(226, 185, 56, 0.05); border: 1px dashed rgba(226, 185, 56, 0.3); border-radius: 14px; padding: 16px; margin-bottom: 24px; }
        .instruction-header { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
        .instruction-header i { color: var(--gold); font-size: 20px; }
        .instruction-header h4 { font-size: 13px; font-weight: 700; color: var(--gold); }
        .instruction-box p { font-size: 12px; color: var(--text-dim); line-height: 1.5; }
        
        /* Info Pesanan */
        .order-info { display: flex; justify-content: space-between; background-color: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 14px; padding: 16px; margin-bottom: 20px; }
        .info-col { flex: 1; }
        .info-col:last-child { text-align: right; border-left: 1px solid var(--border-color); padding-left: 16px; }
        .info-label { font-size: 10px; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
        .info-value { font-size: 16px; font-weight: 700; color: var(--text-light); }
        .info-col:last-child .info-value { color: var(--gold); }
        
        /* Ringkasan */
        .summary-title { font-size: 11px; font-weight: 700; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
        .summary-item { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 10px; }
        .summary-item .name { color: var(--text-dim); }
        .summary-item .name span { color: var(--gold); font-weight: 600; margin-left: 4px; }
        .summary-item .price { font-weight: 600; color: var(--text-light); }
        .summary-promo { display: flex; justify-content: space-between; font-size: 13px; padding-top: 12px; margin-top: 12px; border-top: 1px solid var(--border-color); color: var(--emerald); }
        .summary-total { display: flex; justify-content: space-between; align-items: center; padding-top: 16px; margin-top: 12px; border-top: 1px solid var(--border-color); }
        .summary-total span { font-size: 13px; font-weight: 700; color: var(--text-dim); }
        .summary-total strong { font-size: 18px; font-weight: 700; color: var(--gold); }
        
        .btn-back { display: block; text-align: center; width: 100%; background-color: var(--card-dark); border: 1px solid var(--border-color); color: var(--text-light); padding: 14px; border-radius: 12px; font-size: 13px; font-weight: 600; text-decoration: none; margin-top: 20px; transition: 0.2s; }
        .btn-back:hover { background-color: #1a2235; }
    </style>
</head>
<body>

<?php 
    $metode = isset($pesanan['metode_pembayaran']) ? strtolower($pesanan['metode_pembayaran']) : 'tunai'; 
    $isSelesai = ($pesanan['status'] === 'selesai');
?>

<div class="container">
    <div class="card">
        
        <?php if ($isSelesai): ?>
            <div class="status-header success">
                <div class="icon-circle"><i class="fa-solid fa-check"></i></div>
                <h1 class="status-title">Pesanan Diproses!</h1>
                <p class="status-desc">Pembayaran berhasil. Dapur kami sedang menyiapkan pesananmu.</p>
            </div>
        <?php else: ?>
            <div class="status-header pending">
                <div class="icon-circle"><i class="fa-solid fa-clock"></i></div>
                <h1 class="status-title">Menunggu Pembayaran</h1>
                <p class="status-desc">Selesaikan pembayaran agar pesanan segera diproses.</p>
            </div>
        <?php endif; ?>

        <div class="card-body">
            
            <?php if (!$isSelesai): ?>
                <div class="instruction-box">
                    <?php if ($metode == 'tunai'): ?>
                        <div class="instruction-header">
                            <i class="fa-solid fa-cash-register"></i>
                            <h4>Langkah Selanjutnya</h4>
                        </div>
                        <p>Segera tunjukkan layar ini atau sebutkan kode pesanan ke kasir untuk melakukan pembayaran tunai.</p>
                    
                    <?php else: ?>
                        <div class="instruction-header">
                            <i class="fa-solid fa-rotate"></i>
                            <h4>Menunggu Konfirmasi</h4>
                        </div>
                        <p>Jika kamu sudah melakukan pembayaran melalui Duitku, sistem kami sedang memverifikasinya. Halaman ini bisa kamu *refresh* beberapa saat lagi.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="order-info">
                <div class="info-col">
                    <p class="info-label">Kode Pesanan</p>
                    <p class="info-value"><?= esc($pesanan['kode_pesanan']) ?></p>
                </div>
                <div class="info-col">
                    <p class="info-label">Meja</p>
                    <p class="info-value"><?= esc($meja['nomor_meja']) ?></p>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <p class="info-label">Atas Nama (<?= strtoupper($metode) ?>)</p>
                <p style="font-size: 15px; font-weight: 600; color: var(--text-light);"><?= esc($pesanan['nama_pemesan']) ?></p>
            </div>

            <div>
                <p class="summary-title">Ringkasan Pesanan</p>
                
                <?php foreach ($items as $item): ?>
                <div class="summary-item">
                    <span class="name"><?= esc($item['nama_menu']) ?> <span>x<?= $item['qty'] ?></span></span>
                    <span class="price">Rp <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?></span>
                </div>
                <?php endforeach; ?>

                <?php if (isset($pesanan['potongan']) && $pesanan['potongan'] > 0): ?>
                <div class="summary-promo">
                    <span style="font-weight: 600;">Potongan (<?= esc($pesanan['kode_promo']) ?>)</span>
                    <span style="font-weight: 700;">-Rp <?= number_format($pesanan['potongan'], 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>

                <div class="summary-total">
                    <span>Total Pembayaran</span>
                    <strong>Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></strong>
                </div>
            </div>

            <a href="<?= base_url('order/' . $meja['kode_unik']) ?>" class="btn-back">Kembali ke Menu</a>

        </div>
    </div>
</div>

</body>
</html>