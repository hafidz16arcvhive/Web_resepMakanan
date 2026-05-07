<?php
session_start();
session_destroy(); // Hapus semua data session

header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>