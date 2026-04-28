<?php
session_start();
require_once("template.php");
echo gen_header("Обработка заказа", "<link rel=\"stylesheet\" href=\"checkout.css\">");

if (!isset($_SESSION["user"])){
    header('Location: /index.php?redirect=/checkout');
}
?>
`
<main class="container" id="app">
    <div class="checkout-grid">

        <!-- LEFT: Order summary -->
        <div class="panel">
        <span class="panel-head"><h2 class="panel-title"><i class="fas fa-receipt"></i> Ваш заказ</h2></span>    
            <div class="order-items-scroll" id="orderItemsList">
                <!-- populated by JS -->
            </div>

            <div class="order-total-row">
                <span class="total-label">Итого:</span>
                <span class="total-amount" id="orderTotal">0.00 ₽</span>
            </div>
        </div>

        <!-- RIGHT: Delivery form -->
        <div class="panel">
            <span class="panel-head">
                <h2 class="panel-title"><i class="fas fa-map-marker-alt"></i> Доставка</h2>
            </span>

            <form method="POST" action="submit.php" id="orderForm">
                <div class="field-group">
                    <label class="field-label" for="phone">
                        <i class="fas fa-phone"></i> Телефон
                    </label>
                    <input class="field-input" type="tel" id="phone" name="phone" placeholder="+7 (___) ___-__-__"
                        required>
                </div>

                <div class="field-group">
                    <label class="field-label" for="address">
                        <i class="fas fa-location-dot"></i> Адрес доставки
                    </label>
                    <input class="field-input" type="text" id="address" name="address"
                        placeholder="Улица, дом, квартира" required>
                </div>

                <div class="field-group">
                    <label class="field-label" for="comment">
                        <i class="fas fa-comment"></i> Комментарий к заказу
                    </label>
                    <textarea class="field-input field-textarea" id="comment" name="comment"
                        placeholder="Например: домофон не работает, звонить..." rows="3"></textarea>
                </div>

                <input type="hidden" name="cartJSON" id="cartJSON" />

                <p style="checkout-warning">
                    <i class="fas fa-circle-info" style="color:#e05a2c;"></i>
                    Проверьте телефон и адрес перед отправкой — мы не проверяем их автоматически.
                </p>
                <button type="submit" class="btn-primary submit-btn" id="submitBtn" disabled>
                    <i class="fas fa-check-circle"></i> Оформить заказ
                </button>
            </form>
        </div>

    </div>
</main>

<script src="checkout.js">
</script>

</body>

</html>