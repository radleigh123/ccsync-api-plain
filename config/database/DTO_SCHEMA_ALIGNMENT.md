# DTO Schema Alignment Analysis

## Executive Summary

Currently, the DTOs use a different naming convention (camelCase) and structure than the actual `ccsync_api` database schema (snake_case). This document outlines the discrepancies and proposes changes to align DTOs with the actual database schema.

**Recommendation:** Restructure DTOs to match the database schema exactly to eliminate transformation layers and ensure consistency.

---

## 1. Users Table Schema vs UserDTO

### Database Schema (`ccsync_api.users`)

```sql
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,                    -- SINGLE FIELD (not name_first/name_last)
  `email` varchar(255) NOT NULL UNIQUE,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `firebase_uid` varchar(255) UNIQUE NULL,
  `id_school_number` int(10) UNSIGNED NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','guest') NOT NULL DEFAULT 'user',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
);
```

### Current UserDTO Definition

```php
class UserDTO {
    public int $id;                    // ✓ Matches
    public string $idNumber;           // Maps to: id_school_number (naming mismatch)
    public string $firstName;          // ✗ Database has single 'name' field (MISMATCH)
    public string $lastName;           // ✗ Database has single 'name' field (MISMATCH)
    public ?string $suffix;            // ✗ Database has no suffix field (EXTRA)
    public string $email;              // ✓ Matches
    public string $role;               // ✓ Matches
    public bool $isActive;             // ✗ Database has no is_active field (EXTRA)
    public string $createdAt;          // ✓ Matches (naming convention different but functional)
    public string $updatedAt;          // ✓ Matches (naming convention different but functional)
    // Missing: firebase_uid, email_verified_at, remember_token (INCOMPLETE)
}
```

### Issues Identified

| Field | Database | Current DTO | Issue |
|-------|----------|------------|-------|
| name | `name` (varchar) | firstName + lastName | Split vs Single |
| suffix | None | `suffix` | Extra field in DTO |
| id_school_number | `id_school_number` (int) | `idNumber` (string) | Type mismatch + naming |
| firebase_uid | `firebase_uid` (varchar) | Missing | Missing field |
| email_verified_at | `email_verified_at` (timestamp) | Missing | Missing field |
| is_active | None | `isActive` (bool) | Extra field in DTO |
| role | `role` (enum) | `role` (string) | ✓ Matches |
| remember_token | `remember_token` (varchar) | Missing | Missing field |
| created_at/updated_at | `created_at`, `updated_at` | `createdAt`, `updatedAt` | ✓ Matches (naming convention) |

---

## 2. Members Table Schema vs MemberDTO

### Database Schema (`ccsync_api.members`)

```sql
CREATE TABLE `members` (
  `id` bigint(20) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `suffix` varchar(50) DEFAULT NULL,
  `id_school_number` int(10) UNSIGNED NOT NULL UNIQUE,
  `email` varchar(255) DEFAULT NULL UNIQUE,
  `birth_date` date NOT NULL,
  `enrollment_date` date NOT NULL,
  `program` varchar(255) NOT NULL,
  `year` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;
```

### Current MemberDTO Definition

```php
class MemberDTO {
    public int $id;                    // ✓ Matches
    public int $userId;                // ✗ Database has NO userId (no user FK)
    public string $idNumber;           // Maps to: id_school_number (naming mismatch)
    public string $firstName;          // ✓ Matches (naming convention)
    public string $lastName;           // ✓ Matches (naming convention)
    public string $email;              // ✓ Matches
    public ?string $suffix;            // ✓ Matches
    public string $birthDate;          // ✓ Matches (naming convention)
    public string $enrollmentDate;     // ✓ Matches (naming convention)
    public string $program;            // ✓ Matches
    public int $yearLevel;             // Maps to: year (naming mismatch, but close)
    public bool $isPaid;               // ✓ Matches (naming convention)
    public ?int $semesterId;           // ✗ Database has no semester_id (EXTRA)
    public string $createdAt;          // ✓ Matches (naming convention)
    public string $updatedAt;          // ✓ Matches (naming convention)
}
```

### Issues Identified

| Field | Database | Current DTO | Issue |
|-------|----------|------------|-------|
| id | `id` | `id` | ✓ Matches |
| first_name | `first_name` | `firstName` | ✓ Matches (naming convention) |
| last_name | `last_name` | `lastName` | ✓ Matches (naming convention) |
| suffix | `suffix` | `suffix` | ✓ Matches |
| id_school_number | `id_school_number` | `idNumber` | Naming mismatch |
| email | `email` | `email` | ✓ Matches |
| birth_date | `birth_date` | `birthDate` | ✓ Matches (naming convention) |
| enrollment_date | `enrollment_date` | `enrollmentDate` | ✓ Matches (naming convention) |
| program | `program` | `program` | ✓ Matches |
| year | `year` | `yearLevel` | Naming mismatch |
| is_paid | `is_paid` | `isPaid` | ✓ Matches (naming convention) |
| created_at | `created_at` | `createdAt` | ✓ Matches (naming convention) |
| updated_at | `updated_at` | `updatedAt` | ✓ Matches (naming convention) |
| (extra) | None | userId | Extra field (not in DB) |
| (extra) | None | semesterId | Extra field (not in DB) |

---

## 3. Proposed Changes

### Option A: Keep DTO Fields, Add Transformations (Current Approach)

**Pros:**
- DTOs can remain more semantically meaningful
- Separation of concerns (DTOs ≠ Database schema)

**Cons:**
- Requires transformation in every API endpoint
- Transformation layer in JavaScript AND PHP
- Increased chance of field mapping errors
- Harder to debug field mismatches
- More code to maintain

### Option B: Restructure DTOs to Match Schema (RECOMMENDED)

**Pros:**
- Single source of truth (DTOs ≡ Database schema)
- No transformation layer needed
- Simpler, more maintainable code
- Easier to debug issues
- Reduced chance of field mapping errors

**Cons:**
- Some field names less semantic (e.g., `yearLevel` → `year`)
- Slight learning curve for developers

**Recommendation:** **OPTION B** - Restructure DTOs to match schema

---

## 4. Restructured DTOs

### UserDTO (Restructured)

**Changes:**
- Replace `firstName` + `lastName` with single `name` field
- Remove `suffix` field (not in database)
- Remove `isActive` field (not in database)
- Add `firebaseUid`, `emailVerifiedAt`, `rememberToken` fields
- Change `idNumber` (string) to `idSchoolNumber` (int) with correct type

```php
class UserDTO {
    public int $id;                           // PK
    public string $name;                      // Full name (single field)
    public string $email;                     // Unique email
    public ?string $emailVerifiedAt;          // Email verification timestamp
    public ?string $firebaseUid;              // Firebase UID (nullable, unique)
    public int $idSchoolNumber;               // School ID (type: int)
    public string $role;                      // enum: 'user', 'admin', 'guest'
    public ?string $rememberToken;            // Remember Me token
    public ?string $createdAt;                // Creation timestamp
    public ?string $updatedAt;                // Last update timestamp
}
```

### MemberDTO (Restructured)

**Changes:**
- Remove `userId` field (not in database - members table is independent)
- Change `idNumber` to `idSchoolNumber` with correct type (int)
- Rename `yearLevel` to `year` to match database
- Remove `semesterId` field (not in database)
- Ensure field types match database (e.g., `is_paid` as tinyint/bool)

```php
class MemberDTO {
    public int $id;                           // PK
    public string $firstName;                 // First name
    public string $lastName;                  // Last name
    public ?string $suffix;                   // Name suffix (optional)
    public int $idSchoolNumber;               // School ID (type: int) - UNIQUE
    public ?string $email;                    // Email (optional, unique if set)
    public string $birthDate;                 // Birth date (YYYY-MM-DD)
    public string $enrollmentDate;            // Enrollment date (YYYY-MM-DD)
    public string $program;                   // Program code (BSCS, BSIS, BSIT, BSCE)
    public int $year;                         // Year level (1-4)
    public bool $isPaid;                      // Payment status (tinyint → bool)
    public ?string $createdAt;                // Creation timestamp
    public ?string $updatedAt;                // Last update timestamp
}
```

---

## 5. Migration Impact

### Files Requiring Updates

1. **Frontend JavaScript Files:**
   - `registerMember.js` - Update field transformations
   - `viewMember.js` - Update field handling
   - Other member/user related JS files

2. **Backend PHP Endpoints:**
   - `createMember.php` - Update field expectations
   - `updateMember.php` - Update field mappings
   - `getUserByIdNumber.php` - Update response fields
   - Other user/member endpoints

3. **SQL Scripts:**
   - `insert_sample_users.sql` - Update if field types change
   - `insert_sample_members.sql` - Create with correct field names

4. **DTO Classes:**
   - `UserDTO.php` - Restructure
   - `MemberDTO.php` - Restructure
   - Related DTOs (UserCreateDTO, MemberCreateDTO, etc.)

---

## 6. Field Transformation Examples

### Before (Current)

```javascript
// registerMember.js - Manual transformation required
const memberData = {
    first_name: formData.firstName,      // Manual transform
    last_name: formData.lastName,        // Manual transform
    birth_date: formData.birthDate,      // Manual transform
    year: formData.yearLevel,            // Manual transform
    is_paid: formData.isPaid ? 1 : 0,    // Manual transform
};
```

### After (Schema-Aligned)

```javascript
// registerMember.js - Direct mapping
const memberData = {
    first_name: member.firstName,        // Direct (still camelCase in JS)
    last_name: member.lastName,
    birth_date: member.birthDate,
    year: member.year,                   // Renamed to match DB
    is_paid: member.isPaid,              // Direct (PHP will handle type)
};
// OR use DTO.toArray() for automatic transformation
```

---

## 7. Implementation Phases

### Phase 1: Restructure Core DTOs
1. Update `UserDTO.php` (remove suffix, isActive; add firebaseUid, emailVerifiedAt, rememberToken)
2. Update `MemberDTO.php` (remove userId, semesterId; rename yearLevel → year; fix types)
3. Update related DTOs (UserCreateDTO, MemberCreateDTO, etc.)

### Phase 2: Update PHP Endpoints
1. Update response mappings to use new DTO structure
2. Update `createMember.php`, `updateMember.php`, etc.
3. Verify field types match database schema

### Phase 3: Update JavaScript Files
1. Remove unnecessary transformations
2. Update API response handling in `registerMember.js`, `viewMember.js`
3. Test member registration and viewing

### Phase 4: Update SQL Scripts
1. Ensure `insert_sample_users.sql` and `insert_sample_members.sql` match schema
2. Verify all field names and types

---

## 8. Key Database Facts

### Users Table
- **Single `name` field:** Not firstName/lastName
- **id_school_number:** Integer, NOT string
- **No isActive field:** All users are implicitly active (no soft deletes)
- **No suffix field:** Members table has suffix, not users
- **Has firebase_uid:** For Firebase auth integration
- **Role enum values:** Only 'user', 'admin', 'guest' (not 'student', 'officer', 'member')

### Members Table
- **Independent from users:** No userId foreign key
- **First_name/last_name:** Separate fields
- **id_school_number:** Integer, UNIQUE
- **No semesterId:** Not yet implemented
- **year:** Stored as tinyint(3), represents 1-4
- **is_paid:** Tinyint(1), effectively boolean

---

## 9. Validation

After restructuring, verify:

- ✓ UserDTO fields match `ccsync_api.users` table exactly
- ✓ MemberDTO fields match `ccsync_api.members` table exactly
- ✓ No extra fields in DTOs that don't exist in database
- ✓ All field types match database types
- ✓ Field names match database snake_case (in transformations)
- ✓ JavaScript files handle new field names correctly
- ✓ PHP endpoints accept/return correct field names
- ✓ SQL scripts use correct field names and types

---

## 10. Recommended Next Steps

1. **Review this document** - Confirm the schema analysis is correct
2. **Approve restructuring approach** - Decide on Option B (schema-aligned DTOs)
3. **Phase 1 Implementation** - Update DTO class definitions
4. **Phase 2 Implementation** - Update PHP endpoints
5. **Phase 3 Implementation** - Update JavaScript files
6. **Phase 4 Implementation** - Update SQL scripts
7. **Testing** - Comprehensive testing of all changes
8. **Deployment** - Deploy restructured DTOs and updated code

---

## Summary of Changes

### UserDTO
- ❌ Remove: `firstName`, `lastName`, `suffix`, `isActive`
- ✅ Add: `name`, `firebaseUid`, `emailVerifiedAt`, `rememberToken`
- 🔄 Update: `idNumber` → `idSchoolNumber` (int)

### MemberDTO
- ❌ Remove: `userId`, `semesterId`
- ✅ Add: Nothing (all other fields present)
- 🔄 Update: `idNumber` → `idSchoolNumber` (int), `yearLevel` → `year` (int)

### Impact
- ✓ Eliminates transformation layers
- ✓ Simplifies API endpoint code
- ✓ Reduces chance of field mapping errors
- ✓ Makes DTOs the canonical representation
- ✓ Easier to maintain and debug

