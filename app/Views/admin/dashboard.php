<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
helper('app_setting');

$storeName        = app_setting('store_name', 'ARZANA');
$storeDescription = app_setting('store_description', 'Ringkasan operasional sistem hari ini.');

$statusLabel = static function (?string $status): string {
    return match ($status) {
        'selesai' => 'Selesai',
        'pending' => 'Menunggu',
        default   => ucfirst((string) $status),
    };
};

$statusClass = static function (?string $status): string {
    return match ($status) {
        'selesai' => 'bg-emerald-500/10 text-emerald-300 border-emerald-400/20',
        'pending' => 'bg-amber-500/10 text-amber-300 border-amber-400/20',
        default   => 'bg-slate-500/10 text-slate-300 border-slate-400/20',
    };
};

$paymentLabel = static function (?string $metode): string {
    return match ($metode) {
        'tunai'    => 'Tunai',
        'qris'     => 'QRIS / E-Wallet',
        'transfer' => 'Transfer Bank',
        default    => ! empty($metode) ? ucfirst((string) $metode) : '-',
    };
};

$paymentIcon = static function (?string $metode): string {
    return match ($metode) {
        'tunai'    => 'fa-money-bill-wave',
        'qris'     => 'fa-qrcode',
        'transfer' => 'fa-building-columns',
        default    => 'fa-credit-card',
    };
};
?>

<div class="space-y-6">

    <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft">
        <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-sky-500/20 blur-3xl"></div>
        <div class="absolute -bottom-28 -left-24 w-72 h-72 rounded-full bg-purple-600/20 blur-3xl"></div>

        <div class="relative p-5 lg:p-7">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-sky-500/10 border border-sky-400/20 text-sky-300 text-xs font-semibold mb-4">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Admin Dashboard
                    </div>

                    <h1 class="text-2xl lg:text-4xl font-extrabold tracking-tight text-white">
                        Dashboard <?= esc($storeName) ?>
                    </h1>

                    <p class="text-sm lg:text-base text-slate-400 mt-2 max-w-2xl">
                        <?= esc($storeDescription ?: 'Pantau pesanan, pendapatan, menu, dan aktivitas operasional restoran dari satu halaman.') ?>
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 min-w-full sm:min-w-[360px] xl:min-w-[420px]">
                    <a href="<?= base_url('admin/menu') ?>"
                       class="group rounded-3xl bg-white/[.04] hover:bg-white/[.07] border border-white/10 p-4 transition">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-300 flex items-center justify-center mb-3 group-hover:scale-105 transition">
                            <i class="fa-solid fa-plus"></i>
                        </div>
                        <p class="text-sm font-bold text-white">Tambah Menu</p>
                        <p class="text-xs text-slate-500 mt-1">Kelola item baru</p>
                    </a>

                    <a href="<?= base_url('admin/meja') ?>"
                       class="group rounded-3xl bg-white/[.04] hover:bg-white/[.07] border border-white/10 p-4 transition">
                        <div class="w-10 h-10 rounded-2xl bg-purple-500/10 text-purple-300 flex items-center justify-center mb-3 group-hover:scale-105 transition">
                            <i class="fa-solid fa-qrcode"></i>
                        </div>
                        <p class="text-sm font-bold text-white">QR Meja</p>
                        <p class="text-xs text-slate-500 mt-1">Cetak QR Code</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="relative overflow-hidden rounded-[1.7rem] border border-white/10 bg-white/[.035] backdrop-blur p-5 shadow-soft">
            <div class="absolute -right-10 -top-10 w-32 h-32 rounded-full bg-sky-500/20 blur-2xl"></div>

            <div class="relative flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-[0.16em]">
                        Pesanan Hari Ini
                    </p>
                    <p class="text-3xl font-extrabold text-white mt-3">
                        <?= number_format($total_pesanan ?? 0, 0, ',', '.') ?>
                    </p>
                    <p class="text-xs text-slate-500 mt-2">
                        Total transaksi masuk
                    </p>
                </div>

                <div class="w-12 h-12 rounded-2xl bg-sky-500/10 border border-sky-400/20 flex items-center justify-center text-sky-300">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[1.7rem] border border-white/10 bg-white/[.035] backdrop-blur p-5 shadow-soft">
            <div class="absolute -right-10 -top-10 w-32 h-32 rounded-full bg-emerald-500/20 blur-2xl"></div>

            <div class="relative flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-[0.16em]">
                        Pendapatan Hari Ini
                    </p>
                    <p class="text-3xl font-extrabold text-white mt-3">
                        Rp <?= number_format($pendapatan ?? 0, 0, ',', '.') ?>
                    </p>
                    <p class="text-xs text-slate-500 mt-2">
                        Hanya transaksi selesai
                    </p>
                </div>

                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center text-emerald-300">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[1.7rem] border border-white/10 bg-white/[.035] backdrop-blur p-5 shadow-soft">
            <div class="absolute -right-10 -top-10 w-32 h-32 rounded-full bg-amber-500/20 blur-2xl"></div>

            <div class="relative flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-[0.16em]">
                        Menu Aktif
                    </p>
                    <p class="text-3xl font-extrabold text-white mt-3">
                        <?= number_format($total_menu ?? 0, 0, ',', '.') ?>
                    </p>
                    <p class="text-xs text-slate-500 mt-2">
                        Item menu tersedia
                    </p>
                </div>

                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-400/20 flex items-center justify-center text-amber-300">
                    <i class="fa-solid fa-bowl-food"></i>
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[1.7rem] border border-white/10 bg-white/[.035] backdrop-blur p-5 shadow-soft">
            <div class="absolute -right-10 -top-10 w-32 h-32 rounded-full bg-purple-500/20 blur-2xl"></div>

            <div class="relative flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-[0.16em]">
                        Total Meja
                    </p>
                    <p class="text-3xl font-extrabold text-white mt-3">
                        <?= number_format($total_meja ?? 0, 0, ',', '.') ?>
                    </p>
                    <p class="text-xs text-slate-500 mt-2">
                        Meja terdaftar
                    </p>
                </div>

                <div class="w-12 h-12 rounded-2xl bg-purple-500/10 border border-purple-400/20 flex items-center justify-center text-purple-300">
                    <i class="fa-solid fa-chair"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        <div class="xl:col-span-2 rounded-[1.7rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft overflow-hidden">
            <div class="p-5 lg:p-6 border-b border-white/10 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-extrabold text-white">Pesanan Terbaru</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Daftar transaksi terbaru yang masuk ke sistem.
                    </p>
                </div>

                <a href="<?= base_url('admin/riwayat') ?>"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-sky-500/10 hover:bg-sky-500/15 border border-sky-400/20 text-sky-300 text-xs font-bold transition">
                    Lihat Semua
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <?php if (empty($pesanan_terbaru)): ?>
                <div class="flex flex-col items-center justify-center py-16 px-5 text-center">
                    <div class="w-16 h-16 rounded-3xl bg-white/[.04] border border-white/10 flex items-center justify-center mb-4">
                        <i class="fa-solid fa-clipboard-list text-slate-500 text-2xl"></i>
                    </div>
                    <p class="text-sm font-bold text-white">Belum ada pesanan</p>
                    <p class="text-xs text-slate-500 mt-1">
                        Pesanan masuk akan ditampilkan di sini.
                    </p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-white/10">
                    <?php foreach ($pesanan_terbaru as $p): ?>
                        <?php
                            $status = $p['status'] ?? 'pending';
                            $metode = $p['metode_pembayaran'] ?? '';
                        ?>

                        <div class="p-5 lg:p-6 hover:bg-white/[.025] transition">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <div class="flex items-start gap-4 min-w-0">
                                    <div class="w-12 h-12 rounded-2xl bg-white/[.04] border border-white/10 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-receipt text-sky-300"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-extrabold text-white">
                                                Meja <?= esc($p['nomor_meja'] ?? '-') ?>
                                            </p>

                                            <span class="text-[11px] px-2 py-1 rounded-full bg-white/[.04] border border-white/10 text-slate-400">
                                                #<?= esc($p['kode_pesanan'] ?? '-') ?>
                                            </span>

                                            <span class="text-[10px] font-bold px-2 py-1 rounded-full border uppercase tracking-wider <?= $statusClass($status) ?>">
                                                <?= esc($statusLabel($status)) ?>
                                            </span>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-xs text-slate-500">
                                            <span>
                                                <i class="fa-solid fa-user mr-1 text-slate-600"></i>
                                                <?= esc($p['nama_pemesan'] ?? '-') ?>
                                            </span>

                                            <span>
                                                <i class="fa-regular fa-clock mr-1 text-slate-600"></i>
                                                <?= ! empty($p['created_at']) ? date('H:i', strtotime($p['created_at'])) : '-' ?>
                                            </span>

                                            <span>
                                                <i class="fa-solid <?= esc($paymentIcon($metode)) ?> mr-1 text-slate-600"></i>
                                                <?= esc($paymentLabel($metode)) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="lg:text-right">
                                    <p class="text-lg font-extrabold text-white">
                                        Rp <?= number_format($p['total_harga'] ?? 0, 0, ',', '.') ?>
                                    </p>

                                    <p class="text-xs text-slate-500 mt-1">
                                        <?= ! empty($p['created_at']) ? date('d M Y', strtotime($p['created_at'])) : '-' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-5">

            <div class="rounded-[1.7rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft overflow-hidden">
                <div class="p-5 border-b border-white/10">
                    <h2 class="text-lg font-extrabold text-white">Akses Cepat</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Shortcut untuk mengelola sistem.
                    </p>
                </div>

                <div class="p-4 space-y-2">
                    <a href="<?= base_url('admin/menu') ?>"
                       class="group flex items-center gap-3 p-3 rounded-2xl hover:bg-white/[.05] border border-white/10 text-sm text-slate-300 transition">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500/10 border border-amber-400/20 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-utensils text-amber-300"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="font-bold text-white">Tambah Menu Baru</p>
                            <p class="text-xs text-slate-500 mt-0.5">Kelola makanan dan minuman</p>
                        </div>

                        <i class="fa-solid fa-chevron-right text-xs text-slate-600 ml-auto group-hover:text-white transition"></i>
                    </a>

                    <a href="<?= base_url('admin/meja') ?>"
                       class="group flex items-center gap-3 p-3 rounded-2xl hover:bg-white/[.05] border border-white/10 text-sm text-slate-300 transition">
                        <div class="w-10 h-10 rounded-2xl bg-purple-500/10 border border-purple-400/20 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-qrcode text-purple-300"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="font-bold text-white">Kelola QR Meja</p>
                            <p class="text-xs text-slate-500 mt-0.5">Generate dan cetak QR</p>
                        </div>

                        <i class="fa-solid fa-chevron-right text-xs text-slate-600 ml-auto group-hover:text-white transition"></i>
                    </a>

                    <a href="<?= base_url('admin/users') ?>"
                       class="group flex items-center gap-3 p-3 rounded-2xl hover:bg-white/[.05] border border-white/10 text-sm text-slate-300 transition">
                        <div class="w-10 h-10 rounded-2xl bg-sky-500/10 border border-sky-400/20 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-user-plus text-sky-300"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="font-bold text-white">Tambah Akun Kasir</p>
                            <p class="text-xs text-slate-500 mt-0.5">Atur user operasional</p>
                        </div>

                        <i class="fa-solid fa-chevron-right text-xs text-slate-600 ml-auto group-hover:text-white transition"></i>
                    </a>

                    <a href="<?= base_url('admin/riwayat') ?>"
                       class="group flex items-center gap-3 p-3 rounded-2xl hover:bg-white/[.05] border border-white/10 text-sm text-slate-300 transition">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 border border-emerald-400/20 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-file-invoice text-emerald-300"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="font-bold text-white">Riwayat Transaksi</p>
                            <p class="text-xs text-slate-500 mt-0.5">Lihat rekap pesanan</p>
                        </div>

                        <i class="fa-solid fa-chevron-right text-xs text-slate-600 ml-auto group-hover:text-white transition"></i>
                    </a>

                    <a href="<?= base_url('admin/pengaturan') ?>"
                       class="group flex items-center gap-3 p-3 rounded-2xl hover:bg-white/[.05] border border-white/10 text-sm text-slate-300 transition">
                        <div class="w-10 h-10 rounded-2xl bg-pink-500/10 border border-pink-400/20 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-store text-pink-300"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="font-bold text-white">Pengaturan Toko</p>
                            <p class="text-xs text-slate-500 mt-0.5">Logo, nama toko, dan favicon</p>
                        </div>

                        <i class="fa-solid fa-chevron-right text-xs text-slate-600 ml-auto group-hover:text-white transition"></i>
                    </a>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-[1.7rem] border border-white/10 bg-gradient-to-br from-sky-500/15 via-purple-600/10 to-transparent p-5 shadow-soft">
                <div class="absolute -right-12 -bottom-12 w-40 h-40 rounded-full bg-sky-400/20 blur-3xl"></div>

                <div class="relative">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center text-sky-300 mb-4">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>

                    <h3 class="text-base font-extrabold text-white">
                        Payment Gateway Aktif
                    </h3>

                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                        Sistem pembayaran mendukung Tunai, QRIS, E-Wallet, dan Transfer Bank melalui Midtrans.
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-300 border border-emerald-400/20">
                            Tunai
                        </span>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-sky-500/10 text-sky-300 border border-sky-400/20">
                            QRIS
                        </span>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-purple-500/10 text-purple-300 border border-purple-400/20">
                            VA Bank
                        </span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<?= $this->endSection() ?>