<?php
require_once '../template.php';
require_once '../mysql.php';
 
validate_admin();

$cats   = get_categories();
$dishes = get_dishes();

$date   = $_GET['date'] ?? date('Y-m-d');
$prev   = date('Y-m-d', strtotime($date . ' -1 day'));
$next   = date('Y-m-d', strtotime($date . ' +1 day'));
$isToday = $date === date('Y-m-d');
$orders = get_orders($date);
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
        <td>
            <div class="order-icon">
                <?php if ($row['img']): ?>
                    <img src="<?= htmlspecialchars($row['img']) ?>" alt="">
                <?php else: ?>
                    <i class="fas fa-utensils"></i>
                <?php endif; ?>
            </div>
        </td>
        <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
        <td style="color:#888"><?= htmlspecialchars($row['cat_name'] ?? '—') ?></td>
        <td class="price-cell"><?= number_format($row['price'],2) ?> ₽</td>
    <?php
    }); ?>

    <!-- ── ORDERS ── -->
    <div style="max-width:1400px;margin:1.5rem auto 0;padding:0 1.5rem">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:1rem">
        <a href="?date=<?= $prev ?>#orders" class="btn-secondary" style="padding:8px 16px;font-size:.85rem">
        <i class="fas fa-chevron-left"></i>
        </a>
        <form method="GET" action="" style="display:flex;align-items:center;gap:8px">
        <input type="date" name="date" value="<?= $date ?>"
            style="padding:8px 12px;border:1.5px solid #eee;border-radius:12px;font-size:.9rem;background:#faf8f7;outline:none;font-family:inherit"
            onchange="this.form.submit()">
        <?php if (!$isToday): ?>
            <a href="?date=<?= date('Y-m-d') ?>#orders" class="btn-primary" style="padding:8px 16px;font-size:.85rem">Сегодня</a>
        <?php endif; ?>
        </form>
        <a href="?date=<?= $next ?>#orders" class="btn-secondary" style="padding:8px 16px;font-size:.85rem;<?= $isToday ? 'opacity:.4;pointer-events:none' : '' ?>">
        <i class="fas fa-chevron-right"></i>
        </a>
    </div>
    </div>

    <?php gen_table("fa-receipt", "orders", "Заказы — " . ($isToday ? "Сегодня" : $date),
    ["ID", "Телефон", "Адрес", "Польз. ID", "Время", "Статус", "Позиций", "Итого"], $orders, function ($row){ ?>
        <td style="color:#bbb;font-size:.85rem">#<?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td class="trunc"><?= htmlspecialchars($row['address']) ?></td>
        <td class="trunc">#<?= ($row['user_id']) ?></td>
        <td style="color:#888;font-size:.85rem"><?= date('H:i', strtotime($row['created_at'])) ?></td>
        <td><?= htmlspecialchars($row['status']) ?></td>
        <td style="color:#888"><?= $row['item_count'] ?></td>
        <td class="price-cell"><?= number_format($row['total'],2) ?> ₽</td>
    <?php }, add: false); ?>

</main>

</body>
</html>