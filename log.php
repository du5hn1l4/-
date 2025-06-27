<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
        exit();
    } else {
        echo "Неверные данные для входа!";
    }
}
?>

<?php include('h.php');
?>
<div class="container d-flex justify-content-center">
  <div class="col-md-6 register-container">
    <h2 class="mt-4 text-center">Вход</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
        <form action="" method="post" class="text-center">
        <div class="mb-3">
            <input type="text" name="email" id="email" class="form-control" placeholder="email" required>
            </div>
            <div class="mb-3">
            <div class="mb-3 position-relative">
  <input type="password" name="password" id="password" class="form-control" placeholder="Пароль" required>
  <button type="button" class="btn btn-outline-secondary btn-sm position-absolute top-50 end-0 translate-middle-y me-2" onclick="togglePassword('password')">
    Показать
  </button>
</div>
            </div>
            <button type="submit" class="btn btn-custom text-center">Войти</button>
        <br>
        <a href="reg.php" class="login-link">Нет аккаунта? Зарегистрироваться</a>
        </form>
    </div>
    </div>
<?php include('f.php');
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function togglePassword(id) {
    const input = document.getElementById(id);
    const button = input.nextElementSibling;
    if (input.type === 'password') {
      input.type = 'text';
      button.textContent = 'Скрыть';
    } else {
      input.type = 'password';
      button.textContent = 'Показать';
    }
  }
</script>
</body>
</html>
