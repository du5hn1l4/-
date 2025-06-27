<?php
session_start();
include('db.php');
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $surname2 = trim($_POST['surname2']);
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm']);
    $errors = [];
    // Проверка данных
    if (empty($full_name) || !preg_match('/^[А-Яа-яA-Za-z\\s]+$/u', $full_name)) {
        $errors[] = "Введите корректное Имя (только буквы кириллицы, латиницы).";
    }
    if (empty($surname) || !preg_match('/^[А-Яа-яA-Za-z\\s]+$/u', $surname)) {
        $errors[] = "Введите корректное Фамилию (только буквы кириллицы, латиницы).";
    }
    if (empty($surname2) || !preg_match('/^[А-Яа-яA-Za-z\\s]+$/u', $surname2)) {
        $errors[] = "Введите корректное Отчество (только буквы кириллицы, латиницы).";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Введите действительный адрес электронной почты.";
    }
    if (!preg_match('/(?=.*\\d).{3,}/', $password)) {
        $errors[] = "Пароль должен содержать хотя бы одну цифру и быть длиной минимум 3 символа.";
    }
    if ($password !== $password_confirm) {
        $errors[] = "Пароли не совпадают.";
    }
    if (empty($errors)) {
        // Проверка на уникальность email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Пользователь с таким email уже существует.";
        } else {
            // Хэширование пароля
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Вставка данных в таблицу users
            $stmt = $pdo->prepare("INSERT INTO users (full_name,surname ,surname2, email, password, is_admin) VALUES (?, ?, ?, ?, ?, '0')");
            $stmt->execute([$full_name, $surname, $surname2, $email, $password_hash]);

            $_SESSION['success'] = "Вы успешно зарегистрировались!";
            header("Location: log.php");
            exit();
        }
    }
}
?>


<?php include('h.php');
?>
<div class="container d-flex justify-content-center">
  <div class="col-md-6 register-container">
    <h2 class="mt-4 text-center">Регистрация</h2>
    <?php if (!empty($errors)): ?>
    <div id="error-alert" class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999; width: 90%; max-width: 400px;" role="alert">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
    <form action="" method="post" class="text-center">
    <div class="mb-3">
        <input type="text" name="surname" id="surname" class="form-control" placeholder="Фамилия" required>
        </div>
        <div class="mb-3">
            <input type="text" name="name" id="name" class="form-control" placeholder="Имя" required>
        </div>
        <div class="mb-3">
        <input type="text" name="surname2" id="surname2" class="form-control" placeholder="Отчество" required>
        </div>
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
        <div class="mb-3">
        <div class="mb-3 position-relative">
  <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="Подтвердите пароль" required>
  <button type="button" class="btn btn-outline-secondary btn-sm position-absolute top-50 end-0 translate-middle-y me-2" onclick="togglePassword('password_confirm')">
    Показать
  </button>
</div>
        </div>
        <div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="agree" required>
    <label class="form-check-label" for="agree">
        <a href="policy.php" target="_blank" class="tesct">Я согласен на обработку персональных данных</a>
    </label>
    <div class="invalid-feedback">Вы должны согласиться с условиями</div>
</div>
        <button type="submit" class="btn btn-custom text-center" id="regbtn">Зарегистрироваться</button>
        <br>
        <a href="log.php" class="login-link">Уже есть аккаунт? Войти</a>
    </form>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
    const alert = document.getElementById('error-alert');
    if (alert) {
      setTimeout(() => {
        alert.classList.remove('show');
      }, 3000);
      setTimeout(() => {
        alert.remove();
      }, 4000);
    }
  });
</script>
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


  document.addEventListener('DOMContentLoaded', function() {
    const agreeCheckbox = document.getElementById('agree');
    const registerBtn = document.getElementById('regbtn');
    
    agreeCheckbox.addEventListener('change', function() {
        registerBtn.disabled = !this.checked;
    });
    
    const form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        if (!agreeCheckbox.checked) {
            event.preventDefault();
            agreeCheckbox.classList.add('is-invalid');
        }
    });
});
</script>

<?php include('f.php');
?>
</body>
</html>
