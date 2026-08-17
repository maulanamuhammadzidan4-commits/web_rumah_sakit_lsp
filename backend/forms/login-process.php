<?php
session_start();

require_once '../config/database.php';
require_once '../../config.php';

$usName = isset($_POST['usname']) ? trim($_POST['usname']) : '';
$passw  = isset($_POST['pw']) ? trim($_POST['pw']) : '';
$errors = [];

#-------------------
# INPUT VALIDATION |
#-------------------
if (empty($usName)) $errors[] = 'Username tidak boleh kosong!';
if (empty($passw))  $errors[] = 'Password tidak boleh kosong!';

#-------------------
# DATABASE PROCESS |
#-------------------
if (count($errors) > 0) {
  foreach ($errors as $error) {
    echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '<br>';
  }
} else {
  try {
    $sql = 'SELECT user_id, username, email, password_hash FROM users WHERE username = :username OR email = :email';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $usName, ':email' => $usName]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($passw, $user['password_hash'])) {
      session_regenerate_id(true);

      $rbacSql = "
        SELECT r.role_name, p.permission_name
        FROM user_roles ur
        JOIN roles r ON ur.role_id = r.id
        LEFT JOIN role_permissions rp ON r.id = rp.role_id
        LEFT JOIN permissions p ON rp.permission_id = p.id
        WHERE ur.user_id = :user_id
      ";
      $rbacStmt = $pdo->prepare($rbacSql);
      $rbacStmt->execute([':user_id' => $user['user_id']]);
      $rbacData = $rbacStmt->fetchAll(PDO::FETCH_ASSOC);

      $roles = [];
      $permissions = [];

      foreach ($rbacData as $row) {
        if (!empty($row['role_name']) && !in_array($row['role_name'], $roles, true)) {
          $roles[] = $row['role_name'];
        }
        if (!empty($row['permission_name']) && !in_array($row['permission_name'], $permissions, true)) {
          $permissions[] = $row['permission_name'];
        }
      }

      $_SESSION['user'] = [
        'id'          => $user['user_id'],
        'username'    => $user['username'],
        'roles'       => $roles,
        'permissions' => $permissions
      ];
      $_SESSION['is_logged_in'] = true;

      $redirectUrl = rtrim(BASE_URL, '/') . '/frontend/home.php';
      header("Location: " . $redirectUrl);
      exit;

    } else {
      echo 'Username/Email atau Password salah';
    }

  } catch (PDOException $e) {
    error_log($e->getMessage());
    echo "Gagal melakukan login. Terjadi kendala pada server.";
  }
}
?>