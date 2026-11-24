# MARISIGHT — Marine Information Dashboard (Pulau Tunda, Banten)

Platform web informasi kelautan terpadu berbasis **PHP & MySQL** untuk pemantauan wilayah **Pulau Tunda, Banten** (`-5.8125, 106.2799`).

## Fitur Utama
- **Beranda (`index.php`):** Dashboard dengan navbar responsif (desktop + mobile), jam WIB realtime, cuaca realtime + animasi cuaca, dan akses cepat ke seluruh fitur.
- **Kondisi Perairan:**
  - `cuaca.php` — Cuaca realtime Pulau Tunda (Open-Meteo API + pemetaan kode WMO) dan cuaca berdasarkan geolokasi pengguna.
  - `ombak.php` — Tinggi, periode, arah gelombang, angin, serta grafik prakiraan 72 jam (Open-Meteo Marine API + Chart.js).
  - `spl.php` — Peta interaktif Suhu Permukaan Laut berbasis Leaflet & dataset lokal `data/SPLL.geojson` (302 zona grid).
- **Pasang Surut & Batimetri:**
  - `pasut_php/grafik.php` & `pasut_php/tabel.php` — Grafik dan tabel pasang surut harian + estimasi intensitas ikan.
  - `pasut_php/api/ambil_pasut.php` — Endpoint JSON API data pasang surut.
  - `pasut_php/generate_pasut.php` — Generator prediksi pasang surut harmonik dinamis (terproteksi login + CSRF / CLI).
  - `bath.php`, `contour.php`, `profile.php`, `surface.php` — Peta lokasi survei, 15 dataset batimetri (gabungan, kontur, profil kedalaman, Surface 3D), zoom modal, serta unduh massal ZIP (`bath.php?zip=1`) yang bekerja otomatis bahkan bila `extension=zip` belum aktif di XAMPP.
- **Solunar:**
  - `waktu_bulan.php` — Perhitungan astronomi otomatis realtime (SunCalc) untuk jam terbit bulan, terbenam bulan, iluminasi, dan fase bulan sesuai tanggal pilihan.
  - `fase_bulan.php` — Panduan 8 siklus fase bulan dengan penanda otomatis fase aktif hari ini.
- **Autentikasi Opsional:** `login.php`, `register.php`, `logout.php` menggunakan Prepared Statements, `password_hash`/`password_verify`, dan token CSRF. Semua fitur informasi publik dapat dibuka langsung tanpa wajib login.

## Panduan Instalasi (XAMPP / Lokal)

1. **Letakkan project di Web Root (atau jalankan lewat PHP built-in server):**
   - Salin folder `marisight/` ke `C:\xampp\htdocs\marisight` **atau** jalankan dari folder ini:
     ```bash
     php -S localhost:8000 -t marisight
     ```
2. **Aktifkan MySQL di XAMPP (Database Terbuat Otomatis!):**
   - Cukup nyalakan service **MySQL** di XAMPP Control Panel.
   - Saat halaman yang membutuhkan database pertama kali dibuka (`login.php`, `register.php`, `pasut_php/grafik.php`, `pasut_php/tabel.php`), `config/database.php` **otomatis membuat database `marisight`, tabel `users` & `data_pasut`, serta mengisi 15 hari data pasang surut awal mulai hari ini**.
   - *(Opsional)* File **`database/schema.sql`** tetap tersedia jika Anda ingin mengimpor skema secara manual lewat phpMyAdmin.
3. **Konfigurasi Lingkungan (Opsional):**
   - Salin `.env.example` menjadi `.env` jika host/user/password MySQL berbeda dari default XAMPP (`localhost`, `root`, password kosong, DB `marisight`).
4. **Perbarui Data Pasang Surut via CLI (Opsional):**
   ```bash
   php marisight/pasut_php/generate_pasut.php 30
   ```
5. **Buka di Browser:**
   - `http://localhost/marisight/` (XAMPP) atau `http://localhost:8000/`.
