-- ============================================================================
-- Migration Script: Add name_first and name_last to users table
-- Database: ccsync_api
-- Purpose: Split single `name` field into `name_first` and `name_last` for 
--          uniform schema with member registration requirements
-- ============================================================================

-- ============================================================================
-- STEP 1: Add new columns (nullable initially to accommodate existing data)
-- ============================================================================
ALTER TABLE users 
ADD COLUMN name_first VARCHAR(255) NULL AFTER id,
ADD COLUMN name_last VARCHAR(255) NULL AFTER name_first;

-- ============================================================================
-- STEP 2: Populate new columns by splitting existing names
-- Logic: Split on first space, first part = first_name, rest = last_name
-- Example: "Juan Dela Cruz" → name_first="Juan", name_last="Dela Cruz"
-- ============================================================================
UPDATE users 
SET 
  name_first = TRIM(SUBSTRING_INDEX(name, ' ', 1)),
  name_last = TRIM(SUBSTRING_INDEX(name, ' ', -1));

-- ============================================================================
-- STEP 3: Handle edge cases (names with only one word)
-- If no space found, entire name goes to first_name, last_name = empty string
-- ============================================================================
UPDATE users 
SET name_last = ''
WHERE name_last = name_first AND name NOT LIKE '% %';

-- ============================================================================
-- STEP 4: Make columns NOT NULL (all data is now populated)
-- ============================================================================
ALTER TABLE users 
MODIFY COLUMN name_first VARCHAR(255) NOT NULL,
MODIFY COLUMN name_last VARCHAR(255) NOT NULL;

-- ============================================================================
-- STEP 5: Convert `name` column to a VIRTUAL computed column
-- This maintains backward compatibility - code querying `name` still works
-- The value is computed on-the-fly: CONCAT(name_first, ' ', name_last)
-- ============================================================================
ALTER TABLE users 
MODIFY COLUMN name VARCHAR(255) GENERATED AS (CONCAT(name_first, ' ', name_last)) VIRTUAL;

-- ============================================================================
-- STEP 6: Add indexes for common queries (optional but recommended)
-- ============================================================================
-- ALTER TABLE users ADD INDEX idx_name_first (name_first);
-- ALTER TABLE users ADD INDEX idx_name_last (name_last);

-- ============================================================================
-- Verification Queries - Run these to confirm migration success
-- ============================================================================

-- Check that migration worked correctly
-- SELECT id, name_first, name_last, name FROM users;

-- Verify data integrity (name field should match computed value)
-- SELECT id, name_first, name_last, 
--        CONCAT(name_first, ' ', name_last) as computed_name,
--        name as stored_name
-- FROM users;

-- Verify no NULL values exist
-- SELECT COUNT(*) as null_count FROM users WHERE name_first IS NULL OR name_last IS NULL;

-- ============================================================================
-- ROLLBACK PROCEDURE (if needed)
-- ============================================================================
-- If something goes wrong, run these commands to revert:
--
-- ALTER TABLE users 
-- MODIFY COLUMN name VARCHAR(255) NOT NULL;
--
-- ALTER TABLE users 
-- DROP COLUMN name_first,
-- DROP COLUMN name_last;
-- ============================================================================
