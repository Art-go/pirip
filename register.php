<?php
session_start();
require_once 'mysql.php';
require_once 'template.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['user'])) {
    $user = reg_user($_POST['username'], $_POST['password'], $_POST['confirm_password']);

    if ($user["status"]) {
        $_SESSION['user'] = $user["user"];
        if ($user["user"]["is_admin"]) $_SESSION['admin'] = true;
    }
    $error = $user["error"];
}
if (isset($_SESSION['user'])) {
  if(isset($_POST["redirect"])){
    header("Location: {$_POST["redirect"]}");
  } else {
    header('Location: /index.php');
  }
  exit();
}

$style = <<<HTML
<style>
  * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: system-ui, -apple-system, 'Segoe UI', sans-serif
  }

  body {
      background: #f9f7f5;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center
  }

  .login-box {
      background: white;
      border-radius: 28px;
      padding: 2.5rem 2rem;
      width: 100%;
      max-width: 380px;
      box-shadow: 0 12px 40px -8px rgba(0, 0, 0, 0.12);
      border: 1px solid #fff2e9
  }

  .login-logo {
      text-align: center;
      margin-bottom: 2rem
  }

  .login-logo i {
      font-size: 2.5rem;
      color: #e05a2c
  }

  .login-logo h1 {
      font-size: 1.5rem;
      font-weight: 700;
      margin-top: 8px;
      color: #1e1e1e
  }

  .login-logo p {
      color: #888;
      font-size: 0.9rem;
      margin-top: 4px
  }

  .field {
      margin-bottom: 1.2rem
  }

  label {
      display: block;
      font-weight: 600;
      font-size: 0.88rem;
      color: #555;
      margin-bottom: 6px
  }

  input {
      width: 100%;
      padding: 12px 14px;
      border: 1.5px solid #eee;
      border-radius: 14px;
      font-size: 1rem;
      background: #faf8f7;
      outline: none;
      transition: border-color .2s, box-shadow .2s
  }

  input:focus {
      border-color: #e05a2c;
      box-shadow: 0 0 0 3px rgba(224, 90, 44, .12);
      background: white
  }

  .btn {
      width: 100%;
      margin-top: 1rem;
  }

  .btn:hover {
      background: #e05a2c
  }

  .error {
      background: #fff0ed;
      border: 1px solid #ffd5be;
      color: #c0392b;
      border-radius: 12px;
      padding: 10px 14px;
      font-size: .9rem;
      margin-bottom: 1rem;
      text-align: center
  }
</style>
HTML;

echo gen_head("Регистрация", $style);
?>
<div class="login-box">
  <a href="/">
  <div class="login-logo">
    <i class="fas fa-utensils"></i>
    <h1>Хинкальня</h1>
  </div>
  </a>
  <div id="error" class="error" <?php if(!$error) echo 'style="display:none;"'?>><?= htmlspecialchars($error) ?></div>
  <form method="POST" onsubmit="
  if(this.password.value===this.confirm_password.value)
  {return true}
  else
  {const error = document.getElementById('error'); error.innerHTML = 'Пароль должен совпадать!'; error.style.display = 'block'; return false;}
  ">
    <div class="field">
      <label>Логин</label>
      <input type="text" name="username" autocomplete="username" required>
    </div>
    <div class="field">
      <label>Пароль</label>
      <input type="password" name="password" autocomplete="new-password" required>
    </div>
    <div class="field">
      <label>Повторите Пароль</label>
      <input type="password" name="confirm_password" autocomplete="off" required>
    </div>
    <?php if (isset($_GET["redirect"])) {?> <input type="hidden" name="redirect" value="<?= $_GET["redirect"]?>"> <?php } ?>
    <button class="btn btn-primary" type="submit"><i class="fas fa-user-plus"></i> Зарегестрироваться</button>
  </form>
  <a href="login.php<?php if(isset($_GET["redirect"])) echo "?redirect={$_GET["redirect"]}"?>"><button class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Назад</button></a>
</div>
</body>
</html>
