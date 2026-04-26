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

?>

<?=gen_admin_header("Админ - Хинкальня", '<link rel="stylesheet" href="./admin.css">')?>

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
  <?php gen_table("fa-receipt", "orders", "Заказы", ["ID", "Телефон", "Адрес", "Дата и время", "Статус"], $orders, function ($row){
    ?>
    <td style="color:#bbb;font-size:.85rem">#<?= $row['id'] ?></td>
        <td style="color:#bbb;font-size:.85rem">#<?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td class="trunc"><?= htmlspecialchars($row['address']) ?></td>
        <td style="color:#888;font-size:.85rem"><?= htmlspecialchars($row['created_at']) ?></td>
    <?php
  }); ?>

</main>

</body>
</html>