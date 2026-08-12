/* ===========================================================
   SMEConnect — script.js

   STEP 3 — Sidebar navigation, wishlist, cart
   STEP 4 — Delivery fee estimator
   STEP 5 — Seller trust score
   STEP 6 — Order tracking

   Step 7 — Chatbot will be added later.
   =========================================================== */

document.addEventListener('DOMContentLoaded', () => {
  initNavigation();
  initWishlist();
  initCart();
});

/* ===========================================================
   STEP 3 — SIDEBAR NAVIGATION
   =========================================================== */

function initNavigation() {
  const navItems = document.querySelectorAll('.sidebar .nav-item');

  navItems.forEach(item => {
    item.addEventListener('click', event => {
      event.preventDefault();

      navItems.forEach(navItem => {
        navItem.classList.remove('active');
      });

      item.classList.add('active');
    });
  });
}


/* ===========================================================
   STEP 3 — WISHLIST
   =========================================================== */

function initWishlist() {
  document.querySelectorAll('.p-media .fav').forEach(favBtn => {
    favBtn.addEventListener('click', event => {
      event.stopPropagation();

      const active = favBtn.classList.toggle('active');

      favBtn.textContent = active ? '♥' : '♡';
    });
  });
}


/* ===========================================================
   STEP 3 — CART
   =========================================================== */

/*
   The cart array is the single source of truth.

   The visible cart is always rebuilt from this array.
*/

let cart = [];


function initCart() {
  cart = scrapeInitialCartFromDom();

  renderCart();

  wireProductAddButtons();
  wireMiniAddButtons();
  wireCheckoutButton();
}


/* -----------------------------------------------------------
   Read existing cart items from index.html
   ----------------------------------------------------------- */

function scrapeInitialCartFromDom() {
  const rows = document.querySelectorAll('.cart-card .cart-item');

  const items = [];

  rows.forEach(row => {
    const thumb = row.querySelector('.cart-thumb');

    const name =
      row.querySelector('.ci-name')?.textContent.trim() || 'Item';

    const meta =
      row.querySelector('.ci-meta')?.textContent.trim() || '';

    const qtyText =
      row.querySelector('.qty-row span:nth-child(2)')
        ?.textContent.trim() || '1';

    const priceText =
      row.querySelector('.ci-price')
        ?.textContent.trim() || 'Rs 0';

    const unitTotal = parseRs(priceText);
    const qty = parseInt(qtyText, 10) || 1;

    items.push({
      key: name,
      name,
      meta,
      qty,
      unitPrice: unitTotal / qty,
      thumbBg: thumb?.style.background || 'var(--cream)',
      thumbHtml: thumb?.innerHTML || ''
    });
  });

  return items;
}


/* -----------------------------------------------------------
   Currency helpers
   ----------------------------------------------------------- */

function parseRs(text) {
  return parseFloat(
    String(text).replace(/[^\d.]/g, '')
  ) || 0;
}


function formatRs(amount) {
  return `Rs ${amount.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })}`;
}


/* -----------------------------------------------------------
   Add item to cart
   ----------------------------------------------------------- */

function addToCart({
  key,
  name,
  meta,
  unitPrice,
  thumbBg,
  thumbHtml
}) {
  const existing = cart.find(item => item.key === key);

  if (existing) {
    existing.qty += 1;
  } else {
    cart.push({
      key,
      name,
      meta,
      qty: 1,
      unitPrice,
      thumbBg,
      thumbHtml
    });
  }

  renderCart();
  flashCartCount();
}


/* -----------------------------------------------------------
   Change quantity
   ----------------------------------------------------------- */

function changeQty(key, delta) {
  const item = cart.find(cartItem => cartItem.key === key);

  if (!item) return;

  item.qty += delta;

  if (item.qty <= 0) {
    cart = cart.filter(cartItem => cartItem.key !== key);
  }

  renderCart();
}


/* -----------------------------------------------------------
   Render cart
   ----------------------------------------------------------- */

function renderCart() {
  const container = document.querySelector('.cart-card');

  if (!container) return;

  /*
     Remove previously rendered cart items.
     Keep the cart header, totals and checkout button.
  */

  container
    .querySelectorAll('.cart-item')
    .forEach(element => element.remove());

  const totalsEl = container.querySelector('.totals');

  if (!totalsEl) return;

  cart.forEach(item => {
    const row = document.createElement('div');

    row.className = 'cart-item';
    row.dataset.key = item.key;

    row.innerHTML = `
      <div
        class="cart-thumb gloss"
        style="background:${item.thumbBg}"
      >
        ${item.thumbHtml}
      </div>

      <div>
        <div class="ci-name">${item.name}</div>

        <div class="ci-meta">${item.meta}</div>

        <div class="qty-row">
          <span class="qty-btn" data-action="dec">–</span>

          <span
            class="qty-value"
            style="font-size:12px;font-weight:700"
          >
            ${item.qty}
          </span>

          <span class="qty-btn" data-action="inc">+</span>
        </div>
      </div>

      <div class="ci-price">
        ${formatRs(item.unitPrice * item.qty)}
      </div>
    `;

    totalsEl.before(row);

    const decreaseButton =
      row.querySelector('[data-action="dec"]');

    const increaseButton =
      row.querySelector('[data-action="inc"]');

    decreaseButton.addEventListener('click', () => {
      changeQty(item.key, -1);
    });

    increaseButton.addEventListener('click', () => {
      changeQty(item.key, 1);
    });
  });

  updateCartHead();
  updateTotals();
}


/* -----------------------------------------------------------
   Update cart heading and checkout button
   ----------------------------------------------------------- */

function updateCartHead() {
  const count = cart.reduce(
    (sum, item) => sum + item.qty,
    0
  );

  const heading =
    document.querySelector('.cart-head h3');

  if (heading) {
    heading.textContent = `My cart (${count})`;
  }

  const checkoutBtn =
    document.querySelector('.checkout-btn');

  if (checkoutBtn) {
    /*
       Preserve the arrow icon if one exists.
    */

    const icon = checkoutBtn.querySelector('svg');

    checkoutBtn.textContent = `Checkout (${count})`;

    if (icon) {
      checkoutBtn.appendChild(icon);
    }
  }
}


/* -----------------------------------------------------------
   Update subtotal and total
   ----------------------------------------------------------- */

function updateTotals() {
  const subtotal = cart.reduce(
    (sum, item) =>
      sum + item.unitPrice * item.qty,
    0
  );

  const subtotalEl =
    document.getElementById('cartSubtotal');

  if (subtotalEl) {
    subtotalEl.textContent = formatRs(subtotal);
  }

  /*
     Read any existing discount displayed in the HTML.
  */

  const discountText =
    document.querySelector('.t-row .save')
      ?.textContent || '0';

  const discount = Math.abs(
    parseRs(discountText)
  );

  /*
     Step 4 controls the delivery fee.
  */

  const deliveryFeeText =
    document.getElementById('deliveryFeeDisplay')
      ?.textContent || 'Free';

  const deliveryFee =
    deliveryFeeText.toLowerCase().includes('free')
      ? 0
      : parseRs(deliveryFeeText);

  const total = Math.max(
    0,
    subtotal - discount + deliveryFee
  );

  const totalEl =
    document.getElementById('cartTotal');

  if (totalEl) {
    totalEl.textContent = formatRs(total);
  }
}


/* -----------------------------------------------------------
   Visual feedback when cart changes
   ----------------------------------------------------------- */

function flashCartCount() {
  const heading =
    document.querySelector('.cart-head h3');

  if (!heading) return;

  heading.style.transition =
    'color .15s ease';

  heading.style.color =
    'var(--coral)';

  setTimeout(() => {
    heading.style.color = '';
  }, 300);
}


/* ===========================================================
   STEP 3 — PRODUCT ADD BUTTONS
   =========================================================== */

function wireProductAddButtons() {
  document.querySelectorAll('.p-card').forEach(card => {
    const addBtn =
      card.querySelector('.add-btn');

    if (!addBtn) return;

    addBtn.addEventListener('click', () => {
      const name =
        card.querySelector('.name')
          ?.textContent.trim() || 'Product';

      const priceEl =
        card.querySelector('.price');

      /*
         .price can contain the current price
         and an old/struck-through price.

         We only read the direct text node.
      */

      const priceText = priceEl
        ? priceEl.childNodes[0]?.textContent.trim() || 'Rs 0'
        : 'Rs 0';

      const media =
        card.querySelector('.p-media');

      addToCart({
        key: name,
        name,
        meta:
          card.querySelector('.cat')
            ?.textContent.trim() || '',
        unitPrice: parseRs(priceText),
        thumbBg:
          media?.style.background ||
          'var(--cream)',
        thumbHtml:
          media?.querySelector('svg')
            ?.outerHTML || ''
      });
    });
  });
}


/* ===========================================================
   STEP 3 — "YOU MIGHT ALSO LIKE" BUTTONS
   =========================================================== */

function wireMiniAddButtons() {
  document.querySelectorAll('.mini-item').forEach(item => {
    const addBtn =
      item.querySelector('.mini-add');

    if (!addBtn) return;

    addBtn.addEventListener('click', () => {
      const name =
        item.querySelector('.mini-name')
          ?.textContent.trim() || 'Item';

      const priceText =
        item.querySelector('.mini-price')
          ?.textContent.trim() || 'Rs 0';

      const thumb =
        item.querySelector('.mini-thumb');

      addToCart({
        key: name,
        name,
        meta: '',
        unitPrice: parseRs(priceText),
        thumbBg:
          thumb?.style.background ||
          'var(--cream)',
        thumbHtml:
          thumb?.innerHTML || ''
      });
    });
  });
}


/* ===========================================================
   STEP 3 — CHECKOUT BUTTON
   =========================================================== */

function wireCheckoutButton() {
  const btn =
    document.querySelector('.checkout-btn');

  if (!btn) return;

  btn.addEventListener('click', () => {
    if (cart.length === 0) {
      alert('Your cart is empty.');
      return;
    }

    const original =
      btn.innerHTML;

    btn.innerHTML =
      'Redirecting…';

    btn.disabled = true;

    setTimeout(() => {
      btn.innerHTML = original;
      btn.disabled = false;

      alert(
        "Checkout flow isn't built yet — this is a placeholder for now."
      );
    }, 600);
  });
}


/* ===========================================================
   STEP 4 — DELIVERY FEE ESTIMATOR
   =========================================================== */

/*
   This points to the PHP API folder inside XAMPP.

   Expected structure:

   htdocs/
   ├── smeconnect/
   │   ├── index.html
   │   ├── style.css
   │   └── script.js
   │
   └── smeconnect-backend/
       └── api/
           ├── estimate-delivery.php
           ├── trust-score.php
           └── order-status.php
*/

const API_BASE =
  '/smeconnect-backend/api';


/* -----------------------------------------------------------
   Generic API helper
   ----------------------------------------------------------- */

async function api(path, options = {}) {
  const response =
    await fetch(
      `${API_BASE}/${path}`,
      {
        headers: {
          'Content-Type':
            'application/json'
        },
        ...options
      }
    );

  if (!response.ok) {
    const body =
      await response
        .json()
        .catch(() => ({}));

    throw new Error(
      body.error ||
      `API error ${response.status} on ${path}`
    );
  }

  return response.json();
}


/* -----------------------------------------------------------
   Get cart subtotal
   ----------------------------------------------------------- */

function getCartSubtotal() {
  return cart.reduce(
    (sum, item) =>
      sum + item.unitPrice * item.qty,
    0
  );
}


/* -----------------------------------------------------------
   District selection
   ----------------------------------------------------------- */

async function selectDistrict(el) {
  document
    .querySelectorAll(
      '#districtPills .district-pill'
    )
    .forEach(pill => {
      pill.classList.remove('active');
    });

  el.classList.add('active');

  const districtId =
    el.dataset.districtId;

  const districtName =
    el.textContent.trim();

  const subtotal =
    getCartSubtotal();

  const sub =
    document.getElementById(
      'estimatorSub'
    );

  if (sub) {
    sub.textContent =
      'Checking…';
  }

  try {
    const data =
      await api(
        `estimate-delivery.php?district_id=${encodeURIComponent(
          districtId
        )}&subtotal=${encodeURIComponent(
          subtotal
        )}`
      );

    if (sub) {
      sub.textContent =
        `${
          data.free
            ? 'Free delivery'
            : `Rs ${Number(data.fee).toFixed(2)} delivery fee`
        } to ${data.district} — orders over Rs ${
          data.threshold
        } ship free.`;
    }

    const deliveryLabel =
      document.getElementById(
        'deliveryLabel'
      );

    if (deliveryLabel) {
      deliveryLabel.textContent =
        `Delivery (${districtName})`;
    }

    const deliveryFeeDisplay =
      document.getElementById(
        'deliveryFeeDisplay'
      );

    if (deliveryFeeDisplay) {
      deliveryFeeDisplay.textContent =
        data.free
          ? 'Free'
          : `Rs ${Number(data.fee).toFixed(2)}`;
    }

    updateTotals();

  } catch (error) {
    if (sub) {
      sub.textContent =
        'Could not reach the delivery API — check API_BASE and make sure Apache/MySQL are running in XAMPP.';
    }

    console.error(
      'Delivery estimate failed:',
      error
    );
  }
}


/*
   Run the delivery estimator once on page load
   using the currently active district.
*/

document.addEventListener(
  'DOMContentLoaded',
  () => {
    const activePill =
      document.querySelector(
        '#districtPills .district-pill.active'
      );

    if (activePill) {
      selectDistrict(activePill);
    }
  }
);


/* ===========================================================
   STEP 5 — SELLER TRUST SCORE
   =========================================================== */

async function loadTrustScores() {
  const sellerIds =
    new Set();

  /*
     Find normal seller IDs.
  */

  document
    .querySelectorAll(
      '[data-seller-id]'
    )
    .forEach(element => {
      sellerIds.add(
        element.dataset.sellerId
      );
    });

  /*
     Find maker/trust IDs.
  */

  document
    .querySelectorAll(
      '[data-maker-trust]'
    )
    .forEach(element => {
      sellerIds.add(
        element.dataset.makerTrust
      );
    });

  /*
     Request each seller's score.
  */

  for (const id of sellerIds) {
    try {
      const data =
        await api(
          `trust-score.php?seller_id=${encodeURIComponent(
            id
          )}`
        );

      document
        .querySelectorAll(
          `[data-seller-id="${id}"]`
        )
        .forEach(element => {
          element.textContent =
            data.trust_score;
        });

      document
        .querySelectorAll(
          `[data-maker-trust="${id}"]`
        )
        .forEach(element => {
          element.textContent =
            `Trust ${data.trust_score}`;
        });

    } catch (error) {
      console.error(
        `Could not load trust score for seller ${id}:`,
        error
      );

      /*
         Leave the existing static value
         visible if the API fails.
      */
    }
  }
}


document.addEventListener(
  'DOMContentLoaded',
  loadTrustScores
);


/* ===========================================================
   STEP 6 — ORDER TRACKING
   =========================================================== */

/*
   This is currently a homepage demo.

   Later, this can be replaced with the
   logged-in customer's real order.
*/

const TRACKED_ORDER_CODE =
  'SC-10493';


const TRACK_STEP_DEFS = [
  {
    key: 'placed',
    label: 'Placed'
  },
  {
    key: 'confirmed',
    label: 'Confirmed'
  },
  {
    key: 'out_for_delivery',
    label: 'Out for delivery'
  },
  {
    key: 'delivered',
    label: 'Delivered'
  }
];


const TRUCK_ICON_SVG = `
  <svg
    width="16"
    height="16"
    viewBox="0 0 24 24"
    fill="none"
    stroke="#fff"
    stroke-width="2"
  >
    <rect
      x="1"
      y="6"
      width="15"
      height="12"
      rx="1"
    />
    <path d="M16 10h4l3 3v5h-7z" />
    <circle cx="6" cy="19" r="2" />
    <circle cx="18" cy="19" r="2" />
  </svg>
`;


/* -----------------------------------------------------------
   Load order tracking
   ----------------------------------------------------------- */

async function loadOrderTracking() {
  const oidEl =
    document.getElementById(
      'trackingOid'
    );

  const stepsEl =
    document.getElementById(
      'trackSteps'
    );

  if (!oidEl || !stepsEl) {
    return;
  }

  try {
    const data =
      await api(
        `order-status.php?order_code=${encodeURIComponent(
          TRACKED_ORDER_CODE
        )}`
      );

    const currentIdx =
      TRACK_STEP_DEFS.findIndex(
        step =>
          step.key ===
          data.current_status
      );

    oidEl.textContent =
      `Order #${data.order_code} · Rs ${Number(
        data.total
      ).toFixed(2)}`;

    stepsEl.innerHTML =
      TRACK_STEP_DEFS
        .map((step, index) => {
          const historyEntry =
            data.history?.find(
              history =>
                history.status ===
                step.key
            );

          const time =
            historyEntry
              ? new Date(
                  historyEntry.created_at
                    .replace(' ', 'T')
                ).toLocaleString(
                  'en-GB',
                  {
                    weekday: 'short',
                    hour: '2-digit',
                    minute: '2-digit'
                  }
                )
              : '—';

          let className = '';
          let nodeContent =
            index + 1;

          if (index < currentIdx) {
            className = 'done';
            nodeContent = '✓';

          } else if (
            index === currentIdx
          ) {
            className = 'current';

            nodeContent =
              step.key ===
              'out_for_delivery'
                ? TRUCK_ICON_SVG
                : index + 1;
          }

          return `
            <div class="track-step ${className}">
              <div class="node">
                ${nodeContent}
              </div>

              <div class="label">
                ${step.label}
              </div>

              <div class="time">
                ${time}
              </div>
            </div>
          `;
        })
        .join('');

  } catch (error) {
    oidEl.textContent =
      'Could not load order tracking — check API_BASE and make sure Apache/MySQL are running.';

    console.error(
      'Order tracking failed:',
      error
    );
  }
}


document.addEventListener(
  'DOMContentLoaded',
  loadOrderTracking
);