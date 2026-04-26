(function () {
    "use strict";
    let cart = [];

    const appContainer = document.getElementById("app");
    const cartCountSpan = document.getElementById("cart-count");
    const modalOverlay = document.getElementById("cartModalOverlay");
    const openCartBtn = document.getElementById("openCartBtn");
    const closeModalBtn = document.getElementById("closeModalBtn");
    const cartItemsList = document.getElementById("cartItemsList");
    const cartTotalPriceSpan = document.getElementById("cartTotalPrice");
    const checkoutBtn = document.getElementById("checkoutBtn");

    function saveCart() {
        localStorage.setItem("cart", JSON.stringify(cart));
    }

    function updateCartUI() {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartCountSpan.textContent = totalItems;

        renderCartItems();

        const total = cart.reduce(
            (sum, item) => sum + item.price * item.quantity,
            0
        );
        cartTotalPriceSpan.textContent = `${total.toFixed(2)} ₽`;

        if (checkoutBtn) {
            checkoutBtn.disabled = cart.length === 0;
        }
    }

    function renderCartItems() {
        if (!cartItemsList) return;

        if (cart.length === 0) {
            cartItemsList.innerHTML = `<div class="empty-cart-msg">Корзина пока пуста!</div>`;
            return;
        }

        let itemsHtml = "";
        cart.forEach((item) => {
            itemsHtml += `
                <div class="cart-item" data-cart-id="${item.id}">
                    <div class="cart-item-info">
                        <span class="cart-item-name">${item.name}</span>
                        <span class="cart-item-price">${item.price.toFixed(
                            2
                        )} ₽</span>
                    </div>
                    <div class="cart-item-actions">
                        <button class="qty-btn dec-qty" data-id="${
                            item.id
                        }">−</button>
                        <span class="item-qty">${item.quantity}</span>
                        <button class="qty-btn inc-qty" data-id="${
                            item.id
                        }">+</button>
                        <button class="remove-btn" data-id="${
                            item.id
                        }" title="Удалить"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>
            `;
        });
        cartItemsList.innerHTML = itemsHtml;
    }

    function addToCart(itemId, itemName, itemPrice, itemImg) {
        const existing = cart.find((i) => i.id === itemId);
        if (existing) {
            existing.quantity += 1;
        } else {
            cart.push({
                id: itemId,
                name: itemName,
                price: parseFloat(itemPrice),
                quantity: 1,
                img: itemImg || null,
            });
        }

        saveCart();
        updateCartUI();
    }

    function handleCartAction(e) {
        const target = e.target.closest("button");
        if (!target) return;
        const cartContainer = e.target.closest(".cart-item");
        if (!cartContainer) return;

        const itemId = target.dataset.id || cartContainer.dataset.cartId;
        if (!itemId) return;

        if (target.classList.contains("inc-qty")) {
            const item = cart.find((i) => i.id === itemId);
            if (item) {
                item.quantity += 1;
            }
        } else if (target.classList.contains("dec-qty")) {
            const item = cart.find((i) => i.id === itemId);
            if (item) {
                if (item.quantity > 1) {
                    item.quantity -= 1;
                } else {
                    cart = cart.filter((i) => i.id !== itemId);
                }
            }
        } else if (
            target.classList.contains("remove-btn") ||
            target.closest(".remove-btn")
        ) {
            cart = cart.filter((i) => i.id !== itemId);
        }

        saveCart();
        updateCartUI();
    }

    async function proceedToCheckout() {
        if (cart.length === 0) {
            alert("Корзина пуста!");
            return;
        }
        window.location.href = "/checkout.php";
    }

    function openCartModal() {
        modalOverlay.style.display = "flex";
        renderCartItems();
    }

    function closeCartModal() {
        modalOverlay.style.display = "none";
    }

    cart = JSON.parse(localStorage.getItem("cart") || "[]");
    updateCartUI();

    appContainer.addEventListener("click", (e) => {
        const addBtn = e.target.closest(".add-btn");
        if (!addBtn) return;
        const id = addBtn.dataset.id;
        const name = addBtn.dataset.name;
        const price = addBtn.dataset.price;
        const img = addBtn.dataset.img;
        if (id && name && price) {
            addToCart(id, name, price, img);
        }
    });

    openCartBtn.addEventListener("click", openCartModal);
    closeModalBtn.addEventListener("click", closeCartModal);
    modalOverlay.addEventListener("click", (e) => {
        if (e.target === modalOverlay) closeCartModal();
    });

    cartItemsList.addEventListener("click", handleCartAction);
    checkoutBtn.addEventListener("click", proceedToCheckout);

    window.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && modalOverlay.style.display === "flex") {
            closeCartModal();
        }
    });
})();
