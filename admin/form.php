<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
require_once '../mysql.php';

$table = $_GET['table'] ?? '';
$id    = (int)($_GET['id'] ?? 0);
$allowed = ['categories', 'dishes', 'orders'];
if (!in_array($table, $allowed)) exit('Invalid table');

$row = [];
if ($id) {
    $res = $mysqli->query("SELECT * FROM `$table` WHERE id=$id");
    $row = $res ? ($res->fetch_assoc() ?? []) : [];
}
$isEdit = !empty($row);
$title = $isEdit ? 'Редактировать' : 'Добавить';

$tableLabels = ['categories' => 'Категория', 'dishes' => 'Блюдо', 'orders' => 'Заказ'];
$categories = $mysqli->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);

function val($row, $key, $default = '') {
    return htmlspecialchars($row[$key] ?? $default);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $title ?> — <?= $tableLabels[$table] ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:system-ui,-apple-system,'Segoe UI',sans-serif}
body{background:#f9f7f5;min-height:100vh;padding:2rem 1rem}
.wrap{max-width:540px;margin:0 auto}
.back{display:inline-flex;align-items:center;gap:8px;color:#888;text-decoration:none;
  font-size:.9rem;font-weight:600;margin-bottom:1.5rem;transition:.2s}
.back:hover{color:#e05a2c}
.card{background:white;border-radius:28px;padding:2rem;box-shadow:0 12px 26px -8px rgba(0,0,0,.08);border:1px solid #fff2e9}
h1{font-size:1.5rem;font-weight:700;margin-bottom:1.8rem;padding-bottom:.8rem;border-bottom:3px solid #f0eae4}
.field{margin-bottom:1.3rem}
label{display:block;font-weight:600;font-size:.88rem;color:#555;margin-bottom:6px}
input,select,textarea{width:100%;padding:12px 14px;border:1.5px solid #eee;border-radius:14px;
  font-size:1rem;background:#faf8f7;outline:none;transition:.2s;font-family:inherit;resize:vertical}
input:focus,select:focus,textarea:focus{border-color:#e05a2c;box-shadow:0 0 0 3px rgba(224,90,44,.12);background:white}
.actions{display:flex;gap:12px;margin-top:.5rem}
.btn{flex:1;padding:14px;border:none;border-radius:60px;font-weight:700;font-size:1rem;cursor:pointer;transition:.2s}
.btn-save{background:#1e1e1e;color:white}
.btn-save:hover{background:#e05a2c}
.btn-cancel{background:#f1f1f1;color:#555;text-decoration:none;display:flex;align-items:center;justify-content:center}
.btn-cancel:hover{background:#eee}
</style>
</head>
<body>
<div class="wrap">
  <a class="back" href="index.php#<?= $table ?>"><i class="fas fa-arrow-left"></i> Назад</a>
  <div class="card">
    <h1><?= $title ?>: <?= $tableLabels[$table] ?></h1>
    <form method="POST" action="action.php">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="table" value="<?= $table ?>">
      <input type="hidden" name="id" value="<?= $id ?>">

      <?php if ($table === 'categories'): ?>
        <div class="field">
          <label>Название</label>
          <input type="text" name="name" value="<?= val($row,'name') ?>" required>
        </div>

      <?php elseif ($table === 'dishes'): ?>
        <div class="field">
          <label>Название</label>
          <input type="text" name="name" value="<?= val($row,'name') ?>" required>
        </div>
        <div class="field">
          <label>Категория</label>
          <select name="cat_id" required>
            <option value="">— выберите —</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= (($row['cat_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Цена (₽)</label>
          <input type="number" step="0.01" min="0" name="price" value="<?= val($row,'price','0') ?>" required>
        </div>
        <div class="field">
          <label>URL изображения</label>
          <input type="text" name="img" value="<?= val($row,'img') ?>" placeholder="https://...">
        </div>

      <?php elseif ($table === 'orders'): ?>
        <div class="field">
          <label>Телефон</label>
          <input type="text" name="phone" value="<?= val($row,'phone') ?>">
        </div>
        <div class="field">
          <label>Адрес</label>
          <input type="text" name="address" value="<?= val($row,'address') ?>">
        </div>
        <div class="field">
          <label>Комментарий</label>
          <textarea name="comment" rows="3"><?= val($row,'comment') ?></textarea>
        </div>
        <div class="field">
          <label>Корзина (JSON)</label>
          <textarea name="cart_json" rows="5"><?= val($row,'cart_json') ?></textarea>
        </div>
      <?php endif; ?>

      <div class="actions">
        <a class="btn btn-cancel" href="index.php#<?= $table ?>">Отмена</a>
        <button class="btn btn-save" type="submit"><i class="fas fa-check"></i> Сохранить</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
