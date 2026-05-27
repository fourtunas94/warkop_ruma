<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h1 class="text-xl font-semibold text-slate-900">Riwayat Transaksi</h1>
    <p class="text-sm text-slate-500 mt-0.5">Seluruh daftar pesanan yang telah selesai diproses.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-receipt text-emerald-500"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Total Pesanan</p>
            <p class="text-xl font-semibold text-slate-900"><?= number_format($total_pesanan, 0, ',', '.') ?></p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-sky-50 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-sack-dollar text-sky-500"></i>
        </div>
        <div>
            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Total Pendapatan</p>
            <p class="text-xl font-semibold text-slate-900">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></p>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
    <form action="<?= base_url('admin/riwayat') ?>" method="get" class="flex flex-col sm:flex-row items-end gap-4">
        <div class="flex-1 w-full">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">Dari Tanggal</label>
            <input type="date" name="start_date" value="<?= esc($start_date ?? '') ?>" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all">
        </div>
        <div class="flex-1 w-full">
            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">Sampai Tanggal</label>
            <input type="date" name="end_date" value="<?= esc($end_date ?? '') ?>" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all">
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
            <button type="submit" class="w-full sm:w-auto bg-sky-500 hover:bg-sky-600 text-white font-medium px-6 py-2.5 rounded-lg transition-colors text-sm flex items-center justify-center gap-2">
                <i class="fa-solid fa-filter"></i> Terapkan
            </button>
            <a href="<?= base_url('admin/riwayat') ?>" class="w-full sm:w-auto bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium px-6 py-2.5 rounded-lg transition-colors text-sm flex items-center justify-center gap-2">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <?php if (empty($riwayat)): ?>
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-3">
                <i class="fa-solid fa-clock-rotate-left text-slate-400 text-xl"></i>
            </div>
            <p class="text-sm font-medium text-slate-600">Belum ada riwayat</p>
            <p class="text-xs text-slate-400 mt-1">Transaksi yang sesuai pencarian tidak ditemukan.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        <th class="px-5 py-4 whitespace-nowrap">Waktu Selesai</th>
                        <th class="px-5 py-4 whitespace-nowrap">ID Pesanan</th>
                        <th class="px-5 py-4 whitespace-nowrap">Pelanggan</th>
                        <th class="px-5 py-4 whitespace-nowrap">Meja</th>
                        <th class="px-5 py-4 whitespace-nowrap">Total</th>
                        <th class="px-5 py-4 text-center whitespace-nowrap">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($riwayat as $p): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-4 text-sm text-slate-600 whitespace-nowrap">
                            <?= date('d M Y, H:i', strtotime($p['updated_at'])) ?>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="text-xs font-medium bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md">
                                <?= esc($p['kode_pesanan']) ?>
                            </span>
                        </td>
                        <td class="px-5 py-4 text-sm font-medium text-slate-800 whitespace-nowrap">
                            <?= esc($p['nama_pemesan']) ?>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-600 whitespace-nowrap">
                            <?= esc($p['nomor_meja']) ?>
                        </td>
                        <td class="px-5 py-4 text-sm font-bold text-slate-800 whitespace-nowrap">
                            Rp <?= number_format($p['total_harga'], 0, ',', '.') ?>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-500 text-center whitespace-nowrap">
                            <button type="button" class="text-sky-500 hover:text-sky-600 font-medium" onclick="toggleDetail('detail-<?= $p['id'] ?>')">
                                Lihat <i class="fa-solid fa-chevron-down ml-1"></i>
                            </button>
                        </td>
                    </tr>
                    <tr id="detail-<?= $p['id'] ?>" class="hidden bg-slate-50/50 border-t-0">
                        <td colspan="6" class="px-5 py-4">
                            <div class="bg-white border border-slate-200 rounded-lg p-4">
                                <h4 class="text-xs font-semibold text-slate-800 uppercase tracking-wide mb-2">Item Pesanan</h4>
                                <ul class="space-y-1.5">
                                    <?php foreach ($p['items'] as $item): ?>
                                    <li class="flex justify-between text-sm text-slate-600">
                                        <span class="flex items-center gap-2">
                                            <i class="fa-solid fa-circle-dot text-[8px] text-slate-300"></i>
                                            <?= esc($item['nama_menu']) ?> 
                                            <span class="text-slate-400 text-xs">x<?= $item['qty'] ?></span>
                                        </span>
                                        <span class="font-medium text-slate-700">Rp <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if ($p['catatan']): ?>
                                <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-amber-600 bg-amber-50 rounded p-2">
                                    <i class="fa-solid fa-note-sticky mr-1"></i> Catatan: <?= esc($p['catatan']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    function toggleDetail(id) {
        const el = document.getElementById(id);
        if (el.classList.contains('hidden')) {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    }
</script>

<?= $this->endSection() ?>