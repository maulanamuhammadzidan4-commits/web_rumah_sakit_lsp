<?php
require_once __DIR__ . '/../../config.php';
?>

<form action="<?= BASE_URL ?>backend/form/register.php" method="post" class="auth-form">
    <h3 class="text-center mb-4">Register</h3>
    <div class="mb-3">
        <label for="reg-username" class="form-label">Username</label>
        <input type="text" name="usname" id="reg-username" class="form-control" placeholder="Masukkan username" required>
    </div>
    <div class="mb-3">
        <label for="reg-email" class="form-label">Email</label>
        <input type="email" name="email" id="reg-email" class="form-control" placeholder="example1@gmail.com" required>
    </div>
    <div class="mb-3">
        <label for="reg-pass" class="form-label">Password</label>
        <input type="password" name="pw" id="reg-pass" class="form-control" placeholder="Masukkan password" required>
    </div>
    <button type="submit" class="btn btn-accent w-100 mt-2">Register</button>
</form>