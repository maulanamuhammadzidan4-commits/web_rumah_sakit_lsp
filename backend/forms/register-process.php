<?php
require_once __DIR__ . '/../../config.php';
require_once '../config/database.php';

$userName = isset($_POST['usname']) ? trim($_POST['usname']) : '';
$passw    = isset($_POST['pw']) ? trim($_POST['pw']) : '';
$email    = isset($_POST['email']) ? trim($_POST['email']) : '';
$errors   = [];

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
if (empty($userName)) {
  $errors[] = 'Username tidak boleh kosong';
} else if (!preg_match($usNameRegEx, $userName)) {
  $errors[] = 'Format nama hanya boleh berisi huruf dan spasi';
}

if (empty($passw)) {
  $errors[] = 'Password harus diisi';
} else {
  $pwErrors = pwCheck($passw);
  if (!empty($pwErrors)) {
    $errors = array_merge($errors, $pwErrors);
  }
}

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
    $checkSql = "SELECT user_id FROM users WHERE username = :username OR email = :email";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':username' => $userName, ':email' => $email]);
    
    if ($checkStmt->fetch()) {
      echo "Username atau Email sudah terdaftar. Silakan gunakan yang lain.";
      exit;
    }

    $pdo->beginTransaction();

    $hashedPassword = password_hash($passw, PASSWORD_BCRYPT);
    $sql = "INSERT INTO users (username, password_hash, email) VALUES (:username, :pass, :usemail)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':username' => $userName,
      ':pass'     => $hashedPassword,
      ':usemail'  => $email
    ]);

    $newUserId = $pdo->lastInsertId();

    $defaultRoleId = 3; 
    $roleSql = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";
    $roleStmt = $pdo->prepare($roleSql);
    $roleStmt->execute([
      ':user_id' => $newUserId,
      ':role_id' => $defaultRoleId
    ]);

    $pdo->commit();

    $loginPageUrl = rtrim(BASE_URL, '/') . '/frontend/pages/login-page.php';
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h3>Registrasi Berhasil!</h3>";
    echo "<p>Akun Anda telah terbuat. Silakan masuk untuk melanjutkan.</p>";
    echo "<a href='" . htmlspecialchars($loginPageUrl, ENT_QUOTES, 'UTF-8') . "' style='padding: 10px 20px; background: #059669; color: white; text-decoration: none; border-radius: 5px;'>Kembali ke Halaman Login</a>";
    echo "</div>";

  } catch (PDOException $e) {
    if ($pdo->inTransaction()) {
      $pdo->rollBack();
    }
    error_log($e->getMessage());
    echo 'Gagal menambahkan data. Terjadi kendala pada server.';
  }
}
?>