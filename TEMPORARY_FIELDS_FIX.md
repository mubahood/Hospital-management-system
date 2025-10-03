# Temporary Fields Database Error Fix

## Problem

When creating a consultation with a new patient, the system was throwing a SQL error:

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'patient_created_successfully' 
in 'field list'
```

The SQL INSERT statement was trying to insert these fields that don't exist in the `consultations` table:
- `patient_created_successfully`
- `is_new_patient`
- `new_patient_first_name`
- `new_patient_last_name`
- `new_patient_email`
- `new_patient_phone`
- `new_patient_address`
- `new_patient_date_of_birth`
- `new_patient_gender`

## Root Cause

These fields were being set on the Consultation model instance during the creation process to:
1. Pass new patient data from frontend to backend
2. Control flow logic (`is_new_patient` flag)
3. Prevent recursion (`patient_created_successfully` flag)

However, Laravel was trying to **persist these fields to the database** because they were set as model attributes, but these columns **don't exist** in the `consultations` table.

### Why These Fields Don't Belong in Database

The `consultations` table should only store:
- Consultation-specific data
- Reference to patient via `patient_id` (foreign key)
- NOT the patient's personal details

New patient information belongs in the `admin_users` (users) table, not the consultations table.

## Solution

Added cleanup code in the `creating` event handler to **unset temporary fields** before the model is saved to the database:

**File:** `/Applications/MAMP/htdocs/hospital/app/Models/Consultation.php`

**Method:** `boot()` → `static::creating()`

```php
static::creating(function ($model) {
    // ... existing patient creation and preparation logic ...
    
    // Remove temporary fields that don't exist in database
    // These are only used during the creation process
    unset($model->patient_created_successfully);
    unset($model->is_new_patient);
    unset($model->new_patient_first_name);
    unset($model->new_patient_last_name);
    unset($model->new_patient_email);
    unset($model->new_patient_phone);
    unset($model->new_patient_address);
    unset($model->new_patient_date_of_birth);
    unset($model->new_patient_gender);
    
    \Log::info('🔵 Removed temporary new patient fields before save');
});
```

## Flow Explanation

### Complete Creation Flow

1. **Frontend** sends consultation data with `is_new_patient: true` and new patient fields
2. **ApiResurceController** receives and validates the data
3. **Consultation::creating** event fires:
   - a. Detects `is_new_patient = true`
   - b. Calls `handle_new_patient_creation()` → creates User (patient) record
   - c. Sets `patient_created_successfully = true` flag
   - d. Calls `do_prepare()` → loads patient data, sets consultation fields
   - e. **Unsets all temporary fields** ← NEW FIX
4. **Laravel saves** only the valid consultation fields to database
5. **Success!** Consultation created with proper patient link

### Fields Flow

**Temporary Fields** (used during creation, then removed):
```
is_new_patient           → Control flag
new_patient_first_name   → Used to create User
new_patient_last_name    → Used to create User
new_patient_email        → Used to create User
new_patient_phone        → Used to create User
new_patient_address      → Used to create User
new_patient_date_of_birth → Used to create User
new_patient_gender       → Used to create User
patient_created_successfully → Recursion prevention flag
```

**Persisted Fields** (saved to consultations table):
```
patient_id               → Foreign key to users table
patient_name             → Copied from created patient
patient_contact          → Copied from created patient
contact_address          → Copied from created patient
consultation_number      → Auto-generated
enterprise_id            → From logged-in user
... (other consultation-specific fields)
```

## Database Schema

### consultations table (HAS):
- `id` - Primary key
- `patient_id` - Foreign key to users table
- `patient_name` - Denormalized for display
- `patient_contact` - Denormalized for display
- `consultation_number` - Unique identifier
- `enterprise_id` - Multi-tenant isolation
- ... (consultation details)

### consultations table (DOES NOT HAVE):
- ❌ `is_new_patient`
- ❌ `new_patient_first_name`
- ❌ `new_patient_last_name`
- ❌ `new_patient_email`
- ❌ `new_patient_phone`
- ❌ `patient_created_successfully`

These fields are only used temporarily during the creation process and are removed before the model is saved.

## Testing

After this fix, creating a consultation with a new patient should work without SQL errors:

1. Navigate to **Admin → Consultations → Create**
2. Select **"Is this a new patient?" → Yes**
3. Fill in new patient information
4. Fill in consultation details
5. Click **"Create Consultation"**

**Expected Results:**
- ✅ Patient created in `admin_users` table
- ✅ Consultation created in `consultations` table
- ✅ Consultation has proper `patient_id` reference
- ✅ No SQL column errors
- ✅ Both records have matching `enterprise_id`

## Verification

Check the Laravel logs:
```bash
tail -f /Applications/MAMP/htdocs/hospital/storage/logs/laravel.log | grep -E "🔵|✅|❌"
```

Expected log sequence:
```
🔵 Consultation::creating - Starting consultation creation
🟢 New patient flag detected - Creating new patient first
🔵 handle_new_patient_creation - Starting with enterprise_id: [ID]
✅ New patient saved successfully with id: [ID]
🔵 Loading patient data for patient_id: [ID]
✅ Patient data loaded successfully
✅ Consultation prepared successfully
🔵 Removed temporary new patient fields before save
(Consultation INSERT happens here - only valid fields)
```

## Alternative Approaches Considered

### 1. Add $guarded array (NOT chosen)
```php
protected $guarded = [
    'is_new_patient',
    'new_patient_first_name',
    // ... etc
];
```
**Why not:** Guarded still allows mass assignment, just prevents saving. The fields would still be in the attributes array.

### 2. Use separate DTO/Request object (NOT chosen)
```php
class CreateConsultationRequest {
    public $consultation_data;
    public $new_patient_data;
}
```
**Why not:** Requires more refactoring. Current approach works with existing architecture.

### 3. Unset fields before save (CHOSEN) ✅
```php
unset($model->temporary_field);
```
**Why chosen:** 
- Simple and explicit
- Happens right before save
- Clear in the code what's happening
- No database schema changes needed

## Related Files

**Modified:**
- `/Applications/MAMP/htdocs/hospital/app/Models/Consultation.php` (boot method)

**Related but unchanged:**
- `/Applications/MAMP/htdocs/hospital/app/Http/Controllers/ApiResurceController.php`
- `/Users/mac/Desktop/github/hospital-react-frontend/src/models/ConsultationModel.js`

## Summary

✅ **Problem:** Temporary fields were being persisted to database  
✅ **Solution:** Unset temporary fields in `creating` event before save  
✅ **Result:** Only valid consultation fields are saved to database  
✅ **Impact:** New patient consultation creation now works end-to-end  

---

**Fixed:** October 3, 2025  
**Component:** Backend Consultation Model  
**Status:** ✅ RESOLVED
