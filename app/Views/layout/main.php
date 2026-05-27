<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ARZANA' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: {
                        navy: { 900: '#0f172a', 800: '#1e293b', 700: '#253347', 600: '#334155' }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .nav-link { transition: all .2s ease; }
        .nav-link:hover, .nav-link.active {
            background-color: rgba(255,255,255,.08);
            border-left: 3px solid #38bdf8;
            padding-left: calc(1.25rem - 3px);
        }
        .sidebar-scrollbar::-webkit-scrollbar { width: 4px; }
        .sidebar-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 2px; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased">

<aside class="fixed top-0 left-0 h-screen w-64 bg-navy-900 flex flex-col z-30 sidebar-scrollbar overflow-y-auto">
    <div class="px-5 py-5 border-b border-white/10 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-sky-500 flex items-center justify-center flex-shrink-0">
            <i class="fa-solid fa-cash-register text-white text-sm"></i>
        </div>
        <div>
            <p class="text-white font-semibold text-sm leading-tight">ARZANA</p>
            <p class="text-slate-400 text-xs"><?= ucfirst(session()->get('role')) ?> Panel</p>
        </div>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-0.5">
        <?php if (session()->get('role') == 'admin'): ?>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest px-3 mb-2 mt-1">Utama</p>
            <a href="<?= base_url('admin/dashboard') ?>" class="nav-link flex items-center gap-3 px-5 py-2.5 rounded-lg text-slate-300 text-sm border-l-3 border-transparent">
                <i class="fa-solid fa-gauge-high w-4 text-center"></i>
                <span>Dashboard</span>
            </a>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest px-3 mb-2 mt-4">Manajemen</p>
            <a href="<?= base_url('admin/kategori') ?>" class="nav-link flex items-center gap-3 px-5 py-2.5 rounded-lg text-slate-300 text-sm border-l-3 border-transparent">
                <i class="fa-solid fa-tags w-4 text-center"></i>
                <span>Kelola Kategori</span>
            </a>
            <a href="<?= base_url('admin/menu') ?>" class="nav-link flex items-center gap-3 px-5 py-2.5 rounded-lg text-slate-300 text-sm border-l-3 border-transparent">
                <i class="fa-solid fa-utensils w-4 text-center"></i>
                <span>Kelola Menu</span>
            </a>
            <a href="<?= base_url('admin/meja') ?>" class="nav-link flex items-center gap-3 px-5 py-2.5 rounded-lg text-slate-300 text-sm border-l-3 border-transparent">
                <i class="fa-solid fa-table-cells w-4 text-center"></i>
                <span>Meja & QR Code</span>
            </a>
            <a href="<?= base_url('admin/users') ?>" class="nav-link flex items-center gap-3 px-5 py-2.5 rounded-lg text-slate-300 text-sm border-l-3 border-transparent">
                <i class="fa-solid fa-users w-4 text-center"></i>
                <span>Manajemen Kasir</span>
            </a>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest px-3 mb-2 mt-4">Transaksi</p>
            <a href="<?= base_url('admin/riwayat') ?>" class="nav-link flex items-center gap-3 px-5 py-2.5 rounded-lg text-slate-300 text-sm border-l-3 border-transparent">
                <i class="fa-solid fa-clock-rotate-left w-4 text-center"></i>
                <span>Riwayat Transaksi</span>
            </a>
        <?php else: ?>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest px-3 mb-2 mt-1">Kasir</p>
            <a href="<?= base_url('kasir/dashboard') ?>" class="nav-link flex items-center gap-3 px-5 py-2.5 rounded-lg text-slate-300 text-sm border-l-3 border-transparent">
                <i class="fa-solid fa-bell-concierge w-4 text-center"></i>
                <span>Antrean Pesanan</span>
            </a>
            <a href="<?= base_url('kasir/riwayat') ?>" class="nav-link flex items-center gap-3 px-5 py-2.5 rounded-lg text-slate-300 text-sm border-l-3 border-transparent">
                <i class="fa-solid fa-clock-rotate-left w-4 text-center"></i>
                <span>Riwayat Transaksi</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="px-3 py-4 border-t border-white/10">
        <a href="<?= base_url('logout') ?>" class="flex items-center gap-3 px-5 py-2.5 rounded-lg text-rose-400 hover:bg-rose-500/10 text-sm transition-all duration-200">
            <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
            <span>Keluar</span>
        </a>
    </div>
</aside>

<div class="ml-64 min-h-screen flex flex-col">
    <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-slate-200 px-6 py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-sky-100 flex items-center justify-center">
                <i class="fa-solid fa-user text-sky-600 text-xs"></i>
            </div>
            <span class="text-sm font-medium text-slate-700">
                Selamat datang, <span class="text-slate-900"><?= esc(session()->get('nama_lengkap')) ?></span>
            </span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full capitalize">
                <?= esc(session()->get('role')) ?>
            </span>
        </div>
    </header>

    <main class="flex-1 p-6">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="px-6 py-3 border-t border-slate-200 bg-white">
        <p class="text-xs text-slate-400">&copy; <?= date('Y') ?> ARZANA. All rights reserved.</p>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>