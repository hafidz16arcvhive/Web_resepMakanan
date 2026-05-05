<?php
$host     = 'localhost';
$db_name  = 'avhira_cook';
$username = 'root';
$password = '';  // default XAMPP kosong

try {
 $pdo = new PDO(
  "mysql:host=$host;dbname=$db_name;charset=utf8mb4", // ← ganti di sini
  $username,
  $password
);
  // Aktifkan error mode supaya mudah debug
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  // Hasil query langsung jadi array asosiatif
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
  die(json_encode(['error' => 'Koneksi gagal: ' . $e->getMessage()]));
}
?>