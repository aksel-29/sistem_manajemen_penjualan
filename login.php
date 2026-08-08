<?php
require 'db.php';

if (isset($_SESSION['username'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$rate_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF dulu
    if (!validate_csrf()) {
        $error = 'Sesi tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Rate limiting berdasarkan IP (5 percobaan / 5 menit)
        $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (function_exists('rate_limit_check')) {
            $rl = rate_limit_check('login:' . $client_ip, 5, 300);
            if (!$rl['allowed']) {
                $mins = ceil($rl['retry_after'] / 60);
                $rate_msg = 'Terlalu banyak percobaan login. Coba lagi dalam ' . $mins . ' menit.';
            }
        }

        if ($rate_msg === '' && ($username === '' || $password === '')) {
            $error = 'Masukkan username dan password.';
        } elseif ($rate_msg === '') {
            $stmt = $conn->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();

                if ($user && password_verify($password, $user['password'])) {
                    // Login sukses
                    session_regenerate_id(true); // cegah session fixation
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // reset token

                    if (function_exists('rate_limit_reset')) {
                        rate_limit_reset('login:' . $client_ip);
                    }
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Login gagal. Periksa username dan password.';
                }
            } else {
                $error = 'Terjadi kesalahan sistem. Silakan coba lagi nanti.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Axel Shop - Login</title>
<link rel="stylesheet" href="style.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="login-body">

<div class="login-box">
    <div class="login-outer">
    <div class="login-card">
        <h2>🎮 Axel Shop</h2>
        <p class="subtitle">Silahkan login untuk melanjutkan ke dashboard</p>

    <?php if (!empty($rate_msg)) : ?>
        <div class="login-error"><?= escape($rate_msg) ?></div>
    <?php elseif (!empty($error)) : ?>
        <div class="login-error"><?= escape($error) ?></div>
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'invalid') : ?>
        <div class="login-error">Username atau password salah.</div>
    <?php endif; ?>

    <form method="POST">
        <?= csrf_field() ?>
        <div class="field">
            <label>Username</label>
            <div class="input-icon">
                <span>👤</span>
                <input type="text" name="username" placeholder="Masukkan username" required autocomplete="username">
            </div>
        </div>
        <div class="field">
            <label>Password</label>
            <div class="input-icon">
                <span>🔒</span>
                <input type="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
            </div>
        </div>
        <button type="submit">Login</button>
    </form>

</div>

</body>
</html>
