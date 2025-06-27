<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit;
}
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$id]);
header('Location: admin.php');
exit;
?>
