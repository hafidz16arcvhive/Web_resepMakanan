<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

// Wajib login admin
if (!isset($_SESSION['admin_logged_in'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

// Ambil action dari request
$input  = json_decode(file_get_contents('php://input'), true);
$action = isset($input['action']) ? $input['action'] : '';

// ================================
// TAMBAH RESEP BARU
// ================================
if ($action === 'create') {
  $stmt = $pdo->prepare("
    INSERT INTO recipes 
      (title, category_id, emoji, difficulty, cook_time, servings, 
       ingredients, steps, weather_tags, status)
    VALUES 
      (:title, :category_id, :emoji, :difficulty, :cook_time, :servings,
       :ingredients, :steps, :weather_tags, :status)
  ");

  $stmt->execute([
    ':title'        => $input['title'],
    ':category_id'  => $input['category_id'],
    ':emoji'        => $input['emoji'],
    ':difficulty'   => $input['difficulty'],
    ':cook_time'    => $input['cook_time'],
    ':servings'     => $input['servings'],
    ':ingredients'  => $input['ingredients'], // sudah dipisah | dari frontend
    ':steps'        => $input['steps'],       // sudah dipisah | dari frontend
    ':weather_tags' => $input['weather_tags'],
    ':status'       => $input['status'],
  ]);

  echo json_encode([
    'success' => true,
    'message' => 'Resep berhasil ditambahkan!',
    'id'      => $pdo->lastInsertId()
  ]);
}

// ================================
// EDIT RESEP
// ================================
elseif ($action === 'update') {
  $stmt = $pdo->prepare("
    UPDATE recipes SET
      title        = :title,
      category_id  = :category_id,
      emoji        = :emoji,
      difficulty   = :difficulty,
      cook_time    = :cook_time,
      servings     = :servings,
      ingredients  = :ingredients,
      steps        = :steps,
      weather_tags = :weather_tags,
      status       = :status
    WHERE id = :id
  ");

  $stmt->execute([
    ':title'        => $input['title'],
    ':category_id'  => $input['category_id'],
    ':emoji'        => $input['emoji'],
    ':difficulty'   => $input['difficulty'],
    ':cook_time'    => $input['cook_time'],
    ':servings'     => $input['servings'],
    ':ingredients'  => $input['ingredients'],
    ':steps'        => $input['steps'],
    ':weather_tags' => $input['weather_tags'],
    ':status'       => $input['status'],
    ':id'           => $input['id'],
  ]);

  echo json_encode([
    'success' => true,
    'message' => 'Resep berhasil diperbarui!'
  ]);
}

// ================================
// HAPUS RESEP
// ================================
elseif ($action === 'delete') {
  $id = (int) $input['id'];

  // Hapus log views dulu (karena ada foreign key)
  $pdo->prepare("DELETE FROM recipe_views_log WHERE recipe_id = :id")
      ->execute([':id' => $id]);

  // Baru hapus resepnya
  $pdo->prepare("DELETE FROM recipes WHERE id = :id")
      ->execute([':id' => $id]);

  echo json_encode([
    'success' => true,
    'message' => 'Resep berhasil dihapus!'
  ]);
}

else {
  echo json_encode(['success' => false, 'message' => 'Action tidak dikenali']);
}
?>