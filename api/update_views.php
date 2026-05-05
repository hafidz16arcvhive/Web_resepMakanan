<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
  $stmt = $pdo->prepare("UPDATE recipes SET views = views + 1 WHERE id = :id");
  $stmt->execute([':id' => $id]);

  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false]);
}
?>