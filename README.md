# 🛒 Elektronik Modern
**E-Commerce Elektronik - Laravel 13 & Tailwind CSS 4**

Proyek ini adalah platform toko online elektronik yang mendukung fitur multi-role (Admin, Owner, Customer) dan integrasi alamat real-time se-Indonesia.

---

## 🛠️ Persiapan Awal
Sebelum memulai, pastikan laptop Anda sudah terinstall:
*   **PHP 8.4+** (Penting! Cek dengan `php -v`)
*   **Composer**
*   **Node.js & NPM**
*   **Laragon/XAMPP** (Untuk database MySQL)

---

## 🚀 Cara Instalasi (Langkah Demi Langkah)

Ikuti urutan perintah ini di terminal Anda:

### 1. Ambil Project
```bash
git clone https://github.com/muhammadainulfuady/elektronik-modern.git
cd elektronik-modern
```

### 2. Install Library & Setup File
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### 3. Setup Database
1. Buat database baru bernama `elektronik_modern` di phpMyAdmin.
2. Buka file `.env`, pastikan settingan database sudah benar:
   ```env
   DB_DATABASE=elektronik_modern
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### 4. Setup Pembayaran Midtrans (Sandbox)
1. Buat akun Midtrans dan ambil **Server Key** serta **Client Key** dari dashboard Sandbox.
2. Tambahkan konfigurasi berikut ke `.env` lokal (jangan commit atau membagikan key):
   ```env
   MIDTRANS_SERVER_KEY=SB-Mid-server-...
   MIDTRANS_CLIENT_KEY=SB-Mid-client-...
   MIDTRANS_IS_PRODUCTION=false
   ```
3. Atur URL notifikasi pada dashboard Midtrans ke `https://<domain-publik-anda>/midtrans/notification`.
   Untuk pengujian lokal, gunakan tunnel HTTPS yang mengarah ke aplikasi Laravel.
4. Uji alur pembayaran memakai transaksi dan metode simulasi Sandbox sebelum mengganti ke key Production.

Saat aplikasi siap live, gunakan key Production dan ubah `MIDTRANS_IS_PRODUCTION=true`. Jangan pernah menaruh Server Key di kode frontend.
Jika konfigurasi Laravel sedang di-cache, jalankan `php artisan config:clear` setelah mengubah `.env`.

### 5. Isi Data & Gambar
Jalankan ini secara berurutan:
```bash
php artisan migrate --seed
php artisan storage:link
php artisan fetch:wilayah
```
> **Catatan:** `fetch:wilayah` akan mendownload data alamat seluruh Indonesia. Tunggu sampai selesai (5-10 menit).

### 6. Jalankan Aplikasi
```bash
npm run build
php artisan serve
```
Buka browser: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🔐 Akun Login (Untuk Testing)

| Role | Email | Password |
| :--- | :--- | :--- |
| 🛍️ **Customer** | `fuady@gmail.com` | `password` |
| 💼 **Admin** | `angga@gmail.com` | `password` |
| 👑 **Owner** | `joni@gmail.com` | `password` |

---

## 💡 Masalah & Solusi (Troubleshooting)

*   **Tampilan Berantakan?** Jalankan `npm run build`.
*   **Gambar Produk Hilang?** Jalankan `php artisan storage:link`.
*   **Alamat Kosong?** Pastikan sudah menjalankan `php artisan fetch:wilayah` dengan internet aktif.
*   **PHP Version Error?** Pastikan PHP Anda sudah versi **8.4**. Jika di terminal masih versi lama, cek *Environment Variables* di Windows Anda.

---

Built with ❤️ by **RPL Team 2** (2026)
