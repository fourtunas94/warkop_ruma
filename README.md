## Instalasi Project dari ZIP

Project ARZANA didistribusikan dalam bentuk file ZIP. Setelah mendapatkan file ZIP dari member area atau admin, ikuti langkah berikut.

### 1. Extract file ZIP

Extract file project ke folder server lokal atau hosting.

Contoh untuk Laragon:

```text
C:\laragon\www\arzana
```

Contoh struktur folder setelah extract:

```text
arzana/
├── app/
├── public/
├── system/
├── writable/
├── composer.json
├── composer.lock
├── spark
└── env
```

### 2. Masuk ke folder project

```bash
cd arzana
```

Jika menggunakan Laragon:

```bash
cd C:\laragon\www\arzana
```

### 3. Install dependensi Composer

```bash
composer install
```

Jika package Midtrans belum ikut terinstall, jalankan:

```bash
composer require midtrans/midtrans-php
```

Lalu optimalkan autoload:

```bash
composer dump-autoload -o
```

### 4. Buat file `.env`

Salin file `env` menjadi `.env`:

```bash
cp env .env
```

Untuk Windows, bisa copy manual file `env`, lalu rename menjadi:

```text
.env
```

### 5. Konfigurasi `.env`

Edit file `.env`:

```env
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080'

database.default.hostname = localhost
database.default.database = arzana_db
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306

MIDTRANS_CLIENT_KEY=Mid-client-ISI_CLIENT_KEY_KAMU
MIDTRANS_SERVER_KEY=Mid-server-ISI_SERVER_KEY_KAMU
MIDTRANS_IS_PRODUCTION=false
```

Jika digunakan di hosting, ubah `app.baseURL` menjadi domain aktif:

```env
app.baseURL = 'https://domain-kamu.com'
```

Contoh:

```env
app.baseURL = 'https://pro.arzana.kitangoding.com'
```

### 6. Buat database

Buat database baru di MySQL/MariaDB, misalnya:

```text
arzana_db
```

Lalu sesuaikan nama database di file `.env`.

### 7. Jalankan migrasi

```bash
php spark migrate
```

Jika project menyediakan seeder, jalankan seeder sesuai nama yang tersedia:

```bash
php spark db:seed NamaSeeder
```

### 8. Buat folder upload

```bash
mkdir -p public/uploads/menu
chmod 775 public/uploads/menu
```

Untuk Windows/Laragon, cukup pastikan folder ini ada:

```text
public/uploads/menu
```

### 9. Jalankan project

```bash
php spark serve
```

Akses project di:

```text
http://localhost:8080
```

---

## Catatan Distribusi ZIP

Untuk keamanan, file ZIP yang dibagikan ke member sebaiknya **tidak menyertakan file `.env` asli** yang berisi key rahasia.

Yang aman disertakan:

```text
env
composer.json
composer.lock
app/
public/
system/
writable/
spark
```

Yang sebaiknya tidak disertakan:

```text
.env
writable/logs/*
writable/cache/*
database backup pribadi
key production Midtrans
```

Member perlu mengisi sendiri konfigurasi database dan key Midtrans di file `.env`.

---

## Support

Jika mengalami kendala instalasi, pastikan beberapa hal berikut:

1. Versi PHP minimal 8.1.
2. Composer sudah terinstall.
3. Database sudah dibuat.
4. File `.env` sudah dikonfigurasi.
5. `app.baseURL` sudah sesuai domain.
6. Package Midtrans sudah terinstall.
7. Folder `writable` memiliki permission yang benar.
8. Payment Notification URL Midtrans sudah diarahkan ke:

```text
https://domain-kamu.com/order/midtrans-notification
```

---

## Lisensi Penggunaan

Project ini boleh digunakan dan dimodifikasi oleh member sesuai ketentuan pembelian atau lisensi yang diberikan oleh pemilik project.

Dilarang membagikan ulang, menjual ulang, atau mendistribusikan ulang source code tanpa izin dari pemilik project.



link simulator : https://simulator.sandbox.midtrans.com/