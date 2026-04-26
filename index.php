<?php
require_once("template.php");
require_once("mysql.php");

echo gen_header("Пирип, задание", header_inject: <<<HTML
    <button class="cart-btn" id="openCartBtn">
        <i class="fas fa-shopping-bag"></i>
        <span>Корзина</span>
        <span id="cart-count">0</span>
    </button>
HTML);?>

<main class="container" id="app">
<?php

$categories = fetch_categories();
$dishes = [];

foreach (fetch_items() as $item) {
    $dishes[$item['cat_id']][] = $item;
}

foreach ($categories as $cat) {
    $catId = $cat['id'];
    $catItems = $dishes[$catId] ?? [];

    if (empty($catItems)) {
        continue;
    }
    
    echo '<section class="category-section">';
    echo '<h2 class="category-title">' . htmlspecialchars($cat['name']) . '</h2>';
    echo '<div class="items-grid">';

    foreach ($catItems as $item) {
        echo '
            <div class="food-card" data-item-id="' . $item['id'] . '">
                <div class="item-image">
                    <img src="' . htmlspecialchars($item['img']) . '" alt="' . htmlspecialchars($item['name']) . '" loading="lazy">
                </div>
                <div class="item-name">' . htmlspecialchars($item['name']) . '</div>
                <div class="item-price">' . number_format($item['price'], 2) . ' ₽</div>
                <button class="add-btn"
                    data-id="' . $item['id'] . '"
                    data-name="' . htmlspecialchars($item['name']) . '"
                    data-price="' . $item['price'] . '"
                    data-img="' . htmlspecialchars($item['img']) . '">
                    <i class="fas fa-plus-circle"></i> Добавить в корзину
                </button>
            </div>
        ';
    }

    echo '</div></section>';
}



?>
</main>

<div class="modal-overlay" id="cartModalOverlay">
    <div class="cart-modal">
        <div class="cart-header">
            <h3><i class="fas fa-bag-shopping" style="margin-right: 10px;"></i>Корзина</h3>
            <button class="close-modal" id="closeModalBtn">&times;</button>
        </div>
        <div class="cart-items-container" id="cartItemsList">

        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Итого: </span>
                <span id="cartTotalPrice">0.00 ₽</span>
            </div>
            <button class="checkout-btn" id="checkoutBtn">
                <i class="fas fa-arrow-right"></i> Оплата
            </button>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>