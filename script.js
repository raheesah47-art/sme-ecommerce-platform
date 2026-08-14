/**
 * js/script.js
 * Live product search & filtering for pages/products.php.
 * Debounced fetch calls to api/products.php re-render the grid
 * without a full page reload.
 */

(function () {
    const grid = document.getElementById('product-grid');
    if (!grid) return; // not on the products page

    const qInput = document.getElementById('filter-q');
    const categorySelect = document.getElementById('filter-category');
    const minPriceInput = document.getElementById('filter-min-price');
    const maxPriceInput = document.getElementById('filter-max-price');
    const districtSelect = document.getElementById('filter-district');
    const sortSelect = document.getElementById('filter-sort');
    const resultsCount = document.getElementById('results-count');

    // window.SITE_URL is injected by includes/partials/header.php
    const siteUrl = window.SITE_URL || '';
    const apiBase = siteUrl + '/api/products.php';
    const productPageUrl = siteUrl + '/pages/product.php';

    let debounceTimer = null;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function renderProducts(products) {
        if (!products.length) {
            grid.innerHTML = '<p class="empty-state">No products found.</p>';
            return;
        }

        grid.innerHTML = products.map(function (p) {
            const imageHtml = p.primary_image_url
                ? '<img src="' + escapeHtml(p.primary_image_url) + '" alt="' + escapeHtml(p.name) + '">'
                : '<div class="no-image-placeholder">' + escapeHtml(p.name.charAt(0)) + '</div>';

            const stockBadge = p.stock_quantity <= 0
                ? '<span class="badge badge-out-of-stock">Out of stock</span>'
                : '';

            return (
                '<a class="product-card" href="' + productPageUrl + '?id=' + p.id + '">' +
                    '<div class="product-card-image">' + imageHtml + stockBadge + '</div>' +
                    '<div class="product-card-body">' +
                        '<h3>' + escapeHtml(p.name) + '</h3>' +
                        '<p class="product-card-seller">' + escapeHtml(p.sme_business_name) +
                            ' <span class="trust-badge" title="Trust Score">\u2605 ' + p.sme_trust_score + '</span></p>' +
                        '<p class="product-card-price">Rs ' + p.price.toFixed(2) + '</p>' +
                    '</div>' +
                '</a>'
            );
        }).join('');
    }

    function fetchProducts() {
        const params = new URLSearchParams();
        if (qInput.value.trim()) params.set('q', qInput.value.trim());
        if (categorySelect.value) params.set('category', categorySelect.value);
        if (minPriceInput.value) params.set('min_price', minPriceInput.value);
        if (maxPriceInput.value) params.set('max_price', maxPriceInput.value);
        if (districtSelect.value) params.set('district', districtSelect.value);
        if (sortSelect.value) params.set('sort', sortSelect.value);

        fetch(apiBase + '?' + params.toString())
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success) {
                    grid.innerHTML = '<p class="empty-state">Something went wrong loading products.</p>';
                    return;
                }
                resultsCount.textContent = data.count + ' product' + (data.count === 1 ? '' : 's') + ' found';
                renderProducts(data.products);
            })
            .catch(function () {
                grid.innerHTML = '<p class="empty-state">Could not load products. Check your connection.</p>';
            });
    }

    function debouncedFetch() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchProducts, 300);
    }

    [qInput, minPriceInput, maxPriceInput].forEach(function (el) {
        el.addEventListener('input', debouncedFetch);
    });
    [categorySelect, districtSelect, sortSelect].forEach(function (el) {
        el.addEventListener('change', fetchProducts);
    });
})();


const API_BASE = 'api'; // adjust if your api/ folder is elsewhere relative to index.html
 
async function apiPost(path, body) {
  const res = await fetch(`${API_BASE}/${path}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body)
  });
  const data = await res.json();
  if (!res.ok) throw new Error(data.error || 'Request failed');
  return data;
}
 
async function apiGet(path) {
  const res = await fetch(`${API_BASE}/${path}`);
  const data = await res.json();
  if (!res.ok) throw new Error(data.error || 'Request failed');
  return data;
}
 
// Called by each "+" button on a product card: <button class="add-btn" onclick="addToCart(1, this)">+</button>
async function addToCart(productId, btnEl) {
  btnEl.disabled = true;
  try {
    const cart = await apiPost('add-to-cart.php', { product_id: productId, quantity: 1 });
    renderCart(cart);
  } catch (err) {
    alert(err.message);
  } finally {
    btnEl.disabled = false;
  }
}
 
async function changeQuantity(cartItemId, newQty) {
  try {
    const cart = await apiPost('update-cart-item.php', { cart_item_id: cartItemId, quantity: newQty });
    renderCart(cart);
  } catch (err) {
    alert(err.message);
  }
}
 
async function removeItem(cartItemId) {
  const cart = await apiPost('remove-cart-item.php', { cart_item_id: cartItemId });
  renderCart(cart);
}
 
async function loadCart() {
  const cart = await apiGet('get-cart.php');
  renderCart(cart);
}
 
// Rebuilds the cart panel from live data instead of the old hardcoded HTML.
function renderCart(cart) {
  const list = document.getElementById('cartItemsList');
  const headCount = document.getElementById('cartCount');
  const subtotalEl = document.getElementById('cartSubtotal');
  const totalEl = document.getElementById('cartTotal');
  const checkoutBtn = document.getElementById('checkoutBtn');
 
  if (headCount) headCount.textContent = `(${cart.count})`;
  if (subtotalEl) subtotalEl.textContent = `Rs ${cart.subtotal.toFixed(2)}`;
  if (totalEl) totalEl.textContent = `Rs ${cart.subtotal.toFixed(2)}`; // delivery fee added at checkout step
  if (checkoutBtn) checkoutBtn.textContent = `Checkout (${cart.count})`;
 
  if (!list) return;
  list.innerHTML = '';
 
  if (cart.items.length === 0) {
    list.innerHTML = '<p style="padding:16px;color:var(--ink-soft)">Your cart is empty.</p>';
    return;
  }
 
  cart.items.forEach(item => {
    const row = document.createElement('div');
    row.className = 'cart-item';
    row.innerHTML = `
      <div>
        <div class="ci-name">${item.name}</div>
        <div class="ci-meta">${item.seller_name}</div>
        <div class="qty-row">
          <span class="qty-btn" onclick="changeQuantity(${item.cart_item_id}, ${item.quantity - 1})">–</span>
          <span style="font-size:12px;font-weight:700">${item.quantity}</span>
          <span class="qty-btn" onclick="changeQuantity(${item.cart_item_id}, ${item.quantity + 1})">+</span>
        </div>
      </div>
      <div class="ci-price">Rs ${item.line_total.toFixed(2)}</div>
    `;
    list.appendChild(row);
  });
}
 
// Called by the Checkout button: <button id="checkoutBtn" class="checkout-btn" onclick="checkout()">
async function checkout() {
  const activeDistrict = document.querySelector('.district-pill.active');
  const districtId = activeDistrict ? activeDistrict.dataset.districtId : 1;
 
  try {
    const result = await apiPost('checkout.php', {
      district_id: parseInt(districtId, 10),
      buyer_name: 'Rahee N.' // replace with logged-in user's name once auth is wired to the front end
    });
    const codes = result.orders.map(o => o.order_code).join(', ');
    alert(`Order placed! Order code(s): ${codes}`);
    loadCart(); // cart is now empty, refresh the panel
  } catch (err) {
    alert(err.message);
  }
}
 
// Load the real cart from the DB as soon as the page loads,
// instead of showing the old hardcoded 4-item cart.
document.addEventListener('DOMContentLoaded', loadCart);
 