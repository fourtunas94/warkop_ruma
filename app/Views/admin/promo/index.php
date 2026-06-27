<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php
$errors = session()->getFlashdata('errors') ?? [];

$formatRupiah = static function ($value): string {
    return 'Rp ' . number_format((int) $value, 0, ',', '.');
};

$formatTanggal = static function ($value): string {
    if (empty($value)) {
        return '-';
    }

    return date('d M Y, H:i', strtotime($value));
};

$formatDateInput = static function ($value): string {
    if (empty($value)) {
        return '';
    }

    return date('Y-m-d\TH:i', strtotime($value));
};

$discountLabel = static function (array $promo) use ($formatRupiah): string {
    if (($promo['tipe_diskon'] ?? '') === 'persen') {
        $label = (int) $promo['nilai_diskon'] . '%';

        if ((int) ($promo['maksimal_diskon'] ?? 0) > 0) {
            $label .= ' maks. ' . $formatRupiah($promo['maksimal_diskon']);
        }

        return $label;
    }

    return $formatRupiah($promo['nilai_diskon'] ?? 0);
};
?>

<div class="space-y-6">

    <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft">
        <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-pink-500/20 blur-3xl"></div>
        <div class="absolute -bottom-28 -left-24 w-72 h-72 rounded-full bg-sky-600/20 blur-3xl"></div>

        <div class="relative p-5 lg:p-7">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-pink-500/10 border border-pink-400/20 text-pink-300 text-xs font-semibold mb-4">
                        <i class="fa-solid fa-gift"></i>
                        Promo & Voucher
                    </div>

                    <h1 class="text-2xl lg:text-4xl font-extrabold tracking-tight text-white">
                        Kelola Kode Promo
                    </h1>

                    <p class="text-sm lg:text-base text-slate-400 mt-2 max-w-2xl">
                        Buat voucher diskon nominal atau persen dengan minimal belanja, kuota, dan periode berlaku.
                    </p>
                </div>

                <div class="rounded-3xl bg-white/[.04] border border-white/10 p-4 min-w-full sm:min-w-[280px] lg:min-w-[320px]">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-pink-500/10 border border-pink-400/20 flex items-center justify-center text-pink-300">
                            <i class="fa-solid fa-ticket"></i>
                        </div>

                        <div>
                            <p class="text-sm font-bold text-white">Total Promo</p>
                            <p class="text-2xl font-extrabold text-white mt-1">
                                <?= number_format(count($promos ?? []), 0, ',', '.') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
            <i class="fa-solid fa-circle-check"></i>
            <span><?= esc(session()->getFlashdata('success')) ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="flex items-center gap-3 rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= esc(session()->getFlashdata('error')) ?></span>
        </div>
    <?php endif; ?>

    <?php if (! empty($errors)): ?>
        <div class="rounded-2xl border border-red-400/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">
            <p class="font-bold mb-2 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                Terdapat kesalahan:
            </p>

            <ul class="list-disc list-inside space-y-1 text-xs">
                <?php foreach ((array) $errors as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <div class="xl:col-span-1">
            <div class="rounded-[1.7rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft p-5 lg:p-6 xl:sticky xl:top-24">
                <div class="mb-5">
                    <h2 class="text-lg font-extrabold text-white">
                        Buat Promo Baru
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Kode promo akan digunakan pelanggan di halaman checkout.
                    </p>
                </div>

                <form action="<?= base_url('admin/promo/store') ?>" method="post" class="space-y-4">
                    <?= csrf_field() ?>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                            Kode Promo
                        </label>
                        <input type="text"
                               name="kode"
                               value="<?= esc(old('kode')) ?>"
                               placeholder="Contoh: HEMAT10"
                               class="w-full uppercase rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                            Nama Promo
                        </label>
                        <input type="text"
                               name="nama_promo"
                               value="<?= esc(old('nama_promo')) ?>"
                               placeholder="Contoh: Diskon Pembukaan"
                               class="w-full rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                            Tipe Diskon
                        </label>
                        <select name="tipe_diskon"
                                class="w-full rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                            <option class="bg-slate-950 text-white" value="nominal" <?= old('tipe_diskon', 'nominal') === 'nominal' ? 'selected' : '' ?>>
                                Nominal Rupiah
                            </option>
                            <option class="bg-slate-950 text-white" value="persen" <?= old('tipe_diskon') === 'persen' ? 'selected' : '' ?>>
                                Persen
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                            Nilai Diskon
                        </label>
                        <input type="number"
                               name="nilai_diskon"
                               value="<?= esc(old('nilai_diskon', 0)) ?>"
                               min="0"
                               placeholder="Contoh nominal: 10000 / persen: 10"
                               class="w-full rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                        <p class="text-[11px] text-slate-500 mt-2">
                            Kalau tipe persen, isi 10 untuk diskon 10%.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-1 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                                Maks. Diskon
                            </label>
                            <input type="number"
                                   name="maksimal_diskon"
                                   value="<?= esc(old('maksimal_diskon', 0)) ?>"
                                   min="0"
                                   placeholder="0 = tanpa batas"
                                   class="w-full rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                                Minimal Belanja
                            </label>
                            <input type="number"
                                   name="minimal_belanja"
                                   value="<?= esc(old('minimal_belanja', 0)) ?>"
                                   min="0"
                                   placeholder="0 = tanpa minimal"
                                   class="w-full rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                            Kuota
                        </label>
                        <input type="number"
                               name="kuota"
                               value="<?= esc(old('kuota', 0)) ?>"
                               min="0"
                               placeholder="0 = tidak terbatas"
                               class="w-full rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                            Berlaku Mulai
                        </label>
                        <input type="datetime-local"
                               name="berlaku_mulai"
                               value="<?= esc(old('berlaku_mulai')) ?>"
                               class="w-full rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                            Berlaku Sampai
                        </label>
                        <input type="datetime-local"
                               name="berlaku_sampai"
                               value="<?= esc(old('berlaku_sampai')) ?>"
                               class="w-full rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                            Status
                        </label>
                        <select name="is_active"
                                class="w-full rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                            <option class="bg-slate-950 text-white" value="1" <?= old('is_active', 1) == 1 ? 'selected' : '' ?>>
                                Aktif
                            </option>
                            <option class="bg-slate-950 text-white" value="0" <?= old('is_active') == 0 ? 'selected' : '' ?>>
                                Nonaktif
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                            Deskripsi
                        </label>
                        <textarea name="deskripsi"
                                  rows="3"
                                  placeholder="Catatan internal promo"
                                  class="w-full rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400 resize-none"><?= esc(old('deskripsi')) ?></textarea>
                    </div>

                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-500 to-purple-600 px-5 py-3 text-sm font-bold text-white shadow-glow hover:opacity-95 transition">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Simpan Promo
                    </button>
                </form>
            </div>
        </div>

        <div class="xl:col-span-2">
            <div class="rounded-[1.7rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft overflow-hidden">
                <div class="p-5 lg:p-6 border-b border-white/10">
                    <h2 class="text-lg font-extrabold text-white">
                        Daftar Promo
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Kelola voucher yang bisa digunakan pelanggan saat checkout.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/10 bg-white/[.025] text-xs font-bold text-slate-500 uppercase tracking-[0.16em]">
                                <th class="px-5 py-4 whitespace-nowrap">Kode</th>
                                <th class="px-5 py-4 whitespace-nowrap">Diskon</th>
                                <th class="px-5 py-4 whitespace-nowrap">Syarat</th>
                                <th class="px-5 py-4 whitespace-nowrap">Kuota</th>
                                <th class="px-5 py-4 text-center whitespace-nowrap">Status</th>
                                <th class="px-5 py-4 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/10">
                            <?php if (! empty($promos)): ?>
                                <?php foreach ($promos as $p): ?>
                                    <tr class="hover:bg-white/[.025] transition">
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div>
                                                <span class="inline-flex items-center gap-2 text-xs font-black bg-pink-500/10 text-pink-300 border border-pink-400/20 px-3 py-1.5 rounded-full">
                                                    <i class="fa-solid fa-ticket"></i>
                                                    <?= esc($p['kode']) ?>
                                                </span>

                                                <p class="text-sm font-bold text-white mt-2">
                                                    <?= esc($p['nama_promo']) ?>
                                                </p>

                                                <?php if (! empty($p['deskripsi'])): ?>
                                                    <p class="text-xs text-slate-500 mt-1 max-w-[240px] truncate">
                                                        <?= esc($p['deskripsi']) ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <p class="text-sm font-extrabold text-white">
                                                <?= esc($discountLabel($p)) ?>
                                            </p>
                                            <p class="text-xs text-slate-500 mt-1 capitalize">
                                                <?= esc($p['tipe_diskon']) ?>
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <p class="text-xs text-slate-400">
                                                Min. <?= $formatRupiah($p['minimal_belanja'] ?? 0) ?>
                                            </p>
                                            <p class="text-xs text-slate-500 mt-1">
                                                <?= esc($formatTanggal($p['berlaku_mulai'] ?? null)) ?>
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                s/d <?= esc($formatTanggal($p['berlaku_sampai'] ?? null)) ?>
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <?php if ((int) ($p['kuota'] ?? 0) > 0): ?>
                                                <p class="text-sm font-bold text-white">
                                                    <?= number_format((int) $p['jumlah_digunakan'], 0, ',', '.') ?>
                                                    /
                                                    <?= number_format((int) $p['kuota'], 0, ',', '.') ?>
                                                </p>
                                                <p class="text-xs text-slate-500 mt-1">
                                                    Terpakai
                                                </p>
                                            <?php else: ?>
                                                <p class="text-sm font-bold text-white">Unlimited</p>
                                                <p class="text-xs text-slate-500 mt-1">
                                                    <?= number_format((int) $p['jumlah_digunakan'], 0, ',', '.') ?> terpakai
                                                </p>
                                            <?php endif; ?>
                                        </td>

                                        <td class="px-5 py-4 text-center whitespace-nowrap">
                                            <form action="<?= base_url('admin/promo/toggle/' . $p['id']) ?>" method="post" class="inline">
                                                <?= csrf_field() ?>

                                                <button type="submit"
                                                        class="inline-flex items-center gap-2 text-[10px] font-bold px-3 py-1.5 rounded-full border uppercase tracking-wider transition
                                                        <?= (int) $p['is_active'] === 1
                                                            ? 'bg-emerald-500/10 text-emerald-300 border-emerald-400/20 hover:bg-emerald-500/15'
                                                            : 'bg-slate-500/10 text-slate-400 border-slate-400/20 hover:bg-slate-500/15' ?>">
                                                    <i class="fa-solid fa-circle text-[7px] <?= (int) $p['is_active'] === 1 ? 'text-emerald-400' : 'text-slate-500' ?>"></i>
                                                    <?= (int) $p['is_active'] === 1 ? 'Aktif' : 'Nonaktif' ?>
                                                </button>
                                            </form>
                                        </td>

                                        <td class="px-5 py-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button"
                                                        onclick='openEditPromo(<?= json_encode([
                                                            'id'              => $p['id'],
                                                            'kode'            => $p['kode'],
                                                            'nama_promo'      => $p['nama_promo'],
                                                            'deskripsi'       => $p['deskripsi'],
                                                            'tipe_diskon'     => $p['tipe_diskon'],
                                                            'nilai_diskon'    => $p['nilai_diskon'],
                                                            'maksimal_diskon' => $p['maksimal_diskon'],
                                                            'minimal_belanja' => $p['minimal_belanja'],
                                                            'kuota'           => $p['kuota'],
                                                            'berlaku_mulai'   => $formatDateInput($p['berlaku_mulai'] ?? null),
                                                            'berlaku_sampai'  => $formatDateInput($p['berlaku_sampai'] ?? null),
                                                            'is_active'       => $p['is_active'],
                                                        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                                        class="inline-flex items-center gap-2 text-xs font-bold text-sky-300 hover:text-white bg-sky-500/10 hover:bg-sky-500/20 border border-sky-400/20 px-3 py-2 rounded-xl transition">
                                                    <i class="fa-solid fa-pen"></i>
                                                    Edit
                                                </button>

                                                <form action="<?= base_url('admin/promo/delete/' . $p['id']) ?>"
                                                      method="post"
                                                      onsubmit="return confirm('Hapus promo ini?')">
                                                    <?= csrf_field() ?>

                                                    <button type="submit"
                                                            class="inline-flex items-center gap-2 text-xs font-bold text-rose-300 hover:text-white bg-rose-500/10 hover:bg-rose-500/20 border border-rose-400/20 px-3 py-2 rounded-xl transition">
                                                        <i class="fa-solid fa-trash"></i>
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-5 py-20 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-16 h-16 rounded-3xl bg-white/[.04] border border-white/10 flex items-center justify-center">
                                                <i class="fa-solid fa-gift text-slate-500 text-2xl"></i>
                                            </div>

                                            <div>
                                                <p class="text-sm font-bold text-white">Belum ada promo</p>
                                                <p class="text-xs text-slate-500 mt-1">
                                                    Buat promo pertama dari form sebelah kiri.
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<div id="modal-edit-promo" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-sm p-4 overflow-y-auto">
    <div class="min-h-full flex items-center justify-center">
        <div class="w-full max-w-2xl rounded-[2rem] border border-white/10 bg-slate-950 shadow-soft overflow-hidden">
            <div class="p-5 border-b border-white/10 flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-extrabold text-white">Edit Promo</h3>
                    <p class="text-xs text-slate-500 mt-1">Perbarui data kode promo.</p>
                </div>

                <button type="button"
                        onclick="closeEditPromo()"
                        class="w-10 h-10 rounded-2xl bg-white/[.04] hover:bg-white/[.08] border border-white/10 text-slate-300 hover:text-white transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="form-edit-promo" action="#" method="post" class="p-5">
                <?= csrf_field() ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">Kode Promo</label>
                        <input type="text" name="kode" id="edit_kode"
                               class="w-full uppercase rounded-2xl bg-slate-900 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">Nama Promo</label>
                        <input type="text" name="nama_promo" id="edit_nama_promo"
                               class="w-full rounded-2xl bg-slate-900 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">Tipe Diskon</label>
                        <select name="tipe_diskon" id="edit_tipe_diskon"
                                class="w-full rounded-2xl bg-slate-900 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                            <option class="bg-slate-950 text-white" value="nominal">Nominal Rupiah</option>
                            <option class="bg-slate-950 text-white" value="persen">Persen</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">Nilai Diskon</label>
                        <input type="number" name="nilai_diskon" id="edit_nilai_diskon" min="0"
                               class="w-full rounded-2xl bg-slate-900 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">Maks. Diskon</label>
                        <input type="number" name="maksimal_diskon" id="edit_maksimal_diskon" min="0"
                               class="w-full rounded-2xl bg-slate-900 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">Minimal Belanja</label>
                        <input type="number" name="minimal_belanja" id="edit_minimal_belanja" min="0"
                               class="w-full rounded-2xl bg-slate-900 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">Kuota</label>
                        <input type="number" name="kuota" id="edit_kuota" min="0"
                               class="w-full rounded-2xl bg-slate-900 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">Status</label>
                        <select name="is_active" id="edit_is_active"
                                class="w-full rounded-2xl bg-slate-900 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                            <option class="bg-slate-950 text-white" value="1">Aktif</option>
                            <option class="bg-slate-950 text-white" value="0">Nonaktif</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">Berlaku Mulai</label>
                        <input type="datetime-local" name="berlaku_mulai" id="edit_berlaku_mulai"
                               class="w-full rounded-2xl bg-slate-900 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">Berlaku Sampai</label>
                        <input type="datetime-local" name="berlaku_sampai" id="edit_berlaku_sampai"
                               class="w-full rounded-2xl bg-slate-900 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">Deskripsi</label>
                        <textarea name="deskripsi" id="edit_deskripsi" rows="3"
                                  class="w-full rounded-2xl bg-slate-900 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400 resize-none"></textarea>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 mt-6 pt-5 border-t border-white/10">
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-500 to-purple-600 px-5 py-3 text-sm font-bold text-white shadow-glow hover:opacity-95 transition">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Simpan Perubahan
                    </button>

                    <button type="button"
                            onclick="closeEditPromo()"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white/[.04] hover:bg-white/[.08] border border-white/10 px-5 py-3 text-sm font-semibold text-slate-300 hover:text-white transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const BASE_URL_PROMO = '<?= rtrim(base_url('admin/promo'), '/') ?>';

    function openEditPromo(data) {
        const modal = document.getElementById('modal-edit-promo');
        const form = document.getElementById('form-edit-promo');

        form.action = BASE_URL_PROMO + '/update/' + data.id;

        document.getElementById('edit_kode').value = data.kode || '';
        document.getElementById('edit_nama_promo').value = data.nama_promo || '';
        document.getElementById('edit_deskripsi').value = data.deskripsi || '';
        document.getElementById('edit_tipe_diskon').value = data.tipe_diskon || 'nominal';
        document.getElementById('edit_nilai_diskon').value = data.nilai_diskon || 0;
        document.getElementById('edit_maksimal_diskon').value = data.maksimal_diskon || 0;
        document.getElementById('edit_minimal_belanja').value = data.minimal_belanja || 0;
        document.getElementById('edit_kuota').value = data.kuota || 0;
        document.getElementById('edit_berlaku_mulai').value = data.berlaku_mulai || '';
        document.getElementById('edit_berlaku_sampai').value = data.berlaku_sampai || '';
        document.getElementById('edit_is_active').value = String(data.is_active ?? 1);

        modal.classList.remove('hidden');
    }

    function closeEditPromo() {
        document.getElementById('modal-edit-promo').classList.add('hidden');
    }

    document.getElementById('modal-edit-promo').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditPromo();
        }
    });
</script>

<?= $this->endSection() ?>