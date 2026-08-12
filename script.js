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