# DTO Schema Alignment - Documentation Package

## Overview

This package contains comprehensive documentation and reference materials for restructuring DTOs to match the `ccsync_api` database schema.

---

## Files Created

### 1. Schema Analysis Document
**File:** `DTO_SCHEMA_ALIGNMENT.md`  
**Location:** `ccsync-api-plain/config/database/`  
**Purpose:** Comprehensive analysis of current DTOs vs database schema  
**Contains:**
- Executive summary
- Users table schema vs UserDTO comparison
- Members table schema vs MemberDTO comparison
- Issues identification with detailed table
- Proposed changes (Option A vs Option B)
- Restructured DTO definitions
- Migration impact analysis
- Field transformation examples
- Implementation phases
- Validation checklist
- Recommended next steps

**Read this first for understanding the scope and rationale**

---

### 2. Implementation Guide
**File:** `IMPLEMENTATION_GUIDE.md`  
**Location:** `ccsync-api-plain/config/database/`  
**Purpose:** Step-by-step implementation instructions  
**Contains:**
- Phase 1: DTO restructuring with code examples
- Phase 2: PHP endpoint updates with code snippets
- Phase 3: JavaScript file updates with before/after
- Phase 4: SQL script updates
- Phase 5: Testing checklist
- Rollback plan
- Verification steps
- Documentation updates
- Timeline and estimated hours

**Use this during implementation for step-by-step guidance**

---

### 3. Quick Reference Guide
**File:** `QUICK_REFERENCE.md`  
**Location:** `ccsync-api-plain/config/database/`  
**Purpose:** Quick lookup for changes and mappings  
**Contains:**
- Changes summary table (Before → After)
- File changes required (all files listed)
- Field mapping reference (DTO → Database)
- JavaScript transformation examples
- API response format changes
- Breaking changes summary
- Verification checklist
- Rollback commands
- Implementation checklist
- Quick stats

**Reference this during coding for field mappings and file lists**

---

### 4. Restructured UserDTO Example
**File:** `UserDTO_RESTRUCTURED.php`  
**Location:** `ccsync-v1/src/DTOs/`  
**Purpose:** Complete, production-ready UserDTO implementation  
**Contains:**
- Complete UserDTO class (matches users table)
- UserCreateDTO class
- UserUpdateDTO class
- UserQueryDTO class
- UserAuthDTO class
- Full documentation and field descriptions
- Proper type hints and annotations

**Copy contents into UserDTO.php after review**

---

### 5. Restructured MemberDTO Example
**File:** `MemberDTO_RESTRUCTURED.php`  
**Location:** `ccsync-v1/src/DTOs/`  
**Purpose:** Complete, production-ready MemberDTO implementation  
**Contains:**
- Complete MemberDTO class (matches members table)
- MemberCreateDTO class
- MemberUpdateDTO class
- MemberQueryDTO class
- Full documentation and field descriptions
- Proper type hints and annotations

**Copy contents into MemberDTO.php after review**

---

## How to Use These Documents

### For Planning Phase
1. Read: `DTO_SCHEMA_ALIGNMENT.md` (full document)
2. Review: `QUICK_REFERENCE.md` (changes summary section)
3. Understand the rationale and scope

### For Implementation Phase
1. Reference: `IMPLEMENTATION_GUIDE.md` (Phase 1-5)
2. Use: `QUICK_REFERENCE.md` (field mapping reference)
3. Copy: `UserDTO_RESTRUCTURED.php` and `MemberDTO_RESTRUCTURED.php`
4. Execute step-by-step following the implementation guide

### For Testing Phase
1. Use: `IMPLEMENTATION_GUIDE.md` (Phase 5 - Testing Checklist)
2. Reference: `QUICK_REFERENCE.md` (Verification Checklist)
3. Ensure all tests pass

### For Troubleshooting
1. Check: `QUICK_REFERENCE.md` (Breaking Changes)
2. Verify: `QUICK_REFERENCE.md` (Field Mapping Reference)
3. Reference: `DTO_SCHEMA_ALIGNMENT.md` (detailed analysis)

---

## Key Takeaways

### Main Changes

**UserDTO:**
- ❌ Remove: `firstName`, `lastName`, `suffix`, `isActive`
- ✅ Add: `name`, `firebaseUid`, `emailVerifiedAt`, `rememberToken`
- 🔄 Rename: `idNumber` → `idSchoolNumber` (with type int)

**MemberDTO:**
- ❌ Remove: `userId`, `semesterId`
- 🔄 Rename: `idNumber` → `idSchoolNumber` (type int), `yearLevel` → `year`

### Benefits

✓ Single source of truth (DTOs = Database Schema)  
✓ Eliminates transformation layers  
✓ Reduces code complexity  
✓ Fewer field mapping errors  
✓ Easier to maintain and debug  
✓ Better alignment with database design  

### Impact

- 2 main DTOs require restructuring
- 5+ PHP endpoints need updates
- 2+ JavaScript files need updates
- Estimated 9-13 hours of work
- Low risk (well-defined scope)

---

## Files to Modify (Not Yet Done)

### DTOs
- [ ] `ccsync-v1/src/DTOs/UserDTO.php` - Replace with restructured version
- [ ] `ccsync-v1/src/DTOs/MemberDTO.php` - Replace with restructured version
- [ ] `ccsync-v1/src/DTOs/UserCreateDTO.php` - Update (if separate file)
- [ ] `ccsync-v1/src/DTOs/MemberCreateDTO.php` - Update (if separate file)

### PHP Endpoints
- [ ] `ccsync-api-plain/auth/getUserByIdNumber.php` - Update response format
- [ ] `ccsync-api-plain/member/createMember.php` - Accept new field names
- [ ] `ccsync-api-plain/member/updateMember.php` - Accept new field names
- [ ] `ccsync-api-plain/member/getMembers.php` - Return new field names
- [ ] Other member/user endpoints - Review and update as needed

### JavaScript
- [ ] `ccsync-v1/src/js/pages/home/member/registerMember.js` - Update form handling
- [ ] `ccsync-v1/src/js/pages/home/member/viewMember.js` - Update display
- [ ] Other member/user related JS - Review and update as needed

### SQL
- [ ] `ccsync-api-plain/config/database/insert_sample_users.sql` - Verify
- [ ] `ccsync-api-plain/config/database/insert_sample_members.sql` - Create/Update

---

## Related Context

### Previous Work Completed
- Phase 2 (DTOs & Validation): All 32+ DTOs created
- Member Management Rendering Fix: Imports added to registerMember.js
- API Integration: Both member pages migrated to ccsync-api-plain
- Mock Data Removal: viewMember.js uses live API
- Sample User Data: insert_sample_users.sql created

### What This Alignment Enables
- Clean API contracts between frontend/backend
- Easier API endpoint development
- Reduced transformation overhead
- Better error debugging
- Cleaner database queries

---

## Database Schema Reference

### Users Table Structure
```sql
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL PRIMARY KEY,
  `name` varchar(255) NOT NULL,                    -- SINGLE FIELD
  `email` varchar(255) NOT NULL UNIQUE,
  `email_verified_at` timestamp NULL,
  `firebase_uid` varchar(255) UNIQUE NULL,
  `id_school_number` int(10) UNSIGNED NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','guest') DEFAULT 'user',
  `remember_token` varchar(100) NULL,
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL
);
```

### Members Table Structure
```sql
CREATE TABLE `members` (
  `id` bigint(20) UNSIGNED NOT NULL PRIMARY KEY,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `suffix` varchar(50) NULL,
  `id_school_number` int(10) UNSIGNED NOT NULL UNIQUE,
  `email` varchar(255) UNIQUE NULL,
  `birth_date` date NOT NULL,
  `enrollment_date` date NOT NULL,
  `program` varchar(255) NOT NULL,
  `year` tinyint(3) UNSIGNED NOT NULL,          -- 1-4
  `is_paid` tinyint(1) NOT NULL,                -- 0-1
  `created_at` timestamp NULL,
  `updated_at` timestamp NULL
);
```

---

## Next Steps

1. **Review**: Read `DTO_SCHEMA_ALIGNMENT.md` for full context
2. **Plan**: Decide if restructuring will be done in phases or all at once
3. **Backup**: Create backups of all files to be modified
4. **Implement**: Follow `IMPLEMENTATION_GUIDE.md` step by step
5. **Test**: Use testing checklist from implementation guide
6. **Verify**: Ensure all verification steps pass
7. **Document**: Update any additional documentation needed

---

## Document Statistics

| Document | Type | Size | Purpose |
|----------|------|------|---------|
| DTO_SCHEMA_ALIGNMENT.md | Analysis | Comprehensive | Understanding & Planning |
| IMPLEMENTATION_GUIDE.md | Guide | Step-by-step | Execution |
| QUICK_REFERENCE.md | Reference | Lookup Tables | Field Mappings & Checklists |
| UserDTO_RESTRUCTURED.php | Code | Production-ready | Implementation Template |
| MemberDTO_RESTRUCTURED.php | Code | Production-ready | Implementation Template |

---

## Support & Questions

If you have questions during implementation:

1. **About Schema:** Check `DTO_SCHEMA_ALIGNMENT.md` section 1-2
2. **About Steps:** Check `IMPLEMENTATION_GUIDE.md` for your phase
3. **About Mappings:** Check `QUICK_REFERENCE.md` field mapping tables
4. **About Code:** Review the restructured DTO examples
5. **About Breaking Changes:** Check `QUICK_REFERENCE.md` breaking changes section

---

## Version History

**v2.0** (Current - Schema Aligned)
- Restructured DTOs to match database schema
- Removed fields not in database (userId, semesterId, isActive, suffix from users)
- Added missing fields (firebaseUid, emailVerifiedAt, rememberToken)
- Renamed fields for consistency (idNumber→idSchoolNumber, yearLevel→year)

**v1.0** (Previous)
- Original DTO design with camelCase throughout
- Transformation layers in JavaScript and PHP
- Some fields not matching database schema

---

## Conclusion

This documentation package provides everything needed to successfully restructure DTOs to match the database schema. The changes will improve code quality, reduce bugs, and make the system more maintainable.

**Recommendation:** Start with reading `DTO_SCHEMA_ALIGNMENT.md` to understand the full scope, then proceed with implementation following `IMPLEMENTATION_GUIDE.md`.

