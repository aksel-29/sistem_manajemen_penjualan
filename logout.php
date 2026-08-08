<?php
require 'db.php';

// Logout harus memakai POST + CSRF agar tidak mudah diserang (CSRF logout)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf()) {
    // Jika bukan POST (misal diklik dari link), tampilkan halaman konfirmasi logout
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        check_login(); // pastikan masih login untuk melihat konfirmasi
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
        <meta charset="utf-8">
        <title>Logout - Axel Shop</title>
        <link rel="stylesheet" href="style.css">
        </head>
        <body class="login-body">
        <div class="login-box">
            <div class="login-card">
                <h2>👋 Logout</h2>
                <p>Yakin ingin keluar dari Axel Shop?</p>
                <form method="POST">
                    <?= csrf_field() ?>
                    <button type="submit">Ya, Logout</button>
                    <a class="inline-btn" href="dashboard.php" style="display:inline-block;margin-top:10px;text-align:center;width:100%;box-sizing:border-box;">Batal</a>
                </form>
            </div>
        </div>
        </body>
        </html>
        <?php
        exit;
    }
    die('Permintaan tidak valid.');
}

session_unset();
session_destroy();
// Hapus cookie session
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
header('Location: login.php');
exit;
?>
