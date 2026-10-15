# Axel Shop

Axel Shop adalah sistem manajemen penjualan yang fungsional, ringan, dan aman untuk mencatat serta memantau keuntungan dari tiga lini usaha:

- Akun game
- Top up
- Kelinci

Project ini dibuat untuk membantu pemilik usaha mencatat penjualan dan menghitung profit secara terpusat dari laptop atau ponsel.

## Fitur utama

- Dashboard profit per lini usaha
- Manajemen stok akun game
- Pencatatan transaksi top up
- Pencatatan penjualan kelinci
- Autentikasi login user
- Proteksi basic terhadap CSRF dan rate limiting login

## Teknologi yang digunakan

- PHP
- MySQL / MariaDB
- HTML + CSS + PHP native

## Struktur proyek

Project saat ini masih menggunakan struktur sederhana, dengan file utama berada di root folder untuk memudahkan pengembangan awal.

Berikut gambaran umum:

```text
.
├── README.md
├── .gitignore
├── accounts.php
├── dashboard.php
├── login.php
├── logout.php
├── proses_login.php
├── rabbit.php
├── top_up.php
├── style.css
├── .env.example
└── db.php  (sesuaikan dengan konfigurasi database lokal Anda)
```

Catatan:
- Untuk rilis yang lebih rapi, project bisa diorganisir ke folder `public/`, `src/`, `views/`, `config/`, dan `assets/`.
- Saat ini, fokus utama adalah kemudahan penggunaan dan dokumentasi agar repo mudah dibagikan.

## Persyaratan

- PHP 8.x
- MySQL/MariaDB
- Web server (Apache/Nginx) atau PHP built-in server

## Langkah instalasi

1. Clone repository

```bash
git clone https://github.com/aksel-29/sistem_manajemen_penjualan.git
cd sistem_manajemen_penjualan
```

2. Siapkan database MySQL

Buat database baru, misalnya:

```sql
CREATE DATABASE axel_shop;
```

3. Siapkan koneksi database

Buat file `db.php` berdasarkan kebutuhan lokal Anda, atau gunakan konfigurasi yang sesuai untuk project Anda.

Contoh koneksi dasar:

```php
<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'axel_shop';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die('Koneksi database gagal: ' . $conn->connect_error);
}
```

4. Jalankan aplikasi

Gunakan server lokal PHP atau setup Apache/Nginx.

Contoh dengan PHP built-in server:

```bash
php -S localhost:8000
```

Lalu buka di browser:

```text
http://localhost:8000/login.php
```

## Konfigurasi keamanan

- Jangan commit credential database ke repository.
- Gunakan file konfigurasi lokal atau `.env` yang disimpan di environment.
- Pastikan `db.php` tidak menaruh password sensitif di repo publik.

## Catatan penggunaan

Project ini cocok untuk penggunaan internal usaha kecil menengah yang ingin mengelola penjualan dan profit secara sederhana tanpa setup yang terlalu kompleks.

## Lisensi

Project ini belum ditentukan lisensinya. Jika ingin dibagikan secara umum, disarankan menambahkan lisensi seperti MIT.
