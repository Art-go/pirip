(function () {
    "use strict";
    // ── Load cart from localStorage ──
    let cart = [];
    try {
        cart = JSON.parse(localStorage.getItem("cart") || "[]");
    } catch (e) {}

    const listEl = document.getElementById("orderItemsList");
    const totalEl = document.getElementById("orderTotal");
    const submitBtn = document.getElementById("submitBtn");
    const cartJSON = document.getElementById("cartJSON");
    const form = document.getElementById("orderForm");

    function formatPrice(n) {
        return Number(n).toFixed(2) + " ₽";
    }

    function render() {
        if (!cart.length) {
            listEl.innerHTML =
                '<div class="empty-order-msg"><i class="fas fa-basket-shopping" style="font-size:2rem;color:#e05a2c;display:block;margin-bottom:10px"></i>Корзина пуста</div>';
            totalEl.textContent = formatPrice(0);
            submitBtn.disabled = true;
            return;
        }

        let grandTotal = 0;
        listEl.innerHTML = cart
            .map((item) => {
                const lineTotal = item.price * item.quantity;
                grandTotal += lineTotal;
                const imgTag = item.img
                    ? `<img src="${item.img}" alt="${item.name}">`
                    : `<i class="fas fa-utensils"></i>`;
                return `
                <div class="order-row" data-id="${item.id}">
                    <div class="order-icon">${imgTag}</div>
                    <div class="order-name">${item.name}</div>
                    
                    <div class="cart-item-actions">
                        <button class="qty-btn dec-qty" data-id="${item.id}">−</button>
                        <span class="item-qty">${item.quantity}</span>
                        <button class="qty-btn inc-qty" data-id="${item.id}">+</button>
                        <button class="remove-btn" data-id="${item.id}" title="Удалить">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    <div class="order-meta">
                        <div class="order-calc">${formatPrice(item.price)} × ${item.quantity}</div>
                        <div class="order-row-total">${formatPrice(lineTotal)}</div>
                    </div>
                </div>`;
            })
            .join("");

        totalEl.textContent = formatPrice(grandTotal);
        submitBtn.disabled = false;

        // update header cart count
        const countEl = document.getElementById("cart-count");
        if (countEl)
            countEl.textContent = cart.reduce((s, i) => s + i.quantity, 0);
    }

    form.addEventListener("submit", function (e) {
        if (!cart.length) {
            e.preventDefault();
            return;
        }
        cartJSON.value = JSON.stringify(cart);
        localStorage.removeItem("cart");
    });

    listEl.addEventListener('click', function (e) {
        const btn = e.target.closest('button');
        if (!btn) return;
        const id = btn.dataset.id;
        if (!id) return;
    
        if (btn.classList.contains('inc-qty')) {
            const item = cart.find(i => i.id === id);
            if (item) item.quantity += 1;
        } else if (btn.classList.contains('dec-qty')) {
            const item = cart.find(i => i.id === id);
            if (item) {
                item.quantity > 1 ? item.quantity -= 1 : (cart = cart.filter(i => i.id !== id));
            }
        } else if (btn.classList.contains('remove-btn') || btn.closest('.remove-btn')) {
            cart = cart.filter(i => i.id !== id);
        }
    
        localStorage.setItem('cart', JSON.stringify(cart));
        render();
    });

    render();
})();
