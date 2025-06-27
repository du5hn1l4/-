<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
// Инициализация корзины
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
// Если форма отправлена
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $company_name = htmlspecialchars($_POST['company_name']);
    $email = htmlspecialchars($_POST['email']);
    $address = htmlspecialchars($_POST['address']);
    $delivery_date = htmlspecialchars($_POST['delivery_date']);
    $comments = htmlspecialchars($_POST['comments']);
    // Вставляем заказ в таблицу orders
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, first_name, last_name, company_name, email, address, delivery_date, comments, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $first_name, $last_name, $company_name, $email, $address, $delivery_date, $comments, 'в обработке']);
    // Получаем ID последнего заказа
    $order_id = $pdo->lastInsertId();
    // Добавляем товары из корзины в таблицу order_items
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $product_id, $item['quantity'], $item['price']]);
    }
    // Очистка корзины после оформления заказа
    unset($_SESSION['cart']);
    // Перенаправление на страницу с подтверждением
    header('Location: order_confirmation.php');
    exit();
}
?>


<?php include('h.php');
?>

<div class="container d-flex justify-content-center">
  <div class="col-md-6 register-container mb-3">
    <h2 class="mt-4 text-center">Оформление заказа</h2>
    <form action="checkout.php" method="post" class="text-center ">
    <div class="mb-3">
                <div class="mb-3">
                <input type="text" name="first_name" id="first_name" class="form-control" placeholder="Имя" required>
                </div>
                <div class="mb-3">
                <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Фамилия" required>
                </div>
                <div class="mb-3">
                <input type="text" name="company_name" id="company_name" class="form-control" placeholder="ИП (если нет, то просто напишите ФИО)" required>
                </div>
                <div class="mb-3">
                <input type="text" name="email" id="email" class="form-control" placeholder="email" required>
                </div>
                <div class="mb-3">
                <input type="text" name="address" id="address" class="form-control" placeholder="Адрес" required>
                </div>
                <div class="mb-3">
                <input type="date" name="delivery_date" id="delivery_date" class="form-control" placeholder="" required>
                </div>
                <div class="mb-3">
                <input type="text" name="comments" id="comments" class="form-control" placeholder="Комментарий" required>
            </div>
        </div>

        <button type="submit" class="btn btn-custom btn-lg">Оформить заявку</button>
    </form>
  </div>
</div>

<?php include('f.php');
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
