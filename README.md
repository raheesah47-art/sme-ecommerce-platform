# SMEConnect — Shop local. Support growth.

SMEConnect is a PHP/MySQL multi-vendor marketplace prototype for Mauritian SMEs.

## Current working architecture

- PHP application (`index.php` is the canonical entry point)
- MySQL database (`database/schema.sql` + `database/seed.sql`)
- Session-based shopping cart
- Customer registration/login with password hashing and CSRF protection
- Product catalogue, search and categories
- SME/store directory and trust scores
- Checkout, order creation and stock deduction
- Order history and tracking timeline
- Delivery-fee estimator API
- Trust-score API
- Order-status API

## Local XAMPP setup

1. Copy the repository into `/Applications/XAMPP/xamppfiles/htdocs/SMEConnect`.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin and import `database/schema.sql`.
4. Import `database/seed.sql`.
5. Open `http://localhost/SMEConnect/`.

Demo accounts after seeding:

- Admin: `admin@smeconnect.mu` / `Admin@123`
- SME: `craftsmu@smeconnect.mu` / `SmeOwner@123`
- Customer: `customer1@example.com` / `Customer@123`

## Important

The old static `index.html`/`script.js` frontend was removed because it was a separate, incompatible application layer. The PHP application is now the single source of truth.

Chatbot functionality is intentionally not part of the core buyer flow yet and can be integrated after the marketplace is stable.
