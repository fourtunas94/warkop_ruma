<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h1 class="text-xl font-semibold text-slate-900">Manajemen Akun</h1>
    <p class="text-sm text-slate-500 mt-0.5">Kelola akun akses untuk Admin dan Kasir.</p>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3 rounded-lg">
    <i class="fa-solid fa-circle-check"></i> <?= session()->getFlashdata('success') ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Tambah Akun Baru</h2>
            <form action="<?= base_url('admin/users/store') ?>" method="post" class="space-y-4">
                <?= csrf_field() ?>
                
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Username</label>
                    <input type="text" name="username" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Password</label>
                    <input type="password" name="password" required minlength="6" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1.5">Role Akses</label>
                    <select name="role" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 transition-all bg-white">
                        <option value="kasir">Kasir</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <button type="submit" class="w-full bg-sky-500 hover:bg-sky-600 text-white font-medium px-4 py-2.5 rounded-lg transition-colors text-sm mt-2">
                    Simpan Akun
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            <th class="px-5 py-4 whitespace-nowrap">Pengguna</th>
                            <th class="px-5 py-4 whitespace-nowrap">Username</th>
                            <th class="px-5 py-4 whitespace-nowrap">Role</th>
                            <th class="px-5 py-4 text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada data akun.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                                            <i class="fa-solid fa-user text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium text-slate-800"><?= esc($u['nama_lengkap']) ?></span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600 whitespace-nowrap">
                                    <?= esc($u['username']) ?>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <?php if ($u['role'] == 'admin'): ?>
                                        <span class="text-[10px] font-semibold text-violet-600 bg-violet-50 border border-violet-100 px-2 py-0.5 rounded-md uppercase tracking-wider">Admin</span>
                                    <?php else: ?>
                                        <span class="text-[10px] font-semibold text-sky-600 bg-sky-50 border border-sky-100 px-2 py-0.5 rounded-md uppercase tracking-wider">Kasir</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <form action="<?= base_url('admin/users/delete/' . $u['id']) ?>" method="post" onsubmit="return confirm('Yakin ingin menghapus akun ini?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-colors flex items-center justify-center mx-auto" <?= (session()->get('id') == $u['id']) ? 'disabled' : '' ?>>
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

<?= $this->endSection() ?>