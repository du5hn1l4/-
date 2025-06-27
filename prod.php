<?php
session_start();
require 'db.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "Товар не найден.";
    exit;
}

$images = $product['image'] ? json_decode($product['image'], true) : [];
?>


<?php include('h.php');
?>
<div class="container my-5">

  <div class="product-wrapper">

    <!-- фотографии -->
    <div id="Carousel" class="carousel slide product-carousel" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php if ($images): ?>
          <?php foreach ($images as $index => $image): ?>
            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
              <img src="img/<?= htmlspecialchars($image) ?>" class="d-block w-100" alt="Фото товара">
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="carousel-item active">
            <img src="img/placeholder.jpg" class="d-block w-100" alt="Фото недоступно">
          </div>
        <?php endif; ?>
      </div>

      <?php if (count($images) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#Carousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Предыдущее</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#Carousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Следующее</span>
        </button>
      <?php endif; ?>
    </div>

    <!-- Информация-->
    <div class="product-info mt-4 mt-md-0">
    <h1 class="mb-4"><?= htmlspecialchars($product['name']) ?></h1>
      <h3 class=""><?= number_format($product['price'], 2) ?> руб/кг</h3>
      <a href="cart.php?add=<?= $product['id'] ?>"  class=" btn btn-custom btn-lg mt-5 mb-5"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart" viewBox="0 0 16 16">
  <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
</svg></a>
    <h3 class="lead">описание:<br><?= nl2br(htmlspecialchars($product['description'])) ?></h3>
    </div>

  </div>
</div>
<?php include('f.php');
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>