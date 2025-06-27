<?php
session_start();
require 'db.php';
// Проверка на админа
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: index.php');
    exit();
}
// Получение всех заказов
$stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Обновление статуса заказа
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = htmlspecialchars($_POST['status']);
    // Обновление статуса в базе данных
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    // Перезагрузка страницы после изменения статуса
    header('Location: orders.php');
    exit();
}
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
?>

<?php include('h.php');
?>
  <div class="container my-5">
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

      <div class="col-md-10">
        <div class="main-card register-container">
          <h3 class="text-center mb-4">Заявки</h3>

  <?php foreach ($orders as $order): ?> 
  <div class="order-card row rr">
    <div class="col-sm-8 text-dark">
      <p><strong>№ <?= $order['id'] ?></strong></p>
      <?php
        $pi = $pdo->prepare("
          SELECT p.name, oi.quantity
          FROM order_items oi 
          JOIN products p ON oi.product_id = p.id
          WHERE oi.order_id = ?
        ");
        $pi->execute([$order['id']]);
        foreach ($pi->fetchAll(PDO::FETCH_ASSOC) as $prod) {
          echo htmlspecialchars($prod['name']) 
             . ' — ' . (int)$prod['quantity'] . ' шт.<br>';
        }
      ?>
      <p><em>Комментарий:</em><br><?= nl2br(htmlspecialchars($order['comments'])) ?></p>
    </div>

    <div class="col-sm-4 text-center">
      <p><strong>Дата доставки до:</strong><br><?= htmlspecialchars($order['delivery_date']) ?></p>
      <form method="post">
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
        <select name="status" class="form-select mb-2">
          <?php foreach (['в обработке','отправлен','доставлен','отменен'] as $st): ?>
            <option value="<?= $st ?>" <?= $order['status'] == $st ? 'selected' : '' ?>>
              <?= $st ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" name="update_status" class="btn btn-11">
          Сохранить
        </button>
      </form>
    </div>
  </div>
<?php endforeach; ?>


        </div>
      </div>
    </div>
  </div>
  <?php include('f.php');
?>
</body>
</html>
