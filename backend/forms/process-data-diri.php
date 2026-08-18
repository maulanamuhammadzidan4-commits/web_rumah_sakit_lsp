<?php
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../backend/config/database.php';

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/pages/login-page.php');
    exit;
}

$userId = (int)($_SESSION['user']['id'] ?? 0);
if ($userId <= 0) {
    $_SESSION['form_error'] = 'Sesi pengguna tidak valid.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/pages/login-page.php');
    exit;
}

$fullName = trim((string)($_POST['fullname'] ?? ''));
$telp = trim((string)($_POST['telp'] ?? ''));
$address = trim((string)($_POST['address'] ?? ''));
$bloodType = trim((string)($_POST['bloodtype'] ?? ''));
$medicalHistory = trim((string)($_POST['medicalhistory'] ?? ''));

$errors = [];
if ($fullName === '') {
    $errors[] = 'Nama lengkap wajib diisi.';
}
if ($telp === '') {
    $errors[] = 'Nomor telepon wajib diisi.';
} elseif (!preg_match('/^[0-9+\-\s()]+$/', $telp)) {
    $errors[] = 'Format nomor telepon tidak valid.';
}
if ($address === '') {
    $errors[] = 'Alamat wajib diisi.';
}

if (!empty($errors)) {
    $_SESSION['form_error'] = implode('<br>', $errors);
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/pages/data-diri.php');
    exit;
}

try {
    $pdo->beginTransaction();

    $updateUser = $pdo->prepare('
        UPDATE users
        SET full_name = :full_name,
            telp = :telp,
            address = :address
        WHERE user_id = :user_id
    ');
    $updateUser->execute([
        ':full_name' => $fullName,
        ':telp' => $telp,
        ':address' => $address,
        ':user_id' => $userId,
    ]);

    $checkMedical = $pdo->prepare('SELECT id FROM user_medical_profiles WHERE user_id = :user_id LIMIT 1');
    $checkMedical->execute([':user_id' => $userId]);

    if ($checkMedical->fetch()) {
        $medicalStmt = $pdo->prepare('
            UPDATE user_medical_profiles
            SET blood_type = :blood_type,
                medical_history = :medical_history,
                updated_at = CURRENT_TIMESTAMP
            WHERE user_id = :user_id
        ');
    } else {
        $medicalStmt = $pdo->prepare('
            INSERT INTO user_medical_profiles (user_id, blood_type, medical_history, updated_at)
            VALUES (:user_id, :blood_type, :medical_history, CURRENT_TIMESTAMP)
        ');
    }

    $medicalStmt->execute([
        ':user_id' => $userId,
        ':blood_type' => $bloodType,
        ':medical_history' => $medicalHistory,
    ]);

    $pdo->commit();

    $_SESSION['profile_success'] = 'Data diri berhasil disimpan.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/pages/profile.php');
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log($e->getMessage());
    $_SESSION['form_error'] = 'Gagal menyimpan data diri. Silakan coba lagi.';
    header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/pages/data-diri.php');
    exit;
}
