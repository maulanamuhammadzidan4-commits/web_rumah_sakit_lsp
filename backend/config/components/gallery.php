<?php

require_once __DIR__ . '/../database.php';

function get_gallery(PDO $pdo): array{
  try {

    $stmt = $pdo->query("SELECT id, file_name, title, description FROM gallery");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

  } catch(PDOException $e) {
    error_log($e->getMessage());
    return ['error: gagal mengambil data gallery dari database'];
  }
}

?>