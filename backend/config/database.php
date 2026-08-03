<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
    $dotenv->load();

    $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);
} catch (\Exception $e) {
    die("Gagal membuat konfigurasi lingkungan: " . $e->getMessage());
}

$dbHost = $_ENV['DB_HOST'];
$dbPort = $_ENV['DB_PORT'] ?? '3306';
$dbName = $_ENV['DB_NAME'];
$dbUser = $_ENV['DB_USER'];
$dbPass = $_ENV['DB_PASS'];

try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lempar exception jika terjadi error
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Array asosiatif
        PDO::ATTR_EMULATE_PREPARES   => false,                 // Menggunakan prepared statement asli
    ];

    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Koneksi database gagal");
}