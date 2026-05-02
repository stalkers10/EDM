-- EDM project schema
-- Import this file into an empty MySQL/MariaDB database selected for the project.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `passwd` varchar(255) NOT NULL,
  `phone_num` varchar(30) DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_storage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('file','folder') NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_extension` varchar(20) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `keywords` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_admin_storage_admin_id` (`admin_id`),
  KEY `idx_admin_storage_parent_id` (`parent_id`),
  KEY `idx_admin_storage_type` (`type`),
  KEY `idx_admin_storage_file_extension` (`file_extension`),
  KEY `idx_admin_storage_created_at` (`created_at`),
  CONSTRAINT `fk_admin_storage_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_admin_storage_parent` FOREIGN KEY (`parent_id`) REFERENCES `admin_storage` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('file','folder') NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_extension` varchar(20) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `keywords` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_documents_user_id` (`user_id`),
  KEY `idx_user_documents_parent_id` (`parent_id`),
  KEY `idx_user_documents_type` (`type`),
  KEY `idx_user_documents_file_extension` (`file_extension`),
  KEY `idx_user_documents_created_at` (`created_at`),
  CONSTRAINT `fk_user_documents_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_documents_parent` FOREIGN KEY (`parent_id`) REFERENCES `user_documents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- `public_shares.file_id` can point to either `admin_storage.id` or `user_documents.id`,
-- so it intentionally does not use a foreign key.
CREATE TABLE IF NOT EXISTS `public_shares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `share_hash` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_public_shares_hash` (`share_hash`),
  KEY `idx_public_shares_file_id` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- `shared_with_users.file_id` can also reference either admin or user documents,
-- so the source is stored separately and the file id is not constrained.
CREATE TABLE IF NOT EXISTS `shared_with_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `file_source` enum('admin','user') NOT NULL,
  `shared_with_id` int(11) NOT NULL,
  `shared_by_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_shared_with_users_target` (`file_id`,`file_source`,`shared_with_id`),
  KEY `idx_shared_with_users_shared_with_id` (`shared_with_id`),
  KEY `idx_shared_with_users_shared_by_id` (`shared_by_id`),
  CONSTRAINT `fk_shared_with_users_shared_with` FOREIGN KEY (`shared_with_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shared_with_users_shared_by` FOREIGN KEY (`shared_by_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
