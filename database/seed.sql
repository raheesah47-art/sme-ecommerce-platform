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