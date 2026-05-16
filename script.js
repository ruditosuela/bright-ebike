// SEARCH FUNCTION
const searchInput = document.getElementById("searchInput");
const searchResults = document.getElementById("searchResults");
const ebikeCards = document.querySelectorAll(".ebike-card");

if (searchInput) {
    searchInput.addEventListener("input", () => {
        const value = searchInput.value.toLowerCase().trim();

        // Filter cards on e-bikes.php
        if (ebikeCards.length > 0) {
            ebikeCards.forEach(card => {
                const name = card.querySelector(".ebike-name").textContent.toLowerCase();
                card.style.display = name.includes(value) ? "block" : "none";
            });
        }

        // dropdown search function
        if (!searchResults) return;

        if (value.length < 1) {
            searchResults.innerHTML = "";
            searchResults.style.display = "none";
            return;
        }

        fetch(`search.php?q=${encodeURIComponent(value)}`)
            .then(res => res.json())
            .then(products => {
                searchResults.innerHTML = "";

                if (products.length === 0) {
                    searchResults.innerHTML = `<div class="searchNoResult">No results found</div>`;
                    searchResults.style.display = "block";
                    return;
                }

                products.forEach(product => {
                    const imagePath = product.image
                        ? `assets/${product.image}`
                        : `assets/default-bike.png`;

                    const item = document.createElement("div");
                    item.classList.add("searchResultItem");
                    item.innerHTML = `
                        <img src="${imagePath}" alt="${product.product_name}">
                        <div class="searchResultInfo">
                            <span class="searchResultName">${product.product_name}</span>
                            <span class="searchResultPrice">₱${parseFloat(product.price).toLocaleString()}</span>
                        </div>
                    `;

                    item.addEventListener("click", () => {
                        window.location.href = `e-bikes.php?highlight=${encodeURIComponent(product.product_name)}`;
                    });

                    searchResults.appendChild(item);
                });

                searchResults.style.display = "block";
            });
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
        if (searchResults && !searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = "none";
        }
    });
}

// higlight ebikes when searching
const urlParams = new URLSearchParams(window.location.search);
const highlightName = urlParams.get("highlight");

if (highlightName) {
    const allCards = document.querySelectorAll(".ebike-card");
    allCards.forEach(card => {
        const name = card.querySelector(".ebike-name");
        if (name && name.textContent.trim().toLowerCase() === highlightName.toLowerCase()) {
            card.scrollIntoView({ behavior: "smooth", block: "center" });
            card.classList.add("highlighted");

            setTimeout(() => card.classList.remove("highlighted"), 3000);
        }
    });
}

// NAV BAR SHOPPING CART POP UP FUNCTION
document.addEventListener("DOMContentLoaded", () => {

    const cartBtn = document.getElementById("cartBtn");
    const cartOverlay = document.getElementById("cartOverlay");
    const cartWindow = document.getElementById("cartWindow");
    const cartCloseBtn = document.getElementById("cartCloseBtn");

    if (cartBtn) {
        cartBtn.addEventListener("click", () => {
            cartOverlay.classList.add("active");
            cartWindow.classList.add("active");
        });
    }

    if (cartCloseBtn) {
        cartCloseBtn.addEventListener("click", () => {
            cartOverlay.classList.remove("active");
            cartWindow.classList.remove("active");
        });
    }

    if (cartOverlay) {
        cartOverlay.addEventListener("click", () => {
            cartOverlay.classList.remove("active");
            cartWindow.classList.remove("active");
        });
    }

});


// NAV BAR SHOPPING CART

const cartContent = document.getElementById("cartContent");
const totalPriceElement = document.querySelector(".cartTotalCheckout span");
const cartCount = document.getElementById("cartCount");

let cart = JSON.parse(localStorage.getItem("cart")) || [];

function saveCart() {
    localStorage.setItem("cart", JSON.stringify(cart));
}


function renderCart() {
    if (!cartContent || !totalPriceElement || !cartCount) return;
    cartContent.innerHTML = "";

    let total = 0;

    cart.forEach((item, index) => {
        let qty = item.quantity || 1;
        total += item.price * qty;

        const cartItem = document.createElement("div");
        cartItem.classList.add("cartItem");

        cartItem.innerHTML = `
            <div class="cartRow">

        <div class="productCol">
            <small class="label">Product</small>
            <p class="productName">${item.name}</p>
            <span class="price">₱${item.price.toLocaleString()}</span>
        </div>

        <div class="qtyCol">
            <small class="label">Quantity</small>

            <div class="qtyControls">
                <button class="minusBtn">−</button>
                <span>${qty}</span>
                <button class="plusBtn">+</button>
                <button class="removeItem">x</button>
            </div>
        </div>

    </div>
        `;

        // MINUS QUANTITY
        cartItem.querySelector(".minusBtn").addEventListener("click", () => {
            cart[index].quantity = Math.max(1, (cart[index].quantity || 1) - 1);

            saveCart();
            renderCart();
        });

        // PLUS QUANTITY
        cartItem.querySelector(".plusBtn").addEventListener("click", () => {
            cart[index].quantity = (cart[index].quantity || 1) + 1;
            saveCart();
            renderCart();
        });
        // REMOVE FROM CART
        cartItem.querySelector(".removeItem").addEventListener("click", () => {
            cart.splice(index, 1);
            saveCart();
            renderCart();
        });

        cartContent.appendChild(cartItem);
    });

    totalPriceElement.textContent = `₱${total.toLocaleString()}`;

    // update badge
    let itemCount = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);
    cartCount.textContent = itemCount;

    if (cart.length > 0) {
        cartCount.style.display = "block";
    } else {
        cartCount.style.display = "none";
    }
}


document.querySelectorAll(".addToCart").forEach(button => {
    button.addEventListener("click", () => {

        const card = button.closest(".ebike-card");

        const product_id = button.dataset.id;
        const name = button.dataset.name;
        const price = parseFloat(button.dataset.price);
        const image = card.querySelector(".ebike-image").src;

        let existingItem = cart.find(item => item.product_id === product_id);

        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                product_id,
                name,
                price,
                image,
                quantity: 1
            });
        }

        saveCart();
        renderCart();
    });
});

renderCart();

const links = document.querySelectorAll(".navLinks a");
const page = location.pathname.split("/").pop();

links.forEach(link => {
    if (link.href.includes(page)) {
        link.classList.add("active");
    }
});


const MERCHANT_GCASH_NUMBER = "09708101973";
 
const checkoutModalOverlay = document.getElementById("checkoutModalOverlay");
const checkoutModal        = document.getElementById("checkoutModal");
const checkoutModalClose   = document.getElementById("checkoutModalClose");
 
function openCheckoutModal() {
    renderCheckoutModal();
    checkoutModalOverlay.classList.add("active");
    checkoutModal.classList.add("active");
    document.body.classList.add("modalOpen");
}
 
function closeCheckoutModal() {
    checkoutModalOverlay.classList.remove("active");
    checkoutModal.classList.remove("active");
    document.body.classList.remove("modalOpen");
}
 
if (checkoutModalClose) {
    checkoutModalClose.addEventListener("click", closeCheckoutModal);
}
if (checkoutModalOverlay) {
    checkoutModalOverlay.addEventListener("click", closeCheckoutModal);
}
 
function renderCheckoutModal() {
    const itemsContainer = document.getElementById("checkoutModalItems");
    if (!itemsContainer) return;
 
    itemsContainer.innerHTML = "";
    let subtotal = 0;
 
    cart.forEach(item => {
        let qty = item.quantity || 1;
        let itemTotal = item.price * qty;
        subtotal += itemTotal;
 
        itemsContainer.innerHTML += `
            <div class="checkoutItem">
                <span>${item.name}</span>
                <span>${qty}</span>
                <span>₱${itemTotal.toLocaleString()}</span>
            </div>
        `;
    });
 
    document.getElementById("checkoutSubtotal").innerText = "₱" + subtotal.toLocaleString();
    document.getElementById("checkoutTotal").innerText    = "₱" + (subtotal + 50).toLocaleString();
}
 
// Replaces old goToCheckout — opens modal instead of redirecting
function goToCheckout() {
    if (cart.length === 0) {
        alert("Your cart is empty!");
        return;
    }
 
    // Close the cart window first
    const cartOverlay = document.getElementById("cartOverlay");
    const cartWindow  = document.getElementById("cartWindow");
    if (cartOverlay) cartOverlay.classList.remove("active");
    if (cartWindow)  cartWindow.classList.remove("active");
 
    openCheckoutModal();
}
 
// GCash payment panel toggle
const paymentSelect  = document.getElementById("paymentSelect");
const gcashPanel     = document.getElementById("gcashPanel");
const gcashNumberInput = document.getElementById("gcashNumber");
const openGcashBtn   = document.getElementById("openGcashBtn");
 
if (paymentSelect) {
    paymentSelect.addEventListener("change", () => {
        if (paymentSelect.value === "gcash") {
            gcashPanel.classList.add("visible");
        } else {
            gcashPanel.classList.remove("visible");
            if (gcashNumberInput) gcashNumberInput.value = "";
            if (openGcashBtn) openGcashBtn.style.display = "none";
        }
    });
}
 
if (gcashNumberInput) {
    gcashNumberInput.addEventListener("input", () => {
        const val = gcashNumberInput.value.trim();
        if (openGcashBtn) {
            openGcashBtn.style.display = /^09\d{9}$/.test(val) ? "block" : "none";
        }
    });
}
 
if (openGcashBtn) {
    openGcashBtn.addEventListener("click", openGcashApp);
}
 
function getCartTotal() {
    let subtotal = 0;
    cart.forEach(item => { subtotal += item.price * (item.quantity || 1); });
    return subtotal + 50;
}
 
function openGcashApp() {
    const amount = getCartTotal();
    const gcashDeepLink = `gcash://send?to=${MERCHANT_GCASH_NUMBER}&amount=${amount}`;
    window.location.href = gcashDeepLink;
    setTimeout(() => {
        if (!document.hidden) window.location.href = "https://www.gcash.com";
    }, 2000);
}
 
// Place Order button
const placeOrderBtn = document.getElementById("placeOrderBtn");
if (placeOrderBtn) {
    placeOrderBtn.addEventListener("click", () => {
 
        if (cart.length === 0) {
            alert("Cart is empty!");
            return;
        }
 
        const paymentMethod = paymentSelect ? paymentSelect.value : "cod";
 
        if (paymentMethod === "gcash") {
            const gcashNumber = gcashNumberInput ? gcashNumberInput.value.trim() : "";
            if (!gcashNumber) {
                alert("Please enter your GCash number.");
                if (gcashNumberInput) gcashNumberInput.focus();
                return;
            }
            if (!/^09\d{9}$/.test(gcashNumber)) {
                alert("Please enter a valid GCash number (e.g. 09XXXXXXXXX).");
                if (gcashNumberInput) gcashNumberInput.focus();
                return;
            }
        }
 
        let subtotal = 0;
        cart.forEach(item => { subtotal += item.price * (item.quantity || 1); });
        const total = subtotal + 50;
 
        fetch("placeOrder.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                cart: cart,
                total: total,
                payment: paymentMethod,
                gcash_number: paymentMethod === "gcash" && gcashNumberInput
                    ? gcashNumberInput.value.trim()
                    : null
            })
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            localStorage.removeItem("cart");
            cart = [];
            renderCart();
            closeCheckoutModal();
            window.location.href = "index.php";
        })
        .catch(error => {
            console.error(error);
            alert("Something went wrong.");
        });
    });
}







// AVATAR FUNCTION
const profileBtn = document.getElementById("profileBtn");
const profileOverlay = document.getElementById("profileOverlay");
const profileWindow = document.getElementById("profileWindow");
const profileCloseBtn = document.getElementById("profileCloseBtn");

if (profileBtn) {
profileBtn.addEventListener("click", () => {
   if (profileBtn && profileOverlay && profileWindow && profileCloseBtn) {

    profileBtn.addEventListener("click", () => {
        profileOverlay.classList.add("active");
        profileWindow.classList.add("active");
    });

    profileCloseBtn.addEventListener("click", () => {
        profileOverlay.classList.remove("active");
        profileWindow.classList.remove("active");
    });

    profileOverlay.addEventListener("click", () => {
        profileOverlay.classList.remove("active");
        profileWindow.classList.remove("active");
    });

    document.querySelectorAll(".loginBtn, .signupBtn").forEach(btn => {
        btn.addEventListener("click", () => {
            profileOverlay.classList.remove("active");
            profileWindow.classList.remove("active");
        });
    });
    
}
}); 
}

//  FEATURED E-BIKES
function filterBikes(type) {
    const bikes = document.querySelectorAll(".ebike-card");

    bikes.forEach(bike => {

        if (type === "all") {
            bike.style.display = "flex";
        }
        else if (bike.classList.contains(type)) {
            bike.style.display = "flex";
        }
        else {
            bike.style.display = "none";
        }

    });
}

const two = document.querySelector('.TwoWheelerActive');
const three = document.querySelector('.ThreeWheeler');
const four = document.querySelector('.FourWheeler');

const twoDisplay = document.querySelector('.TwoWheelsActiveDisplay');
const threeDisplay = document.querySelector('.ThreeWheelsDisplay');
const fourDisplay = document.querySelector('.FourWheelsDisplay');

const left = document.querySelector('.arrowLeft');
const right = document.querySelector('.arrowRight');

if (two && three && four && twoDisplay && threeDisplay && fourDisplay && left && right) {

    const originalTwo = twoDisplay.innerHTML;
    const originalThree = threeDisplay.innerHTML;
    const originalFour = fourDisplay.innerHTML;

    function reset() {
        two.classList.remove('TwoWheelerActive');
        three.classList.remove('TwoWheelerActive');
        four.classList.remove('TwoWheelerActive');

        if (!two.classList.contains('TwoWheeler')) two.classList.add('TwoWheeler');
        if (!three.classList.contains('ThreeWheeler')) three.classList.add('ThreeWheeler');
        if (!four.classList.contains('FourWheeler')) four.classList.add('FourWheeler');

        twoDisplay.style.display = "none";
        threeDisplay.style.display = "none";
        fourDisplay.style.display = "none";
    }

    function rotateLeft(container) {
        container.appendChild(container.firstElementChild);
    }

    function rotateRight(container) {
        container.prepend(container.lastElementChild);
    }

    function resetCarouselOrder() {
        twoDisplay.innerHTML = originalTwo;
        threeDisplay.innerHTML = originalThree;
        fourDisplay.innerHTML = originalFour;
    }

    two.addEventListener('click', () => {
        reset();
        resetCarouselOrder();
        two.classList.add('TwoWheelerActive');
        twoDisplay.style.display = "flex";
    });

    three.addEventListener('click', () => {
        reset();
        resetCarouselOrder();
        three.classList.add('TwoWheelerActive');
        threeDisplay.style.display = "flex";
    });

    four.addEventListener('click', () => {
        reset();
        resetCarouselOrder();
        four.classList.add('TwoWheelerActive');
        fourDisplay.style.display = "flex";
    });

    // left.addEventListener('click', () => {
    //     if (two.classList.contains('TwoWheelerActive')) rotateLeft(twoDisplay);
    //     else if (three.classList.contains('TwoWheelerActive')) rotateLeft(threeDisplay);
    //     else if (four.classList.contains('TwoWheelerActive')) rotateLeft(fourDisplay);
    // });

    // right.addEventListener('click', () => {
    //     if (two.classList.contains('TwoWheelerActive')) rotateRight(twoDisplay);
    //     else if (three.classList.contains('TwoWheelerActive')) rotateRight(threeDisplay);
    //     else if (four.classList.contains('TwoWheelerActive')) rotateRight(fourDisplay);
    // });
        function isMobile() {
        return window.innerWidth <= 768;
    }

    function getActiveDisplay() {
        if (two.classList.contains('TwoWheelerActive')) return twoDisplay;
        if (three.classList.contains('TwoWheelerActive')) return threeDisplay;
        if (four.classList.contains('TwoWheelerActive')) return fourDisplay;
        return null;
    }

    function mobileSlide(container, direction) {
        const images = Array.from(container.querySelectorAll('img'));
        const visible = images.findIndex(img => img.style.display !== 'none');
        
        // hide current
        images[visible].style.display = 'none';
        
        // show next or previous
        let next;
        if (direction === 'right') {
            next = (visible + 1) % images.length;
        } else {
            next = (visible - 1 + images.length) % images.length;
        }
        images[next].style.display = 'block';
    }

    left.addEventListener('click', () => {
        const active = getActiveDisplay();
        if (!active) return;

        if (isMobile()) {
            mobileSlide(active, 'left');
        } else {
            rotateLeft(active);
        }
    });

    right.addEventListener('click', () => {
        const active = getActiveDisplay();
        if (!active) return;

        if (isMobile()) {
            mobileSlide(active, 'right');
        } else {
            rotateRight(active);
        }
    });
}

//FILTER
(function () {
    let pendingBrand = "all";
    let pendingPrice = "all";
    let activeBrand  = "all";
    let activePrice  = "all";

    const openFilterBtn  = document.getElementById("openFilterBtn");
    const filterOverlay  = document.getElementById("filterOverlay");
    const filterModal    = document.getElementById("filterModal");
    const filterCloseBtn = document.getElementById("filterCloseBtn");
    const filterResetBtn = document.getElementById("filterResetBtn");
    const filterOkBtn    = document.getElementById("filterOkBtn");

    if (!openFilterBtn || !filterOverlay || !filterModal || !filterCloseBtn || !filterResetBtn || !filterOkBtn) return;
    openFilterBtn.addEventListener("click", () => {
        pendingBrand = activeBrand;
        pendingPrice = activePrice;
        syncPillUI();
        filterOverlay.classList.add("active");
        filterModal.classList.add("active");
    });

    function closeModal() {
        filterOverlay.classList.remove("active");
        filterModal.classList.remove("active");
    }

    filterCloseBtn.addEventListener("click", closeModal);
    filterOverlay.addEventListener("click", closeModal);

    document.querySelectorAll(".filterPill").forEach(pill => {
        pill.addEventListener("click", () => {
            if (pill.dataset.filter === "brand") pendingBrand = pill.dataset.val;
            if (pill.dataset.filter === "price") pendingPrice = pill.dataset.val;
            syncPillUI();
        });
    });

    function syncPillUI() {
        document.querySelectorAll(".filterPill[data-filter='brand']").forEach(p => {
            p.classList.toggle("active", p.dataset.val === pendingBrand);
        });
        document.querySelectorAll(".filterPill[data-filter='price']").forEach(p => {
            p.classList.toggle("active", p.dataset.val === pendingPrice);
        });
    }

    // RESET FILTER
    filterResetBtn.addEventListener("click", () => {
        pendingBrand = activeBrand = "all";
        pendingPrice = activePrice = "all";
        syncPillUI();
        applyFilters();
        closeModal();
    });
    
    // SAVE FILTER
    filterOkBtn.addEventListener("click", () => {
        activeBrand = pendingBrand;
        activePrice = pendingPrice;
        applyFilters();
        closeModal();
    });

    function applyFilters() {
        document.querySelectorAll(".ebike-card").forEach(card => {
            const cardBrand = (card.dataset.brand || "").toLowerCase();
            const cardPrice = parseFloat(card.dataset.price || "0");

            let brandMatch = activeBrand === "all"
                || (activeBrand === "other" ? !["ofero","wuso","nwow","bosn"].includes(cardBrand) : cardBrand === activeBrand);

            let priceMatch = activePrice === "all"
                || (activePrice === "budget"  && cardPrice < 30000)
                || (activePrice === "mid"     && cardPrice >= 30000 && cardPrice <= 60000)
                || (activePrice === "premium" && cardPrice > 60000);

            card.style.display = (brandMatch && priceMatch) ? "flex" : "none";
        });
    }
})();

//VIEW SPECS E-BIKE
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".viewSpecs").forEach(button => {
        button.addEventListener("click", () => {
            const name = button.dataset.name;
            const specs = button.dataset.specs;

            document.getElementById("specsProductName").textContent = name;

            const specsFormatted = specs.split("|").map(s => `<p>${s.trim()}</p>`).join("");
            document.getElementById("specsContent").innerHTML = specsFormatted;

            document.getElementById("specsOverlay").classList.add("active");
            document.getElementById("specsModal").classList.add("active");
        });
    });

    const specsCloseBtn = document.getElementById("specsCloseBtn");
    const specsOverlay = document.getElementById("specsOverlay");

    if (specsCloseBtn) {
        specsCloseBtn.addEventListener("click", () => {
            specsOverlay.classList.remove("active");
            document.getElementById("specsModal").classList.remove("active");
        });
    }

    if (specsOverlay) {
        specsOverlay.addEventListener("click", () => {
            specsOverlay.classList.remove("active");
            document.getElementById("specsModal").classList.remove("active");
        });
    }
});


const schedBtn = document.querySelector(".schedBtn");

if (schedBtn) {
    const modal = document.getElementById("scheduleModal");
    const overlay = document.getElementById("modalOverlay");
    const closeBtn = document.getElementById("closeModal");

    schedBtn.addEventListener("click", () => {
        modal.classList.add("active");
        overlay.classList.add("active");
    });

    closeBtn.addEventListener("click", () => {
        modal.classList.remove("active");
        overlay.classList.remove("active");
    });

    overlay.addEventListener("click", () => {
        modal.classList.remove("active");
        overlay.classList.remove("active");
    });
}

// HAMBURGER MENU
const hamburger = document.getElementById("hamburger");
const navLinks = document.getElementById("navLinks");

if (hamburger && navLinks) {
    hamburger.addEventListener("click", () => {
        navLinks.classList.toggle("open");
    });
}