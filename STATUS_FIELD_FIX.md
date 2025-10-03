# User Status Field Type Fix

## Problem

When creating a new patient during consultation creation, the system was throwing a SQL error:

```
SQLSTATE[HY000]: General error: 1366 Incorrect integer value: 'Active' 
for column 'status' at row 1
```

## Root Cause

The `status` field in the `admin_users` (users) table is defined as an **integer** column that accepts:
- `1` = Active
- `0` = Inactive

However, the `handle_new_patient_creation()` method was setting:
```php
$newPatient->status = 'Active'; // ❌ WRONG - String instead of integer
```

## Solution

Changed the status assignment to use the correct integer value:

```php
$newPatient->status = 1; // ✅ CORRECT - 1 = Active, 0 = Inactive
```

## File Modified

**File:** `/Applications/MAMP/htdocs/hospital/app/Models/Consultation.php`

**Method:** `handle_new_patient_creation()`

**Line:** ~250

**Change:**
```php
// BEFORE
$newPatient->status = 'Active';

// AFTER  
$newPatient->status = 1; // 1 = Active, 0 = Inactive (integer field)
```

## Database Schema Reference

The `admin_users` table has:
- `status` - **INTEGER/TINYINT** - User account status (1=Active, 0=Inactive)
- `card_status` - **VARCHAR** - Card activation status ("Active", "Inactive", "Suspended")
- `user_type` - **VARCHAR** - User role ("Patient", "Doctor", "Admin", etc.)

## Testing

After this fix, creating a consultation with a new patient should work correctly:

1. Navigate to **Admin → Consultations → Create**
2. Select **"Is this a new patient?" → Yes**
3. Fill in new patient details
4. Submit the form

**Expected Result:** ✅ Patient created with `status = 1` (Active)

## Related Fields

Other status fields in the User model that use **STRING** values (not affected):
- `card_status` - "Active", "Inactive", "Suspended"
- `dependent_status` - "Active", "Inactive"  
- `belongs_to_company_status` - "Active", "Inactive"

Only the main `status` field uses integer values.

---

**Fixed:** October 3, 2025  
**Impact:** New patient creation during consultation now works correctly  
**Status:** ✅ RESOLVED
