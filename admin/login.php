<?php
session_start();
require_once '../mysql.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['username'] === ADMIN_USER && $_POST['password'] === ADMIN_PASS) {
        $_SESSION['admin'] = true;
        header('Location: index.php');
        exit;
    }
    $error = 'Неверный логин или пароль.';
}

if (isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Вход — Админ</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:system-ui,-apple-system,'Segoe UI',sans-serif}
body{background:#f9f7f5;min-height:100vh;display:flex;align-items:center;justify-content:center}
.login-box{background:white;border-radius:28px;padding:2.5rem 2rem;width:100%;max-width:380px;
  box-shadow:0 12px 40px -8px rgba(0,0,0,0.12);border:1px solid #fff2e9}
.login-logo{text-align:center;margin-bottom:2rem}
.login-logo i{font-size:2.5rem;color:#e05a2c}
.login-logo h1{font-size:1.5rem;font-weight:700;margin-top:8px;color:#1e1e1e}
.login-logo p{color:#888;font-size:0.9rem;margin-top:4px}
.field{margin-bottom:1.2rem}
label{display:block;font-weight:600;font-size:0.88rem;color:#555;margin-bottom:6px}
input{width:100%;padding:12px 14px;border:1.5px solid #eee;border-radius:14px;
  font-size:1rem;background:#faf8f7;outline:none;transition:border-color .2s,box-shadow .2s}
input:focus{border-color:#e05a2c;box-shadow:0 0 0 3px rgba(224,90,44,.12);background:white}
.btn{width:100%;background:#1e1e1e;color:white;border:none;padding:14px;border-radius:60px;
  font-weight:700;font-size:1rem;cursor:pointer;transition:.2s;margin-top:.5rem}
.btn:hover{background:#e05a2c}
.error{background:#fff0ed;border:1px solid #ffd5be;color:#c0392b;border-radius:12px;
  padding:10px 14px;font-size:.9rem;margin-bottom:1rem;text-align:center}
</style>
</head>
<body>
<div class="login-box">
  <div class="login-logo">
    <i class="fas fa-utensils"></i>
    <h1>Хинкальня</h1>
    <p>Панель управления</p>
  </div>
  <?php if ($error): ?>
  <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="field">
      <label>Логин</label>
      <input type="text" name="username" autocomplete="username" required>
    </div>
    <div class="field">
      <label>Пароль</label>
      <input type="password" name="password" autocomplete="current-password" required>
    </div>
    <button class="btn" type="submit"><i class="fas fa-sign-in-alt"></i> Войти</button>
  </form>
</div>
</body>
</html>