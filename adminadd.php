<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit;
}
// удаление изображений
if (isset($_POST['delete_images']) && isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    $images = $prod['image'] ? json_decode($prod['image'], true) : [];
    $uploadDir = __DIR__ . '/img/';
    foreach ($images as $image) {
        $imagePath = $uploadDir . $image;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
    $stmt->execute([json_encode([], JSON_UNESCAPED_UNICODE), $id]);

    header('Location: adminadd.php');
    exit;
}
// Обработка редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = (int)$_POST['edit_id'];
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $type = $_POST['type'] ?? 'свежая';

    if (empty($name) || empty($description) || empty($price) || empty($type)) {
        echo "Пожалуйста, заполните все поля.";
        exit;
    }
    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, type = ? WHERE id = ?");
    $stmt->execute([$name, $description, $price, $type, $id]);
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = __DIR__ . '/img/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        $images = $prod['image'] ? json_decode($prod['image'], true) : [];
        foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $orig = basename($_FILES['images']['name'][$i]);
                $safe = time() . "_{$i}_" . preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $orig);
                move_uploaded_file($tmp, $uploadDir . $safe);
                $images[] = $safe;
            }
        }
        $stmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
        $stmt->execute([json_encode($images, JSON_UNESCAPED_UNICODE), $id]);
    }
    header('Location: adminadd.php');
    exit;
}
// Список товаров
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$edit_product = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<?php include('h.php');
?>

<div class="container py-4">
  <!-- редактирование -->
  <?php if ($edit_product): ?>
    <div class="row justify-content-center mb-5">
      <div class="col-md-8">
        <div class="card shadow-sm register-cont">
          <div class="card-header register-cont text-white">
            <h5 class="mb-0">Редактировать товар ID <?= $edit_product['id'] ?></h5>
          </div>
          <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
              <input type="hidden" name="edit_id" value="<?= $edit_product['id'] ?>">
              <div class="mb-3">
                <label class="form-label">Название</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($edit_product['name']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Описание</label>
                <textarea name="description" class="form-control" required><?= htmlspecialchars($edit_product['description']) ?></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Цена</label>
                <input type="number" step="0.01" min="0" name="price" class="form-control" value="<?= htmlspecialchars($edit_product['price']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Тип</label>
                <select name="type" class="form-select" required>
                  <?php
                  $types = ['рыба горячего копчения', 'рыба холодного копчения', 'слабосолёная', 'рыба вяленая', 'свежая'];
                  foreach ($types as $type):
                  ?>
                    <option value="<?= $type ?>" <?= $edit_product['type'] == $type ? 'selected' : '' ?>>
                      <?= htmlspecialchars($type) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Изображения</label>
                <input type="file" name="images[]" class="form-control" multiple>
              </div>

              <button type="submit" class="btn btn-11 w-100">Сохранить</button>
              <button type="submit" name="delete_images" class="btn btn-secondary1 w-100 mt-2" onclick="return confirm('Удалить все изображения?');">Удалить все изображения</button>
              <a href="adminadd.php" class="btn btn-custom w-100 mt-2">Отмена</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
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
    <div class="col-12 col-lg-9  register-container">
  <!-- Таблица товаров-->
  <div class="row justify-content-center text-center ">
    <div class="col-md-10 text-center">
      <h2 class="mb-4">Товары</h2>
      <a href="admin.php" class="btn btn-custom mb-3">Добавить</a>
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Название</th>
            <th>Цена</th>
            <th>Описание</th>
            <th>Тип</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $product): ?>
            <tr class="text-light">
              <td><?= htmlspecialchars($product['name']) ?></td>
              <td><?= number_format($product['price'], 2) ?> руб/кг</td>
              <td><?= htmlspecialchars($product['description']) ?></td>
              <td><?= htmlspecialchars($product['type']) ?></td>
              <td>
                <a href="?edit_id=<?= $product['id'] ?>" class="btn btn-primary1 btn-sm mt-1 mb-1">Редактировать</a>
                <a href="delete_product.php?id=<?= $product['id'] ?>" class="btn btn-secondary1 btn-sm mb-1" onclick="return confirm('Удалить товар?');">Удалить</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  </div>
  </div>
</div>
</div>
<?php include('f.php');
?>

</body>
</html>
