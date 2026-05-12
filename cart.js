function openMenu() {
    document.getElementById("sideMenu").style.right = "0";
}

function closeMenu() {
    document.getElementById("sideMenu").style.right = "-250px";
}

// LOAD CART
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// DISPLAY CART
function displayCart() {

    const cartItems = document.getElementById('cartItems');
    const totalPrice = document.getElementById('totalPrice');

    // EMPTY CART
    if (cart.length === 0) {
        cartItems.innerHTML = `
            <p style="text-align:center; color:#666;">
                Your cart is empty 😢
            </p>
        `;
        totalPrice.textContent = '0';
        return;
    }

    let total = 0;

    cartItems.innerHTML = cart.map((item, index) => {

        // DEFAULT QUANTITY
        let quantity = item.quantity || 1;

        // ITEM TOTAL
        let itemTotal = item.price * quantity;

        // ADD TO GRAND TOTAL
        total += itemTotal;

        return `
        <div class="item"
            style="
                display:flex;
                justify-content:space-between;
                align-items:center;
                padding:15px;
                border-bottom:1px solid #eee;
                gap:15px;
            ">

            <!-- PRODUCT IMAGE -->
            <img src="${item.image}"
                style="
                    width:80px;
                    height:80px;
                    object-fit:cover;
                    border-radius:10px;
                ">

            <!-- PRODUCT INFO -->
            <div style="flex:1;">

                <h4 style="margin:0 0 8px 0;">
                    ${item.name}
                </h4>

                <p style="margin:0; color:#555;">
                    ₱${item.price} × ${quantity}
                </p>

                <small style="color:#7b6db0; font-weight:bold;">
                    Total: ₱${itemTotal}
                </small>

                ${item.customerName ? `
                    <p style="margin-top:8px; font-size:14px; color:#777;">
                        Customer: ${item.customerName}
                    </p>
                ` : ""}

                ${item.size ? `
                    <p style="font-size:14px; color:#777;">
                        Size: ${item.size}
                    </p>
                ` : ""}

            </div>

            <!-- REMOVE BUTTON -->
            <span
                class="remove"
                onclick="removeItem(${index})"
                style="
                    color:red;
                    cursor:pointer;
                    font-weight:bold;
                ">
                Remove
            </span>

        </div>
        `;

    }).join('');

    // DISPLAY TOTAL
    totalPrice.textContent = total.toLocaleString();
}

// REMOVE ITEM
function removeItem(index) {

    cart.splice(index, 1);

    localStorage.setItem('cart', JSON.stringify(cart));

    displayCart();
}

// GO TO CHECKOUT
function goCheckout() {

    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }

    window.location.href = "checkout.php";
}

// INITIALIZE
displayCart();