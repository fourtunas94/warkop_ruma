# ARZANA — Sistem Pemesanan QR Code

Sistem pemesanan berbasis QR Code untuk restoran dan kafe, dibangun dengan CodeIgniter 4. Pelanggan cukup scan QR di meja, pilih menu, dan pesan — tanpa perlu antri ke kasir.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.x-orange.svg)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-purple.svg)

---

## Fitur

- **QR Code per meja** — setiap meja punya QR unik, scan langsung ke halaman menu
- **Halaman menu publik** — dark theme, tanpa login, mobile-friendly
- **Keranjang belanja** — berbasis localStorage, qty bisa diketik langsung
- **Dashboard Admin** — kelola menu, kategori, meja, dan akun kasir
- **Dashboard Kasir** — antrean pesanan real-time dengan notifikasi suara
- **Riwayat transaksi** — rekap pesanan dan pendapatan harian
- **Pembayaran tunai** — pelanggan bayar langsung ke kasir

---

## Teknologi

| Komponen | Detail |
|---|---|
| Backend | CodeIgniter 4 (PHP 8.1+) |
| Frontend Admin | Tailwind CSS, Font Awesome 6 |
| Frontend Pelanggan | Tailwind CSS, dark theme |
| Database | MySQL / MariaDB |
| QR Code | qrcodejs (client-side) |

---

## Instalasi

### Prasyarat
- PHP 8.1 atau lebih baru
- MySQL / MariaDB
- Composer

### Langkah

**1. Clone repositori**
```bash
git clone https://github.com/username/arzana.git
cd arzana
```

**2. Install dependensi**
```bash
composer install
```

**3. Salin dan konfigurasi environment**
```bash
cp env .env
```

Edit file `.env`:
```
CI_ENVIRONMENT = development

database.default.hostname = localhost
database.default.database = arzana_db
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
```

**4. Buat database dan jalankan migrasi**
```bash
php spark migrate
```

**5. Buat folder upload**
```bash
mkdir -p public/uploads/menu
chmod 775 public/uploads/menu
```

**6. Jalankan server**
```bash
php spark serve
```

Akses di `http://localhost:8080`

---

## Struktur Route

| Method | URL | Keterangan |
|---|---|---|
| GET | `/` | Halaman login |
| GET | `/order/{kode}` | Halaman menu pelanggan (publik) |
| GET | `/order/checkout/{kode}` | Halaman checkout |
| POST | `/order/submit/{kode}` | Submit pesanan |
| GET | `/admin/dashboard` | Dashboard admin |
| GET | `/admin/menu` | Kelola menu |
| GET | `/admin/kategori` | Kelola kategori |
| GET | `/admin/meja` | Kelola meja & QR |
| GET | `/kasir/dashboard` | Antrean pesanan kasir |

---

## Alur Penggunaan

```
Admin buat meja → Generate QR otomatis
        ↓
Pelanggan scan QR di meja
        ↓
Pilih menu → tambah ke keranjang
        ↓
Checkout → isi nama → kirim pesanan
        ↓
Kasir terima notifikasi → proses → selesai
        ↓
Pelanggan bayar tunai ke kasir
```

---

## Akun Default

Buat akun admin pertama melalui seeder atau langsung insert ke database:

```sql
INSERT INTO users (nama_lengkap, username, password, role) 
VALUES ('Admin', 'admin', MD5('admin123'), 'admin');
```

> Ganti password setelah login pertama.

---

## Lisensi

Proyek ini dirilis di bawah [MIT License](LICENSE). Bebas digunakan, dimodifikasi, dan didistribusikan — dengan tetap mencantumkan credit asli.

---

## Kontribusi

Pull request dan issue sangat disambut. Kalau project ini membantu, beri bintang di GitHub.
