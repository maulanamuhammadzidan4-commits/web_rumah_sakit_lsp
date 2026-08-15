<?php

session_start();

require_once '../config/database.php';
require_once '../../config.php';

$usName = isset($_POST['usname']) ? trim($_POST['usname']) : '';
$passw = isset($_POST['pw']) ? trim($_POST['pw']) :  '';
$errors = [];

#-------------------
# INPUT VALIDATION |
#-------------------
if (empty($usName)) $errors[] = 'Username tidak boleh kosong!';
if (empty($passw)) $errors[] = 'Password tidak boleh kosong!';

#-------------------
# DATABASE PROCESS |
#-------------------

if (count($errors) > 0){
  foreach ($errors as $error) {
    echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '<br>';
  }
} else {
  try{
    $sql = 'SELECT user_id, username, email, password_hash FROM users WHERE username = :username OR email = :email';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $usName, ':email' => $usName]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($passw, $user['password_hash'])){
      //pengamanan dari session fixation
      session_regenerate_id(true);

      //save user session
      $_SESSION['user_id'] = $user['user_id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['is_logged_in'] = true;

      //redirect ke halman utama
      header("Location: " . BASE_URL . "/frontend/home.php");
      exit;
    } else {
      echo 'Username/Email atau Password salah';
    }

  } catch (PDOException $e){
    error_log($e->getMessage());
    echo "Gagal melakukan login. Terjadi kendala pada server.";
  }
}

?>