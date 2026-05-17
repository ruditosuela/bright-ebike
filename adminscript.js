//Responsive sidebar
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.sidebar ul li a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });
});

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('active');
}

function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('active');
}



// PRODUCT MANAGEMENT FUNCTION
function openModal() {
    document.getElementById("productModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("productModal").style.display = "none";
}

// EDIT PRODUCT MODAL
function openEditModal() {
    document.getElementById("editProductModal").style.display = "flex";
}

function closeEditModal() {
    document.getElementById("editProductModal").style.display = "none";
}

// EDIT FUNCTION
function editProduct(id, name, category, price, stock) {
    document.getElementById("edit_product_id").value = id;
    document.getElementById("edit_product_name").value = name;
    document.getElementById("edit_category").value = category;
    document.getElementById("edit_price").value = price;
    document.getElementById("edit_stock").value = stock;

    openEditModal();
}

window.onclick = function (event) {
    const addModal = document.getElementById("productModal");
    const editModal = document.getElementById("editProductModal");

    if (event.target === addModal) {
        addModal.style.display = "none";
    }

    if (event.target === editModal) {
        editModal.style.display = "none";
    }
};

// NEW

function openReviewModal(id, customer, product, rating, text, date) {
    document.getElementById("modalReviewId").innerText = id;
    document.getElementById("modalReviewCustomer").innerText = customer;
    document.getElementById("modalReviewProduct").innerText = product;
    document.getElementById("modalReviewDate").innerText = date;
    document.getElementById("modalReviewText").innerText = text;
 
    const stars = parseInt(rating);
   document.getElementById("modalReviewStars").innerHTML =
    '★'.repeat(stars) + '☆'.repeat(5 - stars) + 
    '<span style="color: black;">  (' + stars + '/5)</span>';
 
    document.getElementById("reviewModal").style.display = "flex";
}
 
function closeReviewModal() {
    document.getElementById("reviewModal").style.display = "none";
}
 
window.addEventListener("click", function (event) {
    const modal = document.getElementById("reviewModal");
    if (modal && event.target === modal) {
        modal.style.display = "none";
    }
});

//ORDER MANAGEMENT FUNCTION
//View orders
function openViewOrder(orderId, customer, email, payment, status, date, total){

    document.getElementById("viewOrderModal").style.display = "flex";
    // ORDER INFO
    document.querySelector(".order-info").innerHTML = `
        <p><strong>Order ID:</strong> #${orderId}</p>
        <p><strong>Customer Name:</strong> ${customer}</p>
        <p><strong>Email:</strong> ${email}</p>
        <p><strong>Payment Method:</strong> ${payment}</p>
        <p><strong>Status:</strong> ${status}</p>
        <p><strong>Order Date:</strong> ${date}</p>
    `;

    // ITEMS
   fetch(`fetchOrderItems.php?order_id=${orderId}`)
    .then(res => res.json())
    .then(items => {

        const tbody = document.getElementById("orderItemsBody");
        tbody.innerHTML = "";

        let subtotal = 0;

        items.forEach(item => {

            const qty = Number(item.quantity);
            const price = Number(item.price);

            subtotal += qty * price;

            tbody.innerHTML += `
                <tr>
                    <td>${item.product_name}</td>
                    <td>${qty}</td>
                    <td>₱${price.toLocaleString()}</td>
                </tr>
            `;
        });

        const shipping = 50;
        const grandTotal = subtotal + shipping;

        document.getElementById("subtotalLine").innerHTML =
            `<strong>Subtotal:</strong> ₱${subtotal.toLocaleString()}`;

        document.getElementById("shippingLine").innerHTML =
            `<strong>Shipping Fee:</strong> ₱${shipping.toLocaleString()}`;

        document.getElementById("totalLine").innerHTML =
            `<strong>Total:</strong> ₱${grandTotal.toLocaleString()}`;
    });
    // SUMMARY
}

document.addEventListener("DOMContentLoaded", function () {

    const modal = document.getElementById("viewOrderModal");
    const closeBtn = document.getElementById("closeViewModal");

    // close button
    if (closeBtn) {
        closeBtn.onclick = function () {
            modal.style.display = "none";
        };
    }

    // click outside modal
    window.onclick = function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };
});

function setStatus(button) {

    let row = button.closest("tr");

    let selectedStatus =
        row.querySelector(".statusSelect").value;

    row.querySelector(".hiddenStatus").value =
        selectedStatus;
}


//Update orders
document.addEventListener("DOMContentLoaded", function () {

    const rows = document.querySelectorAll("tr[data-status]");

    rows.forEach(row => {
        const status = row.dataset.status;

        switch (status) {
            case "Processing":
                row.style.backgroundColor = "#c4bebe";
                break;

            case "Shipped":
                row.style.backgroundColor = "#d1ecf1";
                break;

            case "Completed":
                row.style.backgroundColor = "#d4edda";
                break;

            case "Cancelled":
                row.style.backgroundColor = "#f8d7da";
                break;

            default:
                row.style.backgroundColor = "";
        }
    });

});

// CUSTOMER MANAGEMENT
function openViewCustomer(id, name, email, contact, address, orders) {

    document.getElementById("customerId").innerText = id;
    document.getElementById("customerName").innerText = name;
    document.getElementById("customerEmail").innerText = email;
    document.getElementById("customerContact").innerText = contact || "N/A";
    document.getElementById("customerAddress").innerText = address || "N/A";
    document.getElementById("customerOrders").innerText = orders;

    document.getElementById("customerModal").style.display = "flex";

    // LOAD ORDER HISTORY
    fetch(`fetchCustomersOrders.php?user_id=${id}`)
        .then(res => res.json())
        .then(orderList => {
            const tbody = document.getElementById("customerOrderHistory");
            tbody.innerHTML = "";

            if (orderList.length === 0) {
                tbody.innerHTML = `<tr><td colspan="4">No orders found.</td></tr>`;
                return;
            }

            orderList.forEach(order => {
                tbody.innerHTML += `
                    <tr>
                        <td>#${order.order_id}</td>
                        <td>₱${Number(order.total_amount).toLocaleString()}</td>
                        <td>${order.status}</td>
                        <td>${order.created_at}</td>
                    </tr>
                `;
            });
        });
}

// CLOSE MODAL
function closeCustomerModal() {
    document.getElementById("customerModal").style.display = "none";
}

// CLICK OUTSIDE TO CLOSE
window.addEventListener("click", function (event) {
    const modal = document.getElementById("customerModal");

    if (event.target === modal) {
        modal.style.display = "none";
    }
});




// LOGOUT FUNCTION
function openLogoutModal() {
    document.getElementById("logoutModal").style.display = "flex";
}

function closeLogoutModal() {
    document.getElementById("logoutModal").style.display = "none";
}

// close when clicking outside
window.addEventListener("click", function (event) {
    const modal = document.getElementById("logoutModal");

    if (event.target === modal) {
        modal.style.display = "none";
    }
});