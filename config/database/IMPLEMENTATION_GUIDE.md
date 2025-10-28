# DTO Schema Alignment - Implementation Guide

## Overview

This guide provides step-by-step instructions for restructuring DTOs to match the `ccsync_api` database schema. The changes ensure DTOs are the canonical representation with no transformation layers needed.

---

## Phase 1: DTO Class Restructuring

### Step 1.1: Backup Current DTOs

Before making changes, backup existing DTOs:

```bash
# In ccsync-v1/src/DTOs/
cp UserDTO.php UserDTO_BACKUP.php
cp MemberDTO.php MemberDTO_BACKUP.php
```

### Step 1.2: Replace UserDTO.php

**Changes Made:**
- ✅ Replace `firstName` + `lastName` with single `name` field
- ❌ Remove `suffix` field (not in users table)
- ❌ Remove `isActive` field (not in database)
- ✅ Add `firebaseUid` field
- ✅ Add `emailVerifiedAt` field
- ✅ Add `rememberToken` field
- 🔄 Rename `idNumber` → `idSchoolNumber` (int type)

**File Location:** `ccsync-v1/src/DTOs/UserDTO.php`

See: `UserDTO_RESTRUCTURED.php` for complete implementation

**Key DTOs:**
- `UserDTO` - Complete profile
- `UserCreateDTO` - User creation
- `UserUpdateDTO` - User profile updates
- `UserQueryDTO` - User search
- `UserAuthDTO` - Auth response

### Step 1.3: Replace MemberDTO.php

**Changes Made:**
- ❌ Remove `userId` field (members table has no FK to users)
- ❌ Remove `semesterId` field (not in database)
- 🔄 Rename `idNumber` → `idSchoolNumber` (int type)
- 🔄 Rename `yearLevel` → `year` (int type)
- ✅ Keep all other fields

**File Location:** `ccsync-v1/src/DTOs/MemberDTO.php`

See: `MemberDTO_RESTRUCTURED.php` for complete implementation

**Key DTOs:**
- `MemberDTO` - Complete profile
- `MemberCreateDTO` - Member creation/registration
- `MemberUpdateDTO` - Member updates
- `MemberQueryDTO` - Member search

---

## Phase 2: PHP Endpoint Updates

### Step 2.1: Update `createMember.php`

**File:** `ccsync-api-plain/member/createMember.php`

**Changes Needed:**

```php
// OLD: Expected camelCase from API
$firstName = $data['firstName'] ?? null;
$lastName = $data['lastName'] ?? null;

// NEW: Use snake_case directly from JSON
$firstName = $data['first_name'] ?? null;
$lastName = $data['last_name'] ?? null;

// OLD: Handle yearLevel
$year = $data['yearLevel'] ?? null;

// NEW: Handle year directly
$year = $data['year'] ?? null;
```

**Database Insert:**

```php
// Both old and new should insert same fields
$query = "INSERT INTO members (first_name, last_name, birth_date, year, is_paid, ...) 
          VALUES (?, ?, ?, ?, ?, ...)";
```

### Step 2.2: Update `updateMember.php`

**File:** `ccsync-api-plain/member/updateMember.php`

Similar changes - accept snake_case fields directly

### Step 2.3: Update `getUserByIdNumber.php`

**File:** `ccsync-api-plain/auth/getUserByIdNumber.php`

**Current Response:**

```php
// OLD: Returns camelCase
$response = [
    'id' => $user['id'],
    'firstName' => $user['name_first'],  // MISMATCH - database has single 'name'
    'lastName' => $user['name_last'],    // MISMATCH
    'idNumber' => $user['id_school_number'],
    'email' => $user['email'],
];
```

**Updated Response:**

```php
// NEW: Returns schema-aligned format
$response = [
    'id' => $user['id'],
    'name' => $user['name'],              // Single field now
    'email' => $user['email'],
    'idSchoolNumber' => $user['id_school_number'],
    'firebaseUid' => $user['firebase_uid'],
    'role' => $user['role'],
    'emailVerifiedAt' => $user['email_verified_at'],
];
```

### Step 2.4: Update `getMembers.php`

**File:** `ccsync-api-plain/member/getMembers.php`

**Current Response:**

```php
// OLD: Might use yearLevel
$members[] = [
    'id' => $member['id'],
    'firstName' => $member['first_name'],
    'lastName' => $member['last_name'],
    'yearLevel' => $member['year'],      // Renamed
    'isPaid' => (bool) $member['is_paid'],
];
```

**Updated Response:**

```php
// NEW: Use schema field names directly
$members[] = [
    'id' => $member['id'],
    'firstName' => $member['first_name'],
    'lastName' => $member['last_name'],
    'idSchoolNumber' => $member['id_school_number'],
    'birthDate' => $member['birth_date'],
    'enrollmentDate' => $member['enrollment_date'],
    'program' => $member['program'],
    'year' => $member['year'],            // Direct field name
    'isPaid' => (bool) $member['is_paid'],
];
```

---

## Phase 3: JavaScript File Updates

### Step 3.1: Update `registerMember.js`

**File:** `ccsync-v1/src/js/pages/home/member/registerMember.js`

**Current Code:**

```javascript
// OLD: Transform camelCase to snake_case manually
async function submitMemberForm(event) {
    event.preventDefault();
    
    const formData = {
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        yearLevel: parseInt(document.getElementById('year').value),
        birthDate: document.getElementById('birthDate').value,
        isPaid: document.getElementById('isPaid').checked
    };
    
    // Manual transformation
    const apiData = {
        first_name: formData.firstName,
        last_name: formData.lastName,
        year: formData.yearLevel,
        birth_date: formData.birthDate,
        is_paid: formData.isPaid ? 1 : 0
    };
}
```

**Updated Code:**

```javascript
// NEW: Still use camelCase in form handling, but simplify transformations
async function submitMemberForm(event) {
    event.preventDefault();
    
    const formData = {
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        year: parseInt(document.getElementById('year').value),  // CHANGED: yearLevel → year
        birthDate: document.getElementById('birthDate').value,
        isPaid: document.getElementById('isPaid').checked
    };
    
    // Transform to snake_case for API
    const apiData = {
        first_name: formData.firstName,
        last_name: formData.lastName,
        year: formData.year,                // Direct mapping now
        birth_date: formData.birthDate,
        is_paid: formData.isPaid ? 1 : 0,
        enrollment_date: new Date().toISOString().split('T')[0]  // Add if missing
    };
    
    // Call API
    try {
        const response = await fetch('/ccsync-plain-php/member/createMember.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(apiData)
        });
        
        const result = await response.json();
        // Handle response...
    } catch (error) {
        console.error('Error creating member:', error);
    }
}
```

### Step 3.2: Update `viewMember.js`

**File:** `ccsync-v1/src/js/pages/home/member/viewMember.js`

**Current Code:**

```javascript
// OLD: Handles yearLevel from API
async function loadMembers() {
    const response = await fetch('/ccsync-plain-php/member/getMembers.php', {
        headers: { 'Authorization': `Bearer ${token}` }
    });
    
    const members = await response.json();
    members.forEach(member => {
        console.log(member.yearLevel);  // OLD field name
    });
}
```

**Updated Code:**

```javascript
// NEW: Handles year field directly
async function loadMembers() {
    const response = await fetch('/ccsync-plain-php/member/getMembers.php', {
        headers: { 'Authorization': `Bearer ${token}` }
    });
    
    const members = await response.json();
    members.forEach(member => {
        console.log(member.year);  // NEW field name
        // Use member.idSchoolNumber instead of parsing
        console.log(member.idSchoolNumber);
    });
}
```

### Step 3.3: Helper Function for Transformations

**Create:** `ccsync-v1/src/js/utils/dtoTransform.js` (optional, for consistency)

```javascript
/**
 * Transform camelCase object to snake_case for API
 * @param {Object} obj - Object with camelCase keys
 * @param {Array<string>} fields - Fields to transform
 * @returns {Object} Object with snake_case keys
 */
export function toSnakeCase(obj, fields) {
    const result = {};
    
    const camelToSnake = (str) => 
        str.replace(/[A-Z]/g, letter => `_${letter.toLowerCase()}`);
    
    for (const field of fields) {
        if (field in obj) {
            const snakeKey = camelToSnake(field);
            result[snakeKey] = obj[field];
        }
    }
    
    return result;
}

/**
 * Transform snake_case object to camelCase from API response
 * @param {Object} obj - Object with snake_case keys
 * @returns {Object} Object with camelCase keys
 */
export function toCamelCase(obj) {
    const snakeToCamel = (str) => 
        str.replace(/_([a-z])/g, (_, letter) => letter.toUpperCase());
    
    const result = {};
    for (const [key, value] of Object.entries(obj)) {
        result[snakeToCamel(key)] = value;
    }
    return result;
}
```

**Usage in registerMember.js:**

```javascript
import { toSnakeCase } from '../../utils/dtoTransform.js';

async function submitMemberForm(event) {
    event.preventDefault();
    
    const formData = {
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        year: parseInt(document.getElementById('year').value),
        birthDate: document.getElementById('birthDate').value,
        isPaid: document.getElementById('isPaid').checked
    };
    
    // Use helper function
    const apiData = toSnakeCase(formData, [
        'firstName', 'lastName', 'year', 'birthDate', 'isPaid'
    ]);
    
    // Add enrollment date
    apiData.enrollment_date = new Date().toISOString().split('T')[0];
    
    // Call API...
}
```

---

## Phase 4: SQL Script Updates

### Step 4.1: Update `insert_sample_users.sql`

**File:** `ccsync-api-plain/config/database/insert_sample_users.sql`

Ensure it uses correct field names:

```sql
INSERT INTO `ccsync_api`.`users` (
    `id`,
    `name`,                    -- SINGLE FIELD (not name_first, name_last)
    `email`,
    `id_school_number`,        -- INT type
    `password`,
    `role`,                    -- enum('user','admin','guest')
    `firebase_uid`,
    `created_at`,
    `updated_at`
) VALUES
(3, 'John Doe', 'john.doe@example.com', 20210001, 
 '$2y$12$K9/r.RR3WPJTvJXGDEWGXuGaB0t5wTXECU.5NZeVVxB.oKTKhfN.2', 
 'user', NULL, NOW(), NOW());
```

### Step 4.2: Create `insert_sample_members.sql`

**File:** `ccsync-api-plain/config/database/insert_sample_members.sql`

```sql
INSERT INTO `ccsync_api`.`members` (
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
) VALUES
(
    'John',
    'Doe',
    NULL,
    20210001,
    'john.doe@example.com',
    '2002-05-15',
    NOW(),
    'BSCS',
    1,
    0,
    NOW(),
    NOW()
);
```

---

## Phase 5: Testing Checklist

### Unit Tests to Create

```javascript
// Test: registerMember.js
describe('Member Registration', () => {
    test('should accept year field instead of yearLevel', () => {
        // ...
    });
    
    test('should send first_name and last_name to API', () => {
        // ...
    });
});

// Test: viewMember.js
describe('Member View', () => {
    test('should display year from API response', () => {
        // ...
    });
    
    test('should handle idSchoolNumber correctly', () => {
        // ...
    });
});
```

### Integration Tests

1. **Create Member Flow:**
   - Fill form with year (not yearLevel)
   - Submit and verify API receives correct fields
   - Verify member created in database with correct field names

2. **View Members Flow:**
   - Load members page
   - Verify each member displays correct year value
   - Verify all fields display correctly

3. **Update Member Flow:**
   - Edit member record
   - Change year, program, etc.
   - Verify API receives correct snake_case fields
   - Verify database updated with correct values

### Manual Testing

1. **User Registration:**
   ```
   - Create new user account
   - Verify user created with single 'name' field
   - Verify id_school_number stored as int
   ```

2. **Member Registration:**
   ```
   - Look up user by ID
   - Fill member registration form
   - Submit and verify member created
   - Check database: first_name, last_name separate
   - Check database: year field (1-4)
   ```

3. **Member Listing:**
   ```
   - View all members
   - Verify display shows correct year level
   - Verify all fields display properly
   ```

---

## Migration Summary

### Before Changes
```
UserDTO uses:        Database has:
- firstName          - name (single field)
- lastName           - (no last_name)
- idNumber           - id_school_number
- (no firebaseUid)   - firebase_uid
- isActive           - (no is_active)

MemberDTO uses:      Database has:
- yearLevel          - year
- (has userId)       - (no user_id FK)
```

### After Changes
```
UserDTO uses:        Database has:
- name               - name ✓
- idSchoolNumber     - id_school_number ✓
- firebaseUid        - firebase_uid ✓
- emailVerifiedAt    - email_verified_at ✓

MemberDTO uses:      Database has:
- year               - year ✓
- idSchoolNumber     - id_school_number ✓
- (no userId)        - (no user_id FK) ✓
```

---

## Rollback Plan

If issues occur during implementation:

1. **Restore DTOs:**
   ```bash
   cp UserDTO_BACKUP.php UserDTO.php
   cp MemberDTO_BACKUP.php MemberDTO.php
   ```

2. **Revert API Endpoints:**
   - Use git to revert PHP endpoint changes
   - Or restore from backup

3. **Revert JavaScript:**
   - Comment out new code
   - Use git to restore previous versions

4. **Revert Database (if modified):**
   - Restore from backup database dump

---

## Verification Steps

After implementation, verify:

- ✓ All DTOs match database schema field names
- ✓ All DTOs have correct field types
- ✓ No extra fields in DTOs (e.g., userId, semesterId)
- ✓ PHP endpoints accept correct field names
- ✓ JavaScript transforms camelCase properly
- ✓ Sample data inserts with correct field names
- ✓ Member registration works end-to-end
- ✓ Member listing displays correctly
- ✓ Member updates work properly

---

## Documentation Updates

After implementation, update:

1. **API Documentation:** Document expected field names (snake_case)
2. **DTO Documentation:** Update with new schema
3. **Developer Guide:** Explain camelCase (JavaScript) → snake_case (PHP/Database) pattern
4. **Database Schema Docs:** Confirm field names and types

---

## Timeline

- **Phase 1 (DTOs):** 2-3 hours
- **Phase 2 (PHP Endpoints):** 2-3 hours  
- **Phase 3 (JavaScript):** 2-3 hours
- **Phase 4 (SQL):** 1 hour
- **Phase 5 (Testing):** 2-3 hours

**Total Estimated Time:** 9-13 hours

---

## Questions & Support

If you encounter issues:

1. Check the schema alignment document (DTO_SCHEMA_ALIGNMENT.md)
2. Review the restructured DTO examples
3. Compare database schema with DTO definitions
4. Check API endpoint expectations
5. Verify JavaScript transformations

