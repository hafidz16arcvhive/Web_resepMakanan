<?php
require_once 'config/database.php';

// Ambil data admin dari database
$stmt = $pdo->query("SELECT * FROM admin_users WHERE username = 'admin'");
$user = $stmt->fetch();

echo "<pre>";
echo "Data user: ";
print_r($user);
echo "</pre>";

// Test password_verify manual
$passwordYangDiketik = 'admin123';
$hashDiDatabase = $user['password'];

if (password_verify($passwordYangDiketik, $hashDiDatabase)) {
  echo "✅ Password COCOK!";
} else {
  echo "❌ Password TIDAK cocok — hash bermasalah";
}
?>