<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-semibold text-slate-900">Kelola Kategori</h1>
        <p class="text-sm text-slate-500 mt-0.5">Atur kategori untuk pengelompokan menu.</p>
    </div>
    <a href="<?= base_url('admin/kategori/create') ?>" class="inline-flex items-center gap-2 bg-sky-500 hover:bg-sky-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <i class="fa-solid fa-plus text-xs"></i> Tambah Kategori
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3 rounded-lg">
    <i class="fa-solid fa-circle-check"></i>
    <?= session()->getFlashdata('success') ?>
</div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<div class="mb-4 flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-700 text-sm px-4 py-3 rounded-lg">
    <i class="fa-solid fa-circle-exclamation"></i>
    <?= session()->getFlashdata('error') ?>
</div>
<?php endif; ?>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50 text-left">
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-8">No</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nama Kategori</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Slug</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Deskripsi</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide text-center">Status</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (!empty($kategoris)): ?>
                <?php foreach ($kategoris as $i => $k): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-5 py-3.5 text-slate-400"><?= $i + 1 ?></td>
                    <td class="px-5 py-3.5 font-medium text-slate-800"><?= esc($k['nama_kategori']) ?></td>
                    <td class="px-5 py-3.5 text-slate-500 font-mono text-xs"><?= esc($k['slug']) ?></td>
                    <td class="px-5 py-3.5 text-slate-500 max-w-xs truncate"><?= esc($k['deskripsi']) ?: '—' ?></td>
                    <td class="px-5 py-3.5 text-center">
                        <form action="<?= base_url('admin/kategori/toggle/' . $k['id']) ?>" method="post" class="inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-md transition-colors
                                <?= $k['is_active'] ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' ?>">
                                <i class="fa-solid fa-circle text-xs <?= $k['is_active'] ? 'text-emerald-400' : 'text-slate-300' ?>"></i>
                                <?= $k['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="<?= base_url('admin/kategori/edit/' . $k['id']) ?>" class="inline-flex items-center gap-1.5 text-xs text-slate-600 hover:text-sky-600 bg-slate-100 hover:bg-sky-50 px-3 py-1.5 rounded-lg transition-colors">
                                <i class="fa-solid fa-pen text-xs"></i> Edit
                            </a>
                            <form action="<?= base_url('admin/kategori/delete/' . $k['id']) ?>" method="post" onsubmit="return confirm('Hapus kategori ini?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="inline-flex items-center gap-1.5 text-xs text-slate-600 hover:text-rose-600 bg-slate-100 hover:bg-rose-50 px-3 py-1.5 rounded-lg transition-colors">
                                    <i class="fa-solid fa-trash text-xs"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="px-5 py-14 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center">
                                <i class="fa-solid fa-tags text-slate-400 text-lg"></i>
                            </div>
                            <p class="text-sm font-medium text-slate-600">Belum ada kategori</p>
                            <p class="text-xs text-slate-400">Klik tombol tambah untuk membuat kategori pertama.</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>