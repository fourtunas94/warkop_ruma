<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="space-y-6">

    <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft">
        <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-amber-500/20 blur-3xl"></div>
        <div class="absolute -bottom-28 -left-24 w-72 h-72 rounded-full bg-purple-600/20 blur-3xl"></div>

        <div class="relative p-5 lg:p-7">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-500/10 border border-amber-400/20 text-amber-300 text-xs font-semibold mb-4">
                        <i class="fa-solid fa-layer-group"></i>
                        Kategori Menu
                    </div>

                    <h1 class="text-2xl lg:text-4xl font-extrabold tracking-tight text-white">
                        Kelola Kategori
                    </h1>

                    <p class="text-sm lg:text-base text-slate-400 mt-2 max-w-2xl">
                        Atur kategori untuk pengelompokan menu makanan, minuman, dan produk lainnya.
                    </p>
                </div>

                <a href="<?= base_url('admin/kategori/create') ?>"
                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-500 to-purple-600 px-5 py-3 text-sm font-bold text-white shadow-glow hover:opacity-95 transition">
                    <i class="fa-solid fa-plus"></i>
                    Tambah Kategori
                </a>
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

    <div class="rounded-[1.7rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft overflow-hidden">
        <div class="p-5 lg:p-6 border-b border-white/10 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-white">
                    Data Kategori
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Daftar kategori yang tersedia di sistem.
                </p>
            </div>

            <div class="hidden sm:flex items-center gap-2 rounded-2xl bg-white/[.04] border border-white/10 px-4 py-2">
                <i class="fa-solid fa-database text-slate-500 text-xs"></i>
                <span class="text-xs font-semibold text-slate-400">
                    <?= number_format(count($kategoris ?? []), 0, ',', '.') ?> Data
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/10 bg-white/[.025] text-xs font-bold text-slate-500 uppercase tracking-[0.16em]">
                        <th class="px-5 py-4 whitespace-nowrap w-8">No</th>
                        <th class="px-5 py-4 whitespace-nowrap">Nama Kategori</th>
                        <th class="px-5 py-4 whitespace-nowrap">Slug</th>
                        <th class="px-5 py-4 whitespace-nowrap">Deskripsi</th>
                        <th class="px-5 py-4 text-center whitespace-nowrap">Status</th>
                        <th class="px-5 py-4 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-white/10">
                    <?php if (! empty($kategoris)): ?>
                        <?php foreach ($kategoris as $i => $k): ?>
                            <tr class="hover:bg-white/[.025] transition">
                                <td class="px-5 py-4 text-slate-500 whitespace-nowrap">
                                    <?= $i + 1 ?>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-amber-500/10 border border-amber-400/20 flex items-center justify-center text-amber-300">
                                            <i class="fa-solid fa-layer-group text-sm"></i>
                                        </div>

                                        <div>
                                            <p class="text-sm font-bold text-white">
                                                <?= esc($k['nama_kategori'] ?? '-') ?>
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                Kategori menu
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="text-xs font-mono bg-white/[.04] text-slate-400 border border-white/10 px-2.5 py-1 rounded-full">
                                        <?= esc($k['slug'] ?? '-') ?>
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-400 max-w-xs truncate">
                                    <?= esc($k['deskripsi'] ?? '') ?: '—' ?>
                                </td>

                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <form action="<?= base_url('admin/kategori/toggle/' . $k['id']) ?>" method="post" class="inline">
                                        <?= csrf_field() ?>

                                        <button type="submit"
                                                class="inline-flex items-center gap-2 text-[10px] font-bold px-3 py-1.5 rounded-full border uppercase tracking-wider transition
                                                <?= ! empty($k['is_active'])
                                                    ? 'bg-emerald-500/10 text-emerald-300 border-emerald-400/20 hover:bg-emerald-500/15'
                                                    : 'bg-slate-500/10 text-slate-400 border-slate-400/20 hover:bg-slate-500/15' ?>">
                                            <i class="fa-solid fa-circle text-[7px] <?= ! empty($k['is_active']) ? 'text-emerald-400' : 'text-slate-500' ?>"></i>
                                            <?= ! empty($k['is_active']) ? 'Aktif' : 'Nonaktif' ?>
                                        </button>
                                    </form>
                                </td>

                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?= base_url('admin/kategori/edit/' . $k['id']) ?>"
                                           class="inline-flex items-center gap-2 text-xs font-bold text-sky-300 hover:text-white bg-sky-500/10 hover:bg-sky-500/20 border border-sky-400/20 px-3 py-2 rounded-xl transition">
                                            <i class="fa-solid fa-pen"></i>
                                            Edit
                                        </a>

                                        <form action="<?= base_url('admin/kategori/delete/' . $k['id']) ?>"
                                              method="post"
                                              onsubmit="return confirm('Hapus kategori ini?')">
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
                                        <i class="fa-solid fa-tags text-slate-500 text-2xl"></i>
                                    </div>

                                    <div>
                                        <p class="text-sm font-bold text-white">Belum ada kategori</p>
                                        <p class="text-xs text-slate-500 mt-1">
                                            Klik tombol tambah untuk membuat kategori pertama.
                                        </p>
                                    </div>

                                    <a href="<?= base_url('admin/kategori/create') ?>"
                                       class="mt-2 inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-sky-500 to-purple-600 px-5 py-3 text-sm font-bold text-white shadow-glow hover:opacity-95 transition">
                                        <i class="fa-solid fa-plus"></i>
                                        Tambah Kategori
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?= $this->endSection() ?>