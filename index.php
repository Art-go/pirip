<?php
session_start();
require_once("template.php");
require_once("mysql.php");

echo gen_header("Пирип, задание", header_inject: <<<HTML
    <button class="btn-primary" id="openCartBtn">
        <i class="fas fa-shopping-bag"></i>
        <span>Корзина</span>
        <span id="cart-count">0</span>
    </button>
HTML);?>

<main class="container" id="app">
<?php

$categories = get_categories();
$dishes = [];

foreach (get_dishes() as $item) {
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
            <div class="food-card" data-id="' . $item['id'] . '"
                    data-name="' . htmlspecialchars($item['name']) . '"
                    data-description="' . htmlspecialchars($item['description']) . '"
                    data-price="' . $item['price'] . '"
                    data-img="' . htmlspecialchars($item['img']) . '">
                <div class="item-image">
                    <img src="' . htmlspecialchars($item['img']) . '" alt="' . htmlspecialchars($item['name']) . '" loading="lazy">
                </div>
                <div class="item-name">' . htmlspecialchars($item['name']) . '</div>
                <div class="item-price">' . number_format($item['price'], 2) . ' ₽</div>
                <button class="add-btn">
                    <i class="fas fa-plus-circle"></i> Добавить в корзину
                </button>
            </div>
        ';
    }

    echo '</div></section>';
}



?>
</main>
<div class="modal-overlay" id="itemCardModalOverlay">
    <div class="cart-modal">
         <div class="cart-header">
            <h3><i class="fas fa-utensils" style="margin-right: 10px;"></i><span id="itemCardTitle">Блюдо</span></h3>
            <button class="close-modal">&times;</button>
        </div>

        <div class="food-card-modal">
            <div class="item-modal-image">
                 <img src="" loading="lazy" id="itemCardModalImg"> 
            </div>
            <div class="item-name" id="itemCardModalName"> Блюдо</div>
            <div class="item-desc" id="itemCardModalDescription"> Описание</div>
            <div class="item-price"><span id="itemCardModalPrice">0.00</span> ₽</div>
        </div>

        <div class="cart-footer">
            <button class="modal-btn" id="foodModalAddBtn">
                <i class="fas fa-plus-circle"></i> Добавить в корзину
            </button>
            <div class="cart-item-actions modal-item-action" id="itemCardButtons">
                <button class="qty-btn dec-qty" data-id="id"><i class="fas fa-minus"></i></button>
                <span class="item-qty">Кол-во</span>
                <button class="qty-btn inc-qty" data-id="id"><i class="fas fa-plus"></i></button>
                <button class="remove-btn" data-id="id" title="Удалить"><i class="fas fa-trash-alt"></i></button>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="cartModalOverlay">
    <div class="cart-modal">
        <div class="cart-header">
            <h3><i class="fas fa-bag-shopping" style="margin-right: 10px;"></i>Корзина</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="cart-items-container" id="cartItemsList">
            echo
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Итого: </span>
                <span id="cartTotalPrice">0.00 ₽</span>
            </div>
            <button class="modal-btn" id="checkoutBtn">
                <i class="fas fa-arrow-right"></i> Продолжить
            </button>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>