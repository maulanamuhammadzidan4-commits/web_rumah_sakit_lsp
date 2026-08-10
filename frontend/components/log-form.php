<?php
require_once __DIR__ . '/../../config.php';
?>

<form action="<?= BASE_URL; ?>backend/form/login.php" method="post">
    <input type="text" name="usname" id="user-name" placeholder="your name">
    <input type="password" name="pw" id="pass" placeholder="password">
    <button type="submit">Login</button>
</form>