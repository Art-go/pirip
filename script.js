(function () {
    "use strict";
    let cart = [];

    const appContainer = document.getElementById("app");
    const cartCountSpan = document.getElementById("cart-count");
    const cartModalOverlay = document.getElementById("cartModalOverlay");
    const itemCardModalOverlay = document.getElementById("itemCardModalOverlay");
    const openCartBtn = document.getElementById("openCartBtn");
    const closeModalBtns = Array.from(document.getElementsByClassName("close-modal"));
    const cartItemsList = document.getElementById("cartItemsList");
    const cartTotalPriceSpan = document.getElementById("cartTotalPrice");
    const checkoutBtn = document.getElementById("checkoutBtn");
    const itemCardTitle = document.getElementById("itemCardTitle");
    const foodModalAddBtn = document.getElementById("foodModalAddBtn");
    const itemCardModalName = document.getElementById("itemCardModalName");
    const itemCardModalPrice = document.getElementById("itemCardModalPrice");
    const itemCardModalDescription = document.getElementById("itemCardModalDescription");
    const itemCardModalImg = document.getElementById("itemCardModalImg");
    const itemCardButtons = document.getElementById("itemCardButtons");

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
                        }"><i class="fas fa-minus"></i></button>
                        <span class="item-qty">${item.quantity}</span>
                        <button class="qty-btn inc-qty" data-id="${
                            item.id
                        }"><i class="fas fa-plus"></i></button>
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
        if (!target) return null;

        const itemId = target.dataset.id;
        if (!itemId) return null;
        
        const item = cart.find((i) => i.id === itemId);
        
        const remove_item = () => {
            cart = cart.filter((i) => i.id !== itemId);
            itemCardButtons.style.display = "none";
            foodModalAddBtn.style.display = "flex";
        }

        if (target.classList.contains("inc-qty")) {
            item.quantity += 1;
        }
        else if (target.classList.contains("dec-qty")) {
            if (item.quantity > 1) {
                item.quantity -= 1;
            } 
            else {
                remove_item();
                return null;
            }
        } 
        else if (target.closest(".remove-btn")) {
            remove_item();
            return null;
        }

        saveCart();
        updateCartUI();
        return item;
    }

    async function proceedToCheckout() {
        if (cart.length === 0) {
            alert("Корзина пуста!");
            return;
        }
        window.location.href = "/checkout.php";
    }

    function openCartModal() {
        cartModalOverlay.style.display = "flex";
        renderCartItems();
    }

    function closeModal() {
        cartModalOverlay.style.display = "none";
        itemCardModalOverlay.style.display = "none";
    }
    
    let openedItemCard;
    function openItemCard(dataset) {
        openedItemCard = dataset;
        itemCardModalOverlay.style.display = "flex";
        itemCardTitle.innerHTML = dataset.name;
        itemCardModalName.innerHTML = dataset.name;
        itemCardModalPrice.innerHTML = dataset.price;
        itemCardModalImg.src = dataset.img;
        itemCardModalDescription.innerHTML = dataset.description;

        const existing = cart.find((i) => i.id === dataset.id);
        if (existing){
            itemCardButtons.style.display = "flex";
            foodModalAddBtn.style.display = "none";
            itemCardButtons.getElementsByClassName("dec-qty")[0].dataset.id = dataset.id;
            itemCardButtons.getElementsByClassName("item-qty")[0].innerHTML = existing.quantity;
            itemCardButtons.getElementsByClassName("inc-qty")[0].dataset.id = dataset.id;
            itemCardButtons.getElementsByClassName("remove-btn")[0].dataset.id = dataset.id;
        }
        else{
            itemCardButtons.style.display = "none";
            foodModalAddBtn.style.display = "flex";
        }
    }
    

    cart = JSON.parse(localStorage.getItem("cart") || "[]");
    updateCartUI();

    appContainer.addEventListener("click", (e) => {
        const card = e.target.closest(".food-card");
        const addBtn = e.target.closest(".add-btn");
        if (addBtn) {
            const id = card.dataset.id;
            const name = card.dataset.name;
            const price = card.dataset.price;
            const img = card.dataset.img;
            if (id && name && price) {
                addToCart(id, name, price, img);
            }
            return;
        }
        openItemCard(card.dataset);
    });

    openCartBtn.addEventListener("click", openCartModal);

    closeModalBtns.forEach(item => item.addEventListener("click", closeModal));

    cartModalOverlay.addEventListener("click", (e) => {
        if (e.target === cartModalOverlay) closeModal();
    });

    itemCardButtons.addEventListener("click", (e) => {
        const item = handleCartAction(e);
        if (item!=null) itemCardButtons.getElementsByClassName("item-qty")[0].innerHTML = item.quantity;
    })

    foodModalAddBtn.addEventListener("click", (e) => {
        addToCart(openedItemCard.id, openedItemCard.name, openedItemCard.price, openedItemCard.img);
        itemCardButtons.style.display = "flex";
        foodModalAddBtn.style.display = "none";
        itemCardButtons.getElementsByClassName("item-qty")[0].innerHTML = 1;
    })

    cartItemsList.addEventListener("click", handleCartAction);
    checkoutBtn.addEventListener("click", proceedToCheckout);

    window.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            closeModal();
        }
    });
})();
