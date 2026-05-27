<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php $isEdit = isset($kategori); ?>

<div class="mb-6 flex items-center gap-3">
    <a href="<?= base_url('admin/kategori') ?>" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors">
        <i class="fa-solid fa-arrow-left text-xs"></i>
    </a>
    <div>
        <h1 class="text-xl font-semibold text-slate-900"><?= $isEdit ? 'Edit Kategori' : 'Tambah Kategori' ?></h1>
        <p class="text-sm text-slate-500 mt-0.5"><?= $isEdit ? 'Perbarui data kategori yang ada.' : 'Buat kategori menu baru.' ?></p>
    </div>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 text-sm px-4 py-3 rounded-lg">
    <p class="font-medium mb-1 flex items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i> Terdapat kesalahan:</p>
    <ul class="list-disc list-inside space-y-0.5 text-xs">
        <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
        <li><?= esc($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="max-w-xl">
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form action="<?= $isEdit ? base_url('admin/kategori/update/' . $kategori['id']) : base_url('admin/kategori/store') ?>" method="post">
            <?= csrf_field() ?>

            <div class="space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                        Nama Kategori <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="nama_kategori" value="<?= old('nama_kategori', $kategori['nama_kategori'] ?? '') ?>"
                        class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all"
                        placeholder="Contoh: Minuman, Makanan Utama">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" rows="3"
                        class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all resize-none"
                        placeholder="Deskripsi singkat kategori (opsional)"><?= old('deskripsi', $kategori['deskripsi'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                        Status <span class="text-rose-400">*</span>
                    </label>
                    <select name="is_active" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all bg-white">
                        <option value="1" <?= old('is_active', $kategori['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= old('is_active', $kategori['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-5 border-t border-slate-100">
                <button type="submit" class="inline-flex items-center gap-2 bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Kategori' ?>
                </button>
                <a href="<?= base_url('admin/kategori') ?>" class="text-sm text-slate-500 hover:text-slate-700 font-medium px-4 py-2.5 rounded-lg hover:bg-slate-100 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>