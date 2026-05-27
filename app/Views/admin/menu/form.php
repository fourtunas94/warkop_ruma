<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<?php $isEdit = isset($menu); ?>

<div class="mb-6 flex items-center gap-3">
    <a href="<?= base_url('admin/menu') ?>" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-colors">
        <i class="fa-solid fa-arrow-left text-xs"></i>
    </a>
    <div>
        <h1 class="text-xl font-semibold text-slate-900"><?= $isEdit ? 'Edit Menu' : 'Tambah Menu' ?></h1>
        <p class="text-sm text-slate-500 mt-0.5"><?= $isEdit ? 'Perbarui data menu yang ada.' : 'Tambahkan item menu baru.' ?></p>
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

<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <form action="<?= $isEdit ? base_url('admin/menu/update/' . $menu['id']) : base_url('admin/menu/store') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                            Nama Menu <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" name="nama_menu" value="<?= old('nama_menu', $menu['nama_menu'] ?? '') ?>"
                            class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all"
                            placeholder="Contoh: Es Teh Manis, Nasi Goreng">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                            Harga <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                            <input type="number" name="harga" value="<?= old('harga', $menu['harga'] ?? '') ?>" min="0"
                                class="w-full border border-slate-200 rounded-lg pl-9 pr-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all"
                                placeholder="15000">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">Kategori</label>
                        <select name="kategori_id" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all bg-white">
                            <option value="">— Tanpa Kategori —</option>
                            <?php foreach ($kategoris as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= old('kategori_id', $menu['kategori_id'] ?? '') == $k['id'] ? 'selected' : '' ?>>
                                <?= esc($k['nama_kategori']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">Deskripsi</label>
                        <textarea name="deskripsi" rows="3"
                            class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all resize-none"
                            placeholder="Deskripsi singkat menu (opsional)"><?= old('deskripsi', $menu['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                            Stok <span class="text-rose-400">*</span>
                        </label>
                        <select name="stok" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all bg-white">
                            <option value="1" <?= old('stok', $menu['stok'] ?? 1) == 1 ? 'selected' : '' ?>>Tersedia</option>
                            <option value="0" <?= old('stok', $menu['stok'] ?? 1) == 0 ? 'selected' : '' ?>>Habis</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">
                            Status <span class="text-rose-400">*</span>
                        </label>
                        <select name="is_active" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all bg-white">
                            <option value="1" <?= old('is_active', $menu['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= old('is_active', $menu['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5">Gambar Menu</label>
                        <?php if ($isEdit && $menu['gambar']): ?>
                        <div class="mb-2 flex items-center gap-3">
                            <img src="<?= base_url('uploads/menu/' . esc($menu['gambar'])) ?>" alt="Gambar saat ini"
                                class="w-16 h-16 rounded-lg object-cover border border-slate-200">
                            <p class="text-xs text-slate-500">Gambar saat ini. Upload baru untuk mengganti.</p>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="gambar" accept="image/jpg,image/jpeg,image/png,image/webp"
                            class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-sky-50 file:text-sky-600 hover:file:bg-sky-100">
                        <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG, WebP. Maks 2MB.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-5 border-t border-slate-100">
                <button type="submit" class="inline-flex items-center gap-2 bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Menu' ?>
                </button>
                <a href="<?= base_url('admin/menu') ?>" class="text-sm text-slate-500 hover:text-slate-700 font-medium px-4 py-2.5 rounded-lg hover:bg-slate-100 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>