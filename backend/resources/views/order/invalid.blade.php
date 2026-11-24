@php
$storeName        = function_exists('app_setting') ? app_setting('store_name', 'WARKOP RUMA') : 'WARKOP RUMA';
$storeLogo        = function_exists('app_setting') ? app_setting('store_logo', '') : '';
$storeFavicon     = function_exists('app_setting') ? app_setting('store_favicon', '') : '';
$storeWhatsapp    = function_exists('app_setting') ? app_setting('store_whatsapp', '') : '';

$logoSrc = !empty($storeLogo) ? asset($storeLogo) : asset('assets/img/logo_k.jpeg');
$faviconSrc = !empty($storeFavicon) ? asset($storeFavicon) : (!empty($storeLogo) ? asset($storeLogo) : asset('assets/img/logo_k.jpeg'));

$whatsappUrl = '';
if (!empty($storeWhatsapp)) {
    $waNumber = preg_replace('/[^0-9]/', '', $storeWhatsapp);
    $waText   = 'Halo, saya mengalami kendala scan QR Code meja di ' . $storeName . '.';
    $whatsappUrl = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($waText);
}
@endphp

<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" name="viewport"/>
    <title>QR Tidak Valid — {{ $storeName }}</title>
    <link rel="icon" type="image/png" href="{{ $faviconSrc }}">

    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
    <link href="https://fonts.googleapis.com/css2?family=Epilogue:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>

    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#ffc665",
                        "primary-container": "#e5a93c",
                        "on-primary": "#432c00",
                        "background": "#121317",
                        "surface": "#121317",
                        "surface-container": "#1f1f24",
                        "surface-container-high": "#292a2e",
                        "surface-container-lowest": "#0d0e12",
                        "on-surface": "#e3e2e7",
                        "on-surface-variant": "#d4c4b0",
                        "outline": "#9d8f7c"
                    }
                }
            }
        };
    </script>
</head>
<body class="bg-[#121317] text-[#e3e2e7] flex items-center justify-center min-h-screen p-4 font-['Plus_Jakarta_Sans',sans-serif]">
    <div class="w-full max-w-sm bg-[#1f1f24] rounded-3xl p-6 border border-white/5 shadow-2xl text-center flex flex-col items-center">
        <div class="w-16 h-16 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-[34px]">qr_code_scanner</span>
        </div>

        <h1 class="font-['Epilogue'] text-xl font-bold text-white mb-2">QR Code Tidak Valid</h1>
        <p class="text-xs text-[#d4c4b0] leading-relaxed mb-6">
            Kode meja yang Anda scan tidak ditemukan atau sudah tidak aktif. Silakan scan ulang stiker QR Code pada meja Anda.
        </p>

        <div class="w-full space-y-2.5">
            @if(!empty($whatsappUrl))
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="w-full py-3 px-4 rounded-full bg-[#ffc665] text-[#432c00] font-['Space_Grotesk'] text-xs font-bold flex items-center justify-center gap-2 shadow-lg">
                    <span class="material-symbols-outlined text-[18px]">chat</span>
                    <span>Hubungi Petugas / Kasir</span>
                </a>
            @endif

            <a href="{{ url('/') }}" class="w-full py-3 px-4 rounded-full bg-[#292a2e] hover:bg-[#343439] text-[#e3e2e7] font-['Space_Grotesk'] text-xs font-bold flex items-center justify-center gap-2 border border-white/5 transition-all">
                <span class="material-symbols-outlined text-[18px]">monitor</span>
                <span>Buka Layar Monitor Antrean</span>
            </a>
        </div>
    </div>
</body>
</html>
