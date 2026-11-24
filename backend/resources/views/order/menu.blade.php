@php
$storeName        = function_exists('app_setting') ? app_setting('store_name', 'WARKOP RUMA') : 'WARKOP RUMA';
$storeLogo        = function_exists('app_setting') ? app_setting('store_logo', '') : '';
$storeFavicon     = function_exists('app_setting') ? app_setting('store_favicon', '') : '';
$storeDescription = function_exists('app_setting') ? app_setting('store_description', 'Pesan kopi dan menu pilihan lebih mudah langsung dari meja Anda.') : 'Pesan kopi dan menu pilihan lebih mudah langsung dari meja Anda.';
$storeWhatsapp    = function_exists('app_setting') ? app_setting('store_whatsapp', '') : '';

$logoSrc = !empty($storeLogo) ? asset($storeLogo) : asset('assets/img/logo_k.jpeg');
$faviconSrc = !empty($storeFavicon) ? asset($storeFavicon) : (!empty($storeLogo) ? asset($storeLogo) : asset('assets/img/logo_k.jpeg'));

$noMeja = $nomorMeja ?? (is_array($meja) ? ($meja['nomor_meja'] ?? '-') : ($meja->nomor_meja ?? '-'));
$kdMeja = $kodeMeja ?? (is_array($meja) ? ($meja['kode_unik'] ?? '') : ($meja->kode_unik ?? ''));

$whatsappUrl = '';
if (!empty($storeWhatsapp)) {
    $waNumber = preg_replace('/[^0-9]/', '', $storeWhatsapp);
    $waText   = 'Halo, saya pelanggan Meja ' . $noMeja . ' ingin bertanya tentang menu.';
    $whatsappUrl = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($waText);
}

$kategoriList = is_iterable($menuByKategori ?? null) ? $menuByKategori : [];

$formatRupiah = static function ($value): string {
    return 'Rp ' . number_format((int) $value, 0, ',', '.');
};

// Hitung total menu
$totalItemCount = 0;
foreach ($kategoriList as $kat) {
    $totalItemCount += count($kat['items'] ?? []);
}
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" name="viewport"/>
    <meta content="mobile_tab" name="shell-type"/>
    <title>Katalog Menu — {{ $storeName }} (Meja {{ $noMeja }})</title>
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
                        "headline-md": ["Epilogue", "sans-serif"],
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
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-surface text-on-surface flex flex-col min-h-screen selection:bg-primary-container selection:text-on-primary-container">

<!-- Sticky Header -->
<header class="fixed top-0 w-full z-50 pt-safe bg-white/95 backdrop-blur-xl border-b border-slate-200/80 shadow-[0_2px_12px_rgba(15,23,42,0.05)]">
    <div class="max-w-md mx-auto px-4 py-3 flex flex-col gap-2.5">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2.5">
                <img alt="{{ $storeName }} Logo" class="h-9 w-9 rounded-xl object-cover bg-slate-100 border border-slate-200" src="{{ $logoSrc }}" onerror="this.src='https://lh3.googleusercontent.com/aida/AEtjO1UIQ6pajQAMTJJNGDEMNmjp_h2eKG25ypH8FAgQMLF_lMfdsiOwoan3bs-d0-OczzZ4Onu3M1pCHVIdNNP5PwAZnu66g4UJdpYe1N4kwllr2VMcCjNqfzlbku6RnID75-khgTAurnQAQsT4jzPbv2V6kwfFFbZPbAc-wgxN1EIRQ0Fc6djg3u_SM3ibTJHfEjKLZNAu9hc5yaBs0AVkrPMcOUuxvHCIBeQOEimCCL-qW1n43OmvY-9l30oe'"/>
                <div class="flex flex-col">
                    <span class="font-['Space_Grotesk'] text-[11px] font-bold tracking-wider uppercase text-primary">{{ $storeName }}</span>
                    <span class="font-['Epilogue'] text-base font-bold text-on-surface leading-tight">Katalog Menu</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-50 border border-sky-200 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                    <span class="font-['Space_Grotesk'] text-xs text-primary font-bold tracking-wider uppercase">Meja {{ $noMeja }}</span>
                </div>

                @if(!empty($whatsappUrl))
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-primary hover:bg-sky-50 border border-slate-200 transition-colors" title="Hubungi Kasir">
                        <span class="material-symbols-outlined text-[18px]">chat</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Search Input -->
        <div class="relative w-full flex items-center bg-slate-50 rounded-full px-3.5 py-1.5 border border-slate-200 focus-within:border-primary focus-within:bg-white transition-all">
            <span class="material-symbols-outlined text-outline text-[20px] mr-2">search</span>
            <input id="searchInput" class="w-full bg-transparent text-on-surface placeholder:text-outline text-sm focus:outline-none" placeholder="Cari kopi, makanan, cemilan..." type="text" onkeyup="cariMenu(this.value)"/>
            <button class="flex items-center justify-center text-outline hover:text-primary transition-colors pr-1" type="button" onclick="document.getElementById('searchInput').value=''; cariMenu('');">
                <span class="material-symbols-outlined text-[18px]">tune</span>
            </button>
        </div>
    </div>
</header>

<main class="flex flex-col relative w-full pt-28 pb-32 max-w-md mx-auto">
    <!-- Signature Editorial Card -->
    <section class="px-4 mb-4">
        <div class="relative overflow-hidden rounded-2xl bg-white border border-slate-200/80 shadow-[0_8px_24px_rgba(15,23,42,0.08)]">
            <div class="relative h-40 w-full overflow-hidden">
                <img alt="Fresh brew coffee" class="w-full h-full object-cover object-center transform scale-105" src="https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=800&q=80"/>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/75 via-transparent to-transparent"></div>
                <div class="absolute top-3 left-3 flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/95 backdrop-blur-md border border-sky-200 shadow-sm">
                    <span class="material-symbols-outlined text-primary text-[15px]" style="font-variation-settings: 'FILL' 1;">star</span>
                    <span class="font-['Space_Grotesk'] text-[11px] text-primary uppercase font-bold tracking-wider">Favorit Ruma</span>
                </div>
            </div>
            <div class="p-4 flex flex-col gap-1 bg-white relative z-10">
                <h2 class="font-['Epilogue'] text-lg font-bold text-on-surface leading-tight">
                    Racikan Autentik {{ $storeName }}
                </h2>
                <p class="text-xs text-on-surface-variant line-clamp-2">
                    Setiap cangkir diseduh dari biji kopi pilihan dengan aroma rempah dan rasa pekat yang khas.
                </p>
            </div>
        </div>
    </section>

    <!-- Categories Navigation Bar -->
    <section class="mb-3">
        <div class="flex items-center justify-between px-4 mb-2">
            <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-primary text-[18px]">tune</span>
                <h3 class="font-['Space_Grotesk'] text-xs font-bold text-on-surface tracking-wider uppercase">Daftar Kategori</h3>
            </div>
            <span class="font-['Space_Grotesk'] text-xs text-on-surface-variant font-medium" id="menuCountText">{{ $totalItemCount }} Menu Tersedia</span>
        </div>

        <div class="flex gap-2 overflow-x-auto px-4 py-1 no-scrollbar scroll-smooth" id="categoryScroll">
            <button class="category-pill shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-full bg-primary text-white font-['Space_Grotesk'] text-xs font-bold shadow-[0_4px_12px_rgba(2,132,199,0.28)] transition-all" type="button" onclick="filterKategori('semua', this)">
                <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">apps</span>
                <span>Semua</span>
            </button>

            @foreach($kategoriList as $kid => $kat)
                @if(!empty($kat['items']))
                    <button class="category-pill shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-full bg-white text-on-surface-variant hover:text-primary border border-slate-200 font-['Space_Grotesk'] text-xs font-semibold shadow-sm transition-all" type="button" onclick="filterKategori('{{ $kid }}', this)">
                        <span class="material-symbols-outlined text-[16px]">local_cafe</span>
                        <span>{{ $kat['nama'] ?? 'Kategori' }}</span>
                    </button>
                @endif
            @endforeach
        </div>
    </section>

    <!-- Info Note -->
    <section class="px-4 mb-4">
        <div class="flex items-center gap-2.5 p-3 rounded-xl bg-sky-50/90 border border-sky-100">
            <span class="material-symbols-outlined text-primary text-[20px] shrink-0">info</span>
            <p class="text-xs text-slate-600 leading-snug">
                Setiap seduhan dan hidangan disiapkan fresh sesaat setelah pesanan Anda terkonfirmasi.
            </p>
        </div>
    </section>

    <!-- Menu Grid (2-Columns) -->
    <section class="px-4">
        <div class="grid grid-cols-2 gap-3" id="menuGrid">
            @php $adaMenu = false; @endphp
            @foreach($kategoriList as $kid => $kat)
                @php $items = $kat['items'] ?? []; @endphp
                @foreach($items as $item)
                    @php
                        $adaMenu = true;
                        $idItem = (int) ($item['id'] ?? 0);
                        $nama   = $item['nama_menu'] ?? '-';
                        $harga  = (int) ($item['harga'] ?? 0);
                        $stok   = (int) ($item['stok'] ?? 1);
                        $desk   = $item['deskripsi'] ?? '';

                        $gambar = !empty($item['gambar_url']) ? $item['gambar_url'] : (!empty($item['gambar']) ? asset('uploads/menu/' . $item['gambar']) : 'https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?auto=format&fit=crop&w=400&q=80');
                    @endphp
                    <div class="menu-item-card group flex flex-col justify-between rounded-2xl bg-white overflow-hidden border border-slate-200/80 shadow-[0_2px_10px_rgba(15,23,42,0.04)] hover:shadow-[0_8px_24px_rgba(2,132,199,0.12)] hover:border-sky-200 transition-all duration-300" data-category="{{ $kid }}" data-name="{{ strtolower($nama) }}">
                        <div>
                            <!-- Media Container -->
                            <div class="relative w-full aspect-square overflow-hidden bg-slate-100">
                                <img alt="{{ $nama }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="{{ $gambar }}" onerror="this.src='https://images.unsplash.com/photo-1541696432-82c6da8ce7bf?auto=format&fit=crop&w=400&q=80'"/>

                                @if($stok <= 0)
                                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-rose-600 text-white font-['Space_Grotesk'] text-[10px] font-bold uppercase tracking-wider shadow-sm">
                                        Habis
                                    </div>
                                @else
                                    <div class="absolute top-2 left-2 px-2.5 py-0.5 rounded-full bg-white/95 backdrop-blur-md text-primary border border-sky-100 font-['Space_Grotesk'] text-[10px] font-bold shadow-sm">
                                        {{ $kat['nama'] ?? 'Menu' }}
                                    </div>
                                @endif
                            </div>

                            <!-- Card Content -->
                            <div class="p-3 flex flex-col">
                                <h4 class="font-['Epilogue'] text-sm font-bold text-on-surface leading-snug line-clamp-1 group-hover:text-primary transition-colors">
                                    {{ $nama }}
                                </h4>
                                @if(!empty($desk))
                                    <p class="text-[11px] text-on-surface-variant line-clamp-2 mt-1 leading-relaxed">
                                        {{ $desk }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="p-3 pt-0 flex items-center justify-between mt-auto">
                            <div class="flex flex-col">
                                <span class="font-['Space_Grotesk'] text-[10px] text-outline">Harga</span>
                                @if($harga <= 0)
                                    <span class="font-['Space_Grotesk'] text-sm text-emerald-600 font-bold">Gratis</span>
                                @else
                                    <span class="font-['Space_Grotesk'] text-sm text-primary font-bold">Rp {{ number_format($harga, 0, ',', '.') }}</span>
                                @endif
                            </div>

                            <!-- Action wrap (Connected to localStorage cart) -->
                            <div class="action-wrap" data-id="{{ $idItem }}" data-nama="{{ $nama }}" data-harga="{{ $harga }}" data-stok="{{ $stok }}" data-gambar="{{ $gambar }}">
                                @if($stok > 0)
                                    <button class="w-8 h-8 rounded-full bg-primary hover:bg-primary-container text-white flex items-center justify-center shadow-[0_2px_8px_rgba(2,132,199,0.3)] active:scale-90 transition-transform" onclick="tambahKeKeranjang({{ $idItem }})" type="button" aria-label="Tambah {{ $nama }}">
                                        <span class="material-symbols-outlined text-[18px]">add</span>
                                    </button>
                                @else
                                    <button disabled class="px-2.5 py-1 rounded-full bg-slate-200 text-slate-500 text-[10px] font-bold cursor-not-allowed">
                                        Habis
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach

            @if(!$adaMenu)
                <div class="col-span-2 text-center py-16 text-on-surface-variant text-sm">
                    Belum ada menu yang tersedia saat ini.
                </div>
            @endif
        </div>
    </section>
</main>

<!-- Floating Sticky Cart Bar -->
<div class="fixed bottom-5 left-4 right-4 z-40 max-w-md mx-auto">
    <div class="bg-white/95 backdrop-blur-xl rounded-2xl p-3.5 border border-slate-200 shadow-[0_12px_32px_rgba(15,23,42,0.12)] flex items-center justify-between transition-all duration-300">
        <!-- Cart Summary Info -->
        <div class="flex items-center gap-3 min-w-0">
            <div class="relative w-11 h-11 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-primary text-[22px]">shopping_bag</span>
                <span id="pingBadge" class="hidden absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-primary animate-ping"></span>
            </div>
            <div class="flex flex-col min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="font-['Space_Grotesk'] text-sm text-on-surface font-bold truncate" id="badge-count">0 Item</span>
                    <span class="text-outline text-[11px]">•</span>
                    <span class="font-['Space_Grotesk'] text-sm text-primary font-bold" id="total-price">Rp 0</span>
                </div>
                <span class="text-xs text-on-surface-variant truncate">Meja {{ $noMeja }} • Dine-in</span>
            </div>
        </div>

        <!-- Checkout Action Button -->
        <button id="btn-checkout" class="shrink-0 ml-2 inline-flex items-center gap-1.5 px-4 py-2.5 rounded-full bg-gradient-to-r from-primary to-primary-container text-white font-['Space_Grotesk'] text-xs font-bold shadow-[0_4px_14px_rgba(2,132,199,0.3)] active:scale-95 disabled:opacity-40 disabled:pointer-events-none transition-all" onclick="keCheckout()" type="button" disabled>
            <span>Pesanan</span>
            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
        </button>
    </div>
</div>

<!-- Toast Notification -->
<div class="fixed top-24 left-1/2 -translate-x-1/2 z-50 pointer-events-none opacity-0 transition-opacity duration-300" id="toastNotification">
    <div class="px-4 py-2 rounded-full bg-slate-900/95 backdrop-blur-md border border-slate-700 shadow-lg flex items-center gap-2">
        <span class="material-symbols-outlined text-sky-400 text-[18px]">check_circle</span>
        <span class="font-['Space_Grotesk'] text-xs text-white font-medium" id="toastMsg">Ditambahkan ke pesanan</span>
    </div>
</div>

<script>
    const KODE_MEJA = {!! json_encode($kdMeja) !!};
    const CHECKOUT_URL = {!! json_encode(url('order/checkout/' . $kdMeja)) !!};

    let cart = [];
    try {
        const saved = localStorage.getItem('WarkopRuma_cart_' + KODE_MEJA);
        cart = saved ? JSON.parse(saved) : [];
    } catch(e) {
        cart = [];
    }

    function showToast(msg) {
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
        }, 1800);
    }

    function filterKategori(katId, btn) {
        document.querySelectorAll('.category-pill').forEach(b => {
            b.className = 'category-pill shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-full bg-white text-on-surface-variant hover:text-primary border border-slate-200 font-[\'Space_Grotesk\'] text-xs font-semibold shadow-sm transition-all';
        });
        btn.className = 'category-pill shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-full bg-primary text-white font-[\'Space_Grotesk\'] text-xs font-bold shadow-[0_4px_12px_rgba(2,132,199,0.28)] transition-all';

        let visibleCount = 0;
        document.querySelectorAll('.menu-item-card').forEach(card => {
            const matchesCategory = (katId === 'semua' || card.dataset.category === String(katId));
            if (matchesCategory) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const countEl = document.getElementById('menuCountText');
        if (countEl) countEl.textContent = visibleCount + ' Menu Tersedia';
    }

    function cariMenu(keyword) {
        const q = keyword.toLowerCase().trim();
        let visibleCount = 0;
        document.querySelectorAll('.menu-item-card').forEach(card => {
            const name = card.dataset.name || '';
            if (name.includes(q)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        const countEl = document.getElementById('menuCountText');
        if (countEl) countEl.textContent = visibleCount + ' Menu Ditemukan';
    }

    function tambahKeKeranjang(id) {
        const wrap = document.querySelector(`.action-wrap[data-id="${id}"]`);
        if (!wrap) return;

        const nama = wrap.dataset.nama;
        const harga = parseInt(wrap.dataset.harga || '0');
        const gambar = wrap.dataset.gambar || '';

        const item = cart.find(i => i.id === id);
        if (item) {
            item.qty++;
        } else {
            cart.push({ id, nama_menu: nama, harga, gambar, qty: 1, catatan: '' });
            showToast(nama + ' ditambahkan');
        }
        simpanCart();
    }

    function kurangiItem(id) {
        const idx = cart.findIndex(i => i.id === id);
        if (idx > -1) {
            if (cart[idx].qty > 1) {
                cart[idx].qty--;
            } else {
                cart.splice(idx, 1);
            }
            simpanCart();
        }
    }

    function simpanCart() {
        try {
            localStorage.setItem('WarkopRuma_cart_' + KODE_MEJA, JSON.stringify(cart));
        } catch(e) {}
        renderTampilan();
    }

    function renderTampilan() {
        let totalItems = 0;
        let grandTotal = 0;

        document.querySelectorAll('.action-wrap').forEach(wrap => {
            const id = parseInt(wrap.dataset.id);
            const stok = parseInt(wrap.dataset.stok || '1');
            const item = cart.find(i => i.id === id);

            if (!stok) {
                wrap.innerHTML = `<button disabled class="px-2.5 py-1 rounded-full bg-slate-200 text-slate-500 text-[10px] font-bold cursor-not-allowed">Habis</button>`;
                return;
            }

            if (item && item.qty > 0) {
                wrap.innerHTML = `
                    <div class="flex items-center bg-slate-100 rounded-full p-0.5 border border-slate-200">
                        <button type="button" class="w-7 h-7 rounded-full flex items-center justify-center text-on-surface hover:text-primary active:bg-slate-200 transition-colors" onclick="kurangiItem(${id})">
                            <span class="material-symbols-outlined text-[14px]">remove</span>
                        </button>
                        <span class="w-6 text-center font-['Space_Grotesk'] text-xs text-primary font-bold">${item.qty}</span>
                        <button type="button" class="w-7 h-7 rounded-full bg-sky-100 text-primary flex items-center justify-center active:bg-primary active:text-white transition-all" onclick="tambahKeKeranjang(${id})">
                            <span class="material-symbols-outlined text-[14px]">add</span>
                        </button>
                    </div>
                `;
            } else {
                wrap.innerHTML = `
                    <button class="w-8 h-8 rounded-full bg-primary hover:bg-primary-container text-white flex items-center justify-center shadow-[0_2px_8px_rgba(2,132,199,0.3)] active:scale-90 transition-transform" onclick="tambahKeKeranjang(${id})" type="button">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                    </button>
                `;
            }
        });

        cart.forEach(item => {
            totalItems += item.qty;
            grandTotal += (item.qty * item.harga);
        });

        const badgeEl = document.getElementById('badge-count');
        const totalEl = document.getElementById('total-price');
        const pingBadge = document.getElementById('pingBadge');
        const btnCheckout = document.getElementById('btn-checkout');

        if (badgeEl) badgeEl.textContent = totalItems + ' Item';
        if (totalEl) totalEl.textContent = (totalItems > 0 && grandTotal === 0) ? 'Gratis' : ('Rp ' + grandTotal.toLocaleString('id-ID'));

        if (totalItems > 0) {
            if (pingBadge) pingBadge.classList.remove('hidden');
            if (btnCheckout) btnCheckout.disabled = false;
        } else {
            if (pingBadge) pingBadge.classList.add('hidden');
            if (btnCheckout) btnCheckout.disabled = true;
        }
    }

    function keCheckout() {
        if (cart.length === 0) return;
        window.location.href = CHECKOUT_URL;
    }

    renderTampilan();
</script>
</body>
</html>