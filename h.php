<?php
session_start();
require 'db.php';
// Проверка на авторизацию
$is_logged_in = isset($_SESSION['user_id']);
if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['is_admin'] = $user['is_admin'];
    }
}
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === 1; // Проверка, на админа
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Некрасовский деликатес — Админ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Jeju+Myeongjo&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/custom.css" />    
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-md">
  <div class="container-fluid">
  <a class="navbar-brand d-flex align-items-center me-auto" href="index.php">
      <img src="ico/logo].svg" alt="Логотип" width="125" height="45" class="d-inline-block align-text-top">
      <span href="index.php"class="navbar-brand-text d-none d-md-inline">Некрасовский<br>деликатес</span>
    </a>
    <button class="navbar-toggler text-light border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
    <form class="d-flex">
    <?php if ($is_logged_in): ?>
            <?php if ($is_admin): ?>
                <li class="nav-item">
                <a href="adminadd.php" class="nav-link">Админ Профиль</a>
            </li>
            <li class="nav-item">
                <a href="profile.php" class="nav-link">Профиль </a>
                </li>
            <li class="nav-item">
                <a href="cart.php" class="nav-link"> Корзина</a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link">Выход</a>
            </li>
            <?php else: ?>
                <li class="nav-item">
                <a href="profile.php" class="nav-link">Профиль </a>
                </li>
                <li class="nav-item">
                <a href="cart.php" class="nav-link"> Корзина</a>
            </li>
            <?php endif; ?>
        <?php else: ?>
            <li class="nav-item">
            <a href="reg.php" class="nav-link">Регистрация</a>
        </li>
        <a class="nav-link">/</a>
        <li class="nav-item">
            <a href="log.php" class="nav-link">вход</a>
        </li>
        <?php endif; ?>
    </form>
    </li>
      </ul>
    </div>
  </div>
</nav>