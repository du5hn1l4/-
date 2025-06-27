<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Инициализация корзины
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Добавление товара
if (isset($_GET['add'])) {
    $product_id = (int)$_GET['add'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity']++;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1,
                'image' => $product['image'] ? json_decode($product['image'], true)[0] : 'placeholder.jpg'
            ];
        }
    }
}

// Удаление товара из корзины
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$product_id]);
}

// Очистка корзины
if (isset($_GET['clear']) && $_GET['clear'] == 1) {
    unset($_SESSION['cart']);
}

// Обновление количества (AJAX обработка)
if (isset($_POST['ajax_update'])) {
    header('Content-Type: application/json');
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$product_id]);
        echo json_encode(['status' => 'removed']);
        exit();
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        
        // Пересчет итогов
        $total = 0;
        foreach ($_SESSION['cart'] as $id => $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        echo json_encode([
            'status' => 'updated',
            'item_total' => number_format($_SESSION['cart'][$product_id]['price'] * $quantity, 2),
            'cart_total' => number_format($total, 2)
        ]);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit();
}

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
?>

<?php include('h.php'); ?>

<div class="container my-5 register-container text-center">
    <h1 class="mb-4 text-center">Ваша корзина</h1>
    <a href="cart.php?clear=1" class="btn btn-custom btn-lg mb-3">Очистить корзину</a>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <p>Ваша корзина пуста.</p>
    <?php else: ?>
        <div id="cart-container">
            <table class="table table-bordered text-center tesct rounded-1">
                <thead>
                <tr>
                </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    foreach ($_SESSION['cart'] as $id => $item):
                        $item_total = $item['price'] * $item['quantity'];
                        $total += $item_total;
                    ?>
                        <tr id="item-<?= $id ?>">
                            <td><img src="img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" width="100"></td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td>
                                <div class="d-flex justify-content-center align-items-center">
                                    <button class="btn btn-sm btn-outline-secondary quantity-btn minus mt-1" data-id="<?= $id ?>">-</button>
                                    <input type="number" class="form-control form-control-sm mx-2 quantity-input mt-4 " 
                                           value="<?= $item['quantity'] ?>" min="0" style="width: 60px;" 
                                           data-id="<?= $id ?>" data-price="<?= $item['price'] ?>">
                                    <button class="btn btn-sm btn-outline-secondary quantity-btn plus mt-1" data-id="<?= $id ?>">+</button>
                                </div>
                            </td>
                            <td class="item-total" id="item-total-<?= $id ?>">
                                <?= number_format($item_total, 2) ?> руб
                            </td>
                            <td>
                                <button class="btn btn-danger btn-sm remove-item" data-id="<?= $id ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                                        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="d-flex justify-content-between">
                <h3>Итого: <span id="cart-total"><?= number_format($total, 2) ?></span> руб</h3>
                <div>
                    <a href="checkout.php" class="btn btn-11 btn-lg">Оформить заказ</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработчики для кнопок количества
    document.querySelectorAll('.quantity-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantity-input');
            const currentValue = parseInt(input.value);
            const newValue = this.classList.contains('plus') ? currentValue + 1 : currentValue - 1;
            
            input.value = newValue > 0 ? newValue : 0;
            updateCartItem(input.dataset.id, input.value);
        });
    });
    
    // Обработчик изменения вручную
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            updateCartItem(this.dataset.id, this.value);
        });
    });
    
    // Обработчик удаления товара
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.id;
            removeCartItem(productId);
        });
    });
    
    // Функция обновления количества товара
    function updateCartItem(productId, quantity) {
        fetch('cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `ajax_update=1&product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'removed') {
                document.getElementById(`item-${productId}`).remove();
                updateCartTotal();
            } else if (data.status === 'updated') {
                document.getElementById(`item-total-${productId}`).textContent = `${data.item_total} руб`;
                document.getElementById('cart-total').textContent = data.cart_total;
            }
            if (document.querySelectorAll('tbody tr').length === 0) {
                document.getElementById('cart-container').innerHTML = '<p>Ваша корзина пуста.</p>';
            }
        });
    }
    
    // Функция удаления товара
    function removeCartItem(productId) {
        fetch(`cart.php?remove=${productId}`)
        .then(() => {
            document.getElementById(`item-${productId}`).remove();
            updateCartTotal();
            
            if (document.querySelectorAll('tbody tr').length === 0) {
                document.getElementById('cart-container').innerHTML = '<p>Ваша корзина пуста.</p>';
            }
        });
    }
    
    // Функция обновления суммы
    function updateCartTotal() {
        let total = 0;
        document.querySelectorAll('.quantity-input').forEach(input => {
            const price = parseFloat(input.dataset.price);
            const quantity = parseInt(input.value);
            total += price * quantity;
        });
        document.getElementById('cart-total').textContent = total.toFixed(2);
    }
});
</script>

<?php include('f.php'); ?>
</body>
</html>