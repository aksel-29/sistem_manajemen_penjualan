<?php
require 'db.php';
check_login();

$id = 0;
$service_name = '';
$note = '';
$amount = '';
$buy_price = '';
$paid_by = '';
$error = '';

// ===== Aksi Delete (POST + CSRF) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!validate_csrf()) {
        $error = 'Sesi tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
        $delete_id = intval($_POST['delete_id']);
        $stmt = $conn->prepare('DELETE FROM top_up WHERE id = ?');
        $stmt->bind_param('i', $delete_id);
        $stmt->execute();
        $stmt->close();
        header('Location: top_up.php');
        exit;
    }
}

// ===== Aksi Tambah / Edit (POST + CSRF) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_name'])) {
    if (!validate_csrf()) {
        $error = 'Sesi tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
        $id = intval($_POST['id'] ?? 0);
        $service_name = trim($_POST['service_name'] ?? '');
        $note = trim($_POST['note'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);
        $buy_price = floatval($_POST['buy_price'] ?? 0);
        $paid_by = trim($_POST['paid_by'] ?? '');

        if ($service_name === '' || $amount <= 0 || $paid_by === '') {
            $error = 'Lengkapi semua kolom yang wajib.';
        } else {
            if ($id > 0) {
                $stmt = $conn->prepare('UPDATE top_up SET service_name=?, note=?, amount=?, buy_price=?, paid_by=? WHERE id=?');
                $stmt->bind_param('ssddsi', $service_name, $note, $amount, $buy_price, $paid_by, $id);
                if (!$stmt->execute()) {
                    error_log('top_up UPDATE error: ' . $stmt->error);
                }
                $stmt->close();
            } else {
                $stmt = $conn->prepare('INSERT INTO top_up (service_name, note, amount, buy_price, paid_by) VALUES (?, ?, ?, ?, ?)');
                $stmt->bind_param('ssdds', $service_name, $note, $amount, $buy_price, $paid_by);
                if (!$stmt->execute()) {
                    error_log('top_up INSERT error: ' . $stmt->error);
                }
                $stmt->close();
            }
            header('Location: top_up.php');
            exit;
        }
    }
}

if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare('SELECT service_name, note, amount, buy_price, paid_by FROM top_up WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($service_name, $note, $amount, $buy_price, $paid_by);
    $stmt->fetch();
    $stmt->close();
}

$result = $conn->query("SELECT * FROM top_up ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Axel Shop - Penjualan Top Up</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <div class="brand"><span class="brand-name">Axel Shop</span></div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="accounts.php">Stok Akun Game</a>
        <a href="top_up.php" class="active">Penjualan Top Up</a>
        <a href="rabbit.php">Penjualan Kelinci</a>
        <a class="logout" href="logout.php">Logout</a>
    </nav>
</header>
<main class="container">
    <section class="page-header">
        <h1>Penjualan Top Up</h1>
        <p>Catat penjualan top up dengan mudah.</p>
    </section>
    <?php if ($error): ?><div class="alert error"><?= escape($error) ?></div><?php endif; ?>
    <div class="split-grid">
        <div class="card">
            <h2><?= $id > 0 ? 'Edit Top Up' : 'Tambah Top Up' ?></h2>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= escape($id) ?>">
                <label>Nama Layanan</label>
                <input type="text" name="service_name" value="<?= escape($service_name) ?>" placeholder="Contoh: 100 Diamonds MLBB" required>
                <label>Keterangan</label>
                <textarea name="note" placeholder="ID Game / Keterangan tambahan..."><?= escape($note) ?></textarea>
                <label>Harga Jual</label>
                <input type="number" step="0.01" name="amount" value="<?= escape($amount) ?>" placeholder="Harga jual" required>
                <label>Modal (Beli)</label>
                <input type="number" step="0.01" name="buy_price" value="<?= escape($buy_price) ?>" placeholder="Modal harga beli">
                <label>Pembeli</label>
                <input type="text" name="paid_by" value="<?= escape($paid_by) ?>" placeholder="Nama pembeli / nomor WA" required>
                <button type="submit"><?= $id > 0 ? 'Perbarui Top Up' : 'Tambah Top Up' ?></button>
            </form>
        </div>
        <div class="card">
            <h2>Daftar Penjualan Top Up</h2>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Layanan</th>
                            <th>Keterangan</th>
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
                            <?php $profit = $row['amount'] - $row['buy_price']; ?>
                            <tr>
                                <td><strong><?= escape($row['service_name']) ?></strong></td>
                                <td><?= escape($row['note']) ?></td>
                                <td>Rp <?= number_format($row['amount'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($row['buy_price'], 0, ',', '.') ?></td>
                                <td><span class="profit-badge">Rp <?= number_format($profit, 0, ',', '.') ?></span></td>
                                <td><?= escape($row['paid_by']) ?></td>
                                <td class="actions">
                                    <a class="btn edit" href="top_up.php?edit=<?= $row['id'] ?>">Edit</a>
                                    <form method="post" class="inline-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn delete" onclick="return confirm('Hapus data top up?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (!$has_data): ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <span class="big">💎</span>
                                    Belum ada data penjualan top up.
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
