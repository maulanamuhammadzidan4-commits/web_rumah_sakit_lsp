<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../config/database.php';

$target = rtrim(BASE_URL, '/') . '/admin/dashboard/pages/appointment.php';
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) { header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/pages/login-page.php'); exit; }
$roles = array_map('strtolower', array_map('trim', (array) (($_SESSION['user'] ?? [])['roles'] ?? [])));
if (!array_intersect($roles, ['admin', 'super admin', 'super_admin'])) { http_response_code(403); exit('403 - Akses ditolak.'); }

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) { $_SESSION['error_message'] = 'ID janji temu tidak valid.'; header('Location: ' . $target); exit; }
try { $stmt = $pdo->prepare('DELETE FROM appointments WHERE id_appointment = :id'); $stmt->execute([':id' => $id]); $_SESSION['success_message'] = 'Janji temu berhasil dihapus.'; } catch (PDOException $e) { error_log('Admin delete appointment error: ' . $e->getMessage()); $_SESSION['error_message'] = 'Gagal menghapus janji temu.'; }
header('Location: ' . $target); exit;
