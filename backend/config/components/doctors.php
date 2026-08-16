<?php

require_once __DIR__ . '/../database.php';

function get_doctors(PDO $pdo): array{
  try {

    $stmt = $pdo->query("SELECT id, nama_dokter, spesialis, klinik, foto FROM dokter");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

  } catch(PDOException $e) {
    error_log($e->getMessage());
    return ['error: gagal mengambil data dokter dari database'];
  }
}

?>