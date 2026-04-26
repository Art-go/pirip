<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
require_once '../template.php';
require_once '../mysql.php';

// ── Fetch data ──
$cats   = $mysqli->query("SELECT * FROM categories ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$dishes = $mysqli->query("SELECT d.*, c.name AS cat_name FROM dishes d LEFT JOIN categories c ON d.cat_id=c.id ORDER BY d.id")->fetch_all(MYSQLI_ASSOC);

// Orders — may not exist yet
$ordersExist = $mysqli->query("SHOW TABLES LIKE 'orders'")->num_rows > 0;
$orders = $ordersExist
    ? $mysqli->query("SELECT * FROM orders ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC)
    : [];

function gen_table($table_icon, $table_id, $table_name, $columns, $table, $render_row){
    $count = count($table);
    echo <<<HTML
    <div class="adm-section" id="{$table_id}">
      <div class="section-head">
        <div class="section-title">
          <i class="fas $table_icon"></i> {$table_name}
          <span class="count-badge">{$count}</span>
        </div>
      </div>
      <table class="adm-table">
        <thead><tr>
    HTML;

    foreach ($columns as $col) {
        echo "<th>{$col}</th>";
    }
    echo '<th style="width:120px;text-align:right">Действия</th></tr></thead><tbody>';

    if (empty($table)) {
        $colspan = count($columns) + 1;
        echo "<tr class=\"empty-row\"><td colspan=\"{$colspan}\"> Таблица пуста! </td></tr>";
    } else {
        foreach ($table as $row) {
            echo "<tr>";
            $render_row($row);
            $row_name = isset($row['name']) ? ("«" . htmlspecialchars($row['name']) . "»") : "#{$row['id']}?";
            echo <<<HTML
            <td>
            <div class="row-actions">
                <a class="act-btn act-edit" href="form.php?table=$table_id&id={$row['id']}">
                <i class="fas fa-pen"></i> Изменить
                </a>
                <form method="POST" action="action.php" onsubmit="return confirm('Удалить {$row_name}?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="table" value="$table_id">
                <input type="hidden" name="id" value="{$row['id']}">
                <button class="act-btn act-del" type="submit"><i class="fas fa-trash"></i> Удалить</button>
                </form>
            </div>
            </td>
            HTML;
            echo "</tr>";
        }
    }

    echo <<<HTML
        </tbody>
      </table>
      <div class="add-row">
        <a class="add-btn-main" href="form.php?table={$table_id}"><i class="fas fa-plus"></i> Добавить</a>
      </div>
    </div>
    HTML;
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Админ — Хинкальня</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
  *{margin:0;padding:0;box-sizing:border-box;font-family:system-ui,-apple-system,'Segoe UI',sans-serif}
  body{background:#f9f7f5;color:#1e1e1e;min-height:100vh}

  /* header */
  .adm-header{background:white;padding:.9rem 2rem;display:flex;align-items:center;justify-content:space-between;
    box-shadow:0 4px 12px rgba(0,0,0,.04);border-bottom:1px solid #eee;position:sticky;top:0;z-index:10}
  .adm-logo{display:flex;align-items:center;gap:10px;font-weight:700;font-size:1.3rem}
  .adm-logo i{color:#e05a2c}
  .adm-logo small{font-size:.75rem;font-weight:500;color:#aaa;margin-left:2px}
  .header-right{display:flex;align-items:center;gap:14px}
  .site-link{font-size:.85rem;color:#888;text-decoration:none;font-weight:600;display:flex;align-items:center;gap:5px}
  .site-link:hover{color:#e05a2c}
  .logout-btn{background:#f1f1f1;border:none;padding:8px 16px;border-radius:60px;font-weight:600;
    font-size:.85rem;cursor:pointer;color:#555;transition:.2s;display:flex;align-items:center;gap:6px}
  .logout-btn:hover{background:#e05a2c;color:white}

  /* nav tabs */
  .tab-nav{background:white;border-bottom:1px solid #eee;padding:0 2rem;display:flex;gap:0}
  .tab-link{padding:1rem 1.3rem;font-weight:600;font-size:.9rem;color:#888;text-decoration:none;
    border-bottom:3px solid transparent;transition:.15s;display:flex;align-items:center;gap:7px}
  .tab-link:hover{color:#e05a2c}
  .tab-link.active{color:#e05a2c;border-bottom-color:#e05a2c}

  /* main */
  .adm-main{max-width:1400px;margin:0 auto;padding:2rem 1.5rem}

  /* section */
  .adm-section{background:white;border-radius:24px;padding:1.8rem;margin-bottom:2rem;
    box-shadow:0 8px 24px -8px rgba(0,0,0,.08);border:1px solid #fff2e9}
  .section-head{display:flex;align-items:center;justify-content:space-between;
    margin-bottom:1.4rem;padding-bottom:.8rem;border-bottom:3px solid #f0eae4}
  .section-title{font-size:1.3rem;font-weight:700;display:flex;align-items:center;gap:9px}
  .section-title i{color:#e05a2c}
  .count-badge{background:#f0eae4;color:#888;border-radius:99px;padding:2px 12px;font-size:.82rem;font-weight:600}

  /* table */
  .adm-table{width:100%;border-collapse:collapse;font-size:.9rem}
  .adm-table th{text-align:left;padding:10px 12px;font-size:.8rem;font-weight:700;
    text-transform:uppercase;letter-spacing:.04em;color:#aaa;border-bottom:2px solid #f0eae4}
  .adm-table td{padding:10px 12px;border-bottom:1px solid #f7f2ef;vertical-align:middle}
  .adm-table tr:last-child td{border-bottom:none}
  .adm-table tr:hover td{background:#fdf9f7}
  .adm-table img{width:40px;height:40px;border-radius:10px;object-fit:cover;background:#f5f0ec}

  /* row actions */
  .row-actions{display:flex;gap:8px;justify-content:flex-end}
  .act-btn{border:none;padding:7px 14px;border-radius:60px;font-size:.8rem;font-weight:700;
    cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:.15s;text-decoration:none}
  .act-edit{background:#f0eae4;color:#555}
  .act-edit:hover{background:#e05a2c;color:white}
  .act-del{background:#fff0f0;color:#c0392b}
  .act-del:hover{background:#c0392b;color:white}

  /* add button */
  .add-row{padding-top:1.2rem;display:flex;justify-content:flex-end}
  .add-btn-main{background:#1e1e1e;color:white;border:none;padding:11px 22px;border-radius:60px;
    font-weight:700;font-size:.9rem;cursor:pointer;display:inline-flex;align-items:center;gap:8px;
    transition:.2s;text-decoration:none}
  .add-btn-main:hover{background:#e05a2c}

  /* create table notice */
  .notice{background:#fff8f5;border:1.5px dashed #ffd5be;border-radius:14px;padding:1.2rem 1.5rem;
    color:#888;font-size:.9rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem}
  .notice strong{color:#e05a2c}

  /* empty */
  .empty-row td{text-align:center;padding:2rem;color:#bbb;font-style:italic}

  /* price */
  .price-cell{color:#e05a2c;font-weight:700}

  /* truncate */
  .trunc{max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

  @media(max-width:700px){
    .adm-table td:nth-child(3),.adm-table th:nth-child(3){display:none}
    .tab-link span{display:none}
  }
</style>
</head>
<body>

<header class="adm-header">
  <div class="adm-logo">
    <i class="fas fa-utensils"></i>
    <span>Хинкальня <small>admin</small></span>
  </div>
  <div class="header-right">
    <a class="site-link" href="/" target="_blank"><i class="fas fa-external-link-alt"></i> Сайт</a>
    <form method="POST" action="logout.php" style="display:inline">
      <button class="logout-btn"><i class="fas fa-sign-out-alt"></i> Выйти</button>
    </form>
  </div>
</header>

<main class="adm-main">

  <!-- ── CATEGORIES ── -->
  <?php gen_table("fa-layer-group", "categories", "Категории", ["ID", "Название"], $cats, function ($row){
    ?>
    <td style="color:#bbb;font-size:.85rem">#<?= $row['id'] ?></td>
    <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
    <?php
  }); ?>

  <!-- ── DISHES ── -->
  <?php gen_table("fa-utensils", "dishes", "Блюда", ["ID", "Изображение", "Название", "Категория", "Цена"], $dishes, function ($row){
    ?>
    <td style="color:#bbb;font-size:.85rem">#<?= $row['id'] ?></td>
          <td><?php if ($row['img']): ?><img src="<?= htmlspecialchars($row['img']) ?>" alt=""><?php else: ?>—<?php endif; ?></td>
          <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
          <td style="color:#888"><?= htmlspecialchars($row['cat_name'] ?? '—') ?></td>
          <td class="price-cell"><?= number_format($row['price'],2) ?> ₽</td>
    <?php
  }); ?>

  <!-- ── ORDERS ── -->
  <div class="adm-section" id="orders">
    <div class="section-head">
      <div class="section-title">
        <i class="fas fa-receipt"></i> Заказы
        <?php if ($ordersExist): ?>
          <span class="count-badge"><?= count($orders) ?></span>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!$ordersExist): ?>
    <div class="notice">
      <span>Таблица <strong>orders</strong> не существует. Создайте её в БД</span>
    </div>
    <?php else: ?>
    <table class="adm-table">
      <thead>
        <tr><th>ID</th><th>Телефон</th><th>Адрес</th><th>Дата</th><th style="text-align:right">Действия</th></tr>
      </thead>
      <tbody>
        <?php if (empty($orders)): ?>
          <tr class="empty-row"><td colspan="5">Нет заказов</td></tr>
        <?php else: foreach ($orders as $row): ?>
        <tr>
          <td style="color:#bbb;font-size:.85rem">#<?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['phone']) ?></td>
          <td class="trunc"><?= htmlspecialchars($row['address']) ?></td>
          <td style="color:#888;font-size:.85rem"><?= htmlspecialchars($row['created_at']) ?></td>
          <?= gen_buttons($row)?>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
    <div class="add-row">
      <a class="add-btn-main" href="form.php?table=orders"><i class="fas fa-plus"></i> Добавить заказ</a>
    </div>
    <?php endif; ?>
  </div>

</main>

</body>
</html>