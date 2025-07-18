-- User addresses table for saving shipping addresses
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Address name/label or recipient name',
  `phone` varchar(20) NOT NULL COMMENT 'Phone number for this address',
  `province` varchar(100) NOT NULL COMMENT 'Province name',
  `city` varchar(100) NOT NULL COMMENT 'City name',
  `city_id` int(11) DEFAULT NULL COMMENT 'Rajaongkir city ID',
  `address1` text NOT NULL COMMENT 'Primary address line',
  `address2` text DEFAULT NULL COMMENT 'Secondary address line (apartment, suite, etc.)',
  `postal_code` varchar(10) NOT NULL COMMENT 'Postal/ZIP code',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'Latitude for map location',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'Longitude for map location',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Default address flag',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `city_id` (`city_id`),
  KEY `is_default` (`is_default`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index for performance
CREATE INDEX idx_addresses_user_id ON addresses(user_id);
CREATE INDEX idx_addresses_default ON addresses(user_id, is_default); 