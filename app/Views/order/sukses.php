<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Diterima — ARZANA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        gold: '#e2b938',
                        darkbg: '#121212',
                        cardbg: '#1e1e1e',
                        btnbg: '#2a2a2a'
                    }
                }
            }
        }
    </script>
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #121212; color: #ffffff; } </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 py-8">
<div class="w-full max-w-sm">
    <div class="bg-cardbg rounded-2xl border border-gray-800 overflow-hidden shadow-2xl">
        
        <div class="px-6 py-8 text-center border-b border-gray-800 bg-btnbg/30">
            <div class="w-16 h-16 rounded-full bg-emerald-500/20 flex items-center justify-center mx-auto mb-4 border border-emerald-500/30 shadow-[0_0_15px_rgba(16,185,129,0.2)]">
                <i class="fa-solid fa-check text-emerald-400 text-3xl"></i>
            </div>
            <h1 class="text-white font-bold text-2xl mb-1">Pesanan Diterima!</h1>
            <p class="text-gray-400 text-sm">Dapur kami sedang menyiapkan pesananmu.</p>
        </div>

        <div class="px-5 py-6">
            
            <div class="bg-gold/10 border border-gold/30 rounded-xl p-4 flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-full bg-gold/20 flex items-center justify-center flex-shrink-0 animate-pulse">
                    <i class="fa-solid fa-mobile-screen-button text-gold text-xl"></i>
                </div>
                <div>
                    <p class="text-gold font-bold text-sm mb-0.5">Langkah Selanjutnya</p>
                    <p class="text-gray-300 text-xs leading-relaxed">Segera tunjukkan layar ini ke kasir untuk melakukan pembayaran.</p>
                </div>
            </div>

            <div class="flex items-center justify-between bg-darkbg border border-gray-800 rounded-xl px-4 py-3 mb-5">
                <div>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mb-0.5">Kode Pesanan</p>
                    <p class="text-lg font-bold text-white"><?= esc($pesanan['kode_pesanan']) ?></p>
                </div>
                <div class="text-right border-l border-gray-800 pl-4">
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mb-0.5">Meja</p>
                    <p class="text-lg font-bold text-gold"><?= esc($meja['nomor_meja']) ?></p>
                </div>
            </div>

            <div class="mb-5">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Atas Nama</p>
                <p class="text-base font-bold text-white"><?= esc($pesanan['nama_pemesan']) ?></p>
            </div>

            <div class="mb-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Ringkasan Pesanan</p>
                <div class="space-y-3">
                    <?php foreach ($items as $item): ?>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-300 font-medium"><?= esc($item['nama_menu']) ?> <span class="text-gold font-bold ml-1">x<?= $item['qty'] ?></span></span>
                        <span class="font-bold text-white">Rp <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (isset($pesanan['potongan']) && $pesanan['potongan'] > 0): ?>
                <div class="flex items-center justify-between pt-3 mt-3 border-t border-gray-800 text-emerald-400 text-sm">
                    <span class="font-semibold">Potongan (<?= esc($pesanan['kode_promo']) ?>)</span>
                    <span class="font-bold">-Rp <?= number_format($pesanan['potongan'], 0, ',', '.') ?></span>
                </div>
                <?php endif; ?>

                <div class="flex items-center justify-between pt-4 mt-3 border-t border-gray-800">
                    <span class="text-sm font-bold text-gray-400">Total Pembayaran</span>
                    <span class="text-xl font-bold text-gold">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>