-- SMEConnect — schema for delivery tracking, delivery fee estimator, trust score
-- Import this in phpMyAdmin / MySQL first, then run seed.sql


CREATE DATABASE IF NOT EXISTS smeconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smeconnect;
-- ---------- Product catalog ----------
-- References the existing `sellers` table (not a new seller concept).
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  seller_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(60) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  compare_at_price DECIMAL(10,2) DEFAULT NULL,  -- original price, for showing a discount; NULL = no discount
  stock_qty INT NOT NULL DEFAULT 0,
  status ENUM('active','draft') NOT NULL DEFAULT 'active',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (seller_id) REFERENCES sellers(id)
) ENGINE=InnoDB;
 
-- ---------- Cart ----------
-- Session-based: no login required to add to cart.
-- cart_token is a random string stored in the PHP session (see includes/cart.php).
CREATE TABLE cart_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cart_token VARCHAR(64) NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id),
  INDEX idx_cart_token (cart_token)
) ENGINE=InnoDB;
 
-- ---------- Order line items ----------
-- `orders` already stores subtotal/delivery/total, but not which products were bought.
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,  -- price at time of purchase, in case product.price changes later
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE DATABASE IF NOT EXISTS smeconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smeconnect;

-- ---------- Delivery fee estimator ----------
CREATE TABLE districts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  base_fee DECIMAL(10,2) NOT NULL,
  free_delivery_threshold DECIMAL(10,2) NOT NULL DEFAULT 1500.00
) ENGINE=InnoDB;

-- ---------- Trust score ----------
CREATE TABLE sellers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_name VARCHAR(120) NOT NULL,
  district_id INT,
  avg_rating DECIMAL(3,2) NOT NULL DEFAULT 0,      -- out of 5
  response_rate DECIMAL(5,2) NOT NULL DEFAULT 0,   -- percentage, 0-100
  disputes_count INT NOT NULL DEFAULT 0,
  id_verified TINYINT(1) NOT NULL DEFAULT 0,
  joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (district_id) REFERENCES districts(id)
) ENGINE=InnoDB;

-- ---------- Orders + delivery tracking ----------
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(20) NOT NULL UNIQUE,
  buyer_name VARCHAR(120),
  seller_id INT NOT NULL,
  district_id INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  delivery_fee DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (seller_id) REFERENCES sellers(id),
  FOREIGN KEY (district_id) REFERENCES districts(id)
) ENGINE=InnoDB;

CREATE TABLE order_status_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  status ENUM('placed','confirmed','out_for_delivery','delivered') NOT NULL,
  note VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB;