<?php
// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'username_db');
define('DB_PASS', 'password_db');
define('DB_NAME', 'nama_database');

// Membuat koneksi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");
?>