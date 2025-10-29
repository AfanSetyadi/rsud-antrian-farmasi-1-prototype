<?php
// Database configuration
$host = '10.10.1.90';
$dbname = 'db_kiosk_antrian_03'; // Sesuaikan dengan nama database Anda
$username = 'postgres'; // Sesuaikan dengan username database Anda
$password = 'postgres'; // Sesuaikan dengan password database Anda

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Print endpoint configuration
$print_endpoint = 'http://10.10.12.166/direct-print/pakai_usb.php';

// Loket configuration
$id_loket = 1; // ID untuk Farmasi 1
$id_jenis_antrian = 1; // ID untuk jenis antrian farmasi
?>