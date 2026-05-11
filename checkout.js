// LOAD TOTAL FROM CART
function loadTotal() {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    let total = 0;

    let container = document.getElementById("checkoutItems");
    container.innerHTML = "";

    if (cart.length === 0) {
        container.innerHTML = "<p>Your cart is empty 😢</p>";
        document.getElementById("total").innerText = "0";
        return;
    }

    cart.forEach(item => {
        let qty = item.quantity || 1;
        let itemTotal = item.price * qty;
        total += itemTotal;

        container.innerHTML += `
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px; border-bottom:1px solid #eee;">
                
                <div style="display:flex; align-items:center; gap:10px;">
                    <img src="${item.image}" style="width:60px; height:60px; object-fit:cover; border-radius:8px;">
                    
                    <div>
                        <strong>${item.name}</strong><br>
                        <small>₱${item.price} x ${qty}</small>
                    </div>
                </div>

                <div style="text-align:right;">
                    <strong>₱${itemTotal}</strong><br>
                    <small>👤 ${item.customerName || ""}</small>
                </div>

            </div>
        `;
    });

    document.getElementById("total").innerText = total.toLocaleString();
}

let selectedMethod = "";

// SELECT PAYMENT
function selectMethod(method) {
    selectedMethod = method;
    alert(method + " selected");
}

// CONFIRM PAYMENT
function confirmPayment() {
    let name = document.getElementById("name").value;
    let number = document.getElementById("number").value;

    if (!selectedMethod) {
        alert("Please select a payment method");
        return;
    }

    if (selectedMethod !== "COD" && (name === "" || number === "")) {
        alert("Please fill payment details");
        return;
    }

    alert("Payment Successful via " + selectedMethod + "!");

    // CLEAR CART AFTER PAYMENT
    localStorage.removeItem("cart");

    window.location.href = "home.html";
}

// LOAD ON PAGE
window.onload = loadTotal;