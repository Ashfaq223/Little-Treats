-- Little Treats Bakery - schema changes for password reset, audit log,
-- admin roles and contact replies. Run once as a privileged user.

-- Forgot-password tokens (only a hash of the token is stored)
CREATE TABLE IF NOT EXISTS `password_resets` (
  `reset_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`reset_id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Audit log of admin actions
CREATE TABLE IF NOT EXISTS `audit_log` (
  `audit_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `admin_name` varchar(100) DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `entity` varchar(40) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`audit_id`),
  KEY `idx_created` (`created_at`),
  KEY `idx_entity` (`entity`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Admin roles: owner (everything) and staff (day-to-day work only)
ALTER TABLE `admins`
  ADD COLUMN `role` varchar(20) NOT NULL DEFAULT 'owner';

-- Replies to contact messages
ALTER TABLE `contact_messages`
  ADD COLUMN `reply_message` text DEFAULT NULL,
  ADD COLUMN `replied_at` datetime DEFAULT NULL;
