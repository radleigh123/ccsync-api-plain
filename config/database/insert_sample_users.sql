-- ============================================================================
-- CCSync Sample Users Insert Script
-- Database: ccsync_api
-- Purpose: Add registered USERS (not members) for testing
-- 
-- IMPORTANT:
-- - Users added here are registered but NOT members of the organization
-- - They can use these credentials to log in
-- - They can then register themselves as members via register-member.html
-- - Passwords are hashed with bcrypt ($2y$12$...)
-- 
-- ============================================================================

-- Sample test password for all users: "Password@123"
-- Bcrypt hash (cost: 12): $2y$12$K9/r.RR3WPJTvJXGDEWGXuGaB0t5wTXECU.5NZeVVxB.oKTKhfN.2
--
-- You can test login with:
-- Email: juan.delacruz@student.edu
-- Password: Password@123
--
-- Or generate your own hash using: https://www.browserling.com/tools/bcrypt

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
) VALUES
-- User 1: Juan Dela Cruz - Student (BSIT)
(
  3,
  'Juan Dela Cruz',
  'juan.delacruz@student.edu',
  NULL,
  'firebase_user001_xyz123abc',
  20210001,
  '$2y$12$K9/r.RR3WPJTvJXGDEWGXuGaB0t5wTXECU.5NZeVVxB.oKTKhfN.2',
  'user',
  NULL,
  NOW(),
  NOW()
),

-- User 2: Maria Santos - Student (BSCS)
(
  4,
  'Maria Santos',
  'maria.santos@student.edu',
  NULL,
  'firebase_user002_abc456def',
  20210002,
  '$2y$12$K9/r.RR3WPJTvJXGDEWGXuGaB0t5wTXECU.5NZeVVxB.oKTKhfN.2',
  'user',
  NULL,
  NOW(),
  NOW()
),

-- User 3: Carlos Gonzales - Student (BSIT)
(
  5,
  'Carlos Gonzales',
  'carlos.gonzales@student.edu',
  NULL,
  'firebase_user003_def789ghi',
  20210003,
  '$2y$12$K9/r.RR3WPJTvJXGDEWGXuGaB0t5wTXECU.5NZeVVxB.oKTKhfN.2',
  'user',
  NULL,
  NOW(),
  NOW()
),

-- User 4: Ana Rodriguez - Student (BSIS)
(
  6,
  'Ana Rodriguez',
  'ana.rodriguez@student.edu',
  NULL,
  'firebase_user004_ghi012jkl',
  20210004,
  '$2y$12$K9/r.RR3WPJTvJXGDEWGXuGaB0t5wTXECU.5NZeVVxB.oKTKhfN.2',
  'user',
  NULL,
  NOW(),
  NOW()
),

-- User 5: Robert Cruz - Student (BSIT)
(
  7,
  'Robert Cruz',
  'robert.cruz@student.edu',
  NULL,
  'firebase_user005_jkl345mno',
  20210005,
  '$2y$12$K9/r.RR3WPJTvJXGDEWGXuGaB0t5wTXECU.5NZeVVxB.oKTKhfN.2',
  'user',
  NULL,
  NOW(),
  NOW()
),

-- User 6: Patricia Morales - Student (BSCS)
(
  8,
  'Patricia Morales',
  'patricia.morales@student.edu',
  NOW(),
  'firebase_user006_mno678pqr',
  20210006,
  '$2y$12$K9/r.RR3WPJTvJXGDEWGXuGaB0t5wTXECU.5NZeVVxB.oKTKhfN.2',
  'user',
  NULL,
  NOW(),
  NOW()
),

-- User 7: Vincent Reyes - Student (BSIS)
(
  9,
  'Vincent Reyes',
  'vincent.reyes@student.edu',
  NULL,
  'firebase_user007_pqr901stu',
  20210007,
  '$2y$12$K9/r.RR3WPJTvJXGDEWGXuGaB0t5wTXECU.5NZeVVxB.oKTKhfN.2',
  'user',
  NULL,
  NOW(),
  NOW()
),

-- User 8: Sophie Fernandez - Student (BSIT)
(
  10,
  'Sophie Fernandez',
  'sophie.fernandez@student.edu',
  NULL,
  'firebase_user008_stu234vwx',
  20210008,
  '$2y$12$K9/r.RR3WPJTvJXGDEWGXuGaB0t5wTXECU.5NZeVVxB.oKTKhfN.2',
  'user',
  NULL,
  NOW(),
  NOW()
),

-- User 9: Daniel Perez - Student (BSCS)
(
  11,
  'Daniel Perez',
  'daniel.perez@student.edu',
  NULL,
  'firebase_user009_vwx567yza',
  20210009,
  '$2y$12$K9/r.RR3WPJTvJXGDEWGXuGaB0t5wTXECU.5NZeVVxB.oKTKhfN.2',
  'user',
  NULL,
  NOW(),
  NOW()
),

-- User 10: Jessica Liu - Student (BSIS)
(
  12,
  'Jessica Liu',
  'jessica.liu@student.edu',
  NULL,
  'firebase_user010_yza890abc',
  20210010,
  '$2y$12$K9/r.RR3WPJTvJXGDEWGXuGaB0t5wTXECU.5NZeVVxB.oKTKhfN.2',
  'user',
  NULL,
  NOW(),
  NOW()
);

-- ============================================================================
-- Notes:
-- ============================================================================
-- 1. Password: All users have the same password for testing: "Password@123"
--    Bcrypt hash: $2y$12$K9/r.RR3WPJTvJXGDEWGXuGaB0t5wTXECU.5NZeVVxB.oKTKhfN.2
--    Change this hash for production!
--
-- 2. ID numbers used:
--    20210001 - 20210010 (sample school IDs, sequential)
--    Update these to match your actual school ID format
--
-- 3. Firebase UIDs are sample placeholders
--    In production, these would come from Firebase Authentication
--    Can be updated later when users authenticate via Firebase
--
-- 4. All users are created with role 'user' by default
--    Admins should be created separately with role 'admin'
--    Current admins: ID 1 (role: admin), ID 2 (role: user but admin)
--
-- 5. These users can now:
--    ✅ Log in with their email and password
--    ✅ Search for their account in register-member.html
--    ✅ Register themselves as members (creates MemberDTO record)
--
-- 6. Members table remains empty until they register
--    Complete member registration flow:
--    - User logs in
--    - Goes to /pages/home/member/register-member.html
--    - Searches by their ID number (e.g., 20210001)
--    - System auto-fills: firstName, lastName, email
--    - User enters: birthDate, program, yearLevel, isPaid
--    - Submits to ccsync-api-plain/member/createMember.php
--    - User now appears in /pages/home/member/view-member.html
--
-- 7. Database: ccsync_api
--    Users table schema:
--    - id: auto-increment
--    - name: full name (not separated into first/last in DB)
--    - email: unique
--    - id_school_number: unique identifier for user lookup
--    - password: bcrypt hashed
--    - role: 'user', 'admin', or 'guest'
--    - firebase_uid: Firebase authentication ID (can be NULL)
--
-- ============================================================================
