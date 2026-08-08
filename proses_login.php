<?php
/**
 * proses_login.php — Alur login terpusat & aman.
 * Dipakai sebagai jalur login yang konsisten (opsional).
 * Halaman login utama (login.php) sudah menangani login sendiri.
 */
require 'db.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

header('Location: dashboard.php');
exit;
?>
