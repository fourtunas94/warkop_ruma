<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Diterima — ARZANA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Plus Jakarta Sans', sans-serif; } </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4">
<div class="w-full max-w-sm">
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="bg-emerald-500 px-6 py-8 text-center">
            <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-3">
                <i class="fa-solid fa-circle-check text-white text-3xl"></i>
            </div>
            <h1 class="text-white font-bold text-xl">Pesanan Diterima!</h1>
            <p class="text-emerald-100 text-sm mt-1">Silakan tunggu, pesananmu sedang diproses.</p>
        </div>

        <div class="px-5 py-5">
            <div class="flex items-center justify-between bg-slate-50 rounded-xl px-4 py-3 mb-4">
                <div>
                    <p class="text-xs text-slate-400 font-medium">Kode Pesanan</p>
                    <p class="text-lg font-bold text-slate-900"><?= esc($pesanan['kode_pesanan']) ?></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-400 font-medium">Meja</p>
                    <p class="text-lg font-bold text-sky-600"><?= esc($meja['nomor_meja']) ?></p>
                </div>
            </div>

            <div class="mb-4">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Atas Nama</p>
                <p class="text-sm font-semibold text-slate-800"><?= esc($pesanan['nama_pemesan']) ?></p>
            </div>

            <div class="mb-4">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Ringkasan Pesanan</p>
                <div class="space-y-2">
                    <?php foreach ($items as $item): ?>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-700"><?= esc($item['nama_menu']) ?> <span class="text-slate-400">x<?= $item['qty'] ?></span></span>
                        <span class="font-medium text-slate-800">Rp <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="flex items-center justify-between pt-3 mt-3 border-t border-slate-100">
                    <span class="text-sm font-semibold text-slate-700">Total Pembayaran</span>
                    <span class="text-base font-bold text-sky-600">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></span>
                </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 flex items-start gap-2 text-xs text-amber-700">
                <i class="fa-solid fa-circle-info mt-0.5 flex-shrink-0"></i>
                <span>Bayar secara <strong>tunai ke kasir</strong> setelah pesananmu selesai.</span>
            </div>
        </div>
    </div>
</div>
</body>
</html>