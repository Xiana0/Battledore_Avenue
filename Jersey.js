let selectedProduct = {};

function openModal(name, price, image) {
    selectedProduct = { name, price, image };

    document.getElementById("modalTitle").innerText = name;
    document.getElementById("modalPrice").innerText = price;
    document.getElementById("modalTotal").value = "₱" + price;

    document.getElementById("checkoutModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("checkoutModal").style.display = "none";
}

function confirmAddToCart() {
    let name = document.getElementById("customerName").value;
    let contact = document.getElementById("customerContact").value;

    if (name === "" || contact === "") {
        alert("Please fill up all fields!");
        return;
    }

    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    let size = document.getElementById("customerSize").value;
let printName = document.getElementById("customerName").value;
let printNumber = document.getElementById("customerContact").value;

if (name === "" || contact === "" || size === "") {
    alert("Please fill required fields!");
    return;
}

cart.push({
    name: selectedProduct.name,
    price: selectedProduct.price,
    image: selectedProduct.image,
    customerName: name,
    contact: contact,
    size: size,
    printName: printName,
    printNumber: printNumber
});

    localStorage.setItem("cart", JSON.stringify(cart));

    alert("Added to cart!");

    closeModal();
}