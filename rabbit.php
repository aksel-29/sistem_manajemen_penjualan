<?php
require 'db.php';
check_login();

$id = 0;
$rabbit_type = '';
$quantity = '';
$unit_price = '';
$buy_price = '';
$customer = '';
$error = '';

// ===== Aksi Delete (POST + CSRF) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!validate_csrf()) {
        $error = 'Sesi tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
        $delete_id = intval($_POST['delete_id']);
        $stmt = $conn->prepare('DELETE FROM rabbit WHERE id = ?');
        $stmt->bind_param('i', $delete_id);
        $stmt->execute();
        $stmt->close();
        header('Location: rabbit.php');
        exit;
    }
}

// ===== Aksi Tambah / Edit (POST + CSRF) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rabbit_type'])) {
    if (!validate_csrf()) {
        $error = 'Sesi tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
        $id = intval($_POST['id'] ?? 0);
        $rabbit_type = trim($_POST['rabbit_type'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 0);
        $unit_price = floatval($_POST['unit_price'] ?? 0);
        $buy_price = floatval($_POST['buy_price'] ?? 0);
        $customer = trim($_POST['customer'] ?? '');

        if ($rabbit_type === '' || $quantity <= 0 || $unit_price <= 0 || $customer === '') {
            $error = 'Lengkapi semua kolom yang wajib.';
        } else {
            if ($id > 0) {
                $stmt = $conn->prepare('UPDATE rabbit SET rabbit_type=?, quantity=?, unit_price=?, buy_price=?, customer=? WHERE id=?');
                $stmt->bind_param('siddsi', $rabbit_type, $quantity, $unit_price, $buy_price, $customer, $id);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $conn->prepare('INSERT INTO rabbit (rabbit_type, quantity, unit_price, buy_price, customer) VALUES (?, ?, ?, ?, ?)');
                $stmt->bind_param('sidds', $rabbit_type, $quantity, $unit_price, $buy_price, $customer);
                $stmt->execute();
                $stmt->close();
            }
            header('Location: rabbit.php');
            exit;
        }
    }
}

if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare('SELECT rabbit_type, quantity, unit_price, buy_price, customer FROM rabbit WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($rabbit_type, $quantity, $unit_price, $buy_price, $customer);
    $stmt->fetch();
    $stmt->close();
}

$result = $conn->query('SELECT * FROM rabbit ORDER BY id DESC');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Axel Shop - Penjualan Kelinci</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <div class="brand"><span class="brand-name">Axel Shop</span></div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="accounts.php">Stok Akun Game</a>
        <a href="top_up.php">Penjualan Top Up</a>
        <a href="rabbit.php" class="active">Penjualan Kelinci</a>
        <a class="logout" href="logout.php">Logout</a>
    </nav>
</header>
<main class="container">
    <section class="page-header">
        <h1>Penjualan Kelinci</h1>
        <p>Atur catatan kelinci dan transaksi penjualan dengan rapi.</p>
    </section>
    <?php if ($error): ?><div class="alert error"><?= escape($error) ?></div><?php endif; ?>
    <div class="split-grid">
        <div class="card">
            <h2><?= $id > 0 ? 'Edit Kelinci' : 'Tambah Penjualan Kelinci' ?></h2>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= escape($id) ?>">
                <label>Jenis Kelinci</label>
                <input type="text" name="rabbit_type" value="<?= escape($rabbit_type) ?>" placeholder="Contoh: Angora / Rex" required>
                <label>Jumlah</label>
                <input type="number" name="quantity" min="1" value="<?= escape($quantity) ?>" placeholder="Jumlah ekor" required>
                <label>Harga Satuan</label>
                <input type="number" step="0.01" name="unit_price" value="<?= escape($unit_price) ?>" placeholder="Harga per ekor" required>
                <label>Modal (Beli)</label>
                <input type="number" step="0.01" name="buy_price" value="<?= escape($buy_price) ?>" placeholder="Modal per ekor">
                <label>Nama Pembeli</label>
                <input type="text" name="customer" value="<?= escape($customer) ?>" placeholder="Nama pembeli" required>
                <button type="submit"><?= $id > 0 ? 'Perbarui Data' : 'Tambah Data' ?></button>
            </form>
        </div>
        <div class="card">
            <h2>Daftar Penjualan</h2>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                            <th>Harga Jual</th>
                            <th>Modal</th>
                            <th>Profit</th>
                            <th>Pembeli</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $has_data = false; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php $has_data = true; ?>
                            <?php $profit = ($row['unit_price'] - $row['buy_price']) * $row['quantity']; ?>
                            <tr>
                                <td><strong><?= escape($row['rabbit_type']) ?></strong></td>
                                <td><?= escape($row['quantity']) ?> ekor</td>
                                <td>Rp <?= number_format($row['unit_price'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($row['buy_price'], 0, ',', '.') ?></td>
                                <td><span class="profit-badge">Rp <?= number_format($profit, 0, ',', '.') ?></span></td>
                                <td><?= escape($row['customer']) ?></td>
                                <td class="actions">
                                    <a class="btn edit" href="rabbit.php?edit=<?= $row['id'] ?>">Edit</a>
                                    <form method="post" class="inline-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn delete" onclick="return confirm('Hapus data penjualan?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (!$has_data): ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <span class="big">🐇</span>
                                    Belum ada data penjualan kelinci.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
</body>
</html>
