<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// Hanya terima method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
  exit;
}

// Ambil data dari request
$input    = json_decode(file_get_contents('php://input'), true);
$username = isset($input['username']) ? trim($input['username']) : '';
$password = isset($input['password']) ? $input['password'] : '';

// Validasi tidak boleh kosong
if (empty($username) || empty($password)) {
  echo json_encode(['success' => false, 'message' => 'Username dan password wajib diisi']);
  exit;
}

// Cari user di database
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :username");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

// Cek password menggunakan password_verify()
// Fungsi ini membandingkan password asli dengan hash di database
if ($user && password_verify($password, $user['password'])) {
  // Login berhasil — simpan info ke session
  $_SESSION['admin_logged_in'] = true;
  $_SESSION['admin_id']        = $user['id'];
  $_SESSION['admin_username']  = $user['username'];

  echo json_encode(['success' => true, 'message' => 'Login berhasil']);
} else {
  echo json_encode(['success' => false, 'message' => 'Username atau password salah']);
}
?>