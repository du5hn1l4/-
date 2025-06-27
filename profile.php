<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name, surname FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Проверяем данные перед использованием
$full_name = isset($user['full_name']) ? htmlspecialchars((string)$user['full_name']) : '';
$surname = isset($user['surname']) ? htmlspecialchars((string)$user['surname']) : '';

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<?php include('h.php');
?>

<div class="container my-5">
    <div class="row g-5">
        <div class="col-12 col-lg-2 mt-5">
            <div class="card p-3 register-container">
                <h5 class="text-center mb-3">Здравствуйте, <br> <?= $full_name . ' ' . $surname ?></h5>
                <div class="d-grid gap-2">
                    <a href="logout.php" class="btn btn-custom">Выход</a>
                </div>
            </div>
        </div>

      <div class="col-md-10">
        <div class="main-card register-container">
          <h3 class="text-center mb-4">Мои заявки</h3>

          <?php if (empty($orders)): ?>
            <h5 class="text-center ">У вас нет заявок.</p>
          <?php else: ?>
            <?php foreach ($orders as $order): ?> 
              <div class="order-card row rr text-dark" >
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
                  <p><strong>Статус:</strong><br><?= htmlspecialchars($order['status']) ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
  <?php include('f.php');
?>
</body>
</html>
