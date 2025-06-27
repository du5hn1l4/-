<?php 
session_start();
include('db.php');

// типы товаров
$selected_types = $_GET['type'] ?? [];
$typesStmt = $pdo->query("SELECT DISTINCT type FROM products");
$all_types = $typesStmt->fetchAll(PDO::FETCH_COLUMN);

// условия
$where = [];
$params = [];

if (!empty($selected_types)) {
    $placeholders = implode(',', array_fill(0, count($selected_types), '?'));
    $where[] = "type IN ($placeholders)";
    $params = $selected_types;
}

// запрос с фильтрами
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// запрос на получение товаров с учётом фильтров
$stmt = $pdo->prepare("SELECT * FROM products $where_sql ORDER BY id DESC");
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<?php include('h.php');
?>
<div class="container my-4">
    <!-- фильтры мобильное приложение  -->
    <button class="btn-custom2 d-lg-none mb-3 w-100 p-3 mr-2" 
            type="button" data-bs-toggle="collapse" data-bs-target="#mobileFilters">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-compact-down" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1.553 6.776a.5.5 0 0 1 .67-.223L8 9.44l5.776-2.888a.5.5 0 1 1 .448.894l-6 3a.5.5 0 0 1-.448 0l-6-3a.5.5 0 0 1-.223-.67"/>
            </svg>
    </button>

    <div class="row g-3">
      <!-- фильтры -->
      <aside class="col-lg-3 d-none d-lg-block">
        <form method="GET" id="filtersForm" class="filter-panel">
          <h5>Фильтры</h5>
          <hr>
          <?php foreach($all_types as $type): ?>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="type[]" value="<?= htmlspecialchars($type) ?>"
                     id="type<?= md5($type) ?>" <?= in_array($type, $selected_types) ? 'checked' : '' ?>
                     onchange="document.getElementById('filtersForm').submit()">
              <label class="form-check-label" for="type<?= md5($type) ?>">
                <?= htmlspecialchars($type) ?>
              </label>
            </div>
          <?php endforeach; ?>
        </form>
      </aside>
      <div class="col-12 col-lg-9">
        <!-- Фильтры на мобильных -->
        <div class="collapse d-lg-none mb-3" id="mobileFilters">
          <form method="GET" id="filtersFormMobile" class="filter-panel">
            <h5>Фильтры</h5>
            <hr>
            <?php foreach($all_types as $type): ?>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="type[]" value="<?= htmlspecialchars($type) ?>"
                       id="m_type_<?= md5($type) ?>" <?= in_array($type, $selected_types) ? 'checked' : '' ?>
                       onchange="document.getElementById('filtersFormMobile').submit()">
                <label class="form-check-label" for="m_type_<?= md5($type) ?>">
                  <?= htmlspecialchars($type) ?>
                </label>
              </div>
            <?php endforeach; ?>
          </form>
        </div>
        <div class="products-container2 ">
          <div class="row g-3">
            <!-- Карточки товаров -->
            <?php if (empty($products)): ?>
              <p class="text-center w-100">Нет товаров по выбранному типу.</p>
            <?php endif; ?>
            <?php foreach ($products as $product): ?> 
  <div class="col-6 col-md-4 col-lg-3 bq text-center">
    <a href="prod.php?id=<?= $product['id'] ?>" class="text-decoration-none text-dark">
      <div class="card mb-3 h-100">
          <?php
$image = ($product['image'] !== null) ? json_decode($product['image'], true) : [];
$firstImage = $image[0] ?? 'placeholder.jpg';
?>
          <img src="img/<?= htmlspecialchars($firstImage) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>" style="height: 200px; object-fit: cover;">
          <div class="card-body bb">
              <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
              <p class="card-text">Цена: <?= number_format($product['price'], 2) ?> руб/кг</p>
          </div>
      </div>
    </a>
  </div>
<?php endforeach; ?>
        </div>
    </div>
  </div>
</div>
</div>

<?php include('f.php');
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
