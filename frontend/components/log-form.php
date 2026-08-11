<?php
require_once __DIR__ . '/../../config.php';
?>

<form action="<?= BASE_URL; ?>backend/forms/login.php" method="post" class="auth-form" id="usData">
    <h3 class="text-center mb-4">Login</h3>
    <div class="mb-3">
        <label for="login-username" class="form-label">Username</label>
        <input type="text" name="usname" id="login-username" class="form-control" placeholder="Masukkan username" required>
    </div>
    <div class="mb-3">
        <label for="login-pass" class="form-label">Password</label>
        <input type="password" name="pw" id="login-pass" class="form-control" placeholder="Masukkan password" required>
    <div id="error-box"></div>
    </div>
    <button type="submit" class="btn btn-accent w-100 mt-2">Login</button>
</form>