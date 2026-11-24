# QR Order System — Warkop Ruma

Sistem pemesanan berbasis QR Code meja untuk kafe/warkop, terdiri dari **Web Pelanggan & REST API (Laravel 12)** serta **Aplikasi Kasir & Admin (Flutter Web/Mobile)**.

## Struktur Proyek

```text
qr-order-system/
├── backend/    # Laravel 12 — Web Pelanggan (Scan QR, Checkout, Status) & REST API
└── mobile/     # Flutter — Aplikasi Kasir POS (Open Bill, Split Bill, Merge Bill, Cetak Struk/KOT, Laporan PDF) & Dashboard Admin
```

## Teknologi
- **Backend & Web Pelanggan**: Laravel 12, PHP 8.2+, PostgreSQL, Laravel Sanctum, Midtrans
- **Aplikasi Kasir & Admin**: Flutter (Web / Android / iOS)

## Cara Menjalankan

### 1. Backend (Laravel)
```bash
cd backend
cp .env.example .env
# Sesuaikan DB_CONNECTION, DB_DATABASE, DB_USERNAME, DB_PASSWORD di .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=0.0.0.0 --port=8000
```

### 2. Aplikasi Kasir & Admin (Flutter)
```bash
cd mobile
flutter pub get
# Sesuaikan baseUrl di lib/config/api_config.dart
flutter run -d chrome --release
```

### 3. Akses Halaman Pelanggan (QR Meja)
Buka di browser: `http://localhost:8000/order/meja01`
