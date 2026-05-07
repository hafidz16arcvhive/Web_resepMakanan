<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// Cek apakah sudah login
// Kalau belum, tolak akses
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

// ================================
// 1. STATISTIK UMUM
// ================================

// Total resep
$stmt = $pdo->query("SELECT COUNT(*) as total FROM recipes");
$totalRecipes = $stmt->fetch()['total'];

// Total resep bulan ini
$stmt = $pdo->query("
  SELECT COUNT(*) as total FROM recipes
  WHERE MONTH(created_at) = MONTH(NOW())
  AND YEAR(created_at) = YEAR(NOW())
");
$newThisMonth = $stmt->fetch()['total'];

// Total views hari ini
$stmt = $pdo->query("
  SELECT COALESCE(SUM(view_count), 0) as total
  FROM recipe_views_log
  WHERE view_date = CURDATE()
");
$todayViews = $stmt->fetch()['total'];

// Total views kemarin
$stmt = $pdo->query("
  SELECT COALESCE(SUM(view_count), 0) as total
  FROM recipe_views_log
  WHERE view_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
");
$yesterdayViews = $stmt->fetch()['total'];

// Hitung naik/turun views
$viewsDiff = $todayViews - $yesterdayViews;

// Total likes
$stmt = $pdo->query("SELECT COALESCE(SUM(likes), 0) as total FROM recipes");
$totalLikes = $stmt->fetch()['total'];

// ================================
// 2. TOP 5 RESEP PALING DIMINATI
// ================================

$stmt = $pdo->query("
  SELECT
    r.id,
    r.title,
    r.emoji,
    r.views,
    r.likes,
    r.rating,
    c.name AS category_name
  FROM recipes r
  JOIN categories c ON r.category_id = c.id
  WHERE r.status = 'published'
  ORDER BY r.views DESC
  LIMIT 5
");
$topRecipes = $stmt->fetchAll();

// ================================
// 3. GRAFIK VIEWS 7 HARI TERAKHIR
// ================================

$stmt = $pdo->query("
  SELECT
    DATE_FORMAT(view_date, '%a') AS day_label,
    SUM(view_count) AS total
  FROM recipe_views_log
  WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
  GROUP BY view_date
  ORDER BY view_date ASC
");
$weeklyViews = $stmt->fetchAll();

// ================================
// 4. TABEL SEMUA RESEP
// ================================

$stmt = $pdo->query("
  SELECT
    r.id,
    r.title,
    r.emoji,
    r.views,
    r.likes,
    r.rating,
    r.status,
    r.created_at,
    c.name AS category_name
  FROM recipes r
  JOIN categories c ON r.category_id = c.id
  ORDER BY r.views DESC
");
$allRecipes = $stmt->fetchAll();

// Kirim semua data sekaligus
echo json_encode([
  'success' => true,
  'stats'   => [
    'total_recipes'  => $totalRecipes,
    'new_this_month' => $newThisMonth,
    'today_views'    => $todayViews,
    'views_diff'     => $viewsDiff,
    'total_likes'    => $totalLikes,
  ],
  'top_recipes'  => $topRecipes,
  'weekly_views' => $weeklyViews,
  'all_recipes'  => $allRecipes,
]);
?>