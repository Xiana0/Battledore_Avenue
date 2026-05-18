let selectedProduct = {};

let selectedType = "";

function openModal(
    name,
    price,
    image,
    hasColor = false,
    isRental = false
) {

    selectedProduct = {
        name: name,
        price: price,
        image: image
    };

    if (isRental) {

        selectedType = "racket";

    }
    else if (hasColor) {

        selectedType = "accessory";

    }
    else {

        selectedType = "jersey";

    }

    document.getElementById("modalTitle").innerText = name;

    document.getElementById("modalPrice").innerText = price;

    document.getElementById("modalTotal").value = "₱" + price;

    document.getElementById("checkoutModal").style.display = "flex";


    // RESET FIRST

    document.getElementById(
        "durationSection"
    ).style.display = "none";

    document.getElementById(
        "colorSection"
    ).style.display = "none";


    // COLOR SECTION

    let colorSection =
        document.getElementById("colorSection");

    if (colorSection && hasColor) {

        colorSection.style.display = "block";

    }


    // RENTAL SECTION

    let durationSection =
        document.getElementById("durationSection");

    if (durationSection && isRental) {

        durationSection.style.display = "block";

    }

}


function closeModal() {

    document.getElementById("checkoutModal")
        .style.display = "none";

}


function confirmAddToCart() {

    let customerName =
        document.getElementById("customerName").value;

    let customerContact =
        document.getElementById("customerContact").value;

    if (customerName === "" || customerContact === "") {

        alert("Please fill up all fields!");

        return;

    }

    let size = "";

    let sizeInput =
        document.getElementById("customerSize");

    if (sizeInput) {

        size = sizeInput.value;

    }

    let color = "";

    let colorInput =
        document.getElementById("productColor");

    if (colorInput) {

        color = colorInput.value;

    }

    let duration = "";

    let durationInput =
        document.getElementById("rentingday");

    if (durationInput) {

        duration = durationInput.value;

    }

    let jerseyNumber = "";

    let jerseyNumberInput =
        document.getElementById("jerseyNumber")

    if (jerseyNumberInput) {

        jerseyNumber =
            jerseyNumberInput.value;

    }

    let cart =
        JSON.parse(localStorage.getItem("cart"))
        || [];

    cart.push({

        type: selectedType,

        name: selectedProduct.name,

        price: selectedProduct.price,

        image: selectedProduct.image,

        customerName: customerName,

        customerContact: customerContact,

        print_name: customerName,

        print_number: jerseyNumber,

        size: size,

        color: color,

        duration: duration,

        quantity: 1

    });

    localStorage.setItem(
        "cart",
        JSON.stringify(cart)
    );

    alert("Added to cart!");

    closeModal();

}