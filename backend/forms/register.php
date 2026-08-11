<?php
require_once '../config/database.php';
$userName = $_POST['usname'];
$passw = $_POST['pw'];
$email = $_POST['email'];

try{
  $sql = "INSERT INTO users (usname, pass, usemail) VALUES (:usname, :pass, :usemail);";
  $stmt = $pdo->prepare($sql);

  $stmt->execute([
    ':usname' => $userName,
    ':pass' => $passw,
    ':usemail' => $email
  ]);

  $lastId = $pdo->lastInsertId();
  echo 'Data berhasil ditambahkan!';
} catch (PDOException $e){
  echo 'Gagal menambahkan data: ' . $e->getMessage();
}
?>