-- Insert Sample Student User
INSERT INTO `users` (
  `id`,
  `name`,
  `email`,
  `email_verified_at`,
  `firebase_uid`,
  `id_school_number`,
  `password`,
  `role`,
  `remember_token`,
  `created_at`,
  `updated_at`
) VALUES (
  3,
  'Sample Student',
  'samplestudent@gmail.com',
  NULL,
  NULL,
  19999999,
  '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5EQaYx2h5PBIS',
  'user',
  NULL,
  NOW(),
  NOW()
);

-- Insert Sample Student Member
INSERT INTO `members` (
  `id`,
  `first_name`,
  `last_name`,
  `suffix`,
  `id_school_number`,
  `email`,
  `birth_date`,
  `enrollment_date`,
  `program`,
  `year`,
  `is_paid`,
  `created_at`,
  `updated_at`
) VALUES (
  3,
  'Sample',
  'Student',
  NULL,
  19999999,
  'samplestudent@gmail.com',
  '2005-01-15',
  '2024-09-01',
  'BSCS',
  1,
  0,
  NOW(),
  NOW()
);

-- Update AUTO_INCREMENT for users table
ALTER TABLE `users` AUTO_INCREMENT=4;

-- Update AUTO_INCREMENT for members table
ALTER TABLE `members` AUTO_INCREMENT=301;
