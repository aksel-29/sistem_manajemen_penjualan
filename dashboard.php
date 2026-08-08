<?php
require 'db.php';
check_login();

// ===== Laba Bersih (Profit) =====
// Profit Akun Game (estimasi dari stok: sell - buy)
$profit_akun = 0;
$res = $conn->query('SELECT COALESCE(SUM(sell_price - buy_price),0) AS profit FROM accounts');
if ($res) { $row = $res->fetch_assoc(); $profit_akun = (float)$row['profit']; }

// Profit Top Up: harga jual (amount) - modal (buy_price)
$profit_topup = 0;
$res = $conn->query('SELECT COALESCE(SUM(amount - buy_price),0) AS profit FROM top_up');
if ($res) { $row = $res->fetch_assoc(); $profit_topup = (float)$row['profit']; }

// Profit Kelinci: (harga jual - modal) * jumlah
$profit_kelinci = 0;
$res = $conn->query('SELECT COALESCE(SUM((unit_price - buy_price) * quantity),0) AS profit FROM rabbit');
if ($res) { $row = $res->fetch_assoc(); $profit_kelinci = (float)$row['profit']; }

$total_profit = $profit_akun + $profit_topup + $profit_kelinci;

function rupiah($num) {
    return 'Rp ' . number_format($num, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Axel Shop - Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <div class="brand"><span class="brand-name">Axel Shop</span></div>
    <nav>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="accounts.php">Stok Akun Game</a>
        <a href="top_up.php">Penjualan Top Up</a>
        <a href="rabbit.php">Penjualan Kelinci</a>
        <a class="logout" href="logout.php">Logout</a>
    </nav>
</header>
<main class="container">
    <section class="hero">
        <h1>Selamat datang, <?= escape($_SESSION['username']) ?>!</h1>
        <p>Catat semua penjualan dan stok dengan mudah di Axel Shop.</p>
    </section>
    <section class="stats-row">
        <div class="stat-card">
            <div class="stat-icon">🎮</div>
            <div class="stat-label">Profit Akun Game</div>
            <div class="stat-num"><?= rupiah($profit_akun) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💎</div>
            <div class="stat-label">Profit Top Up</div>
            <div class="stat-num"><?= rupiah($profit_topup) ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🐇</div>
            <div class="stat-label">Profit Kelinci</div>
            <div class="stat-num"><?= rupiah($profit_kelinci) ?></div>
        </div>
        <div class="stat-card stat-total">
            <div class="stat-icon">💰</div>
            <div class="stat-label">Total Profit</div>
            <div class="stat-num"><?= rupiah($total_profit) ?></div>
        </div>
    </section>
    <div class="cards-grid">
        <article class="card">
            <h2>🎮 Stok Akun Game</h2>
            <p>Kelola daftar akun game beserta stok, harga beli, dan harga jual.</p>
            <a class="inline-btn" href="accounts.php">Buka Stok Akun</a>
        </article>
        <article class="card">
            <h2>💎 Penjualan Top Up</h2>
            <p>Catat setiap transaksi top up dengan detail layanan dan pembeli.</p>
            <a class="inline-btn" href="top_up.php">Buka Top Up</a>
        </article>
        <article class="card">
            <h2>🐇 Penjualan Kelinci</h2>
            <p>Catat penjualan kelinci beserta jenis, jumlah, dan harga satuan.</p>
            <a class="inline-btn" href="rabbit.php">Buka Kelinci</a>
        </article>
    </div>
</main>
</body>
</html>

