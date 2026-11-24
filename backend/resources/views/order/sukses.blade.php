@php
$storeName        = function_exists('app_setting') ? app_setting('store_name', 'WARKOP RUMA') : 'WARKOP RUMA';
$storeLogo        = function_exists('app_setting') ? app_setting('store_logo', '') : '';
$storeFavicon     = function_exists('app_setting') ? app_setting('store_favicon', '') : '';
$storeDescription = function_exists('app_setting') ? app_setting('store_description', 'Terima kasih sudah memesan.') : 'Terima kasih sudah memesan.';
$storeWhatsapp    = function_exists('app_setting') ? app_setting('store_whatsapp', '') : '';

$logoSrc = !empty($storeLogo) ? asset($storeLogo) : asset('assets/img/logo_k.jpeg');
$faviconSrc = !empty($storeFavicon) ? asset($storeFavicon) : (!empty($storeLogo) ? asset($storeLogo) : asset('assets/img/logo_k.jpeg'));

$metode = isset($pesanan['metode_pembayaran'])
    ? strtolower((string) $pesanan['metode_pembayaran'])
    : 'tunai';

$statusBayar = strtolower((string) ($pesanan['status'] ?? 'pending'));
$isLunas     = $statusBayar === 'selesai';

$statusPesanan = strtolower((string) ($pesanan['status_pesanan'] ?? 'menunggu'));

$nomorAntrian = $pesanan['nomor_antrian'] ?? '-';
$nomorMeja    = $meja['nomor_meja'] ?? ($meja->nomor_meja ?? '-');
$kodeMeja     = $meja['kode_unik'] ?? ($meja->kode_unik ?? '');
$kodePesanan  = $pesanan['kode_pesanan'] ?? '-';
$waktuPesan   = !empty($pesanan['created_at']) ? date('H:i \W\I\B', strtotime($pesanan['created_at'])) : date('H:i \W\I\B');

$metodeLabel = match ($metode) {
    'tunai'  => 'Tunai di Kasir',
    'qris'   => 'QRIS Instant',
    'gratis' => 'Gratis (Tanpa Biaya)',
    default  => ucfirst($metode),
};

$whatsappUrl = '';
if (!empty($storeWhatsapp)) {
    $waNumber = preg_replace('/[^0-9]/', '', $storeWhatsapp);
    $waText   = 'Halo, saya ingin menanyakan pesanan ' . $kodePesanan . ' di Meja ' . $nomorMeja . '.';
    $whatsappUrl = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($waText);
}

// Menentukan step linimasa aktif (1-4)
$currentStep = 1;
if ($statusPesanan === 'dimasak' || $statusPesanan === 'diproses' || $statusPesanan === 'proses') {
    $currentStep = 2;
} elseif ($statusPesanan === 'siap') {
    $currentStep = 3;
} elseif ($statusPesanan === 'selesai') {
    $currentStep = 4;
}

$totalItemsCount = 0;
foreach ($items ?? [] as $it) {
    $totalItemsCount += (int) ($it['qty'] ?? 1);
}
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" name="viewport"/>
    <meta content="mobile_tab" name="shell-type"/>
    <title>Status Pesanan #{{ $kodePesanan }} — {{ $storeName }}</title>
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
        @media print {
            header, footer, nav, button, .no-print { display: none !important; }
            body { background: #fff !important; color: #000 !important; }
            .print-card { box-shadow: none !important; border: 1px solid #ccc !important; }
        }
    </style>
</head>
<body class="bg-surface text-on-surface flex flex-col min-h-screen selection:bg-primary-container selection:text-on-primary-container">

<!-- Sticky Header -->
<header class="fixed top-0 w-full z-50 pt-safe bg-white/95 backdrop-blur-xl border-b border-slate-200/80 shadow-[0_2px_12px_rgba(15,23,42,0.05)]">
    <div class="max-w-md mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ url('order/' . $kodeMeja) }}" aria-label="Kembali ke menu" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-on-surface hover:text-primary hover:bg-sky-50 active:scale-95 transition-all border border-slate-200">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <div class="flex flex-col">
                <h1 class="font-['Epilogue'] text-base font-bold text-on-surface leading-tight">Status Pesanan</h1>
                <span class="font-['Space_Grotesk'] text-[11px] text-on-surface-variant uppercase tracking-wider">{{ $storeName }}</span>
            </div>
        </div>

        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-sky-50 border border-sky-200 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
            <span class="font-['Space_Grotesk'] text-xs font-bold tracking-wider text-primary uppercase">Meja {{ $nomorMeja }}</span>
        </div>
    </div>
</header>

<main class="flex flex-col relative w-full pt-20 pb-28 max-w-md mx-auto px-4 space-y-4">

    <!-- Reference Bar & Status Pill -->
    <div class="flex flex-col gap-2.5 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between gap-2">
            <div class="flex flex-col">
                <div class="flex items-center gap-1.5">
                    <span class="font-['Space_Grotesk'] text-sm text-primary tracking-wider font-bold">#{{ $kodePesanan }}</span>
                    <button class="text-outline hover:text-primary transition-colors inline-flex items-center" onclick="copyKode()" title="Salin Kode Pesanan">
                        <span class="material-symbols-outlined text-[15px]">content_copy</span>
                    </button>
                </div>
                <span class="text-xs text-on-surface-variant mt-0.5">Dipesan pada {{ $waktuPesan }}</span>
            </div>

            <div class="flex flex-col items-end">
                <span class="font-['Space_Grotesk'] text-[10px] uppercase text-on-surface-variant font-semibold">Nomor Antrian</span>
                <span class="font-['Epilogue'] text-2xl font-bold text-primary leading-tight">{{ $nomorAntrian }}</span>
            </div>
        </div>
    </div>

    <!-- Hero Live Tracking Card -->
    <div class="relative overflow-hidden rounded-2xl bg-white p-5 border border-slate-200/80 shadow-sm flex flex-col gap-4">
        <!-- Ambient Soft Sky Glow Scrims -->
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-10 -left-10 w-44 h-44 bg-sky-400/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex items-center justify-between">
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-['Space_Grotesk'] text-[11px] text-emerald-700 tracking-wider uppercase font-bold">Live Tracking</span>
            </div>
            <div class="flex items-center gap-1 text-on-surface-variant">
                <span class="material-symbols-outlined text-[17px] text-primary">coffee</span>
                <span class="font-['Space_Grotesk'] text-[11px] text-on-surface-variant uppercase font-semibold">Racikan Fresh</span>
            </div>
        </div>

        <!-- Centerpiece Icon & Status Heading -->
        <div class="relative z-10 flex items-center gap-4 pt-1">
            <div class="relative shrink-0 w-16 h-16 rounded-2xl bg-sky-50 border border-sky-200 flex items-center justify-center shadow-sm">
                @if($currentStep >= 3)
                    <span class="material-symbols-outlined text-primary text-[32px] animate-bounce">room_service</span>
                @elseif($currentStep == 2)
                    <span class="material-symbols-outlined text-primary text-[32px] animate-bounce">coffee_maker</span>
                @else
                    <span class="material-symbols-outlined text-primary text-[32px]">check_circle</span>
                @endif
                <span class="absolute inset-0 rounded-2xl bg-sky-400/15 animate-ping opacity-30"></span>
            </div>

            <div class="flex flex-col min-w-0">
                <h2 class="font-['Epilogue'] text-base font-bold text-on-surface leading-snug">
                    @if($statusPesanan === 'selesai')
                        Pesanan Selesai Dinikmati ✨
                    @elseif($statusPesanan === 'siap')
                        Pesanan Siap Disajikan! 🍽️
                    @elseif($statusPesanan === 'dimasak' || $statusPesanan === 'diproses' || $statusPesanan === 'proses')
                        Pesanan Sedang Diracik ☕
                    @else
                        Pesanan Diterima Sistem 📝
                    @endif
                </h2>
                <p class="text-xs text-on-surface-variant mt-1 leading-snug">
                    @if($statusPesanan === 'selesai')
                        Terima kasih sudah menikmati sajian di {{ $storeName }}.
                    @elseif($statusPesanan === 'siap')
                        Pramusaji sedang mengantarkan hidangan ke Meja {{ $nomorMeja }}.
                    @elseif($statusPesanan === 'dimasak' || $statusPesanan === 'diproses' || $statusPesanan === 'proses')
                        Barista sedang meracik kopi dan menyiapkan hidangan meja Anda.
                    @else
                        Nota pesanan Anda telah tercatat dan masuk ke antrean barista.
                    @endif
                </p>
            </div>
        </div>

        <!-- Payment Instruction Alert (if pending) -->
        @if(!$isLunas)
            <div class="relative z-10 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-start gap-2.5">
                <span class="material-symbols-outlined text-[20px] text-amber-600 shrink-0">info</span>
                <div class="space-y-0.5">
                    <span class="font-['Space_Grotesk'] font-bold block text-amber-900">
                        {{ $metode === 'tunai' ? 'Menunggu Pembayaran di Kasir' : 'Menunggu Konfirmasi QRIS' }}
                    </span>
                    <p class="text-[11px] text-amber-700 leading-relaxed">
                        @if($metode === 'tunai')
                            Sebutkan <strong>Nomor Antrian {{ $nomorAntrian }}</strong> atau tunjukkan halaman ini saat membayar di kasir.
                        @else
                            Silakan selesaikan pembayaran QRIS. Halaman ini akan otomatis terupdate setelah pembayaran terverifikasi.
                        @endif
                    </p>
                </div>
            </div>
        @endif

        <!-- Estimated Time Highlight Banner -->
        <div class="relative z-10 flex flex-col gap-1.5 bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5 text-primary">
                    <span class="material-symbols-outlined text-[18px]">schedule</span>
                    <span class="font-['Space_Grotesk'] text-xs font-bold tracking-wider uppercase">Estimasi Penyajian</span>
                </div>
                <span class="font-['Space_Grotesk'] text-xs text-primary font-bold">5 - 10 Menit</span>
            </div>

            <!-- Mini Progress Bar Indicator -->
            <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden mt-1">
                @php
                    $progWidth = match($currentStep) {
                        1 => 'w-1/4',
                        2 => 'w-2/4',
                        3 => 'w-3/4',
                        4 => 'w-full',
                        default => 'w-1/4'
                    };
                @endphp
                <div class="bg-primary h-1.5 rounded-full {{ $progWidth }} transition-all duration-700"></div>
            </div>

            <div class="flex justify-between items-center text-on-surface-variant font-['Space_Grotesk'] text-[11px] pt-0.5">
                <span>Waktu Order: {{ $waktuPesan }}</span>
                <span>Meja {{ $nomorMeja }}</span>
            </div>
        </div>
    </div>

    <!-- Stepper Timeline: Linimasa Seduhan -->
    <div class="flex flex-col bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm gap-4">
        <div class="flex items-center justify-between">
            <h3 class="font-['Epilogue'] text-base text-on-surface font-bold">Linimasa Pesanan</h3>
            <span class="font-['Space_Grotesk'] text-xs text-on-surface-variant tracking-wider uppercase font-semibold">Langkah {{ $currentStep }} dari 4</span>
        </div>

        <div class="relative flex flex-col gap-4 pl-1">
            <!-- Step 1: Pesanan Diterima -->
            <div class="relative flex items-start gap-3.5">
                <div class="absolute left-4 top-8 bottom-0 w-0.5 {{ $currentStep > 1 ? 'bg-primary' : 'bg-slate-200' }} -mb-4"></div>
                <div class="relative z-10 w-8 h-8 rounded-full {{ $currentStep >= 1 ? 'bg-primary text-white' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center shrink-0 shadow-sm">
                    <span class="material-symbols-outlined text-[18px] font-bold">check</span>
                </div>
                <div class="flex flex-col min-w-0 pt-0.5">
                    <div class="flex items-center gap-2">
                        <span class="font-['Space_Grotesk'] text-xs text-on-surface font-bold">Pesanan Diterima</span>
                        <span class="font-['Space_Grotesk'] text-[10px] px-2 py-0.5 rounded-full bg-sky-50 border border-sky-200 text-primary font-bold">{{ $waktuPesan }}</span>
                    </div>
                    <p class="text-xs text-on-surface-variant mt-0.5">Nota pesanan tervalidasi di sistem.</p>
                </div>
            </div>

            <!-- Step 2: Sedang Diracik Barista -->
            <div class="relative flex items-start gap-3.5">
                <div class="absolute left-4 top-8 bottom-0 w-0.5 {{ $currentStep > 2 ? 'bg-primary' : 'bg-slate-200' }} -mb-4"></div>
                <div class="relative z-10 w-8 h-8 rounded-full {{ $currentStep >= 2 ? 'bg-primary-container text-white shadow-sm' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px] {{ $currentStep == 2 ? 'animate-spin' : '' }}">skillet</span>
                </div>
                <div class="flex flex-col min-w-0 pt-0.5 flex-1 {{ $currentStep == 2 ? 'bg-sky-50/70 p-3 rounded-xl border border-sky-200' : '' }}">
                    <div class="flex items-center justify-between gap-1">
                        <span class="font-['Space_Grotesk'] text-xs {{ $currentStep >= 2 ? 'text-primary font-bold' : 'text-on-surface-variant font-medium' }}">Sedang Diracik Barista</span>
                        @if($currentStep == 2)
                            <span class="font-['Space_Grotesk'] text-[9px] px-2 py-0.5 rounded-full bg-sky-100 text-primary font-bold uppercase tracking-wider">Aktif</span>
                        @endif
                    </div>
                    <p class="text-xs {{ $currentStep == 2 ? 'text-on-surface' : 'text-on-surface-variant' }} mt-0.5">Mengekstrak seduhan kopi & menyiapkan makanan.</p>
                </div>
            </div>

            <!-- Step 3: Siap Disajikan -->
            <div class="relative flex items-start gap-3.5">
                <div class="absolute left-4 top-8 bottom-0 w-0.5 {{ $currentStep > 3 ? 'bg-primary' : 'bg-slate-200' }} -mb-4"></div>
                <div class="relative z-10 w-8 h-8 rounded-full {{ $currentStep >= 3 ? 'bg-primary text-white shadow-sm' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px]">room_service</span>
                </div>
                <div class="flex flex-col min-w-0 pt-0.5 flex-1 {{ $currentStep == 3 ? 'bg-sky-50/70 p-3 rounded-xl border border-sky-200' : '' }}">
                    <div class="flex items-center justify-between gap-1">
                        <span class="font-['Space_Grotesk'] text-xs {{ $currentStep >= 3 ? 'text-primary font-bold' : 'text-on-surface-variant font-medium' }}">Siap Disajikan</span>
                        @if($currentStep == 3)
                            <span class="font-['Space_Grotesk'] text-[9px] px-2 py-0.5 rounded-full bg-sky-100 text-primary font-bold uppercase tracking-wider">Antar</span>
                        @endif
                    </div>
                    <p class="text-xs text-on-surface-variant mt-0.5">Pramusaji mengantarkan hidangan ke Meja {{ $nomorMeja }}.</p>
                </div>
            </div>

            <!-- Step 4: Selesai Dinikmati -->
            <div class="relative flex items-start gap-3.5">
                <div class="relative z-10 w-8 h-8 rounded-full {{ $currentStep >= 4 ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px]">verified</span>
                </div>
                <div class="flex flex-col min-w-0 pt-0.5">
                    <span class="font-['Space_Grotesk'] text-xs {{ $currentStep >= 4 ? 'text-emerald-600 font-bold' : 'text-on-surface-variant font-medium' }}">Selesai Dinikmati</span>
                    <p class="text-xs text-on-surface-variant mt-0.5">Selamat menikmati hidangan di {{ $storeName }}.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items Summary Accordion -->
    <div class="flex flex-col bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <button id="summaryToggleBtn" type="button" class="flex items-center justify-between p-4 text-left bg-slate-50 hover:bg-sky-50/70 transition-colors">
            <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-primary text-[20px]">receipt</span>
                <div class="flex flex-col">
                    <span class="font-['Space_Grotesk'] text-xs font-bold text-on-surface">Ringkasan Pesanan ({{ $totalItemsCount }} Item)</span>
                    <span class="font-['Space_Grotesk'] text-xs text-primary font-bold">Total: Rp {{ number_format($pesanan['total_harga'] ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($isLunas)
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-['Space_Grotesk'] text-[10px] font-bold tracking-wider">LUNAS</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-50 border border-amber-200 text-amber-700 font-['Space_Grotesk'] text-[10px] font-bold tracking-wider">MENUNGGU BAYAR</span>
                @endif
                <span id="summaryChevron" class="material-symbols-outlined text-on-surface-variant text-[20px] transition-transform duration-300">expand_more</span>
            </div>
        </button>

        <div id="summaryContent" class="flex flex-col p-4 gap-3 bg-white border-t border-slate-100">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <span class="font-['Space_Grotesk'] text-[11px] text-on-surface-variant uppercase tracking-wider font-semibold">Metode Pembayaran</span>
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-sky-50 border border-sky-200 text-xs text-primary font-semibold">
                    <span class="material-symbols-outlined text-[14px] text-primary">payments</span>
                    <span>{{ $metodeLabel }}</span>
                </div>
            </div>

            <!-- Items List -->
            <div class="flex flex-col gap-2.5">
                @foreach(($items ?? []) as $item)
                    @php $subItem = ($item['harga'] ?? 0) * ($item['qty'] ?? 1); @endphp
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-start gap-2">
                            <span class="px-2 py-0.5 rounded bg-sky-100 text-primary font-['Space_Grotesk'] text-xs font-bold">{{ $item['qty'] ?? 1 }}x</span>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-on-surface">{{ $item['nama_menu'] ?? '-' }}</span>
                                @if(!empty($item['catatan']))
                                    <span class="text-[11px] text-on-surface-variant italic">Catatan: {{ $item['catatan'] }}</span>
                                @endif
                            </div>
                        </div>
                        <span class="font-['Space_Grotesk'] text-xs font-semibold text-on-surface shrink-0">Rp {{ number_format($subItem, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Discounts (Tanpa PB1) -->
            @if(isset($pesanan['potongan']) && (int) $pesanan['potongan'] > 0)
                <div class="flex flex-col gap-1 pt-2 border-t border-slate-100 text-xs">
                    <div class="flex justify-between text-emerald-600 font-bold">
                        <span>Potongan Kupon Promo ({{ $pesanan['kode_promo'] ?? '' }})</span>
                        <span class="font-['Space_Grotesk']">-Rp {{ number_format((int) $pesanan['potongan'], 0, ',', '.') }}</span>
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-between pt-2.5 border-t border-slate-100 p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                <span class="font-['Space_Grotesk'] text-xs font-bold text-on-surface">Total Pembayaran</span>
                <span class="font-['Epilogue'] text-base font-bold text-primary">Rp {{ number_format($pesanan['total_harga'] ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Action: Tambah Pesanan -->
    <a href="{{ url('order/' . $kodeMeja) }}" class="flex items-center justify-center gap-2 w-full py-3.5 px-5 rounded-full bg-primary hover:bg-primary-container text-white font-['Space_Grotesk'] text-sm font-bold shadow-[0_4px_14px_rgba(2,132,199,0.28)] active:scale-95 transition-all">
        <span class="material-symbols-outlined text-[20px]">add_circle</span>
        <span>Tambah Pesanan</span>
    </a>
</main>

<!-- Bottom Reload / Refresh Bar -->
<div class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200/80 shadow-[0_-4px_16px_rgba(15,23,42,0.05)] pb-safe">
    <div class="max-w-md mx-auto p-3 flex items-center justify-between gap-3">
        <a href="{{ url('order/' . $kodeMeja) }}" class="flex-1 py-2.5 px-3 rounded-full bg-slate-100 hover:bg-slate-200 border border-slate-200 text-on-surface text-center font-['Space_Grotesk'] text-xs font-bold transition-all">
            Menu Utama
        </a>
        <button type="button" onclick="location.reload()" class="flex-1 py-2.5 px-3 rounded-full bg-sky-50 hover:bg-sky-100 border border-sky-200 text-primary text-center font-['Space_Grotesk'] text-xs font-bold flex items-center justify-center gap-1.5 transition-all">
            <span class="material-symbols-outlined text-[16px]">refresh</span>
            <span>Segarkan Status</span>
        </button>
    </div>
</div>

<script>
    // Simpan nama pemesan agar saat klik "Tambah Pesanan" tidak perlu isi nama ulang
    (function() {
        try {
            const kodeMeja = {!! json_encode($kodeMeja) !!};
            const namaPemesan = {!! json_encode($pesanan['nama_pemesan'] ?? '') !!};
            if (kodeMeja && namaPemesan && namaPemesan.trim() !== '') {
                localStorage.setItem('WarkopRuma_customer_name_' + kodeMeja, JSON.stringify({
                    name: namaPemesan.trim(),
                    savedAt: Date.now()
                }));
            }
        } catch(e) {}
    })();

    function copyKode() {
        const kode = {!! json_encode($kodePesanan) !!};
        if (navigator.clipboard) {
            navigator.clipboard.writeText(kode).then(() => {
                alert('Kode pesanan ' + kode + ' berhasil disalin.');
            });
        }
    }

    // Accordion toggle
    const toggleBtn = document.getElementById('summaryToggleBtn');
    const content = document.getElementById('summaryContent');
    const chevron = document.getElementById('summaryChevron');

    if (toggleBtn && content && chevron) {
        toggleBtn.addEventListener('click', function() {
            if (content.style.display === 'none') {
                content.style.display = 'flex';
                chevron.style.transform = 'rotate(0deg)';
            } else {
                content.style.display = 'none';
                chevron.style.transform = 'rotate(-90deg)';
            }
        });
    }

    // Auto-refresh jika status belum selesai
    @if(!$isLunas || $statusPesanan !== 'selesai')
        setTimeout(function() {
            location.reload();
        }, 15000);
    @endif
</script>
</body>
</html>