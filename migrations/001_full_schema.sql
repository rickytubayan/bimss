-- ============================================================
-- BARANGAY INFORMATION MANAGEMENT SYSTEM (BIMS)
-- Complete Database Schema
-- Target: MySQL/MariaDB (InnoDB, utf8mb4_unicode_ci)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `bims`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `bims`;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- A. CORE / AUTHENTICATION
-- ============================================================

DROP TABLE IF EXISTS `email_logs`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `user_sessions`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) NULL DEFAULT NULL,
  `last_name` VARCHAR(100) NULL DEFAULT NULL,
  `role` ENUM('captain','kagawad','secretary','treasurer','bhw','tanod','census','sk_chair','resident') NOT NULL DEFAULT 'resident',
  `status` ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `remember_token` VARCHAR(100) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_username` (`username`),
  UNIQUE KEY `uk_users_email` (`email`),
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_sessions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(128) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NULL,
  `expires_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_sessions_token` (`token`),
  INDEX `idx_user_sessions_user_id` (`user_id`),
  INDEX `idx_user_sessions_expires_at` (`expires_at`),
  CONSTRAINT `fk_user_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `description` TEXT NULL,
  `is_system` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_roles_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `module` VARCHAR(50) NOT NULL,
  `action` ENUM('view','create','edit','delete','approve','export') NOT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_permissions_module_action` (`module`, `action`),
  INDEX `idx_permissions_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_role_permissions_combo` (`role_id`, `permission_id`),
  CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `audit_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(50) NOT NULL,
  `table_name` VARCHAR(100) NOT NULL,
  `record_id` INT UNSIGNED NULL,
  `old_value` JSON NULL,
  `new_value` JSON NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_audit_logs_user_id` (`user_id`),
  INDEX `idx_audit_logs_table_name` (`table_name`),
  INDEX `idx_audit_logs_record_id` (`record_id`),
  INDEX `idx_audit_logs_action` (`action`),
  INDEX `idx_audit_logs_created_at` (`created_at`),
  CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `recipient_email` VARCHAR(150) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `status` ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `sent_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_email_logs_status` (`status`),
  INDEX `idx_email_logs_recipient` (`recipient_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- B. GEOGRAPHIC / DEMOGRAPHIC
-- ============================================================

DROP TABLE IF EXISTS `residents`;
DROP TABLE IF EXISTS `households`;
DROP TABLE IF EXISTS `purok_officers`;
DROP TABLE IF EXISTS `puroks`;
DROP TABLE IF EXISTS `barangays`;
DROP TABLE IF EXISTS `cities_municipalities`;
DROP TABLE IF EXISTS `provinces`;
DROP TABLE IF EXISTS `regions`;

CREATE TABLE `regions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(10) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_regions_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `provinces` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(10) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `region_code` VARCHAR(10) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_provinces_code` (`code`),
  INDEX `idx_provinces_region_code` (`region_code`),
  CONSTRAINT `fk_provinces_region` FOREIGN KEY (`region_code`) REFERENCES `regions` (`code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cities_municipalities` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(10) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `province_code` VARCHAR(10) NOT NULL,
  `type` ENUM('city','municipality') NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cities_municipalities_code` (`code`),
  INDEX `idx_cities_municipalities_province_code` (`province_code`),
  CONSTRAINT `fk_cities_municipalities_province` FOREIGN KEY (`province_code`) REFERENCES `provinces` (`code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `barangays` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(10) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `municipality_code` VARCHAR(10) NOT NULL,
  `psgc_10digit` VARCHAR(12) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_barangays_code` (`code`),
  INDEX `idx_barangays_municipality_code` (`municipality_code`),
  CONSTRAINT `fk_barangays_municipality` FOREIGN KEY (`municipality_code`) REFERENCES `cities_municipalities` (`code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `puroks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `barangay_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) NULL,
  `type` ENUM('purok','sitio','zone') NOT NULL DEFAULT 'purok',
  `classification` ENUM('urban','rural') NOT NULL DEFAULT 'urban',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_puroks_barangay_id` (`barangay_id`),
  CONSTRAINT `fk_puroks_barangay` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `households` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `barangay_id` INT UNSIGNED NOT NULL,
  `purok_id` INT UNSIGNED NULL,
  `house_number` VARCHAR(20) NULL,
  `street` VARCHAR(200) NULL,
  `gps_latitude` DECIMAL(8,4) NULL,
  `gps_longitude` DECIMAL(8,4) NULL,
  `classification` ENUM('residential','commercial','industrial','mixed') NOT NULL DEFAULT 'residential',
  `status` ENUM('active','vacant','demolished') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_households_barangay_id` (`barangay_id`),
  INDEX `idx_households_purok_id` (`purok_id`),
  INDEX `idx_households_status` (`status`),
  CONSTRAINT `fk_households_barangay` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_households_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `residents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `household_id` INT UNSIGNED NULL,
  `purok_id` INT UNSIGNED NULL,
  `national_id` VARCHAR(30) NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `middle_name` VARCHAR(100) NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `suffix` VARCHAR(10) NULL,
  `sex` ENUM('male','female') NOT NULL,
  `birthdate` DATE NOT NULL,
  `civil_status` ENUM('single','married','widowed','separated','divorced') NOT NULL DEFAULT 'single',
  `blood_type` VARCHAR(5) NULL,
  `disability_type` ENUM('none','visual','hearing','motor','speech','intellectual','psychosocial','multiple') NOT NULL DEFAULT 'none',
  `is_pwd` TINYINT(1) NOT NULL DEFAULT 0,
  `is_senior` TINYINT(1) NOT NULL DEFAULT 0,
  `is_voter` TINYINT(1) NOT NULL DEFAULT 0,
  `educational_attainment` ENUM('none','elementary','high_school','vocational','college','post_graduate') NULL,
  `occupation` VARCHAR(100) NULL,
  `monthly_income` DECIMAL(12,2) NULL,
  `phone` VARCHAR(20) NULL,
  `email` VARCHAR(150) NULL,
  `photo` VARCHAR(255) NULL,
  `status` ENUM('active','deceased','moved_out','inactive') NOT NULL DEFAULT 'active',
  `is_approved` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_residents_household_id` (`household_id`),
  INDEX `idx_residents_purok_id` (`purok_id`),
  INDEX `idx_residents_last_name` (`last_name`),
  INDEX `idx_residents_first_name` (`first_name`),
  INDEX `idx_residents_status` (`status`),
  INDEX `idx_residents_sex` (`sex`),
  INDEX `idx_residents_birthdate` (`birthdate`),
  INDEX `idx_residents_civil_status` (`civil_status`),
  INDEX `idx_residents_is_voter` (`is_voter`),
  INDEX `idx_residents_is_pwd` (`is_pwd`),
  INDEX `idx_residents_is_senior` (`is_senior`),
  INDEX `idx_residents_national_id` (`national_id`),
  CONSTRAINT `fk_residents_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_residents_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `purok_officers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `purok_id` INT UNSIGNED NOT NULL,
  `resident_id` INT UNSIGNED NOT NULL,
  `position` VARCHAR(50) NOT NULL,
  `term_start` DATE NULL,
  `term_end` DATE NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_purok_officers_purok_id` (`purok_id`),
  INDEX `idx_purok_officers_resident_id` (`resident_id`),
  INDEX `idx_purok_officers_status` (`status`),
  CONSTRAINT `fk_purok_officers_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_purok_officers_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- C. RESIDENT RELATIONSHIPS & HOUSEHOLD
-- ============================================================

DROP TABLE IF EXISTS `vehicles`;
DROP TABLE IF EXISTS `pets`;
DROP TABLE IF EXISTS `move_in_out`;
DROP TABLE IF EXISTS `resident_links`;

CREATE TABLE `resident_links` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `resident_id_a` INT UNSIGNED NOT NULL,
  `resident_id_b` INT UNSIGNED NOT NULL,
  `relationship` ENUM('parent','child','spouse','sibling','guardian','next_of_kin') NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_resident_links_pair` (`resident_id_a`, `resident_id_b`, `relationship`),
  INDEX `idx_resident_links_resident_b` (`resident_id_b`),
  CONSTRAINT `fk_resident_links_resident_a` FOREIGN KEY (`resident_id_a`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_resident_links_resident_b` FOREIGN KEY (`resident_id_b`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `move_in_out` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `resident_id` INT UNSIGNED NOT NULL,
  `type` ENUM('move_in','move_out') NOT NULL,
  `date` DATE NOT NULL,
  `from_address` TEXT NULL,
  `to_address` TEXT NULL,
  `boarder_expiry` DATE NULL,
  `remarks` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_move_in_out_resident_id` (`resident_id`),
  INDEX `idx_move_in_out_type` (`type`),
  INDEX `idx_move_in_out_date` (`date`),
  CONSTRAINT `fk_move_in_out_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `household_id` INT UNSIGNED NOT NULL,
  `species` VARCHAR(50) NOT NULL,
  `breed` VARCHAR(100) NULL,
  `name` VARCHAR(100) NULL,
  `is_registered` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_pets_household_id` (`household_id`),
  CONSTRAINT `fk_pets_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vehicles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `household_id` INT UNSIGNED NOT NULL,
  `type` ENUM('car','motorcycle','tricycle','bicycle','truck','other') NOT NULL,
  `plate_no` VARCHAR(20) NULL,
  `color` VARCHAR(30) NULL,
  `make_model` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_vehicles_household_id` (`household_id`),
  CONSTRAINT `fk_vehicles_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- D. DOCUMENT REQUESTS & CERTIFICATES
-- ============================================================

DROP TABLE IF EXISTS `approval_workflow`;
DROP TABLE IF EXISTS `certificates`;
DROP TABLE IF EXISTS `business_clearances`;
DROP TABLE IF EXISTS `barangay_clearances`;
DROP TABLE IF EXISTS `document_requirements`;
DROP TABLE IF EXISTS `document_requests`;
DROP TABLE IF EXISTS `document_types`;

CREATE TABLE `document_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `category` ENUM('clearance','certificate','permit','other') NOT NULL,
  `fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `requirements_json` JSON NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_document_types_category` (`category`),
  INDEX `idx_document_types_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `document_requests` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `resident_id` INT UNSIGNED NOT NULL,
  `assisted_by` INT UNSIGNED NULL,
  `document_type_id` INT UNSIGNED NOT NULL,
  `purpose` TEXT NULL,
  `status` ENUM('pending','processing','for_signing','ready','released','cancelled') NOT NULL DEFAULT 'pending',
  `qr_code` VARCHAR(255) NULL,
  `tracking_code` VARCHAR(20) NOT NULL,
  `requested_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` TIMESTAMP NULL DEFAULT NULL,
  `released_at` TIMESTAMP NULL DEFAULT NULL,
  `released_to` VARCHAR(150) NULL,
  `representative_name` VARCHAR(150) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_document_requests_tracking_code` (`tracking_code`),
  INDEX `idx_document_requests_resident_id` (`resident_id`),
  INDEX `idx_document_requests_assisted_by` (`assisted_by`),
  INDEX `idx_document_requests_document_type_id` (`document_type_id`),
  INDEX `idx_document_requests_status` (`status`),
  INDEX `idx_document_requests_requested_at` (`requested_at`),
  CONSTRAINT `fk_document_requests_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_document_requests_assisted_by` FOREIGN KEY (`assisted_by`) REFERENCES `residents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_document_requests_document_type` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `document_requirements` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` INT UNSIGNED NOT NULL,
  `requirement_name` VARCHAR(150) NOT NULL,
  `file_path` VARCHAR(255) NULL,
  `is_submitted` TINYINT(1) NOT NULL DEFAULT 0,
  `submitted_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_document_requirements_request_id` (`request_id`),
  CONSTRAINT `fk_document_requirements_request` FOREIGN KEY (`request_id`) REFERENCES `document_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `barangay_clearances` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` INT UNSIGNED NOT NULL,
  `clearance_type` ENUM('residential','business','locational','general') NOT NULL,
  `applicant_name` VARCHAR(200) NOT NULL,
  `address` TEXT NULL,
  `years_of_residence` INT NULL,
  `purpose` VARCHAR(200) NULL,
  `no_record_statement` TEXT NULL,
  `validity_date` DATE NULL,
  `or_number` VARCHAR(30) NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_barangay_clearances_request_id` (`request_id`),
  INDEX `idx_barangay_clearances_clearance_type` (`clearance_type`),
  CONSTRAINT `fk_barangay_clearances_request` FOREIGN KEY (`request_id`) REFERENCES `document_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `business_clearances` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `clearance_id` INT UNSIGNED NOT NULL,
  `trade_name` VARCHAR(200) NOT NULL,
  `business_address` TEXT NULL,
  `nature_of_business` VARCHAR(200) NULL,
  `bir_registration_number` VARCHAR(50) NULL,
  `mayors_permit_number` VARCHAR(50) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_business_clearances_clearance_id` (`clearance_id`),
  CONSTRAINT `fk_business_clearances_clearance` FOREIGN KEY (`clearance_id`) REFERENCES `barangay_clearances` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `certificates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` INT UNSIGNED NOT NULL,
  `certificate_type` ENUM('residency','indigency','good_moral','jobseeker','birth','other') NOT NULL,
  `applicant_data` JSON NULL,
  `purpose` VARCHAR(200) NULL,
  `or_number` VARCHAR(30) NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_certificates_request_id` (`request_id`),
  INDEX `idx_certificates_certificate_type` (`certificate_type`),
  CONSTRAINT `fk_certificates_request` FOREIGN KEY (`request_id`) REFERENCES `document_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `approval_workflow` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` INT UNSIGNED NOT NULL,
  `step_order` INT NOT NULL,
  `role_required` VARCHAR(50) NOT NULL,
  `approver_id` INT UNSIGNED NULL,
  `status` ENUM('pending','approved','rejected','skipped') NOT NULL DEFAULT 'pending',
  `signature_file` VARCHAR(255) NULL,
  `signed_at` TIMESTAMP NULL DEFAULT NULL,
  `remarks` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_approval_workflow_request_id` (`request_id`),
  INDEX `idx_approval_workflow_approver_id` (`approver_id`),
  INDEX `idx_approval_workflow_status` (`status`),
  CONSTRAINT `fk_approval_workflow_request` FOREIGN KEY (`request_id`) REFERENCES `document_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_approval_workflow_approver` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- E. DOCUMENT RENEWALS
-- ============================================================

CREATE TABLE `document_renewals` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `certificate_id` INT UNSIGNED NOT NULL,
  `original_issue_date` DATE NOT NULL,
  `expiry_date` DATE NOT NULL,
  `reminder_sent` TINYINT(1) NOT NULL DEFAULT 0,
  `renewed_to_request_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_document_renewals_certificate_id` (`certificate_id`),
  INDEX `idx_document_renewals_expiry_date` (`expiry_date`),
  INDEX `idx_document_renewals_renewed_to_request_id` (`renewed_to_request_id`),
  CONSTRAINT `fk_document_renewals_certificate` FOREIGN KEY (`certificate_id`) REFERENCES `certificates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_document_renewals_request` FOREIGN KEY (`renewed_to_request_id`) REFERENCES `document_requests` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- F. APPOINTMENTS
-- ============================================================

DROP TABLE IF EXISTS `appointments`;
DROP TABLE IF EXISTS `appointment_slots`;

CREATE TABLE `appointment_slots` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slot_date` DATE NOT NULL,
  `time_start` TIME NOT NULL,
  `time_end` TIME NOT NULL,
  `max_capacity` INT NOT NULL DEFAULT 10,
  `current_booked` INT NOT NULL DEFAULT 0,
  `slot_type` ENUM('regular','priority') NOT NULL DEFAULT 'regular',
  `status` ENUM('open','full','cancelled') NOT NULL DEFAULT 'open',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_appointment_slots_slot_date` (`slot_date`),
  INDEX `idx_appointment_slots_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `appointments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `resident_id` INT UNSIGNED NOT NULL,
  `slot_id` INT UNSIGNED NOT NULL,
  `purpose` VARCHAR(200) NOT NULL,
  `status` ENUM('booked','completed','cancelled','no_show') NOT NULL DEFAULT 'booked',
  `queue_number` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_appointments_resident_id` (`resident_id`),
  INDEX `idx_appointments_slot_id` (`slot_id`),
  INDEX `idx_appointments_status` (`status`),
  CONSTRAINT `fk_appointments_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_appointments_slot` FOREIGN KEY (`slot_id`) REFERENCES `appointment_slots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- G. COMPLAINTS & BLOTTER
-- ============================================================

DROP TABLE IF EXISTS `visitor_logs`;
DROP TABLE IF EXISTS `blotter_witnesses`;
DROP TABLE IF EXISTS `blotters`;
DROP TABLE IF EXISTS `complaints`;

CREATE TABLE `complaints` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `resident_id` INT UNSIGNED NULL,
  `category` ENUM('road_damage','clogged_canal','stray_animal','streetlight','noise','garbage','other') NOT NULL,
  `description` TEXT NOT NULL,
  `photo_paths` JSON NULL,
  `purok_id` INT UNSIGNED NULL,
  `location_text` VARCHAR(255) NULL,
  `status` ENUM('submitted','investigating','resolved','dismissed') NOT NULL DEFAULT 'submitted',
  `assigned_to` INT UNSIGNED NULL,
  `resolution_notes` TEXT NULL,
  `resolved_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_complaints_resident_id` (`resident_id`),
  INDEX `idx_complaints_purok_id` (`purok_id`),
  INDEX `idx_complaints_category` (`category`),
  INDEX `idx_complaints_status` (`status`),
  INDEX `idx_complaints_assigned_to` (`assigned_to`),
  CONSTRAINT `fk_complaints_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_complaints_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_complaints_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `blotters` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_type` ENUM('online','walk_in') NOT NULL,
  `reporter_name` VARCHAR(200) NOT NULL,
  `reporter_contact` VARCHAR(20) NULL,
  `incident_type` ENUM('theft','physical_injury','vawc','fraud','quarrel','trespassing','other') NOT NULL,
  `narrative` TEXT NOT NULL,
  `location` VARCHAR(255) NULL,
  `purok_id` INT UNSIGNED NULL,
  `date_time_of_incident` DATETIME NOT NULL,
  `status` ENUM('filed','investigating','for_hearing','resolved','closed') NOT NULL DEFAULT 'filed',
  `tanod_assigned` INT UNSIGNED NULL,
  `case_number` VARCHAR(30) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_blotters_purok_id` (`purok_id`),
  INDEX `idx_blotters_incident_type` (`incident_type`),
  INDEX `idx_blotters_status` (`status`),
  INDEX `idx_blotters_date_time_of_incident` (`date_time_of_incident`),
  INDEX `idx_blotters_tanod_assigned` (`tanod_assigned`),
  INDEX `idx_blotters_case_number` (`case_number`),
  CONSTRAINT `fk_blotters_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_blotters_tanod_assigned` FOREIGN KEY (`tanod_assigned`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `blotter_witnesses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `blotter_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `contact` VARCHAR(20) NULL,
  `statement` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_blotter_witnesses_blotter_id` (`blotter_id`),
  CONSTRAINT `fk_blotter_witnesses_blotter` FOREIGN KEY (`blotter_id`) REFERENCES `blotters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `visitor_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `visitor_name` VARCHAR(200) NOT NULL,
  `purpose` VARCHAR(200) NULL,
  `id_type` VARCHAR(50) NULL,
  `id_number` VARCHAR(50) NULL,
  `vehicle_plate` VARCHAR(20) NULL,
  `time_in` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time_out` TIMESTAMP NULL DEFAULT NULL,
  `host_resident_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_visitor_logs_host_resident_id` (`host_resident_id`),
  INDEX `idx_visitor_logs_time_in` (`time_in`),
  CONSTRAINT `fk_visitor_logs_host_resident` FOREIGN KEY (`host_resident_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- H. FINANCE & TREASURY
-- ============================================================

DROP TABLE IF EXISTS `donation_records`;
DROP TABLE IF EXISTS `ayuda_distributions`;
DROP TABLE IF EXISTS `tax_ledgers`;
DROP TABLE IF EXISTS `official_receipts`;
DROP TABLE IF EXISTS `expense_records`;
DROP TABLE IF EXISTS `income_records`;
DROP TABLE IF EXISTS `statutory_allocations`;
DROP TABLE IF EXISTS `budget_line_items`;
DROP TABLE IF EXISTS `budgets`;
DROP TABLE IF EXISTS `chart_of_accounts`;

CREATE TABLE `chart_of_accounts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(20) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `type` ENUM('asset','liability','equity','income','expense') NOT NULL,
  `parent_id` INT UNSIGNED NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_chart_of_accounts_code` (`code`),
  INDEX `idx_chart_of_accounts_type` (`type`),
  INDEX `idx_chart_of_accounts_parent_id` (`parent_id`),
  CONSTRAINT `fk_chart_of_accounts_parent` FOREIGN KEY (`parent_id`) REFERENCES `chart_of_accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `budgets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fiscal_year` YEAR NOT NULL,
  `fund_type` ENUM('general','development','sk','drrm','gad') NOT NULL,
  `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('draft','approved','active','closed') NOT NULL DEFAULT 'draft',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_budgets_fiscal_year` (`fiscal_year`),
  INDEX `idx_budgets_fund_type` (`fund_type`),
  INDEX `idx_budgets_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `budget_line_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `budget_id` INT UNSIGNED NOT NULL,
  `account_id` INT UNSIGNED NOT NULL,
  `allocated_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `utilized_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_budget_line_items_budget_id` (`budget_id`),
  INDEX `idx_budget_line_items_account_id` (`account_id`),
  CONSTRAINT `fk_budget_line_items_budget` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_budget_line_items_account` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `statutory_allocations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fiscal_year` YEAR NOT NULL,
  `fund_type` VARCHAR(50) NOT NULL,
  `mandated_percentage` DECIMAL(5,2) NOT NULL,
  `allocated_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `spent_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_statutory_allocations_fiscal_year` (`fiscal_year`),
  INDEX `idx_statutory_allocations_fund_type` (`fund_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `income_records` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `income_date` DATE NOT NULL,
  `source` VARCHAR(200) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `or_number` VARCHAR(30) NULL,
  `collector_id` INT UNSIGNED NULL,
  `reference_no` VARCHAR(50) NULL,
  `fund_type` ENUM('general','development','sk','drrm','gad') NOT NULL DEFAULT 'general',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_income_records_income_date` (`income_date`),
  INDEX `idx_income_records_fund_type` (`fund_type`),
  INDEX `idx_income_records_collector_id` (`collector_id`),
  INDEX `idx_income_records_or_number` (`or_number`),
  CONSTRAINT `fk_income_records_collector` FOREIGN KEY (`collector_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `expense_records` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `expense_date` DATE NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payee` VARCHAR(200) NOT NULL,
  `purpose` TEXT NULL,
  `or_number` VARCHAR(30) NULL,
  `dv_number` VARCHAR(30) NULL,
  `approved_by` INT UNSIGNED NULL,
  `fund_type` ENUM('general','development','sk','drrm','gad') NOT NULL DEFAULT 'general',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_expense_records_expense_date` (`expense_date`),
  INDEX `idx_expense_records_fund_type` (`fund_type`),
  INDEX `idx_expense_records_approved_by` (`approved_by`),
  INDEX `idx_expense_records_or_number` (`or_number`),
  CONSTRAINT `fk_expense_records_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `official_receipts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sequence_no` VARCHAR(30) NOT NULL,
  `receipt_date` DATE NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payer` VARCHAR(200) NOT NULL,
  `purpose` VARCHAR(200) NULL,
  `or_type` ENUM('income','refund') NOT NULL DEFAULT 'income',
  `printed_by` INT UNSIGNED NULL,
  `is_voided` TINYINT(1) NOT NULL DEFAULT 0,
  `void_reason` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_official_receipts_sequence_no` (`sequence_no`),
  INDEX `idx_official_receipts_receipt_date` (`receipt_date`),
  INDEX `idx_official_receipts_printed_by` (`printed_by`),
  INDEX `idx_official_receipts_is_voided` (`is_voided`),
  CONSTRAINT `fk_official_receipts_printed_by` FOREIGN KEY (`printed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tax_ledgers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `taxpayer_name` VARCHAR(200) NOT NULL,
  `tax_type` ENUM('rpt','business') NOT NULL,
  `assessed_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `amount_due` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `penalties` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `amount_paid` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `due_date` DATE NOT NULL,
  `payment_date` DATE NULL,
  `or_number` VARCHAR(30) NULL,
  `status` ENUM('unpaid','partial','paid','delinquent') NOT NULL DEFAULT 'unpaid',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tax_ledgers_taxpayer_name` (`taxpayer_name`),
  INDEX `idx_tax_ledgers_tax_type` (`tax_type`),
  INDEX `idx_tax_ledgers_due_date` (`due_date`),
  INDEX `idx_tax_ledgers_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ayuda_distributions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `program_name` VARCHAR(150) NOT NULL,
  `resident_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `type` ENUM('cash','food','medicine','other') NOT NULL,
  `distributed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `distributed_by` INT UNSIGNED NULL,
  `biometric_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_ayuda_distributions_resident_id` (`resident_id`),
  INDEX `idx_ayuda_distributions_program_name` (`program_name`),
  INDEX `idx_ayuda_distributions_distributed_by` (`distributed_by`),
  INDEX `idx_ayuda_distributions_distributed_at` (`distributed_at`),
  CONSTRAINT `fk_ayuda_distributions_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ayuda_distributions_distributed_by` FOREIGN KEY (`distributed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `donation_records` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `donor_name` VARCHAR(200) NOT NULL,
  `type` ENUM('cash','kind') NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `items_description` TEXT NULL,
  `received_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `received_by` INT UNSIGNED NULL,
  `purpose` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_donation_records_donor_name` (`donor_name`),
  INDEX `idx_donation_records_type` (`type`),
  INDEX `idx_donation_records_received_by` (`received_by`),
  CONSTRAINT `fk_donation_records_received_by` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- I. HEALTH & SOCIAL WELFARE
-- ============================================================

DROP TABLE IF EXISTS `health_programs`;
DROP TABLE IF EXISTS `disease_surveillance`;
DROP TABLE IF EXISTS `beneficiaries_4ps`;
DROP TABLE IF EXISTS `senior_pwd_profiles`;
DROP TABLE IF EXISTS `child_growth_records`;
DROP TABLE IF EXISTS `immunization_records`;
DROP TABLE IF EXISTS `maternal_records`;
DROP TABLE IF EXISTS `health_profiles`;

CREATE TABLE `health_profiles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `resident_id` INT UNSIGNED NOT NULL,
  `blood_type` VARCHAR(5) NULL,
  `allergies` TEXT NULL,
  `medical_conditions` TEXT NULL,
  `emergency_medications` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_health_profiles_resident_id` (`resident_id`),
  CONSTRAINT `fk_health_profiles_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `maternal_records` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `resident_id` INT UNSIGNED NOT NULL,
  `lmp_date` DATE NULL,
  `expected_due_date` DATE NULL,
  `prenatal_visits` JSON NULL,
  `birth_weight` DECIMAL(5,2) NULL,
  `birth_date` DATE NULL,
  `complications` TEXT NULL,
  `attending_midwife` VARCHAR(200) NULL,
  `status` ENUM('pregnant','delivered','postpartum') NOT NULL DEFAULT 'pregnant',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_maternal_records_resident_id` (`resident_id`),
  INDEX `idx_maternal_records_status` (`status`),
  INDEX `idx_maternal_records_expected_due_date` (`expected_due_date`),
  CONSTRAINT `fk_maternal_records_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `immunization_records` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `child_resident_id` INT UNSIGNED NOT NULL,
  `vaccine_name` VARCHAR(100) NOT NULL,
  `dose_number` INT NOT NULL,
  `date_administered` DATE NOT NULL,
  `batch_number` VARCHAR(50) NULL,
  `next_schedule` DATE NULL,
  `administered_by` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_immunization_records_child_resident_id` (`child_resident_id`),
  INDEX `idx_immunization_records_vaccine_name` (`vaccine_name`),
  INDEX `idx_immunization_records_date_administered` (`date_administered`),
  INDEX `idx_immunization_records_next_schedule` (`next_schedule`),
  CONSTRAINT `fk_immunization_records_child_resident` FOREIGN KEY (`child_resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `child_growth_records` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `child_resident_id` INT UNSIGNED NOT NULL,
  `age_months` INT NOT NULL,
  `weight_kg` DECIMAL(5,2) NOT NULL,
  `height_cm` DECIMAL(5,2) NOT NULL,
  `head_circumference` DECIMAL(5,2) NULL,
  `nutrition_status` ENUM('normal','overweight','underweight','severely_underweight','wasted','stunted') NOT NULL DEFAULT 'normal',
  `recorded_by` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_child_growth_records_child_resident_id` (`child_resident_id`),
  INDEX `idx_child_growth_records_nutrition_status` (`nutrition_status`),
  INDEX `idx_child_growth_records_age_months` (`age_months`),
  CONSTRAINT `fk_child_growth_records_child_resident` FOREIGN KEY (`child_resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `senior_pwd_profiles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `resident_id` INT UNSIGNED NOT NULL,
  `type` ENUM('senior','pwd') NOT NULL,
  `id_type` VARCHAR(50) NULL,
  `id_number` VARCHAR(50) NULL,
  `pension_status` ENUM('active','inactive','pending') NOT NULL DEFAULT 'pending',
  `monthly_allowance` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `grocery_benefits` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `osca_number` VARCHAR(30) NULL,
  `pwd_number` VARCHAR(30) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_senior_pwd_profiles_resident_type` (`resident_id`, `type`),
  INDEX `idx_senior_pwd_profiles_type` (`type`),
  INDEX `idx_senior_pwd_profiles_pension_status` (`pension_status`),
  CONSTRAINT `fk_senior_pwd_profiles_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `beneficiaries_4ps` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `resident_id` INT UNSIGNED NOT NULL,
  `household_id` INT UNSIGNED NOT NULL,
  `compliance_status` ENUM('compliant','non_compliant','suspended') NOT NULL DEFAULT 'compliant',
  `last_fds_date` DATE NULL,
  `benefits_received` JSON NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_beneficiaries_4ps_resident_id` (`resident_id`),
  INDEX `idx_beneficiaries_4ps_household_id` (`household_id`),
  INDEX `idx_beneficiaries_4ps_compliance_status` (`compliance_status`),
  CONSTRAINT `fk_beneficiaries_4ps_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_beneficiaries_4ps_household` FOREIGN KEY (`household_id`) REFERENCES `households` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `disease_surveillance` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `disease_name` VARCHAR(100) NOT NULL,
  `date_reported` DATE NOT NULL,
  `purok_id` INT UNSIGNED NULL,
  `patient_age` INT NULL,
  `patient_sex` ENUM('male','female') NULL,
  `status` ENUM('suspected','confirmed','recovered','deceased') NOT NULL DEFAULT 'suspected',
  `reported_to_doctor` TINYINT(1) NOT NULL DEFAULT 0,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_disease_surveillance_disease_name` (`disease_name`),
  INDEX `idx_disease_surveillance_date_reported` (`date_reported`),
  INDEX `idx_disease_surveillance_purok_id` (`purok_id`),
  INDEX `idx_disease_surveillance_status` (`status`),
  CONSTRAINT `fk_disease_surveillance_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `health_programs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `target_group` VARCHAR(100) NULL,
  `schedule_date` DATE NOT NULL,
  `attendees_count` INT NOT NULL DEFAULT 0,
  `conducted_by` VARCHAR(100) NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_health_programs_schedule_date` (`schedule_date`),
  INDEX `idx_health_programs_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- J. PEACE & ORDER
-- ============================================================

DROP TABLE IF EXISTS `tanod_schedules`;
DROP TABLE IF EXISTS `cctv_cameras`;
DROP TABLE IF EXISTS `kp_cfa`;
DROP TABLE IF EXISTS `kp_settlements`;
DROP TABLE IF EXISTS `kp_hearings`;
DROP TABLE IF EXISTS `kp_cases`;
DROP TABLE IF EXISTS `pangkat_panels`;
DROP TABLE IF EXISTS `lupon_members`;

CREATE TABLE `lupon_members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `resident_id` INT UNSIGNED NOT NULL,
  `position` ENUM('chair','secretary','member') NOT NULL DEFAULT 'member',
  `appointed_date` DATE NULL,
  `oath_date` DATE NULL,
  `term_end` DATE NULL,
  `status` ENUM('active','inactive','resigned','removed') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_lupon_members_resident_id` (`resident_id`),
  INDEX `idx_lupon_members_position` (`position`),
  INDEX `idx_lupon_members_status` (`status`),
  CONSTRAINT `fk_lupon_members_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kp_cases` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_number` VARCHAR(30) NOT NULL,
  `complainant_resident_id` INT UNSIGNED NOT NULL,
  `respondent_resident_id` INT UNSIGNED NOT NULL,
  `nature_of_dispute` ENUM('civil','criminal','other') NOT NULL,
  `cause_of_action` TEXT NOT NULL,
  `date_filed` DATE NOT NULL,
  `status` ENUM('pending_mediation','pending_pangkat','pending_conciliation','settled','repudiated','cfa_issued','barred','executed') NOT NULL DEFAULT 'pending_mediation',
  `date_mediation` DATE NULL,
  `mediation_outcome` ENUM('settled','failed','no_hearing') NULL,
  `date_pangkat` DATE NULL,
  `conciliation_outcome` ENUM('settled','failed','arbitrated') NULL,
  `settlement_details` TEXT NULL,
  `cfa_number` VARCHAR(30) NULL,
  `cfa_date` DATE NULL,
  `cfa_valid_until` DATE NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_kp_cases_case_number` (`case_number`),
  INDEX `idx_kp_cases_complainant_resident_id` (`complainant_resident_id`),
  INDEX `idx_kp_cases_respondent_resident_id` (`respondent_resident_id`),
  INDEX `idx_kp_cases_status` (`status`),
  INDEX `idx_kp_cases_date_filed` (`date_filed`),
  INDEX `idx_kp_cases_nature_of_dispute` (`nature_of_dispute`),
  CONSTRAINT `fk_kp_cases_complainant` FOREIGN KEY (`complainant_resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kp_cases_respondent` FOREIGN KEY (`respondent_resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pangkat_panels` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id` INT UNSIGNED NOT NULL,
  `chairperson_id` INT UNSIGNED NOT NULL,
  `member1_id` INT UNSIGNED NULL,
  `member2_id` INT UNSIGNED NULL,
  `secretary_id` INT UNSIGNED NULL,
  `constituted_date` DATE NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_pangkat_panels_case_id` (`case_id`),
  INDEX `idx_pangkat_panels_chairperson_id` (`chairperson_id`),
  INDEX `idx_pangkat_panels_member1_id` (`member1_id`),
  INDEX `idx_pangkat_panels_member2_id` (`member2_id`),
  INDEX `idx_pangkat_panels_secretary_id` (`secretary_id`),
  CONSTRAINT `fk_pangkat_panels_case` FOREIGN KEY (`case_id`) REFERENCES `kp_cases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pangkat_panels_chairperson` FOREIGN KEY (`chairperson_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pangkat_panels_member1` FOREIGN KEY (`member1_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pangkat_panels_member2` FOREIGN KEY (`member2_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pangkat_panels_secretary` FOREIGN KEY (`secretary_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kp_hearings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id` INT UNSIGNED NOT NULL,
  `hearing_type` ENUM('mediation','conciliation','arbitration') NOT NULL,
  `scheduled_date` DATETIME NOT NULL,
  `actual_date` DATETIME NULL,
  `outcome` ENUM('settled','failed','adjourned','no_show_complainant','no_show_respondent') NULL,
  `notes` TEXT NULL,
  `next_schedule` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_kp_hearings_case_id` (`case_id`),
  INDEX `idx_kp_hearings_scheduled_date` (`scheduled_date`),
  INDEX `idx_kp_hearings_hearing_type` (`hearing_type`),
  INDEX `idx_kp_hearings_outcome` (`outcome`),
  CONSTRAINT `fk_kp_hearings_case` FOREIGN KEY (`case_id`) REFERENCES `kp_cases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kp_settlements` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id` INT UNSIGNED NOT NULL,
  `type` ENUM('amicable_settlement','arbitration_award') NOT NULL,
  `details` TEXT NOT NULL,
  `signed_by_complainant` TINYINT(1) NOT NULL DEFAULT 0,
  `signed_by_respondent` TINYINT(1) NOT NULL DEFAULT 0,
  `attested_by` INT UNSIGNED NULL,
  `settlement_date` DATE NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_kp_settlements_case_id` (`case_id`),
  INDEX `idx_kp_settlements_attested_by` (`attested_by`),
  CONSTRAINT `fk_kp_settlements_case` FOREIGN KEY (`case_id`) REFERENCES `kp_cases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kp_settlements_attested_by` FOREIGN KEY (`attested_by`) REFERENCES `residents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kp_cfa` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `case_id` INT UNSIGNED NOT NULL,
  `certificate_number` VARCHAR(30) NOT NULL,
  `issued_date` DATE NOT NULL,
  `valid_until` DATE NOT NULL,
  `issued_by` INT UNSIGNED NULL,
  `attested_by` INT UNSIGNED NULL,
  `status` ENUM('active','expired','used') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_kp_cfa_certificate_number` (`certificate_number`),
  INDEX `idx_kp_cfa_case_id` (`case_id`),
  INDEX `idx_kp_cfa_status` (`status`),
  INDEX `idx_kp_cfa_issued_by` (`issued_by`),
  INDEX `idx_kp_cfa_attested_by` (`attested_by`),
  CONSTRAINT `fk_kp_cfa_case` FOREIGN KEY (`case_id`) REFERENCES `kp_cases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kp_cfa_issued_by` FOREIGN KEY (`issued_by`) REFERENCES `residents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_kp_cfa_attested_by` FOREIGN KEY (`attested_by`) REFERENCES `residents` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tanod_schedules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `schedule_date` DATE NOT NULL,
  `shift_start` TIME NOT NULL,
  `shift_end` TIME NOT NULL,
  `assignment_type` ENUM('patrol','checkpoint','standby','event_duty') NOT NULL DEFAULT 'patrol',
  `assigned_members` JSON NULL,
  `status` ENUM('active','completed','cancelled') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_tanod_schedules_schedule_date` (`schedule_date`),
  INDEX `idx_tanod_schedules_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cctv_cameras` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `location` VARCHAR(200) NOT NULL,
  `ip_address` VARCHAR(45) NULL,
  `rtsp_url` VARCHAR(255) NULL,
  `gps_latitude` DECIMAL(8,4) NULL,
  `gps_longitude` DECIMAL(8,4) NULL,
  `status` ENUM('online','offline','maintenance') NOT NULL DEFAULT 'offline',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_cctv_cameras_status` (`status`),
  INDEX `idx_cctv_cameras_location` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- K. DRRM
-- ============================================================

DROP TABLE IF EXISTS `relief_distributions`;
DROP TABLE IF EXISTS `relief_inventory`;
DROP TABLE IF EXISTS `rdana_protection`;
DROP TABLE IF EXISTS `rdana_health`;
DROP TABLE IF EXISTS `rdana_food_security`;
DROP TABLE IF EXISTS `rdana_shelter`;
DROP TABLE IF EXISTS `rdana_needs`;
DROP TABLE IF EXISTS `rdana_lifelines`;
DROP TABLE IF EXISTS `rdana_effects`;
DROP TABLE IF EXISTS `rdana_reports`;
DROP TABLE IF EXISTS `evacuation_occupants`;
DROP TABLE IF EXISTS `evacuation_centers`;
DROP TABLE IF EXISTS `hazard_zones`;
DROP TABLE IF EXISTS `disaster_events`;

CREATE TABLE `disaster_events` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `type` ENUM('flood','typhoon','earthquake','fire','landslide','volcanic','drought','other') NOT NULL,
  `datetime_start` DATETIME NOT NULL,
  `datetime_end` DATETIME NULL DEFAULT NULL,
  `severity` ENUM('minor','moderate','severe','catastrophic') NOT NULL,
  `gps_latitude` DECIMAL(8,4) NULL,
  `gps_longitude` DECIMAL(8,4) NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_disaster_events_type` (`type`),
  INDEX `idx_disaster_events_severity` (`severity`),
  INDEX `idx_disaster_events_datetime_start` (`datetime_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `hazard_zones` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `purok_id` INT UNSIGNED NOT NULL,
  `street_name` VARCHAR(200) NULL,
  `hazard_type` ENUM('flood','landslide','fire','earthquake','storm_surge','other') NOT NULL,
  `risk_level` ENUM('low','medium','high','very_high') NOT NULL DEFAULT 'medium',
  `historical_events_count` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_hazard_zones_purok_id` (`purok_id`),
  INDEX `idx_hazard_zones_hazard_type` (`hazard_type`),
  INDEX `idx_hazard_zones_risk_level` (`risk_level`),
  CONSTRAINT `fk_hazard_zones_purok` FOREIGN KEY (`purok_id`) REFERENCES `puroks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `evacuation_centers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `address` TEXT NULL,
  `barangay_id` INT UNSIGNED NOT NULL,
  `max_capacity` INT NOT NULL DEFAULT 0,
  `current_occupancy` INT NOT NULL DEFAULT 0,
  `gps_latitude` DECIMAL(8,4) NULL,
  `gps_longitude` DECIMAL(8,4) NULL,
  `facilities_json` JSON NULL,
  `status` ENUM('open','closed','full','maintenance') NOT NULL DEFAULT 'open',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_evacuation_centers_barangay_id` (`barangay_id`),
  INDEX `idx_evacuation_centers_status` (`status`),
  CONSTRAINT `fk_evacuation_centers_barangay` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `evacuation_occupants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `center_id` INT UNSIGNED NOT NULL,
  `family_id` INT UNSIGNED NULL,
  `resident_id` INT UNSIGNED NOT NULL,
  `date_in` DATE NOT NULL,
  `date_out` DATE NULL DEFAULT NULL,
  `status` ENUM('evacuated','returned','transferred') NOT NULL DEFAULT 'evacuated',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_evacuation_occupants_center_id` (`center_id`),
  INDEX `idx_evacuation_occupants_family_id` (`family_id`),
  INDEX `idx_evacuation_occupants_resident_id` (`resident_id`),
  INDEX `idx_evacuation_occupants_status` (`status`),
  CONSTRAINT `fk_evacuation_occupants_center` FOREIGN KEY (`center_id`) REFERENCES `evacuation_centers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_evacuation_occupants_family` FOREIGN KEY (`family_id`) REFERENCES `households` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_evacuation_occupants_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rdana_reports` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `disaster_event_id` INT UNSIGNED NOT NULL,
  `assessment_date` DATE NOT NULL,
  `assessed_by` VARCHAR(200) NULL,
  `local_authority_name` VARCHAR(200) NULL,
  `local_authority_position` VARCHAR(100) NULL,
  `summary_narrative` TEXT NULL,
  `status` ENUM('draft','submitted','consolidated') NOT NULL DEFAULT 'draft',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_rdana_reports_disaster_event_id` (`disaster_event_id`),
  INDEX `idx_rdana_reports_status` (`status`),
  CONSTRAINT `fk_rdana_reports_disaster_event` FOREIGN KEY (`disaster_event_id`) REFERENCES `disaster_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rdana_effects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rdana_id` INT UNSIGNED NOT NULL,
  `category` ENUM('affected','displaced','dead','injured','missing') NOT NULL,
  `male_count` INT NOT NULL DEFAULT 0,
  `female_count` INT NOT NULL DEFAULT 0,
  `children_count` INT NOT NULL DEFAULT 0,
  `senior_count` INT NOT NULL DEFAULT 0,
  `pwd_count` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_rdana_effects_rdana_id` (`rdana_id`),
  INDEX `idx_rdana_effects_category` (`category`),
  CONSTRAINT `fk_rdana_effects_rdana` FOREIGN KEY (`rdana_id`) REFERENCES `rdana_reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rdana_lifelines` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rdana_id` INT UNSIGNED NOT NULL,
  `facility_type` ENUM('road','electricity','communication','hospital','school','airport','seaport','water_supply','market','residential','other') NOT NULL,
  `status` ENUM('functional','damaged','destroyed') NOT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_rdana_lifelines_rdana_id` (`rdana_id`),
  INDEX `idx_rdana_lifelines_facility_type` (`facility_type`),
  INDEX `idx_rdana_lifelines_status` (`status`),
  CONSTRAINT `fk_rdana_lifelines_rdana` FOREIGN KEY (`rdana_id`) REFERENCES `rdana_reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rdana_needs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rdana_id` INT UNSIGNED NOT NULL,
  `sector` ENUM('health','food','wash','shelter','protection','education','livelihood','other') NOT NULL,
  `description` TEXT NOT NULL,
  `priority` ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_rdana_needs_rdana_id` (`rdana_id`),
  INDEX `idx_rdana_needs_sector` (`sector`),
  INDEX `idx_rdana_needs_priority` (`priority`),
  CONSTRAINT `fk_rdana_needs_rdana` FOREIGN KEY (`rdana_id`) REFERENCES `rdana_reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rdana_shelter` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rdana_id` INT UNSIGNED NOT NULL,
  `destroyed_count` INT NOT NULL DEFAULT 0,
  `damaged_count` INT NOT NULL DEFAULT 0,
  `percentage_destroyed` VARCHAR(20) NULL,
  `percentage_damaged` VARCHAR(20) NULL,
  `immediate_needs` JSON NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_rdana_shelter_rdana_id` (`rdana_id`),
  CONSTRAINT `fk_rdana_shelter_rdana` FOREIGN KEY (`rdana_id`) REFERENCES `rdana_reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rdana_food_security` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rdana_id` INT UNSIGNED NOT NULL,
  `access_to_food` ENUM('yes','no') NOT NULL,
  `main_sources` JSON NULL,
  `food_needs` JSON NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_rdana_food_security_rdana_id` (`rdana_id`),
  CONSTRAINT `fk_rdana_food_security_rdana` FOREIGN KEY (`rdana_id`) REFERENCES `rdana_reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rdana_health` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rdana_id` INT UNSIGNED NOT NULL,
  `access_to_health` ENUM('yes','no','unknown') NOT NULL DEFAULT 'unknown',
  `facilities_json` JSON NULL,
  `main_concerns` JSON NULL,
  `medicine_status` ENUM('adequate','inadequate','none') NOT NULL DEFAULT 'none',
  `immediate_needs` JSON NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_rdana_health_rdana_id` (`rdana_id`),
  CONSTRAINT `fk_rdana_health_rdana` FOREIGN KEY (`rdana_id`) REFERENCES `rdana_reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rdana_protection` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rdana_id` INT UNSIGNED NOT NULL,
  `cases_reported` TINYINT(1) NOT NULL DEFAULT 0,
  `vulnerable_populations` JSON NULL,
  `protection_needs` JSON NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_rdana_protection_rdana_id` (`rdana_id`),
  CONSTRAINT `fk_rdana_protection_rdana` FOREIGN KEY (`rdana_id`) REFERENCES `rdana_reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `relief_inventory` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_name` VARCHAR(150) NOT NULL,
  `category` ENUM('food','water','medicine','blanket','hygiene','other') NOT NULL,
  `quantity` INT NOT NULL DEFAULT 0,
  `unit` VARCHAR(30) NULL,
  `expiry_date` DATE NULL,
  `storage_location` VARCHAR(200) NULL,
  `status` ENUM('available','depleted','expired') NOT NULL DEFAULT 'available',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_relief_inventory_category` (`category`),
  INDEX `idx_relief_inventory_status` (`status`),
  INDEX `idx_relief_inventory_expiry_date` (`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `relief_distributions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `inventory_id` INT UNSIGNED NOT NULL,
  `resident_id` INT UNSIGNED NOT NULL,
  `disaster_event_id` INT UNSIGNED NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `distributed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `distributed_by` VARCHAR(100) NULL,
  `or_number` VARCHAR(30) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_relief_distributions_inventory_id` (`inventory_id`),
  INDEX `idx_relief_distributions_resident_id` (`resident_id`),
  INDEX `idx_relief_distributions_disaster_event_id` (`disaster_event_id`),
  INDEX `idx_relief_distributions_distributed_at` (`distributed_at`),
  CONSTRAINT `fk_relief_distributions_inventory` FOREIGN KEY (`inventory_id`) REFERENCES `relief_inventory` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_relief_distributions_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_relief_distributions_disaster_event` FOREIGN KEY (`disaster_event_id`) REFERENCES `disaster_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- L. INFRASTRUCTURE & LIVELIHOOD
-- ============================================================

DROP TABLE IF EXISTS `farmer_registry`;
DROP TABLE IF EXISTS `job_applications`;
DROP TABLE IF EXISTS `job_postings`;
DROP TABLE IF EXISTS `venue_bookings`;
DROP TABLE IF EXISTS `maintenance_logs`;
DROP TABLE IF EXISTS `assets`;

CREATE TABLE `assets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `category` ENUM('vehicle','equipment','facility','furniture','other') NOT NULL,
  `description` TEXT NULL,
  `purchase_date` DATE NULL,
  `purchase_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `current_condition` ENUM('excellent','good','fair','poor','non_functional') NOT NULL DEFAULT 'good',
  `next_maintenance` DATE NULL,
  `status` ENUM('available','in_use','under_repair','disposed') NOT NULL DEFAULT 'available',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_assets_category` (`category`),
  INDEX `idx_assets_status` (`status`),
  INDEX `idx_assets_current_condition` (`current_condition`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `maintenance_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `asset_id` INT UNSIGNED NOT NULL,
  `maintenance_date` DATE NOT NULL,
  `description` TEXT NOT NULL,
  `cost` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `performed_by` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_maintenance_logs_asset_id` (`asset_id`),
  INDEX `idx_maintenance_logs_maintenance_date` (`maintenance_date`),
  CONSTRAINT `fk_maintenance_logs_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `venue_bookings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venue_name` VARCHAR(200) NOT NULL,
  `booker_resident_id` INT UNSIGNED NOT NULL,
  `event_date` DATE NOT NULL,
  `time_start` TIME NOT NULL,
  `time_end` TIME NOT NULL,
  `purpose` VARCHAR(200) NOT NULL,
  `status` ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `payment_status` ENUM('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_venue_bookings_booker_resident_id` (`booker_resident_id`),
  INDEX `idx_venue_bookings_event_date` (`event_date`),
  INDEX `idx_venue_bookings_status` (`status`),
  INDEX `idx_venue_bookings_payment_status` (`payment_status`),
  CONSTRAINT `fk_venue_bookings_booker` FOREIGN KEY (`booker_resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_postings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_name` VARCHAR(200) NOT NULL,
  `position` VARCHAR(200) NOT NULL,
  `salary_range` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `requirements` TEXT NULL,
  `posted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_job_postings_is_active` (`is_active`),
  INDEX `idx_job_postings_posted_at` (`posted_at`),
  INDEX `idx_job_postings_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_applications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` INT UNSIGNED NOT NULL,
  `resident_id` INT UNSIGNED NOT NULL,
  `resume_path` VARCHAR(255) NULL,
  `cover_letter` TEXT NULL,
  `status` ENUM('pending','reviewed','interviewed','hired','rejected') NOT NULL DEFAULT 'pending',
  `applied_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_job_applications_job_id` (`job_id`),
  INDEX `idx_job_applications_resident_id` (`resident_id`),
  INDEX `idx_job_applications_status` (`status`),
  CONSTRAINT `fk_job_applications_job` FOREIGN KEY (`job_id`) REFERENCES `job_postings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_job_applications_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `farmer_registry` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `resident_id` INT UNSIGNED NOT NULL,
  `farm_size_hectares` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `primary_crops` TEXT NULL,
  `livestock` TEXT NULL,
  `registration_date` DATE NOT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_farmer_registry_resident_id` (`resident_id`),
  INDEX `idx_farmer_registry_status` (`status`),
  CONSTRAINT `fk_farmer_registry_resident` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- M. NOTIFICATIONS
-- ============================================================

DROP TABLE IF EXISTS `notification_templates`;
DROP TABLE IF EXISTS `emergency_alerts`;
DROP TABLE IF EXISTS `bulletins`;
DROP TABLE IF EXISTS `notifications`;

CREATE TABLE `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `recipient_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('in_app','email','emergency') NOT NULL DEFAULT 'in_app',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `link` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_notifications_recipient_id` (`recipient_id`),
  INDEX `idx_notifications_type` (`type`),
  INDEX `idx_notifications_is_read` (`is_read`),
  INDEX `idx_notifications_created_at` (`created_at`),
  CONSTRAINT `fk_notifications_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bulletins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(200) NOT NULL,
  `content` TEXT NOT NULL,
  `category` ENUM('general','health','safety','event','emergency','job') NOT NULL DEFAULT 'general',
  `posted_by` INT UNSIGNED NULL,
  `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
  `published_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_bulletins_category` (`category`),
  INDEX `idx_bulletins_posted_by` (`posted_by`),
  INDEX `idx_bulletins_is_pinned` (`is_pinned`),
  INDEX `idx_bulletins_published_at` (`published_at`),
  INDEX `idx_bulletins_expires_at` (`expires_at`),
  CONSTRAINT `fk_bulletins_posted_by` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `emergency_alerts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `alert_type` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  `target_puroks` JSON NULL,
  `severity` ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `sent_by` INT UNSIGNED NULL,
  `status` ENUM('active','resolved','expired') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_emergency_alerts_severity` (`severity`),
  INDEX `idx_emergency_alerts_status` (`status`),
  INDEX `idx_emergency_alerts_sent_by` (`sent_by`),
  CONSTRAINT `fk_emergency_alerts_sent_by` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notification_templates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `template_text` TEXT NOT NULL,
  `variables` JSON NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_notification_templates_name` (`name`),
  INDEX `idx_notification_templates_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- N. TRANSPARENCY
-- ============================================================

CREATE TABLE `transparency_documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `document_type` ENUM('budget','income_expenditure','nta_utilization','procurement','awards','monthly_collections','annual_report') NOT NULL,
  `fiscal_year` YEAR NOT NULL,
  `quarter` INT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `posted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `valid_until` DATE NULL,
  `status` ENUM('posted','expired','removed') NOT NULL DEFAULT 'posted',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_transparency_documents_document_type` (`document_type`),
  INDEX `idx_transparency_documents_fiscal_year` (`fiscal_year`),
  INDEX `idx_transparency_documents_quarter` (`quarter`),
  INDEX `idx_transparency_documents_status` (`status`),
  INDEX `idx_transparency_documents_posted_at` (`posted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- END OF SCHEMA
-- Total: 86 tables
-- ============================================================
