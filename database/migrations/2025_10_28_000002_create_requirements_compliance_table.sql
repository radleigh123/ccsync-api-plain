-- Migration: 2025_10_28_000002_create_requirements_compliance_table
-- Created: 2025-10-28
-- Purpose: Create requirements_compliance table to track member compliance with requirements

CREATE TABLE IF NOT EXISTS `requirements_compliance` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `requirement_id` bigint(20) UNSIGNED NOT NULL,
  `member_id` bigint(20) UNSIGNED NOT NULL,
  `compliance_status` enum('complied','not_complied','pending') NOT NULL DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_requirement_id` (`requirement_id`),
  KEY `idx_member_id` (`member_id`),
  FOREIGN KEY (`requirement_id`) REFERENCES `requirements`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial sample compliance data
INSERT INTO `requirements_compliance` (`requirement_id`, `member_id`, `compliance_status`, `submitted_at`) VALUES
-- Good Moral Certificate (requirement 1)
(1, 1, 'complied', '2025-09-15 10:00:00'),
(1, 2, 'complied', '2025-09-18 14:30:00'),
(1, 3, 'not_complied', '2025-09-20 09:00:00'),
(1, 4, 'pending', '2025-09-21 08:00:00'),
(1, 5, 'complied', '2025-09-22 11:15:00'),
-- Transcript of Records (requirement 2)
(2, 1, 'complied', '2025-09-16 08:45:00'),
(2, 2, 'pending', '2025-09-20 12:00:00'),
(2, 3, 'complied', '2025-09-19 15:20:00'),
(2, 4, 'complied', '2025-09-21 10:00:00'),
(2, 5, 'not_complied', '2025-09-23 13:45:00'),
-- Birth Certificate (requirement 3)
(3, 1, 'pending', '2025-09-25 09:00:00'),
(3, 2, 'complied', '2025-09-17 12:00:00'),
(3, 3, 'complied', '2025-09-18 16:30:00'),
(3, 4, 'not_complied', '2025-09-24 09:15:00'),
(3, 5, 'complied', '2025-09-20 14:00:00'),
-- Medical Certificate (requirement 4)
(4, 1, 'complied', '2025-09-25 10:30:00'),
(4, 2, 'complied', '2025-09-26 11:00:00'),
(4, 3, 'pending', '2025-09-27 13:00:00'),
(4, 4, 'complied', '2025-09-27 15:45:00'),
(4, 5, 'pending', '2025-09-28 10:00:00'),
-- Valid ID Submission (requirement 5)
(5, 1, 'complied', '2025-09-10 09:00:00'),
(5, 2, 'complied', '2025-09-11 10:30:00'),
(5, 3, 'complied', '2025-09-12 14:00:00'),
(5, 4, 'complied', '2025-09-13 16:45:00'),
(5, 5, 'complied', '2025-09-14 11:20:00');
