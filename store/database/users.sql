-- Users table for authentication (supports Google, Facebook, and regular login)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL COMMENT 'Hashed password for regular login',
  `google_id` varchar(255) DEFAULT NULL COMMENT 'Google OAuth user ID',
  `facebook_id` varchar(255) DEFAULT NULL COMMENT 'Facebook OAuth user ID',
  `avatar` varchar(500) DEFAULT NULL COMMENT 'Profile picture URL',
  `login_type` enum('regular', 'google', 'facebook', 'mixed') NOT NULL DEFAULT 'regular' COMMENT 'Primary login method',
  `remember_token` varchar(255) DEFAULT NULL COMMENT 'Remember me token',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Email verification status',
  `phone` varchar(20) DEFAULT NULL COMMENT 'User phone number',
  `date_of_birth` date DEFAULT NULL COMMENT 'User date of birth',
  `gender` enum('male', 'female', 'other', 'prefer_not_to_say') DEFAULT NULL COMMENT 'User gender',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Account status',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `google_id` (`google_id`),
  UNIQUE KEY `facebook_id` (`facebook_id`),
  KEY `remember_token` (`remember_token`),
  KEY `login_type` (`login_type`),
  KEY `last_login` (`last_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: Insert a test user for regular login
-- INSERT INTO `users` (`email`, `name`, `password`, `created_at`) VALUES
-- ('test@example.com', 'Test User', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW());
-- Password is 'password' (hashed with bcrypt)

-- Add some indexes for performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_google_id ON users(google_id);
CREATE INDEX idx_users_remember_token ON users(remember_token); 