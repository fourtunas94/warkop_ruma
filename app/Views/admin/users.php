<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="space-y-6">

    <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft">
        <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-sky-500/20 blur-3xl"></div>
        <div class="absolute -bottom-28 -left-24 w-72 h-72 rounded-full bg-purple-600/20 blur-3xl"></div>

        <div class="relative p-5 lg:p-7">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-sky-500/10 border border-sky-400/20 text-sky-300 text-xs font-semibold mb-4">
                        <i class="fa-solid fa-users-gear"></i>
                        User Management
                    </div>

                    <h1 class="text-2xl lg:text-4xl font-extrabold tracking-tight text-white">
                        Manajemen Akun
                    </h1>

                    <p class="text-sm lg:text-base text-slate-400 mt-2 max-w-2xl">
                        Kelola akun akses untuk Admin dan Kasir yang dapat masuk ke sistem.
                    </p>
                </div>

                <div class="rounded-3xl bg-white/[.04] border border-white/10 p-4 min-w-full sm:min-w-[280px] lg:min-w-[320px]">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-purple-500/10 border border-purple-400/20 flex items-center justify-center text-purple-300">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>

                        <div>
                            <p class="text-sm font-bold text-white">
                                Total Akun
                            </p>
                            <p class="text-2xl font-extrabold text-white mt-1">
                                <?= number_format(count($users ?? []), 0, ',', '.') ?>
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

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <div class="xl:col-span-1">
            <div class="rounded-[1.7rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft p-5 lg:p-6 xl:sticky xl:top-24">
                <div class="mb-5">
                    <h2 class="text-lg font-extrabold text-white">
                        Tambah Akun Baru
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Buat akses baru untuk admin atau kasir.
                    </p>
                </div>

                <form action="<?= base_url('admin/users/store') ?>" method="post" class="space-y-4">
                    <?= csrf_field() ?>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                            Nama Lengkap
                        </label>
                        <input type="text"
                               name="nama_lengkap"
                               required
                               placeholder="Contoh: Kasir Utama"
                               class="w-full rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                            Username
                        </label>
                        <input type="text"
                               name="username"
                               required
                               placeholder="Contoh: kasir01"
                               class="w-full rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                            Password
                        </label>
                        <input type="password"
                               name="password"
                               required
                               minlength="6"
                               placeholder="Minimal 6 karakter"
                               class="w-full rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-[0.16em] mb-2">
                            Role Akses
                        </label>
                        <select name="role"
                                required
                                class="w-full rounded-2xl bg-slate-950/60 border border-white/10 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-400">
                            <option class="bg-slate-950 text-white" value="kasir">Kasir</option>
                            <option class="bg-slate-950 text-white" value="admin">Admin</option>
                        </select>
                    </div>

                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-500 to-purple-600 px-5 py-3 text-sm font-bold text-white shadow-glow hover:opacity-95 transition">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Simpan Akun
                    </button>
                </form>
            </div>
        </div>

        <div class="xl:col-span-2">
            <div class="rounded-[1.7rem] border border-white/10 bg-white/[.035] backdrop-blur shadow-soft overflow-hidden">
                <div class="p-5 lg:p-6 border-b border-white/10 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-extrabold text-white">
                            Daftar Akun
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Akun admin dan kasir yang terdaftar.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-white/10 bg-white/[.025] text-xs font-bold text-slate-500 uppercase tracking-[0.16em]">
                                <th class="px-5 py-4 whitespace-nowrap">Pengguna</th>
                                <th class="px-5 py-4 whitespace-nowrap">Username</th>
                                <th class="px-5 py-4 whitespace-nowrap">Role</th>
                                <th class="px-5 py-4 text-center whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/10">
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="4" class="px-5 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-16 h-16 rounded-3xl bg-white/[.04] border border-white/10 flex items-center justify-center">
                                                <i class="fa-solid fa-users text-slate-500 text-2xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-white">Belum ada data akun</p>
                                                <p class="text-xs text-slate-500 mt-1">Tambahkan akun baru dari form sebelah kiri.</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <tr class="hover:bg-white/[.025] transition">
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-sky-500 to-purple-600 p-[2px]">
                                                    <div class="w-full h-full rounded-full bg-slate-950 flex items-center justify-center text-slate-300">
                                                        <i class="fa-solid fa-user text-xs"></i>
                                                    </div>
                                                </div>

                                                <div>
                                                    <p class="text-sm font-bold text-white">
                                                        <?= esc($u['nama_lengkap'] ?? '-') ?>
                                                    </p>
                                                    <p class="text-xs text-slate-500">
                                                        ID: <?= esc($u['id'] ?? '-') ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-5 py-4 text-sm text-slate-400 whitespace-nowrap">
                                            <?= esc($u['username'] ?? '-') ?>
                                        </td>

                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <?php if (($u['role'] ?? '') === 'admin'): ?>
                                                <span class="text-[10px] font-bold text-purple-300 bg-purple-500/10 border border-purple-400/20 px-2.5 py-1 rounded-full uppercase tracking-wider">
                                                    Admin
                                                </span>
                                            <?php else: ?>
                                                <span class="text-[10px] font-bold text-sky-300 bg-sky-500/10 border border-sky-400/20 px-2.5 py-1 rounded-full uppercase tracking-wider">
                                                    Kasir
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="px-5 py-4 text-center whitespace-nowrap">
                                            <form action="<?= base_url('admin/users/delete/' . $u['id']) ?>"
                                                  method="post"
                                                  onsubmit="return confirm('Yakin ingin menghapus akun ini?');">
                                                <?= csrf_field() ?>

                                                <button type="submit"
                                                        class="w-9 h-9 rounded-xl bg-rose-500/10 border border-rose-400/20 text-rose-300 hover:bg-rose-500 hover:text-white transition inline-flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed"
                                                    <?= (session()->get('id') == ($u['id'] ?? null)) ? 'disabled' : '' ?>>
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

<?= $this->endSection() ?>