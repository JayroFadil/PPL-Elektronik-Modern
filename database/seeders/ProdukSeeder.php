<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProdukSeeder extends Seeder
{
    /**
     * Harga pasaran Indonesia (dalam Rupiah) per produk.
     * Sumber: estimasi harga retail resmi & marketplace Indonesia 2025.
     */
    private const HARGA_PRODUK = [
        // ── HANDPHONE ──
        'infinix-smart-20' => 1299000,   // Infinix Smart 20 — entry-level
        'iphone-13' => 9499000,   // iPhone 13 128GB
        'iphone-17' => 18999000,  // iPhone 17 256GB
        'realme-c35' => 1899000,   // Realme C35 4/64GB
        'redmi-note-10-pro' => 3299000,   // Redmi Note 10 Pro 6/128GB
        'redmi-note-13-pro' => 3799000,   // Redmi Note 13 Pro 8/256GB

        // ── KABEL ──
        'kabel-vga-to-hdmi' => 75000,     // Kabel Adapter VGA to HDMI
        'kabel-hdmi' => 45000,     // Kabel HDMI 1.5m
        'kabel-tembaga' => 35000,     // Kabel Tembaga listrik per meter
        'kabel-vga' => 55000,     // Kabel VGA 1.5m

        // ── KAMERA ──
        'canon' => 8499000,   // Canon EOS / PowerShot series
        'sony' => 12999000,  // Sony Alpha mirrorless

        // ── KULKAS ──
        'kulkas-sony' => 4599000,   // Kulkas 2 pintu (brand lokal/umum)

        // ── LAPTOP ──
        'asus-ideaped' => 6999000,   // ASUS IdeaPad entry-level
        'asus-rog' => 24999000,  // ASUS ROG gaming laptop
        'asus-tuf' => 14999000,  // ASUS TUF Gaming
        'asus-vivobook' => 7499000,   // ASUS VivoBook 14
        'hp-victus' => 12499000,  // HP Victus Gaming 15
        'lenovo-loq' => 11999000,  // Lenovo LOQ Gaming

        // ── TV ──
        'tv-polytron' => 2799000,   // Polytron LED TV 32"
        'tv-sharp' => 3499000,   // Sharp Aquos LED TV 42"
        'tv-sony' => 7999000,   // Sony Bravia Smart TV 43"
    ];

    private const BERAT_PRODUK = [
        'infinix-smart-20' => 1500,
        'iphone-13' => 500,
        'iphone-17' => 500,
        'realme-c35' => 500,
        'redmi-note-10-pro' => 500,
        'redmi-note-13-pro' => 500,
        'kabel-vga-to-hdmi' => 300,
        'kabel-hdmi' => 250,
        'kabel-tembaga' => 1000,
        'kabel-vga' => 300,
        'canon' => 1000,
        'sony' => 1000,
        'kulkas-sony' => 40000,
        'asus-ideaped' => 2500,
        'asus-rog' => 3000,
        'asus-tuf' => 3000,
        'asus-vivobook' => 2500,
        'hp-victus' => 3000,
        'lenovo-loq' => 3000,
        'tv-polytron' => 8000,
        'tv-sharp' => 8000,
        'tv-sony' => 10000,
    ];

    /**
     * Deskripsi spesifik per produk.
     */
    private const DESKRIPSI_PRODUK = [
        // ── HANDPHONE ──
        'infinix-smart-20' => 'Infinix Smart 20 dengan layar 6.6" HD+, baterai 5000mAh tahan seharian, dan kamera 8MP. Pilihan tepat untuk kebutuhan harian Anda.',
        'iphone-13' => 'iPhone 13 128GB dengan chip A15 Bionic, kamera dual 12MP, layar Super Retina XDR 6.1", dan dukungan 5G. Garansi resmi iBox.',
        'iphone-17' => 'iPhone 17 256GB terbaru dengan chip A19, kamera 48MP ProMotion, layar Dynamic Island, dan desain titanium premium. Garansi resmi.',
        'realme-c35' => 'Realme C35 4/64GB dengan desain slim, layar FHD+ 6.6", kamera 50MP AI Triple Camera, dan prosesor Unisoc T616. Cocok untuk multimedia.',
        'redmi-note-10-pro' => 'Redmi Note 10 Pro 6/128GB dengan kamera 108MP, layar Super AMOLED 120Hz, Snapdragon 732G, dan fast charging 33W. Best value mid-range.',
        'redmi-note-13-pro' => 'Redmi Note 13 Pro 8/256GB dengan kamera 200MP OIS, layar AMOLED 120Hz, Snapdragon 7s Gen 2, dan NFC. Performa flagship di kelas menengah.',

        // ── KABEL ──
        'kabel-vga-to-hdmi' => 'Kabel Adapter VGA to HDMI converter, mendukung resolusi Full HD 1080p. Kompatibel dengan laptop, PC, dan proyektor. Plug & play.',
        'kabel-hdmi' => 'Kabel HDMI 1.5 meter versi 2.0, mendukung resolusi 4K@60Hz dan audio pass-through. Cocok untuk TV, monitor, PS5, dan laptop.',
        'kabel-tembaga' => 'Kabel tembaga murni untuk instalasi listrik, tahan panas dan konduktivitas tinggi. Tersedia per meter, standar SNI.',
        'kabel-vga' => 'Kabel VGA 1.5 meter male-to-male, mendukung resolusi hingga 1920x1080. Ideal untuk monitor, proyektor, dan presentasi.',

        // ── KAMERA ──
        'canon' => 'Kamera Canon dengan sensor CMOS berkualitas tinggi, kemampuan video Full HD, dan lensa serbaguna. Garansi resmi Datascrip.',
        'sony' => 'Kamera Sony Alpha mirrorless dengan sensor full-frame, autofocus cepat, stabilisasi gambar 5-axis, dan kemampuan video 4K. Garansi resmi Sony Indonesia.',

        // ── KULKAS ──
        'kulkas-sony' => 'Kulkas 2 pintu dengan kapasitas besar, teknologi inverter hemat listrik, freezer atas, dan desain elegan. Garansi kompresor 10 tahun.',

        // ── LAPTOP ──
        'asus-ideaped' => 'ASUS IdeaPad dengan prosesor Intel Core i3, RAM 8GB, SSD 256GB, dan layar 14" FHD. Laptop ringan untuk produktivitas harian dan belajar.',
        'asus-rog' => 'ASUS ROG Gaming Laptop dengan prosesor Intel Core i9, RTX 4060, RAM 16GB, SSD 512GB, layar 15.6" 144Hz. Mesin gaming ultimate.',
        'asus-tuf' => 'ASUS TUF Gaming dengan prosesor AMD Ryzen 7, GTX 1650, RAM 8GB, SSD 512GB, layar 15.6" 144Hz. Tangguh untuk gaming dan multitasking.',
        'asus-vivobook' => 'ASUS VivoBook 14 dengan Intel Core i5, RAM 8GB, SSD 512GB, layar 14" FHD IPS. Desain tipis ringan dengan NumberPad.',
        'hp-victus' => 'HP Victus 15 Gaming dengan Intel Core i5-12500H, RTX 3050, RAM 8GB, SSD 512GB, layar 15.6" FHD 144Hz. Performa gaming solid.',
        'lenovo-loq' => 'Lenovo LOQ 15 Gaming dengan Intel Core i5-13420H, RTX 3050, RAM 8GB, SSD 512GB. Laptop gaming entry-level dengan performa kuat.',

        // ── TV ──
        'tv-polytron' => 'Polytron LED TV 32" HD Ready dengan speaker tower, USB movie, dan desain frameless modern. Hemat listrik dan cocok untuk ruang keluarga.',
        'tv-sharp' => 'Sharp Aquos LED TV 42" Full HD dengan Aquos Net+, Google Assistant, Dolby Audio, dan desain ultra-slim. Smart TV pilihan keluarga.',
        'tv-sony' => 'Sony Bravia Smart TV 43" 4K HDR dengan prosesor X1, Google TV, Dolby Vision & Atmos, dan desain minimalis. Pengalaman sinema di rumah.',
    ];

    public function run(): void
    {
        $sourceDir = base_path('ASSETS/BARANG');
        $destDir = storage_path('app/public/products');

        if (!File::exists($destDir)) {
            File::makeDirectory($destDir, 0755, true);
        }

        // Map folder name to Kategori ID
        $kategoriMap = [
            'HANDPHONE' => 1,
            'KABEL' => 2,
            'KAMERA' => 3,
            'KULKAS' => 4,
            'LAPTOP' => 5,
            'TV' => 6,
        ];

        $rows = [];
        $id = 1;

        foreach ($kategoriMap as $folderName => $kategoriId) {
            $folderPath = $sourceDir . '/' . $folderName;

            if (File::isDirectory($folderPath)) {
                $files = File::files($folderPath);

                foreach ($files as $file) {
                    $filename = $file->getFilename();
                    $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

                    // Generate a readable name from filename (e.g., asus-ideaped -> Asus Ideaped)
                    $namaProduk = ucwords(str_replace('-', ' ', $nameWithoutExt));

                    // Copy file to storage
                    File::copy($file->getPathname(), $destDir . '/' . $filename);

                    // Ambil harga dari price map, fallback ke range per kategori
                    $harga = self::HARGA_PRODUK[$nameWithoutExt] ?? $this->hargaFallback($kategoriId);

                    // Ambil deskripsi spesifik, fallback ke deskripsi generik
                    $deskripsi = self::DESKRIPSI_PRODUK[$nameWithoutExt]
                        ?? 'Produk unggulan ' . $namaProduk . ' dengan kualitas terbaik dan garansi resmi.';
                    $beratGram = self::BERAT_PRODUK[$nameWithoutExt]
                        ?? $this->beratFallback($kategoriId);

                    $rows[] = [
                        'id_produk' => $id++,
                        'id_kategori' => $kategoriId,
                        'gambar' => $filename,
                        'nama_produk' => $namaProduk,
                        'deskripsi' => $deskripsi,
                        'harga' => $harga,
                        'stok' => rand(10, 100),
                        'berat_gram' => $beratGram,
                    ];
                }
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('produks')->truncate();
        DB::table('produks')->insert($rows);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Harga fallback berdasarkan kategori jika produk tidak ada di price map.
     */
    private function hargaFallback(int $kategoriId): int
    {
        return match ($kategoriId) {
            1       => rand(15, 50) * 100000,   // Handphone: 1.5jt - 5jt
            2       => rand(2, 15) * 10000,     // Kabel: 20rb - 150rb
            3       => rand(50, 150) * 100000,  // Kamera: 5jt - 15jt
            4       => rand(25, 80) * 100000,   // Kulkas: 2.5jt - 8jt
            5       => rand(50, 250) * 100000,  // Laptop: 5jt - 25jt
            6       => rand(20, 100) * 100000,  // TV: 2jt - 10jt
            default => rand(10, 50) * 100000,
        };
    }

    private function beratFallback(int $kategoriId): int
    {
        return match ($kategoriId) {
            1 => 1500,
            2 => 500,
            3 => 1000,
            4 => 40000,
            5 => 2500,
            6 => 8000,
            default => 1000,
        };
    }
}
