-- Migration: 2025_10_28_000001_create_requirements_table
-- Created: 2025-10-28
-- Purpose: Create requirements table for tracking organizational requirements

CREATE TABLE IF NOT EXISTS `requirements` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('open','closed','archived') NOT NULL DEFAULT 'open',
  `requirement_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert initial sample data
INSERT INTO `requirements` (`name`, `description`, `status`, `requirement_date`) VALUES
('Good Moral Certificate', 'Character clearance from previous institution or employer', 'open', '2025-10-28'),
('Transcript of Records', 'Official transcript showing academic performance and grades', 'open', '2025-10-28'),
('Birth Certificate', 'Certified copy of birth certificate (original or photocopy)', 'open', '2025-10-28'),
('Medical Certificate', 'Health examination clearance from a licensed physician', 'open', '2025-10-30'),
('Valid ID Submission', 'Any valid government-issued identification document', 'closed', '2025-09-30'),
('Proof of Residency', 'Recent utility bill or lease agreement showing current address', 'open', '2025-11-15'),
('NBI Clearance', 'National Bureau of Investigation clearance certificate', 'open', '2025-11-20');
