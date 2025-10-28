-- ============================================
-- CCSYNC Requirements Tables Migration
-- ============================================
-- This script creates the requirements and requirements_compliance tables
-- Run this directly in your database management tool (PhpMyAdmin, MySQL Workbench, etc.)
-- ============================================

-- Create requirements table
CREATE TABLE IF NOT EXISTS `requirements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('open','closed','archived') NOT NULL DEFAULT 'open',
  `requirement_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create requirements_compliance table
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

-- Insert sample requirements
INSERT INTO `requirements` (`name`, `description`, `status`, `requirement_date`) VALUES
('Good Moral Certificate', 'Character clearance from previous institution or employer', 'open', '2025-10-28'),
('Transcript of Records', 'Official transcript showing academic performance and grades', 'open', '2025-10-28'),
('Birth Certificate', 'Certified copy of birth certificate (original or photocopy)', 'open', '2025-10-28'),
('Medical Certificate', 'Health examination clearance from a licensed physician', 'open', '2025-10-30'),
('Valid ID Submission', 'Any valid government-issued identification document', 'closed', '2025-09-30'),
('Proof of Residency', 'Recent utility bill or lease agreement showing current address', 'open', '2025-11-15'),
('NBI Clearance', 'National Bureau of Investigation clearance certificate', 'open', '2025-11-20');

-- Insert sample compliance records
INSERT INTO `requirements_compliance` (`requirement_id`, `member_id`, `compliance_status`, `submitted_at`) VALUES
(1, 1, 'complied', '2025-09-15 10:00:00'),
(1, 2, 'complied', '2025-09-18 14:30:00'),
(1, 3, 'not_complied', '2025-09-20 09:00:00'),
(1, 4, 'pending', '2025-09-21 08:00:00'),
(1, 5, 'complied', '2025-09-22 11:15:00'),
(2, 1, 'complied', '2025-09-16 08:45:00'),
(2, 2, 'pending', '2025-09-20 12:00:00'),
(2, 3, 'complied', '2025-09-19 15:20:00'),
(2, 4, 'complied', '2025-09-21 10:00:00'),
(2, 5, 'not_complied', '2025-09-23 13:45:00'),
(3, 1, 'pending', '2025-09-25 09:00:00'),
(3, 2, 'complied', '2025-09-17 12:00:00'),
(3, 3, 'complied', '2025-09-18 16:30:00'),
(3, 4, 'not_complied', '2025-09-24 09:15:00'),
(3, 5, 'complied', '2025-09-20 14:00:00'),
(4, 1, 'complied', '2025-09-25 10:30:00'),
(4, 2, 'complied', '2025-09-26 11:00:00'),
(4, 3, 'pending', '2025-09-27 13:00:00'),
(4, 4, 'complied', '2025-09-27 15:45:00'),
(4, 5, 'pending', '2025-09-28 10:00:00'),
(5, 1, 'complied', '2025-09-10 09:00:00'),
(5, 2, 'complied', '2025-09-11 10:30:00'),
(5, 3, 'complied', '2025-09-12 14:00:00'),
(5, 4, 'complied', '2025-09-13 16:45:00'),
(5, 5, 'complied', '2025-09-14 11:20:00');
