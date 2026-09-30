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
git clone https://github.com/JayroFadil/test.git
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

### 4. Isi Data & Gambar
Jalankan ini secara berurutan:
```bash
php artisan migrate --seed
php artisan storage:link
php artisan fetch:wilayah
```
> **Catatan:** `fetch:wilayah` akan mendownload data alamat seluruh Indonesia. Tunggu sampai selesai (5-10 menit).

### 5. Jalankan Aplikasi
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
