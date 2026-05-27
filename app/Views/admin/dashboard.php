<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h1 class="text-xl font-semibold text-slate-900">Dashboard Admin</h1>
    <p class="text-sm text-slate-500 mt-0.5">Ringkasan operasional sistem hari ini.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Pesanan Hari Ini</span>
            <div class="w-8 h-8 rounded-lg bg-sky-50 flex items-center justify-center">
                <i class="fa-solid fa-receipt text-sky-500 text-sm"></i>
            </div>
        </div>
        <p class="text-2xl font-semibold text-slate-900"><?= number_format($total_pesanan, 0, ',', '.') ?></p>
        <p class="text-xs text-slate-400 mt-1">Total transaksi masuk</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Pendapatan (Hari Ini)</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                <i class="fa-solid fa-sack-dollar text-emerald-500 text-sm"></i>
            </div>
        </div>
        <p class="text-2xl font-semibold text-slate-900">Rp <?= number_format($pendapatan, 0, ',', '.') ?></p>
        <p class="text-xs text-slate-400 mt-1">Hanya transaksi selesai</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Menu Aktif</span>
            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                <i class="fa-solid fa-utensils text-amber-500 text-sm"></i>
            </div>
        </div>
        <p class="text-2xl font-semibold text-slate-900"><?= number_format($total_menu, 0, ',', '.') ?></p>
        <p class="text-xs text-slate-400 mt-1">Item menu tersedia</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wide">Total Meja</span>
            <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center">
                <i class="fa-solid fa-table-cells text-violet-500 text-sm"></i>
            </div>
        </div>
        <p class="text-2xl font-semibold text-slate-900"><?= number_format($total_meja, 0, ',', '.') ?></p>
        <p class="text-xs text-slate-400 mt-1">Meja terdaftar</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    
    <div class="xl:col-span-2 bg-white rounded-xl border border-slate-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-slate-800">Pesanan Terbaru</h2>
            <a href="<?= base_url('admin/riwayat') ?>" class="text-xs text-sky-500 hover:text-sky-600 font-medium flex items-center gap-1 transition-colors">
                Lihat semua <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>
        
        <?php if (empty($pesanan_terbaru)): ?>
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mb-3">
                    <i class="fa-solid fa-clipboard-list text-slate-400 text-lg"></i>
                </div>
                <p class="text-sm font-medium text-slate-600">Belum ada pesanan</p>
                <p class="text-xs text-slate-400 mt-1">Pesanan masuk akan ditampilkan di sini.</p>
            </div>
        <?php else: ?>
            <div class="divide-y divide-slate-100">
                <?php foreach($pesanan_terbaru as $p): ?>
                <div class="py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center border border-slate-100">
                            <i class="fa-solid fa-receipt text-slate-400 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">
                                Meja <?= esc($p['nomor_meja']) ?> 
                                <span class="text-xs font-normal text-slate-400 ml-1.5">#<?= esc($p['kode_pesanan']) ?></span>
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                <i class="fa-solid fa-user mr-1"></i> <?= esc($p['nama_pemesan']) ?> 
                                <span class="mx-1.5 text-slate-300">·</span> 
                                <?= date('H:i', strtotime($p['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-slate-800 mb-1">Rp <?= number_format($p['total_harga'], 0, ',', '.') ?></p>
                        <?php if($p['status'] == 'selesai'): ?>
                            <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-md uppercase tracking-wider">Selesai</span>
                        <?php else: ?>
                            <span class="text-[10px] font-semibold text-amber-600 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded-md uppercase tracking-wider">Menunggu</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h2 class="text-sm font-semibold text-slate-800 mb-4">Akses Cepat</h2>
        <div class="space-y-2">
            <a href="<?= base_url('admin/menu') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 border border-slate-100 text-sm text-slate-700 transition-all group">
                <div class="w-7 h-7 rounded-md bg-amber-50 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-utensils text-amber-500 text-xs"></i>
                </div>
                <span>Tambah Menu Baru</span>
                <i class="fa-solid fa-chevron-right text-xs text-slate-300 ml-auto group-hover:text-slate-500 transition-colors"></i>
            </a>
            <a href="<?= base_url('admin/meja') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 border border-slate-100 text-sm text-slate-700 transition-all group">
                <div class="w-7 h-7 rounded-md bg-violet-50 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-qrcode text-violet-500 text-xs"></i>
                </div>
                <span>Kelola QR Meja</span>
                <i class="fa-solid fa-chevron-right text-xs text-slate-300 ml-auto group-hover:text-slate-500 transition-colors"></i>
            </a>
            <a href="<?= base_url('admin/users') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 border border-slate-100 text-sm text-slate-700 transition-all group">
                <div class="w-7 h-7 rounded-md bg-sky-50 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-user-plus text-sky-500 text-xs"></i>
                </div>
                <span>Tambah Akun Kasir</span>
                <i class="fa-solid fa-chevron-right text-xs text-slate-300 ml-auto group-hover:text-slate-500 transition-colors"></i>
            </a>
            <a href="<?= base_url('admin/riwayat') ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 border border-slate-100 text-sm text-slate-700 transition-all group">
                <div class="w-7 h-7 rounded-md bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-file-invoice text-emerald-500 text-xs"></i>
                </div>
                <span>Riwayat Transaksi</span>
                <i class="fa-solid fa-chevron-right text-xs text-slate-300 ml-auto group-hover:text-slate-500 transition-colors"></i>
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>