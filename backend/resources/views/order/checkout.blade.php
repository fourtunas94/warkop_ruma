@php
$storeName        = function_exists('app_setting') ? app_setting('store_name', 'WARKOP RUMA') : 'WARKOP RUMA';
$storeLogo        = function_exists('app_setting') ? app_setting('store_logo', '') : '';
$storeFavicon     = function_exists('app_setting') ? app_setting('store_favicon', '') : '';
$storeDescription = function_exists('app_setting') ? app_setting('store_description', 'Konfirmasi pesanan Anda sebelum dikirim ke kasir.') : 'Konfirmasi pesanan Anda sebelum dikirim ke kasir.';

$logoSrc = !empty($storeLogo) ? asset($storeLogo) : asset('assets/img/logo_k.jpeg');
$faviconSrc = !empty($storeFavicon) ? asset($storeFavicon) : (!empty($storeLogo) ? asset($storeLogo) : asset('assets/img/logo_k.jpeg'));

$nomorMeja = $meja['nomor_meja'] ?? ($meja->nomor_meja ?? '-');
$kodeMeja  = $meja['kode_unik'] ?? ($meja->kode_unik ?? '');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" name="viewport"/>
    <meta content="mobile_tab" name="shell-type"/>
    <title>Checkout Meja {{ $nomorMeja }} — {{ $storeName }}</title>
    <link rel="icon" type="image/png" href="{{ $faviconSrc }}">

    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary": "#0284c7",
                        "primary-container": "#0ea5e9",
                        "on-primary": "#ffffff",
                        "on-primary-container": "#ffffff",
                        "secondary": "#0369a1",
                        "secondary-container": "#e0f2fe",
                        "background": "#f1f5f9",
                        "surface": "#f1f5f9",
                        "surface-container": "#ffffff",
                        "surface-container-high": "#e0f2fe",
                        "surface-container-highest": "#e2e8f0",
                        "surface-container-low": "#f8fafc",
                        "surface-container-lowest": "#f8fafc",
                        "on-surface": "#0f172a",
                        "on-surface-variant": "#64748b",
                        "outline": "#94a3b8",
                        "outline-variant": "#cbd5e1",
                        "tertiary": "#d97706",
                        "tertiary-container": "#fef3c7"
                    },
                    fontFamily: {
                        "headline-sm": ["Epilogue", "sans-serif"],
                        "body-md": ["Plus Jakarta Sans", "sans-serif"],
                        "body-sm": ["Plus Jakarta Sans", "sans-serif"],
                        "label-sm": ["Space Grotesk", "sans-serif"],
                        "label-md": ["Space Grotesk", "sans-serif"],
                        "label-lg": ["Space Grotesk", "sans-serif"]
                    }
                }
            }
        };
    </script>

    <style>
        @layer base {
            html, body {
                width: 100vw;
                margin: 0;
                padding: 0;
                overscroll-behavior: none;
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
            .pb-safe { padding-bottom: env(safe-area-inset-bottom, 0px); }
            .pt-safe { padding-top: env(safe-area-inset-top, 0px); }
        }
        ::-webkit-scrollbar { display: none; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-surface text-on-surface flex flex-col min-h-screen selection:bg-primary-container selection:text-on-primary-container">

<!-- Sticky Header -->
<header class="fixed top-0 w-full z-50 pt-safe bg-white/95 backdrop-blur-xl border-b border-slate-200/80 shadow-[0_2px_12px_rgba(15,23,42,0.05)]">
    <div class="max-w-md mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ url('order/' . $kodeMeja) }}" aria-label="Kembali ke menu" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-on-surface hover:text-primary hover:bg-sky-50 active:scale-95 transition-all border border-slate-200">
                <span class="material-symbols-outlined text-[20px]">chevron_left</span>
            </a>
            <div class="flex flex-col">
                <h1 class="font-['Epilogue'] text-base font-bold text-on-surface leading-tight">Detail Pesanan</h1>
                <span class="font-['Space_Grotesk'] text-[11px] text-on-surface-variant uppercase tracking-wider">QR Order {{ $storeName }}</span>
            </div>
        </div>

        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-sky-50 border border-sky-200 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
            <span class="font-['Space_Grotesk'] text-xs font-bold tracking-wider text-primary uppercase">Meja {{ $nomorMeja }}</span>
        </div>
    </div>
</header>

<main class="flex flex-col relative w-full pt-20 pb-32 max-w-md mx-auto px-4 space-y-4">

    <!-- Empty State -->
    <div id="empty-state" class="hidden text-center py-16 px-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm my-6">
        <div class="w-16 h-16 rounded-full bg-sky-50 text-primary mx-auto flex items-center justify-center mb-3 border border-sky-100">
            <span class="material-symbols-outlined text-[32px]">shopping_cart</span>
        </div>
        <h3 class="font-['Epilogue'] text-lg font-bold text-on-surface">Keranjang Masih Kosong</h3>
        <p class="text-xs text-on-surface-variant mt-1 mb-6">Pilih kopi dan menu lezat terlebih dahulu sebelum checkout.</p>
        <a href="{{ url('order/' . $kodeMeja) }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-primary text-on-primary font-['Space_Grotesk'] text-xs font-bold shadow-md hover:bg-secondary transition-colors">
            <span class="material-symbols-outlined text-[18px]">restaurant_menu</span>
            <span>Buka Katalog Menu</span>
        </a>
    </div>

    <!-- Main Checkout Content -->
    <div id="checkout-content" class="space-y-4">
        <!-- Customer & Table Context Card -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm space-y-3.5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[18px]">table_restaurant</span>
                    <span class="font-['Space_Grotesk'] text-xs text-on-surface uppercase tracking-wider font-bold">Pesanan</span>
                </div>
                <div class="flex items-center gap-1 px-2.5 py-1 rounded-full bg-sky-50 border border-sky-200 text-primary text-xs">
                    <span class="material-symbols-outlined text-[14px]">restaurant</span>
                    <span class="font-['Space_Grotesk'] font-semibold">Dine-in / Meja</span>
                </div>
            </div>

            <div class="space-y-3">
                <!-- Saved Customer Name Card (muncul otomatis jika pelanggan pesan lagi) -->
                <div id="saved-name-box" class="hidden rounded-xl bg-sky-50/70 border border-sky-200/80 px-3.5 py-2.5 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[18px]">person</span>
                        </div>
                        <div class="min-w-0">
                            <span class="font-['Space_Grotesk'] text-[10px] text-on-surface-variant uppercase tracking-wider font-semibold block">Memesan Atas Nama</span>
                            <span id="saved-name-display" class="text-sm font-bold text-on-surface truncate block"></span>
                        </div>
                    </div>
                    <button type="button" onclick="editSavedName()" class="px-2.5 py-1 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-primary font-['Space_Grotesk'] text-[11px] font-bold shrink-0 transition-all">
                        Ganti
                    </button>
                </div>

                <!-- Input Nama Pemesan (muncul pada pesanan pertama atau saat klik Ganti) -->
                <div id="name-input-box" class="space-y-1">
                    <label class="font-['Space_Grotesk'] text-[11px] text-on-surface-variant uppercase tracking-wider font-semibold block" for="nama_pemesan">
                        Nama Pemesan <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3.5 text-outline text-[18px]">person_outline</span>
                        <input id="nama_pemesan" type="text" class="w-full bg-slate-50 text-on-surface text-sm rounded-xl pl-10 pr-3.5 py-2.5 border border-slate-200 focus:border-primary focus:bg-white focus:ring-2 focus:ring-sky-500/15 transition-all outline-none placeholder:text-outline" placeholder="Masukkan nama Anda (misal: Budi)"/>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="font-['Space_Grotesk'] text-[11px] text-on-surface-variant uppercase tracking-wider font-semibold block" for="catatan">
                        Catatan Meja (Barista & Kasir)
                    </label>
                    <div class="relative flex items-start">
                        <span class="material-symbols-outlined absolute left-3.5 top-3 text-outline text-[18px]">edit_note</span>
                        <textarea id="catatan" rows="2" class="w-full bg-slate-50 text-on-surface text-xs rounded-xl pl-10 pr-3.5 py-2.5 border border-slate-200 focus:border-primary focus:bg-white focus:ring-2 focus:ring-sky-500/15 transition-all outline-none resize-none placeholder:text-outline" placeholder="Contoh: Diantar sekaligus, minta sedotan & tisu tambahan..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items Section -->
        <div class="space-y-2.5">
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-2">
                    <h2 class="font-['Epilogue'] text-base text-on-surface font-bold">Pesanan Anda</h2>
                    <span id="items-badge-count" class="px-2 py-0.5 rounded-full bg-sky-100 text-primary font-['Space_Grotesk'] text-[11px] font-bold">0 Menu</span>
                </div>
                <span class="font-['Space_Grotesk'] text-[11px] text-on-surface-variant font-medium">Meja {{ $nomorMeja }}</span>
            </div>

            <!-- Dynamic Items Container -->
            <div id="order-list" class="space-y-2.5"></div>

            <!-- Ghost Add More Button -->
            <a href="{{ url('order/' . $kodeMeja) }}" class="w-full py-3 rounded-xl bg-white hover:bg-sky-50/70 border border-dashed border-slate-300 hover:border-primary text-on-surface-variant hover:text-primary flex items-center justify-center gap-2 font-['Space_Grotesk'] text-xs font-semibold transition-all active:scale-[0.99] shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add_circle_outline</span>
                <span>Tambah Menu Kopi / Hidangan Lain</span>
            </a>
        </div>

        <!-- Free Order Banner (khusus pesanan gratis / kategori Games) -->
        <div id="free-order-banner" class="hidden rounded-2xl p-4 bg-emerald-50 border border-emerald-200 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[22px]">sports_esports</span>
            </div>
            <div>
                <span class="font-['Epilogue'] text-sm font-bold text-emerald-800 block">Gratis — Tidak Perlu Pembayaran</span>
                <p class="text-xs text-emerald-700">Pesanan fasilitas/games ini gratis dan langsung diproses ke meja Anda.</p>
            </div>
        </div>

        <!-- Payment Methods Selection -->
        <div id="payment-methods-section" class="space-y-2.5">
            <div class="flex items-center justify-between px-1">
                <h2 class="font-['Epilogue'] text-base text-on-surface font-bold">Metode Pembayaran</h2>
                <div class="flex items-center gap-1 text-primary">
                    <span class="material-symbols-outlined text-[15px]" style="font-variation-settings: 'FILL' 1;">verified_user</span>
                    <span class="font-['Space_Grotesk'] text-[10px] tracking-wider uppercase font-bold">Aman & Cepat</span>
                </div>
            </div>

            <!-- Option 1: Bayar di Kasir (Tunai) -->
            <label class="payment-option relative block cursor-pointer">
                <input type="radio" name="payment_method" value="tunai" class="peer sr-only" checked/>
                <div class="p-3.5 rounded-2xl bg-white border border-slate-200 peer-checked:border-primary peer-checked:bg-sky-50/60 peer-checked:shadow-[0_4px_16px_rgba(2,132,199,0.12)] transition-all">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-sky-100 border border-sky-200 flex items-center justify-center text-primary shrink-0 mt-0.5">
                                <span class="material-symbols-outlined text-[22px]">point_of_sale</span>
                            </div>
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-['Epilogue'] text-sm font-bold text-on-surface">Bayar di Kasir (Tunai)</span>
                                    <span class="px-2 py-0.5 rounded-full bg-primary text-white font-['Space_Grotesk'] text-[9px] font-bold uppercase tracking-wider">Bayar Nanti</span>
                                </div>
                                <p class="text-xs text-on-surface-variant">Pesanan langsung diproses — bayar tunai di kasir</p>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-slate-300 peer-checked:border-primary flex items-center justify-center mt-1 shrink-0">
                            <div class="w-2.5 h-2.5 rounded-full bg-transparent peer-checked:bg-primary transition-all"></div>
                        </div>
                    </div>
                </div>
            </label>

            <!-- Option 2: QRIS Instant -->
            <label class="payment-option relative block cursor-pointer">
                <input type="radio" name="payment_method" value="qris" class="peer sr-only"/>
                <div class="p-3.5 rounded-2xl bg-white border border-slate-200 peer-checked:border-primary peer-checked:bg-sky-50/60 peer-checked:shadow-[0_4px_16px_rgba(2,132,199,0.12)] transition-all">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-primary shrink-0 mt-0.5">
                                <span class="material-symbols-outlined text-[22px]">qr_code_scanner</span>
                            </div>
                            <div class="space-y-0.5">
                                <span class="font-['Epilogue'] text-sm font-bold text-on-surface">QRIS Instant</span>
                                <p class="text-xs text-on-surface-variant">BCA, GoPay, OVO, Dana, ShopeePay & M-Banking</p>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-slate-300 peer-checked:border-primary flex items-center justify-center mt-1 shrink-0">
                            <div class="w-2.5 h-2.5 rounded-full bg-transparent peer-checked:bg-primary transition-all"></div>
                        </div>
                    </div>
                </div>
            </label>
        </div>

        <!-- Cost Breakdown Card -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm space-y-3">
            <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="material-symbols-outlined text-primary text-[18px]">receipt</span>
                <h3 class="font-['Space_Grotesk'] text-xs text-on-surface uppercase tracking-wider font-bold">Rincian Pembayaran</h3>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between items-center text-on-surface-variant">
                    <span>Subtotal Pesanan</span>
                    <span class="font-['Space_Grotesk'] font-bold text-on-surface" id="subtotal-val">Rp 0</span>
                </div>
            </div>

            <div class="h-px bg-slate-100 my-2"></div>

            <div class="flex justify-between items-baseline pt-1">
                <div class="flex flex-col">
                    <span class="font-['Space_Grotesk'] text-[11px] text-on-surface-variant uppercase tracking-wider font-bold">Total Pembayaran</span>
                    <span class="text-[10px] text-outline">Tanpa biaya pajak tambahan</span>
                </div>
                <span class="font-['Epilogue'] text-xl text-primary font-bold tracking-tight" id="order-total">Rp 0</span>
            </div>
        </div>

        <!-- Reassurance Banner -->
        <div class="bg-sky-50/80 rounded-xl p-3 border border-sky-100 flex items-center gap-2.5">
            <span class="material-symbols-outlined text-primary text-[20px] shrink-0">local_cafe</span>
            <p class="text-xs text-slate-600 leading-snug">
                Barista kami akan segera meracik pesanan Anda setelah konfirmasi diterima.
            </p>
        </div>
    </div>
</main>

<!-- Floating Sticky Action Bar -->
<div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200/80 shadow-[0_-4px_16px_rgba(15,23,42,0.05)] pb-safe">
    <div class="max-w-md mx-auto p-4 space-y-2">
        <button id="btn-pesan" type="button" onclick="submitOrder()" class="w-full py-3.5 px-5 rounded-full bg-gradient-to-r from-primary to-primary-container text-white font-['Space_Grotesk'] text-sm font-bold flex items-center justify-between shadow-[0_6px_18px_rgba(2,132,199,0.28)] active:scale-[0.98] transition-all">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px]">restaurant_menu</span>
                <span>Pesan Sekarang</span>
            </div>
            <div class="flex items-center gap-1 font-bold">
                <span id="btn-total-text">Rp 0</span>
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </div>
        </button>

        <div class="flex items-center justify-center gap-1 text-center">
            <span class="material-symbols-outlined text-[13px] text-primary">security</span>
            <span class="font-['Space_Grotesk'] text-[10px] text-on-surface-variant">Transaksi aman & langsung terhubung ke Barista Meja {{ $nomorMeja }}</span>
        </div>
    </div>
</div>

<!-- Toast Modal -->
<div class="fixed top-20 left-1/2 -translate-x-1/2 z-50 pointer-events-none opacity-0 transition-opacity duration-300" id="toastNotification">
    <div class="px-4 py-2.5 rounded-full bg-rose-600 backdrop-blur-md border border-rose-500/30 text-white shadow-xl flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">error</span>
        <span class="font-['Space_Grotesk'] text-xs font-bold" id="toastMsg">Error message</span>
    </div>
</div>

<script>
    const KODE_MEJA  = {!! json_encode($kodeMeja) !!};
    const SUBMIT_URL = '/order/submit/' + encodeURIComponent(KODE_MEJA);
    const MENU_URL   = '/order/' + encodeURIComponent(KODE_MEJA);
    const NAME_STORAGE_KEY = 'WarkopRuma_customer_name_' + KODE_MEJA;
    const NAME_TTL_MS = 6 * 60 * 60 * 1000; // berlaku 6 jam di meja yang sama

    function loadSavedCustomerName() {
        try {
            const raw = localStorage.getItem(NAME_STORAGE_KEY);
            if (!raw) return;
            const parsed = JSON.parse(raw);
            if (!parsed || !parsed.name || !parsed.savedAt) return;
            if (Date.now() - parsed.savedAt > NAME_TTL_MS) {
                localStorage.removeItem(NAME_STORAGE_KEY);
                return;
            }
            const nameInput = document.getElementById('nama_pemesan');
            const savedBox = document.getElementById('saved-name-box');
            const inputBox = document.getElementById('name-input-box');
            const savedDisplay = document.getElementById('saved-name-display');
            if (nameInput && savedBox && inputBox && savedDisplay) {
                nameInput.value = parsed.name;
                savedDisplay.textContent = parsed.name;
                inputBox.classList.add('hidden');
                savedBox.classList.remove('hidden');
            }
        } catch(e) {}
    }

    function saveCustomerName(nama) {
        try {
            localStorage.setItem(NAME_STORAGE_KEY, JSON.stringify({
                name: nama,
                savedAt: Date.now()
            }));
        } catch(e) {}
    }

    function editSavedName() {
        const savedBox = document.getElementById('saved-name-box');
        const inputBox = document.getElementById('name-input-box');
        const nameInput = document.getElementById('nama_pemesan');
        if (savedBox) savedBox.classList.add('hidden');
        if (inputBox) inputBox.classList.remove('hidden');
        if (nameInput) {
            nameInput.focus();
            nameInput.select();
        }
    }

    let cart = [];
    try {
        const saved = localStorage.getItem('WarkopRuma_cart_' + KODE_MEJA);
        cart = saved ? JSON.parse(saved) : [];
    } catch(e) {
        cart = [];
    }

    function showError(msg) {
        const toast = document.getElementById('toastNotification');
        const toastMsg = document.getElementById('toastMsg');
        if (!toast || !toastMsg) return;
        toastMsg.textContent = msg;
        toast.classList.remove('opacity-0');
        toast.classList.add('opacity-100');
        clearTimeout(window.__toastTimer);
        window.__toastTimer = setTimeout(() => {
            toast.classList.remove('opacity-100');
            toast.classList.add('opacity-0');
        }, 2500);
    }

    function formatRupiah(num) {
        return Number(num).toLocaleString('id-ID');
    }

    function updateQty(id, delta) {
        const idx = cart.findIndex(i => i.id === id);
        if (idx > -1) {
            cart[idx].qty += delta;
            if (cart[idx].qty <= 0) {
                cart.splice(idx, 1);
            }
            try {
                localStorage.setItem('WarkopRuma_cart_' + KODE_MEJA, JSON.stringify(cart));
            } catch(e) {}
            renderOrder();
        }
    }

    function renderOrder() {
        const list = document.getElementById('order-list');
        const emptyState = document.getElementById('empty-state');
        const checkoutContent = document.getElementById('checkout-content');
        const btnPesan = document.getElementById('btn-pesan');

        if (cart.length === 0) {
            emptyState.classList.remove('hidden');
            checkoutContent.classList.add('hidden');
            btnPesan.disabled = true;
            return;
        }

        emptyState.classList.add('hidden');
        checkoutContent.classList.remove('hidden');
        btnPesan.disabled = false;

        let subtotal = 0;
        let totalItems = 0;

        list.innerHTML = cart.map(item => {
            const nama = item.nama_menu || '-';
            const qty  = Number(item.qty || 1);
            const sub  = Number(item.harga || 0) * qty;
            const gambar = item.gambar || 'https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?auto=format&fit=crop&w=400&q=80';

            subtotal += sub;
            totalItems += qty;

            return `
                <div class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-sm flex items-center gap-3">
                    <img src="${gambar}" alt="${nama}" class="w-16 h-16 rounded-xl object-cover bg-slate-100 shrink-0 border border-slate-200/60" onerror="this.src='https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?auto=format&fit=crop&w=400&q=80'"/>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-1">
                            <h3 class="font-['Epilogue'] text-sm font-bold text-on-surface truncate">${nama}</h3>
                            <span class="font-['Space_Grotesk'] text-xs font-bold ${sub <= 0 ? 'text-emerald-600' : 'text-primary'} shrink-0">${sub <= 0 ? 'Gratis' : 'Rp ' + formatRupiah(sub)}</span>
                        </div>
                        <span class="text-[11px] text-on-surface-variant block mt-0.5">${Number(item.harga || 0) <= 0 ? 'Gratis (Tanpa Biaya)' : '@ Rp ' + formatRupiah(item.harga || 0)}</span>

                        <div class="flex items-center justify-between mt-2">
                            <span class="text-[10px] text-outline font-['Space_Grotesk'] uppercase font-semibold">Porsi:</span>
                            <div class="flex items-center bg-slate-100 rounded-full p-0.5 border border-slate-200">
                                <button type="button" class="w-6 h-6 rounded-full flex items-center justify-center text-on-surface hover:text-primary active:bg-slate-200" onclick="updateQty(${item.id}, -1)">
                                    <span class="material-symbols-outlined text-[13px]">remove</span>
                                </button>
                                <span class="w-6 text-center font-['Space_Grotesk'] text-xs text-primary font-bold">${qty}</span>
                                <button type="button" class="w-6 h-6 rounded-full bg-sky-100 text-primary flex items-center justify-center active:bg-primary active:text-white" onclick="updateQty(${item.id}, 1)">
                                    <span class="material-symbols-outlined text-[13px]">add</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        document.getElementById('items-badge-count').textContent = totalItems + ' Menu';

        const finalTotal = Math.max(0, subtotal);
        const isFreeOrder = finalTotal === 0;

        document.getElementById('subtotal-val').textContent = isFreeOrder ? 'Gratis' : ('Rp ' + formatRupiah(subtotal));
        document.getElementById('order-total').textContent = isFreeOrder ? 'Gratis' : ('Rp ' + formatRupiah(finalTotal));
        const btnTotalEl = document.getElementById('btn-total-text');
        if (btnTotalEl) btnTotalEl.textContent = isFreeOrder ? 'Gratis' : ('Rp ' + formatRupiah(finalTotal));

        const paySection = document.getElementById('payment-methods-section');
        const freeBanner = document.getElementById('free-order-banner');
        if (paySection) paySection.classList.toggle('hidden', isFreeOrder);
        if (freeBanner) freeBanner.classList.toggle('hidden', !isFreeOrder);
    }

    async function submitOrder() {
        const nama = document.getElementById('nama_pemesan').value.trim();
        const paymentRadio = document.querySelector('input[name="payment_method"]:checked');
        const paymentMethod = paymentRadio ? paymentRadio.value : 'tunai';
        const catatan = document.getElementById('catatan').value.trim();

        if (!nama) {
            showError('Nama pemesan wajib diisi.');
            document.getElementById('nama_pemesan').focus();
            return;
        }

        if (cart.length === 0) {
            showError('Keranjang masih kosong.');
            return;
        }

        const btn = document.getElementById('btn-pesan');
        const originalBtnHtml = btn.innerHTML;
        const restoreBtn = () => {
            btn.innerHTML = originalBtnHtml;
            btn.disabled = false;
            renderOrder();
        };
        btn.disabled = true;
        btn.innerHTML = `<span class="material-symbols-outlined text-[18px] animate-spin">refresh</span><span>Memproses Pesanan...</span>`;

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('nama_pemesan', nama);
        formData.append('catatan', catatan);
        formData.append('payment_method', paymentMethod);
        formData.append('items', JSON.stringify(cart));

        try {
            const res = await fetch(SUBMIT_URL, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const text = await res.text();
            let data;

            try {
                data = JSON.parse(text);
            } catch(e) {
                showError('Respons tidak valid dari server (HTTP ' + res.status + ').');
                restoreBtn();
                return;
            }

            if (data.success) {
                saveCustomerName(nama);
                localStorage.removeItem('WarkopRuma_cart_' + KODE_MEJA);
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.href = MENU_URL;
                }
                return;
            }

            showError(data.message || 'Gagal mengirim pesanan.');
            restoreBtn();
        } catch(err) {
            showError('Gagal terhubung ke server.');
            restoreBtn();
        }
    }

    loadSavedCustomerName();
    renderOrder();
</script>
</body>
</html>