<?php
require_once __DIR__ . '/../../config.php';
?>
<form action="<?= BASE_URL ?>backend/form/register.php" method="post">
    <input type="text" name="usname" id="user-name" placeholder="your name">
    <input type="email" name="email" id="email" placeholder="example1@gmail.com">
    <input type="password" name="pw" id="pass">
    <button type="submit">Register</button>
</form>