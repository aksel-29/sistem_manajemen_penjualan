<?php
require 'db.php';
check_login();

$id = 0;
$game_name = '';
$account_detail = '';
$stock = '';
$buy_price = '';
$sell_price = '';
$success = '';
$error = '';

// ===== Aksi Delete (POST + CSRF) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!validate_csrf()) {
        $error = 'Sesi tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
        $delete_id = intval($_POST['delete_id']);
        $stmt = $conn->prepare('DELETE FROM accounts WHERE id = ?');
        $stmt->bind_param('i', $delete_id);
        $stmt->execute();
        $stmt->close();
        header('Location: accounts.php');
        exit;
    }
}

// ===== Aksi Tambah / Edit (POST + CSRF) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['game_name'])) {
    if (!validate_csrf()) {
        $error = 'Sesi tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
        $id = intval($_POST['id'] ?? 0);
        $game_name = trim($_POST['game_name'] ?? '');
        $account_detail = trim($_POST['account_detail'] ?? '');
        $stock = intval($_POST['stock'] ?? 0);
        $buy_price = floatval($_POST['buy_price'] ?? 0);
        $sell_price = floatval($_POST['sell_price'] ?? 0);

        if ($game_name === '' || $account_detail === '' || $stock < 0 || $buy_price < 0 || $sell_price < 0) {
            $error = 'Lengkapi data dengan benar.';
        } else {
            if ($id > 0) {
                $stmt = $conn->prepare('UPDATE accounts SET game_name=?, account_detail=?, stock=?, buy_price=?, sell_price=? WHERE id=?');
                $stmt->bind_param('ssiddi', $game_name, $account_detail, $stock, $buy_price, $sell_price, $id);
                $stmt->execute();
                $stmt->close();
                $success = 'Data akun game berhasil diperbarui.';
            } else {
                $stmt = $conn->prepare('INSERT INTO accounts (game_name, account_detail, stock, buy_price, sell_price) VALUES (?, ?, ?, ?, ?)');
                $stmt->bind_param('ssidd', $game_name, $account_detail, $stock, $buy_price, $sell_price);
                $stmt->execute();
                $stmt->close();
                $success = 'Akun game berhasil ditambahkan.';
            }
            header('Location: accounts.php');
            exit;
        }
    }
}

if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare('SELECT game_name, account_detail, stock, buy_price, sell_price FROM accounts WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($game_name, $account_detail, $stock, $buy_price, $sell_price);
    $stmt->fetch();
    $stmt->close();
}

$result = $conn->query('SELECT * FROM accounts ORDER BY id DESC');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Axel Shop - Stok Akun Game</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <div class="brand"><span class="brand-name">Axel Shop</span></div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="accounts.php" class="active">Stok Akun Game</a>
        <a href="top_up.php">Penjualan Top Up</a>
        <a href="rabbit.php">Penjualan Kelinci</a>
        <a class="logout" href="logout.php">Logout</a>
    </nav>
</header>
<main class="container">
    <section class="page-header">
        <h1>Stok Akun Game</h1>
        <p>Catat profit akun game dengan mudah.</p>
    </section>
    <?php if ($error): ?><div class="alert error"><?= escape($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert success"><?= escape($success) ?></div><?php endif; ?>

    <div class="split-grid">
        <!-- Form Tambah / Edit -->
        <div class="card">
            <h2><?= $id > 0 ? 'Edit Akun Game' : 'Tambah Akun Game' ?></h2>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= escape($id) ?>">
                <label>Nama Game</label>
                <input type="text" name="game_name" value="<?= escape($game_name) ?>" placeholder="Masukkan nama game" required>
                <label>Detail Akun</label>
                <textarea name="account_detail" placeholder="Detail spesifikasi akun game..." required><?= escape($account_detail) ?></textarea>
                <label>Stok</label>
                <input type="number" name="stock" min="0" value="<?= escape($stock) ?>" placeholder="Jumlah unit" required>
                <label>Harga Beli (Buy)</label>
                <input type="number" step="0.01" name="buy_price" min="0" value="<?= escape($buy_price) ?>" placeholder="Harga beli modal" required>
                <label>Harga Jual (Sell)</label>
                <input type="number" step="0.01" name="sell_price" min="0" value="<?= escape($sell_price) ?>" placeholder="Harga jual ecer" required>
                <button type="submit"><?= $id > 0 ? 'Perbarui Akun' : 'Tambah Akun' ?></button>
            </form>
        </div>

        <!-- Daftar Akun Game -->
        <div class="card">
            <h2>Daftar Akun Game</h2>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Game</th>
                            <th>Detail</th>
                            <th>Stok</th>
                            <th>Buy</th>
                            <th>Sell</th>
                            <th>Profit</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $has_data = false; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php $has_data = true; ?>
                            <?php $profit = $row['sell_price'] - $row['buy_price']; ?>
                            <tr>
                                <td><strong><?= escape($row['game_name']) ?></strong></td>
                                <td><?= escape($row['account_detail']) ?></td>
                                <td><?= escape($row['stock']) ?></td>
                                <td>Rp <?= number_format($row['buy_price'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($row['sell_price'], 0, ',', '.') ?></td>
                                <td><span class="profit-badge">Rp <?= number_format($profit, 0, ',', '.') ?></span></td>
                                <td class="actions">
                                    <a class="btn edit" href="accounts.php?edit=<?= $row['id'] ?>">Edit</a>
                                    <form method="post" class="inline-form">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn delete" onclick="return confirm('Hapus akun ini?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if (!$has_data): ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <span class="big">🎮</span>
                                    Belum ada data akun game.
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
