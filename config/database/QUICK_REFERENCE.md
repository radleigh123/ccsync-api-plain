# DTO Restructuring - Quick Reference

## Changes Summary

### UserDTO: Before → After

| Field | Before | After | Type | Status |
|-------|--------|-------|------|--------|
| id | `id` | `id` | int | ✓ No change |
| name | `firstName` + `lastName` | `name` | string | 🔄 **CHANGED** |
| email | `email` | `email` | string | ✓ No change |
| emailVerifiedAt | ❌ Missing | `emailVerifiedAt` | string\|null | ✅ **ADDED** |
| firebaseUid | ❌ Missing | `firebaseUid` | string\|null | ✅ **ADDED** |
| idNumber | `idNumber` (string) | `idSchoolNumber` (int) | int | 🔄 **CHANGED** |
| role | `role` | `role` | string | ✓ No change |
| rememberToken | ❌ Missing | `rememberToken` | string\|null | ✅ **ADDED** |
| suffix | `suffix` | ❌ Removed | N/A | ❌ **REMOVED** |
| isActive | `isActive` | ❌ Removed | N/A | ❌ **REMOVED** |
| createdAt | `createdAt` | `createdAt` | string\|null | ✓ No change |
| updatedAt | `updatedAt` | `updatedAt` | string\|null | ✓ No change |

### MemberDTO: Before → After

| Field | Before | After | Type | Status |
|-------|--------|-------|------|--------|
| id | `id` | `id` | int | ✓ No change |
| firstName | `firstName` | `firstName` | string | ✓ No change |
| lastName | `lastName` | `lastName` | string | ✓ No change |
| suffix | `suffix` | `suffix` | string\|null | ✓ No change |
| idNumber | `idNumber` (string) | `idSchoolNumber` (int) | int | 🔄 **CHANGED** |
| email | `email` | `email` | string\|null | ✓ No change |
| birthDate | `birthDate` | `birthDate` | string | ✓ No change |
| enrollmentDate | `enrollmentDate` | `enrollmentDate` | string | ✓ No change |
| program | `program` | `program` | string | ✓ No change |
| yearLevel | `yearLevel` | `year` | int | 🔄 **CHANGED** |
| isPaid | `isPaid` | `isPaid` | bool | ✓ No change |
| userId | `userId` | ❌ Removed | N/A | ❌ **REMOVED** |
| semesterId | `semesterId` | ❌ Removed | N/A | ❌ **REMOVED** |
| createdAt | `createdAt` | `createdAt` | string\|null | ✓ No change |
| updatedAt | `updatedAt` | `updatedAt` | string\|null | ✓ No change |

---

## File Changes Required

### DTOs (5 Files)

1. **UserDTO.php** → Replace
   - File: `ccsync-v1/src/DTOs/UserDTO.php`
   - Reference: `UserDTO_RESTRUCTURED.php`

2. **MemberDTO.php** → Replace
   - File: `ccsync-v1/src/DTOs/MemberDTO.php`
   - Reference: `MemberDTO_RESTRUCTURED.php`

3. **UserCreateDTO.php** → Update
4. **MemberCreateDTO.php** → Update
5. **Other related DTOs** → Update as needed

### PHP Endpoints (5+ Files)

1. **auth/getUserByIdNumber.php** → Update response format
2. **member/createMember.php** → Accept schema field names
3. **member/updateMember.php** → Accept schema field names
4. **member/getMembers.php** → Return schema field names
5. **Other member endpoints** → Update as needed

### JavaScript Files (2 Main Files)

1. **src/js/pages/home/member/registerMember.js** → Update form handling
2. **src/js/pages/home/member/viewMember.js** → Update display

### SQL Scripts (2 Files)

1. **config/database/insert_sample_users.sql** → Verify field names
2. **config/database/insert_sample_members.sql** → Create if missing

---

## Field Mapping Reference

### UserDTO → Database

```
DTO Field            →  Database Field        Type      Example
======================================================================
id                   →  users.id              int       1
name                 →  users.name            string    "John Doe"
email                →  users.email           string    "john@example.com"
emailVerifiedAt      →  users.email_verified_at  timestamp  "2025-09-29 14:49:27"
firebaseUid          →  users.firebase_uid    string    "abc123xyz"
idSchoolNumber       →  users.id_school_number  int     20210001
role                 →  users.role            enum      "user"|"admin"|"guest"
rememberToken        →  users.remember_token  string    "token123"
createdAt            →  users.created_at      timestamp  "2025-09-29 14:49:27"
updatedAt            →  users.updated_at      timestamp  "2025-09-29 14:49:27"
```

### MemberDTO → Database

```
DTO Field            →  Database Field        Type      Example
======================================================================
id                   →  members.id            int       1
firstName            →  members.first_name    string    "John"
lastName             →  members.last_name     string    "Doe"
suffix               →  members.suffix        string    "Jr." (nullable)
idSchoolNumber       →  members.id_school_number  int   20210001
email                →  members.email         string    "john@example.com"
birthDate            →  members.birth_date    date      "2002-05-15"
enrollmentDate       →  members.enrollment_date  date   "2025-09-29"
program              →  members.program       string    "BSCS"
year                 →  members.year          tinyint   1,2,3,4
isPaid               →  members.is_paid       tinyint   0,1
createdAt            →  members.created_at    timestamp  "2025-09-29 14:49:28"
updatedAt            →  members.updated_at    timestamp  "2025-09-29 14:49:28"
```

---

## JavaScript Transformation Examples

### Before: Manual Transformation

```javascript
const memberData = {
    first_name: formData.firstName,      // Manual transform
    last_name: formData.lastName,        // Manual transform
    birth_date: formData.birthDate,      // Manual transform
    year: formData.yearLevel,            // Manual transform + rename
    is_paid: formData.isPaid ? 1 : 0,    // Manual transform + type
};
```

### After: Direct Mapping

```javascript
const memberData = {
    first_name: formData.firstName,      // Direct (already camelCase)
    last_name: formData.lastName,        // Direct
    birth_date: formData.birthDate,      // Direct
    year: formData.year,                 // Direct (renamed in DTO)
    is_paid: formData.isPaid,            // Direct (PHP handles type)
};
```

---

## API Response Format Changes

### getUserByIdNumber.php

**Before:**
```json
{
    "id": 1,
    "firstName": "John",
    "lastName": "Doe",
    "idNumber": "20210001",
    "email": "john@example.com"
}
```

**After:**
```json
{
    "id": 1,
    "name": "John Doe",
    "idSchoolNumber": 20210001,
    "email": "john@example.com",
    "firebaseUid": "abc123xyz",
    "emailVerifiedAt": null,
    "role": "user"
}
```

### getMembers.php

**Before:**
```json
[{
    "id": 1,
    "firstName": "John",
    "lastName": "Doe",
    "yearLevel": 1,
    "isPaid": false
}]
```

**After:**
```json
[{
    "id": 1,
    "firstName": "John",
    "lastName": "Doe",
    "idSchoolNumber": 20210001,
    "birthDate": "2002-05-15",
    "program": "BSCS",
    "year": 1,
    "isPaid": false
}]
```

---

## Breaking Changes Summary

### What Will Break

1. ✅ Code expecting `firstName` + `lastName` in UserDTO → Now single `name`
2. ✅ Code expecting `idNumber` → Now `idSchoolNumber`
3. ✅ Code expecting `yearLevel` in MemberDTO → Now `year`
4. ✅ Code checking `userId` in MemberDTO → Field removed
5. ✅ Code checking `isActive` in UserDTO → Field removed
6. ✅ API responses with old field names → New format

### What Won't Break

- ✓ Database queries (still same)
- ✓ User/member creation (if updated)
- ✓ View display (if templates updated)

---

## Verification Checklist

After implementing all changes:

- [ ] UserDTO matches `ccsync_api.users` schema
- [ ] MemberDTO matches `ccsync_api.members` schema
- [ ] All PHP endpoints use correct field names
- [ ] JavaScript files handle new field names
- [ ] Sample SQL scripts use correct field names
- [ ] Member registration works end-to-end
- [ ] Member viewing works correctly
- [ ] Member updates work properly
- [ ] All tests pass
- [ ] No console errors in browser

---

## Rollback Commands

If you need to undo changes:

```bash
# Restore DTOs from backup
cp src/DTOs/UserDTO_BACKUP.php src/DTOs/UserDTO.php
cp src/DTOs/MemberDTO_BACKUP.php src/DTOs/MemberDTO.php

# Revert PHP changes
git checkout ccsync-api-plain/member/createMember.php
git checkout ccsync-api-plain/member/updateMember.php
git checkout ccsync-api-plain/auth/getUserByIdNumber.php

# Revert JavaScript changes
git checkout src/js/pages/home/member/registerMember.js
git checkout src/js/pages/home/member/viewMember.js
```

---

## Implementation Checklist

### Phase 1: DTOs (Priority: HIGH)
- [ ] Create backup of existing DTOs
- [ ] Review UserDTO_RESTRUCTURED.php
- [ ] Review MemberDTO_RESTRUCTURED.php
- [ ] Replace UserDTO.php
- [ ] Replace MemberDTO.php
- [ ] Update other related DTOs

### Phase 2: PHP Endpoints (Priority: HIGH)
- [ ] Update auth/getUserByIdNumber.php
- [ ] Update member/createMember.php
- [ ] Update member/updateMember.php
- [ ] Update member/getMembers.php
- [ ] Update other relevant endpoints

### Phase 3: JavaScript (Priority: HIGH)
- [ ] Update registerMember.js
- [ ] Update viewMember.js
- [ ] Test member registration flow
- [ ] Test member viewing flow

### Phase 4: SQL (Priority: MEDIUM)
- [ ] Verify insert_sample_users.sql
- [ ] Create insert_sample_members.sql
- [ ] Test sample data insertion

### Phase 5: Testing (Priority: HIGH)
- [ ] Unit tests
- [ ] Integration tests
- [ ] Manual testing
- [ ] Browser console check (no errors)

### Phase 6: Documentation (Priority: MEDIUM)
- [ ] Update API docs
- [ ] Update DTO documentation
- [ ] Update developer guide
- [ ] Add migration notes

---

## Quick Stats

| Metric | Count |
|--------|-------|
| DTO Changes | 2 main (5 related) |
| API Endpoints to Update | 5+ |
| JavaScript Files to Update | 2+ |
| Breaking Changes | 5 |
| Files to Modify | 10-15 |
| Estimated Time | 9-13 hours |
| Risk Level | Low (well-defined scope) |

