<?php
// Izinkan akses dari halaman yang sama
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Panggil koneksi database
require_once '../config/database.php';

// Ambil parameter filter dari URL (opsional)
// Contoh: api/get_recipes.php?category=Nusantara&search=soto
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search   = isset($_GET['search'])   ? $_GET['search']   : '';
$weather  = isset($_GET['weather'])  ? $_GET['weather']  : '';

// Mulai bangun query
// Kita JOIN dengan tabel categories supaya dapat nama kategorinya
$sql = "SELECT 
          r.id,
          r.title,
          r.emoji,
          r.difficulty,
          r.cook_time,
          r.servings,
          r.ingredients,
          r.steps,
          r.weather_tags,
          r.views,
          r.likes,
          r.rating,
          r.status,
          c.name AS category_name
        FROM recipes r
        JOIN categories c ON r.category_id = c.id
        WHERE r.status = 'published'";

// Tambahkan filter jika ada
$params = [];

if ($category !== '') {
  $sql .= " AND c.name = :category";
  $params[':category'] = $category;
}

if ($search !== '') {
  $sql .= " AND r.title LIKE :search";
  $params[':search'] = '%' . $search . '%';
}

if ($weather !== '') {
  $sql .= " AND r.weather_tags LIKE :weather";
  $params[':weather'] = '%' . $weather . '%';
}

// Urutkan berdasarkan views terbanyak
$sql .= " ORDER BY r.views DESC";

// Jalankan query dengan PDO (aman dari SQL Injection)
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$recipes = $stmt->fetchAll();

// Ubah ingredients dan steps dari string ke array
// karena di database kita simpan pakai | sebagai pemisah
foreach ($recipes as &$recipe) {
  $recipe['ingredients'] = explode('|', $recipe['ingredients']);
  $recipe['steps']       = explode('|', $recipe['steps']);
  $recipe['weather_tags'] = explode(',', $recipe['weather_tags']);
}

// Kirim sebagai JSON
echo json_encode([
  'success' => true,
  'data'    => $recipes,
  'total'   => count($recipes)
]);
?>