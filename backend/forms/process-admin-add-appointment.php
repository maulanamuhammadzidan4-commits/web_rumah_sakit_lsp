<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../config/database.php';

$target = rtrim(BASE_URL, '/') . '/admin/dashboard/pages/appointment.php';
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) { header('Location: ' . rtrim(BASE_URL, '/') . '/frontend/pages/login-page.php'); exit; }
$roles = array_map('strtolower', array_map('trim', (array) (($_SESSION['user'] ?? [])['roles'] ?? [])));
if (!array_intersect($roles, ['admin', 'super admin', 'super_admin'])) { http_response_code(403); exit('403 - Akses ditolak.'); }

$idUser = (int) ($_POST['id_user'] ?? 0);
$idDoctor = (int) ($_POST['id_dokter'] ?? 0);
$clinic = trim($_POST['klinik'] ?? '');
$dateInput = trim($_POST['tanggal_temu'] ?? '');
$date = DateTime::createFromFormat('Y-m-d\\TH:i', $dateInput);
if ($idUser <= 0 || $idDoctor <= 0 || $clinic === '' || !$date || $date->format('Y-m-d\\TH:i') !== $dateInput) { $_SESSION['error_message'] = 'Data janji temu tidak valid.'; header('Location: ' . $target); exit; }
try { $stmt = $pdo->prepare('INSERT INTO appointments (id_user, id_dokter, klinik, tanggal_temu) VALUES (:user, :doctor, :clinic, :date)'); $stmt->execute([':user' => $idUser, ':doctor' => $idDoctor, ':clinic' => $clinic, ':date' => $date->format('Y-m-d H:i:s')]); $_SESSION['success_message'] = 'Janji temu berhasil ditambahkan.'; } catch (PDOException $e) { error_log('Admin add appointment error: ' . $e->getMessage()); $_SESSION['error_message'] = 'Gagal menambahkan janji temu.'; }
header('Location: ' . $target); exit;
