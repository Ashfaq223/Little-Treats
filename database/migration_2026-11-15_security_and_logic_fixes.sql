-- Little Treats Bakery - schema changes for the security / logic fixes
-- Run once as a privileged user (e.g. root) against little_treats_db.
-- Back the database up first.

-- S5: failed-login tracking ------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scope` varchar(10) NOT NULL,
  `identifier` varchar(150) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_scope_identifier` (`scope`, `identifier`, `attempted_at`),
  KEY `idx_scope_ip` (`scope`, `ip_address`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- L7 / L8: soft delete + availability ------------------------------------
ALTER TABLE `products`
  ADD COLUMN `is_active` tinyint(1) NOT NULL DEFAULT 1,
  ADD COLUMN `is_available` tinyint(1) NOT NULL DEFAULT 1;

-- L7: keep the product name as it was when the order was placed ----------
ALTER TABLE `order_items`
  ADD COLUMN `product_name` varchar(100) DEFAULT NULL AFTER `product_id`;

UPDATE `order_items` oi
JOIN `products` p ON p.product_id = oi.product_id
SET oi.product_name = p.product_name
WHERE oi.product_name IS NULL;

-- L2 / L9 / L10: order fulfilment, payment slip, loyalty award flag -------
ALTER TABLE `orders`
  ADD COLUMN `fulfilment_type` varchar(20) NOT NULL DEFAULT 'Delivery',
  ADD COLUMN `delivery_address` text DEFAULT NULL,
  ADD COLUMN `requested_datetime` datetime DEFAULT NULL,
  ADD COLUMN `order_notes` varchar(500) DEFAULT NULL,
  ADD COLUMN `payment_slip` varchar(255) DEFAULT NULL,
  ADD COLUMN `points_awarded` tinyint(1) NOT NULL DEFAULT 0;

-- Orders already completed have already been through the award step
UPDATE `orders` SET `points_awarded` = 1 WHERE `order_status` = 'Completed';

-- L3: refund flag for cancelled redemptions ------------------------------
ALTER TABLE `loyalty_redemptions`
  ADD COLUMN `points_refunded` tinyint(1) NOT NULL DEFAULT 0;

-- Data integrity (L1, L3, L12) -------------------------------------------
ALTER TABLE `loyalty_redemptions`
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reward_id` (`reward_id`),
  ADD CONSTRAINT `loyalty_redemptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `loyalty_redemptions_ibfk_2` FOREIGN KEY (`reward_id`) REFERENCES `loyalty_rewards` (`reward_id`);

ALTER TABLE `loyalty_points`
  ADD CONSTRAINT `loyalty_points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `chk_points_not_negative` CHECK (`points` >= 0);

ALTER TABLE `categories`
  ADD UNIQUE KEY `uniq_category_name` (`category_name`);
