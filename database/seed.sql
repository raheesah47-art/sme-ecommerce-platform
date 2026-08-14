USE smeconnect;

INSERT INTO districts (name, base_fee, free_delivery_threshold) VALUES
('Port Louis', 50.00, 1500.00),
('Plaines Wilhems', 60.00, 1500.00),
('Moka', 70.00, 1500.00),
('Flacq', 90.00, 1500.00),
('Grand Port', 95.00, 1500.00);

INSERT INTO sellers (business_name, district_id, avg_rating, response_rate, disputes_count, id_verified) VALUES
('Atelier Coco', 1, 4.9, 96.0, 0, 1),
('Ferme Bois Chéri', 3, 4.8, 90.0, 0, 1),
('Karo Design', 2, 4.7, 82.0, 1, 1);

INSERT INTO orders (order_code, buyer_name, seller_id, district_id, subtotal, delivery_fee, total) VALUES
('SC-10493', 'Rahee N.', 1, 1, 2170.00, 0.00, 2170.00);

-- Order history so far: placed -> confirmed -> out for delivery (matches the mockup's tracker)
INSERT INTO order_status_log (order_id, status, note) VALUES
(1, 'placed', 'Order placed by buyer'),
(1, 'confirmed', 'Confirmed by Atelier Coco'),
(1, 'out_for_delivery', 'Courier picked up the parcel');

INSERT INTO sellers (business_name, district_id, avg_rating, response_rate, disputes_count, id_verified) VALUES
('Île Naturelle', 2, 4.6, 88.0, 0, 1);
-- This will be seller id 4, assuming seed.sql ran first with ids 1-3 already used.
 
INSERT INTO products (seller_id, name, category, price, compare_at_price, stock_qty) VALUES
(1, 'Hand-dyed sarong wrap',      'Fashion',        890.00, 1190.00, 14),
(2, 'Organic pineapple, 3-pack',  'Produce',         210.00,  250.00, 40),
(3, 'Woven vetiver table basket', 'Home & craft',    650.00,  930.00,  9),
(4, 'Coconut & vetiver body oil', 'Beauty',          420.00,  470.00, 22),
(1, 'Raffia sun hat',             'Fashion',         380.00,  NULL,   17),
(1, 'Batik tote bag',             'Fashion',         540.00,  NULL,   11),
(2, 'Dried longan snack',         'Produce',         165.00,  NULL,   30);
 