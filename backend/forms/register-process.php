<?php

require_once __DIR__ . '/../../config.php';
require_once '../config/database.php';

$userName   = isset($_POST['usname']) ? trim($_POST['usname']) : '';
$passw      = isset($_POST['pw']) ? trim($_POST['pw']) : '';
$email      = isset($_POST['email']) ? trim($_POST['email']) : '';
$errors     = [];

$usNameRegEx = "/^[a-zA-Z0-9.,\s]+$/";

function pwCheck(string $pw): array {
  $miss = [];

  if (strlen($pw) < 8) $miss[] = 'Password minimal 8 karakter';
  if (!preg_match("/[A-Z]/", $pw)) $miss[] = 'Harus ada minimal satu huruf kapital';
  if (!preg_match("/[a-z]/", $pw)) $miss[] = 'Harus ada minimal satu huruf kecil';
  if (!preg_match("/\d/", $pw)) $miss[] = 'Harus ada minimal satu angka';
  if (!preg_match("/[@#$!%*?&]/", $pw)) $miss[] = 'Harus ada minimal satu simbol unik';

  return $miss;
}

#-------------------
# INPUT VALIDATION |
#-------------------

// Validasi Username
if (empty($userName)) {
  $errors[] = 'User name tidak boleh kosong';
} else if (!preg_match($usNameRegEx, $userName)) {
  $errors[] = 'Format nama hanya boleh berisi huruf dan spasi';
}

// Validasi Password
if (empty($passw)) {
  $errors[] = 'Password harus diisi';
} else {
  $pwErrors = pwCheck($passw);
  if (!empty($pwErrors)) {
    $errors = array_merge($errors, $pwErrors);
  }
}

// Validasi Email
if (empty($email)) {
  $errors[] = 'Email masih kosong';
} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errors[] = 'Format email tidak sesuai standar';
}

#-------------------
# DATABASE PROCESS |
#-------------------

if (count($errors) > 0) {
  foreach ($errors as $error) {
    echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "<br>";
  }
} else {
  try {
    $hashedPassword = password_hash($passw, PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (username, password_hash, email) VALUES (:username, :pass, :usemail)";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
      ':username'  => $userName,
      ':pass'    => $hashedPassword,
      ':usemail' => $email
    ]);

    $lastId = $pdo->lastInsertId();
    
    header("Location: " . BASE_URL . 'frontend/home.php');
    exit;

  } catch (PDOException $e) {
    error_log($e->getMessage());
    echo 'Gagal menambahkan data. Ada masalah pada database.';
  }
}
?>