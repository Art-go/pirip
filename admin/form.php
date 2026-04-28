<?php
require_once '../mysql.php';
require_once '../template.php';

validate_admin();

$table = $_GET['table'] ?? '';
$id    = (int)($_GET['id'] ?? 0);
$allowed = ['categories', 'dishes', 'orders'];
if (!in_array($table, $allowed)) exit('Invalid table');

$row        = $id ? get_row($table, $id) : [];
$categories = get_categories();
$users = get_users();
$isEdit = !empty($row);
$title = $isEdit ? 'Редактировать' : 'Добавить';

$tableLabels = ['categories' => 'Категорию', 'dishes' => 'Блюдо', 'orders' => 'Заказ'];

function val($row, $key, $default = '') {
    return htmlspecialchars($row[$key] ?? $default);
}
$style = <<<HTML
<link rel="stylesheet" href="./admin.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:system-ui,-apple-system,'Segoe UI',sans-serif}
body{background:#f9f7f5;min-height:100vh;display: block;}
.wrap{max-width:600px;margin:0 auto;min-width:500px}
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
</style>
HTML;

echo gen_admin_header("$title - {$tableLabels[$table]}", $style)?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $title ?> — <?= $tableLabels[$table] ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="/styles.css">
</head>
<body>
<div class="wrap">
  <a class="back" href="index.php"><i class="fas fa-arrow-left"></i> Назад</a>
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
          <label>Описание</label>
          <textarea name="description" rows="6"><?= val($row,'description') ?></textarea>
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
          <label>ID Пользователя</label>

          <select name="user_id" required>
            <option value="">— выберите —</option>
            <?php foreach ($users as $user): ?>
              <option value="<?= $user['id'] ?>" <?= (($row['user_id'] ?? '') == $user['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($user['username']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Статус</label>
          <select name="status">
            <?php foreach (['pending','paid','delivered'] as $s): ?>
              <option value="<?= $s ?>" <?= ($row['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Чек</label>
          <input type="text" name="receipt_url" value="<?= val($row,'receipt_url') ?>">
        </div>
        <?php if ($id): $items = get_order_items($id); ?>
        <div class="field">
          <label>Позиции</label>
          <table style="width:100%;border-collapse:collapse;font-size:.9rem;margin-top:4px">
            <thead>
              <tr style="border-bottom:2px solid #f0eae4">
                <th style="text-align:left;padding:6px 4px">Блюдо</th>
                <th style="text-align:right;padding:6px 4px">Кол-во</th>
                <th style="text-align:right;padding:6px 4px">Сумма</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $item): ?>
              <tr style="border-bottom:1px solid #f5f0ec">
                <td style="padding:7px 4px"><?= htmlspecialchars($item['name']) ?></td>
                <td style="text-align:right;padding:7px 4px;color:#888"><?= $item['quantity'] ?></td>
                <td style="text-align:right;padding:7px 4px"><?= number_format($item['subtotal'],2) ?> ₽</td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="2" style="padding:8px 4px;font-weight:700;text-align:right">Итого:</td>
                <td style="text-align:right;padding:8px 4px;font-weight:700"><?= number_format($row['total'],2) ?> ₽</td>
              </tr>
            </tfoot>
          </table>
        </div>
      <?php endif; endif;?>

      <div class="actions">
        <a class="btn btn-secondary" href="index.php#<?= $table ?>">Отмена</a>
        <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Сохранить</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
