<?php
session_start();
require 'db.php';
// Проверка авторизации и прав администратора
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit;
}
// Получение ID товара
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: adminadd.php');
    exit;
}
// удаление файлов изображений
$stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if ($product) {
    $uploadDir = __DIR__ . '/img/';
    $images = $product['image'] ? json_decode($product['image'], true) : [];
    foreach ($images as $img) {
        $path = $uploadDir . $img;
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
// Удаляем запись из бд
$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$id]);

// Перенаправление обратно на список товаров
header('Location: adminadd.php');
exit;
