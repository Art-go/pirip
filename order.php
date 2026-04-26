<?php
require_once("template.php");
require_once("mysql.php");

if (!isset($_GET['id'])) { header('Location: /'); exit; }
$order_id = $_GET["id"];

$order = get_order($order_id);

if (!$order) { header('Location: /'); exit; }

$items = get_order_items($order_id);

$status_map = [
    'pending'   => ['label' => 'Создан',    'icon' => 'fa-clock',        'color' => '#e09a2c'],
    'paid'      => ['label' => 'Оплачен',   'icon' => 'fa-circle-check', 'color' => '#2ca85a'],
    'delivered' => ['label' => 'Доставлен', 'icon' => 'fa-truck',        'color' => '#2c7ae0'],
];
$st = $status_map[$order['status']] ?? $status_map['pending'];

echo gen_header("Заказ #$order_id");
?>

<main class="container" style="max-width:660px">
    <div class="panel" style="text-align:center; padding:2.5rem 2rem 2rem">

        <div style="
            display:inline-flex; align-items:center; gap:10px;
            background:<?= $st['color'] ?>18;
            color:<?= $st['color'] ?>;
            border:2px solid <?= $st['color'] ?>40;
            border-radius:60px; padding:10px 24px;
            font-weight:700; font-size:1.15rem;
            margin-bottom:1.6rem;
        ">
            <i class="fas <?= $st['icon'] ?>"></i>
            <?= $st['label'] ?>
        </div>

        <h2 style="font-size:1.7rem; font-weight:800; margin-bottom:.3rem">
            Заказ <span style="color:#e05a2c">#<?= $order_id ?></span>
        </h2>
        <p style="color:#888; margin-bottom:2rem; font-size:.95rem">
            <?= htmlspecialchars($order['phone']) ?> &nbsp;·&nbsp;
            <?= htmlspecialchars($order['address']) ?>
        </p>

        <?php if (!empty($order['comment'])) {?>
        <p style="color:#888; font-size:.9rem; margin-bottom:1.5rem">
            <i class="fas fa-comment" style="color:#e05a2c"></i>
            <?= htmlspecialchars($order['comment']) ?>
        </p>
        <?php } ?>

        <div class="order-items-scroll" style="text-align:left; margin-bottom:1.4rem">
            <?php foreach ($items as $item): ?>
            <div class="order-row">
                <div class="order-icon">
                    <?php if ($item['img']){ ?>
                        <img src="<?= htmlspecialchars($item['img']) ?>" alt="">
                    <?php } else { ?>
                        <i class="fas fa-utensils"></i>
                    <?php } ?>
                </div>
                <div class="order-name"><?= htmlspecialchars($item['name']) ?></div>
                <div class="order-meta">
                    <div class="order-calc">
                        <?= number_format($item['price'], 2) ?> ₽ × <?= (int)$item['quantity'] ?>
                    </div>
                    <div class="order-row-total">
                        <?= number_format($item['subtotal'], 2) ?> ₽
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="order-total-row" style="margin-bottom:1.8rem">
            <span class="total-label">Итого:</span>
            <span class="total-amount"><?= number_format($order['total'], 2) ?> ₽</span>
        </div>

        <?php if (!empty($order['receipt_url'])): ?>
        <a href="<?= htmlspecialchars($order['receipt_url']) ?>" target="_blank"
           class="btn-secondary" style="display:inline-flex; margin-bottom:1rem">
            <i class="fas fa-receipt"></i> Чек об оплате
        </a><br>
        <?php endif; ?>

        <?php if ($order['status'] === 'pending'): ?>
        <form method="POST" action="submit.php" onsubmit="return confirm('Подтвердить оплату?')">
            <input type="hidden" name="pay" value="1">
            <input type="hidden" name="id" value="<?= $order_id ?>">
            <button type="submit" class="btn-primary" style="margin-top:.5rem">
                <i class="fas fa-credit-card"></i> Оплатить
            </button>
        </form>
        <?php endif; ?>

        <a href="/" style="display:block; margin-top:1.2rem; color:#aaa; font-size:.9rem">
            <i class="fas fa-arrow-left"></i> Вернуться в меню
        </a>
    </div>
</main>

</body>
</html>