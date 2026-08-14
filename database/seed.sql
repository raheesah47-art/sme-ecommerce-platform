USE smeconnect;
INSERT INTO districts(name,base_fee,free_delivery_threshold) VALUES ('Port Louis',50,1500),('Plaines Wilhems',60,1500),('Moka',70,1500),('Flacq',90,1500),('Grand Port',95,1500);
INSERT INTO categories(name,slug) VALUES ('Fashion','fashion'),('Home & Craft','home-craft'),('Beauty','beauty'),('Food & Produce','food-produce'),('Plants','plants');
INSERT INTO users(full_name,email,password_hash,role,phone,district) VALUES
('SMEConnect Admin','admin@smeconnect.mu','$2y$12$Dy7So1gkF7SDkU1j6MzRSObjURxNmK45zzygda3/KYEiomTfMRdpy','admin','52500000','Plaines Wilhems'),
('Crafts Mauritius','craftsmu@smeconnect.mu','$2y$12$aHTm5sHYFuTt/Ae4QzlVL.eyiDyK8notq.iUkLz6cWdJIuYVp3O2G','sme','52500001','Plaines Wilhems'),
('Local Customer','customer1@example.com','$2y$12$ggwpJ1/Jwq63GnlpLF4yWOjsWD039tUewxFqB3WTju0PHGaObmK1S','customer','52500002','Plaines Wilhems');
INSERT INTO smes(user_id,business_name,district,business_phone,description,verified_by_admin,trust_score) VALUES (2,'Crafts Mauritius','Plaines Wilhems','52500001','Handmade products from a Mauritian SME.',1,92);
INSERT INTO products(sme_id,name,slug,description,price,stock_quantity) VALUES
(1,'Handcrafted Decorative Pot','handcrafted-decorative-pot','Handmade decorative pot, crafted locally in Mauritius.',400,20),
(1,'Raffia Sun Hat','raffia-sun-hat','Lightweight locally made raffia sun hat.',380,17),
(1,'Batik Tote Bag','batik-tote-bag','Reusable handcrafted batik tote bag.',540,11),
(1,'Coconut & Vetiver Body Oil','coconut-vetiver-body-oil','Locally inspired body oil.',420,22);
INSERT INTO product_categories(product_id,category_id) VALUES (1,2),(2,1),(3,1),(4,3);
