<?php
session_start();
require 'db.php'; 
//проверка на авторизацию и админа
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit;
}
$success = '';
$errors  = [];
//Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // получение и валидация данных
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $type        = $_POST['type'] ?? '';
    if ($name === '') {
        $errors[] = 'Наименование товара не может быть пустым.';
    }
    if ($description === '') {
        $errors[] = 'Описание не может быть пустым.';
    }
    if ($price === false || $price < 0) {
        $errors[] = 'Введите корректную цену.';
    }
    if (!in_array($type, [
        'рыба горячего копчения',
        'рыба холодного копчения',
        'слабосолёная',
        'рыба вяленая',
        'свежая'
    ], true)) {
        $errors[] = 'Неверный тип товара.';
    }
    // Обработка нескольких файлов
    $uploaded_images = [];
    if (isset($_FILES['image']) && !empty($_FILES['image']['name'][0])) {
        foreach ($_FILES['image']['name'] as $i => $origName) {
            if ($_FILES['image']['error'][$i] === UPLOAD_ERR_OK) {
                $safeName = time() . "_{$i}_" . preg_replace('/[^A-Za-z0-9\.\-_]/', '_', basename($origName));
                $target   = __DIR__ . '/img/' . $safeName;

                if (move_uploaded_file($_FILES['image']['tmp_name'][$i], $target)) {
                    $uploaded_images[] = $safeName;
                }
            }
        }
    }
    if (empty($errors)) {
        $images_json = $uploaded_images
            ? json_encode($uploaded_images, JSON_UNESCAPED_UNICODE)
            : null;
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, price, type, image)
            VALUES (:name, :description, :price, :type, :image)
        ");
        $stmt->execute([
            ':name'        => $name,
            ':description' => $description,
            ':price'       => $price,
            ':type'        => $type,
            ':image'       => $images_json
        ]);
        $success = "Товар «" . htmlspecialchars($name) . "» успешно добавлен!";
    }
}
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
    <ul class="navbar-nav text-center">
    <form class="d-flex">
    <li class="nav-item">
                <a href="admin.php" class="nav-link">Админ Профиль</a>
</li>
<li class="nav-item">
                <a href="cart.php" class="nav-link">Корзина</a>
</li>

    </form>
    </li>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-5">
  <div class="row g-5">
    <div class="col-12 col-lg-2 mt-0">
      <div class="card p-3 register-container">
        <h5 class="text-center mb-3">Админ</h5>
        <div class="d-grid gap-2">
          <a href="orders.php" class="btn btn-custom">Заявки</a>
          <a href="adminadd.php" class="btn btn-custom">Товары</a>
          <a href="logout.php" class="btn btn-custom">Выход</a>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-9 register-container ">
    <h2 class="text-center mb-4">Добавить новый товар</h2>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class=" p-4">
      <div class="mb-3">
        <label class="form-label" for="name">Наименование товара</label>
        <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($name ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="description">Описание</label>
        <textarea id="description" name="description" class="form-control" rows="3" required><?= htmlspecialchars($description ?? '') ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label" for="price">Цена (руб.)</label>
        <input type="number" id="price" name="price" step="0.01" min="0" class="form-control" required value="<?= htmlspecialchars($price ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="type">Тип</label>
        <select id="type" name="type" class="form-select" required>
          <?php
            $types = [
              'рыба горячего копчения',
              'рыба холодного копчения',
              'слабосолёная',
              'рыба вяленая',
              'свежая'
            ];
            foreach ($types as $t):
          ?>
            <option value="<?= $t ?>" <?= (isset($type) && $type === $t) ? 'selected' : '' ?>>
              <?= ucfirst($t) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label" for="image">Изображения (можно несколько)</label>
        <input id="image" name="image[]" type="file" class="form-control" multiple>
      </div>
      <button type="submit" class="btn btn-custom w-100">Добавить товар</button>
    </form>
  </div>
  </div>
  </div>
<footer class="footer mt-auto">
  <div class="container text-center">
    <p class="mt-1">Некрасовский деликатес<br>г.Омск<br>2025 </p>
  </div>
</footer>

</body>

</html>
