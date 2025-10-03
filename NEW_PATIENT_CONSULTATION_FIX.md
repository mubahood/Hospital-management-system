# New Patient Creation with Consultation - Fix Documentation

## Problem Statement

When creating a consultation with a new patient (where `is_new_patient` is `true`), the system was failing to properly:
1. Create the patient record first
2. Link the newly created patient to the consultation
3. Properly set the `enterprise_id` for multi-tenant isolation

The issue only occurred when creating consultations with NEW patients. Existing patients worked fine.

## Root Cause Analysis

The problem was in the execution flow and logging:

1. **Boolean Conversion Issue**: Frontend sends `is_new_patient` as string `"true"` or `"false"`, but the model was checking for boolean `true`
2. **Missing Logging**: No visibility into where the process was failing
3. **Enterprise ID Setting**: While the code was setting `enterprise_id`, the order of operations and error handling was not clear
4. **Validation Timing**: Patient creation validation happened too late in the process

## Solution Implemented

### 1. Enhanced ApiResurceController (`app/Http/Controllers/ApiResurceController.php`)

**Added:**
- Comprehensive logging for consultation creation with new patients
- Improved boolean conversion to handle strings, integers, and actual booleans
- Detailed validation logging
- Enterprise ID verification logging

**Code Changes:**
```php
// Convert string boolean to actual boolean
if ($validData['is_new_patient'] === 'true' || $validData['is_new_patient'] === '1' || $validData['is_new_patient'] === 1) {
    $validData['is_new_patient'] = true;
    \Log::info('✅ Converted is_new_patient to TRUE');
} elseif ($validData['is_new_patient'] === 'false' || $validData['is_new_patient'] === '0' || $validData['is_new_patient'] === 0) {
    $validData['is_new_patient'] = false;
    \Log::info('✅ Converted is_new_patient to FALSE');
}
```

### 2. Enhanced Consultation Model (`app/Models/Consultation.php`)

**Added:**
- Detailed logging throughout the patient creation process
- Proper `enterprise_id` validation before patient creation
- Better error messages with context
- Trimming of input data to prevent whitespace issues
- Explicit enterprise_id setting BEFORE patient creation

**Key Changes:**

**In `boot()` method:**
```php
// Set enterprise_id FIRST before any operations
$model->enterprise_id = $loggedInUser->enterprise_id;

// Handle new patient creation BEFORE do_prepare if is_new_patient is true
if ($model->is_new_patient === true || $model->is_new_patient === 1 || $model->is_new_patient === '1') {
    \Log::info('🟢 New patient flag detected - Creating new patient first');
    $model = Consultation::handle_new_patient_creation($model);
    \Log::info('✅ Patient created successfully', ['patient_id' => $model->patient_id]);
}
```

**In `handle_new_patient_creation()` method:**
```php
// Validate enterprise_id is present
if (empty($enterpriseId)) {
    \Log::error('❌ enterprise_id is missing from logged user', ['user_id' => $loggedUser->id]);
    throw new \Exception('Enterprise context is required to create new patient.');
}

// CRITICAL: Set enterprise_id explicitly BEFORE save
$newPatient->enterprise_id = $enterpriseId;
```

### 3. User Model (`app/Models/User.php`)

**No changes needed** - The existing code already properly sets `enterprise_id` from authenticated user in the `boot()` method:

```php
// Auto-set enterprise_id from authenticated user if not already set
$loggedUser = \Illuminate\Support\Facades\Auth::user();
if ($loggedUser && isset($loggedUser->enterprise_id) && empty($m->enterprise_id)) {
    $m->enterprise_id = $loggedUser->enterprise_id;
}
```

## How It Works Now

### Flow for Creating Consultation with New Patient:

1. **Frontend submits data** with:
   - `is_new_patient: "true"` (string)
   - New patient fields: `new_patient_first_name`, `new_patient_last_name`, `new_patient_phone`, etc.
   - Consultation fields: `chief_complaint`, `consultation_type`, etc.

2. **ApiResurceController receives request**:
   - Logs the raw request data
   - Converts `is_new_patient` string to boolean
   - Validates new patient required fields
   - Logs validation results

3. **Consultation Model `creating` event fires**:
   - Sets `enterprise_id` from logged-in user IMMEDIATELY
   - Detects `is_new_patient === true`
   - Calls `handle_new_patient_creation()` BEFORE `do_prepare()`

4. **Patient Creation Process**:
   - Validates all required new patient fields
   - Checks for duplicate email/phone
   - Validates `enterprise_id` is present
   - Creates new User with:
     - `user_type = 'Patient'`
     - `enterprise_id` from logged-in user
     - `company_id` from logged-in user
     - Proper username and password
   - Saves the patient
   - Updates consultation with `patient_id`
   - Clears temporary new patient fields

5. **Consultation Preparation**:
   - `do_prepare()` now has valid `patient_id`
   - Loads patient details
   - Generates consultation number
   - Sets additional fields

6. **Save Completes**:
   - Consultation saved with proper `patient_id` and `enterprise_id`
   - Returns success response with patient and consultation data

## Testing

### Manual Test via Frontend:

1. Log in to the system
2. Go to **Admin → Consultations → Create**
3. Select **"Is this a new patient?" → Yes**
4. Fill in new patient information:
   - First Name: John
   - Last Name: Doe
   - Email: john.doe@example.com
   - Phone: +256700000001
   - Other optional fields
5. Fill in consultation details:
   - Consultation Type: General Consultation
   - Chief Complaint: Test complaint
   - Other fields as needed
6. Click **"Create Consultation"**
7. **Expected Result**: 
   - Success message displayed
   - Patient created in Users table with `user_type = 'Patient'`
   - Consultation created with `patient_id` linked to new patient
   - Both records have correct `enterprise_id`

### Automated Test via Script:

```bash
# Make script executable
chmod +x test-new-patient-consultation.sh

# Run test (update credentials in script first)
./test-new-patient-consultation.sh
```

### Verify in Database:

```sql
-- Check the newly created patient
SELECT id, first_name, last_name, email, phone_number_1, user_type, enterprise_id
FROM users
WHERE email = 'john.doe@example.com';

-- Check the consultation
SELECT id, consultation_number, patient_id, patient_name, enterprise_id
FROM consultations
ORDER BY created_at DESC
LIMIT 1;

-- Verify they're linked correctly
SELECT 
    c.id as consultation_id,
    c.consultation_number,
    c.patient_id,
    c.enterprise_id as consultation_enterprise_id,
    u.id as user_id,
    u.name as patient_name,
    u.user_type,
    u.enterprise_id as patient_enterprise_id
FROM consultations c
JOIN users u ON c.patient_id = u.id
WHERE u.email = 'john.doe@example.com';
```

## Logging Output

When creating a consultation with a new patient, you should see logs like:

```
[INFO] 🔵 ApiResurceController - Processing Consultation with new patient flag
[INFO] ✅ Converted is_new_patient to TRUE
[INFO] 🟢 Validating new patient fields
[INFO] ✅ New patient validation passed
[INFO] 🔵 Consultation::creating - Starting consultation creation
[INFO] 🟢 New patient flag detected - Creating new patient first
[INFO] 🔵 handle_new_patient_creation - Starting new patient creation
[INFO] 🔵 Creating patient with context
[INFO] 🔵 Attempting to save new patient
[INFO] ✅ New patient saved successfully
[INFO] ✅ Consultation model updated with new patient_id
[INFO] ✅ Patient created successfully
[INFO] ✅ Consultation prepared successfully
```

To view logs in real-time:
```bash
tail -f storage/logs/laravel.log | grep -E "🔵|✅|❌|🟢|⚠️"
```

## Error Scenarios Handled

### 1. Missing Required Fields
**Error**: "Missing required fields for new patient: new_patient_first_name, new_patient_last_name"
**Solution**: Frontend validation ensures all required fields are filled

### 2. Duplicate Email
**Error**: "A patient with this email already exists. Please use existing patient option."
**Solution**: User should select existing patient instead

### 3. Duplicate Phone
**Error**: "A patient with this phone number already exists. Please use existing patient option."
**Solution**: User should select existing patient instead

### 4. Missing Enterprise Context
**Error**: "Enterprise context is required to create new patient."
**Solution**: User must be logged in with valid enterprise_id

### 5. Authentication Failed
**Error**: "User not authenticated. Cannot create consultation."
**Solution**: User must log in again

## Database Schema

### Required Fields in `users` table:
- `first_name` (string)
- `last_name` (string)
- `email` (string, nullable but validated if provided)
- `phone_number_1` (string, nullable but validated if provided)
- `user_type` (enum: 'Patient', 'Doctor', 'Admin', etc.)
- `enterprise_id` (integer, for multi-tenant isolation)
- `company_id` (integer)
- `username` (string, unique)
- `password` (string, hashed)

### Required Fields in `consultations` table:
- `patient_id` (integer, foreign key to users)
- `enterprise_id` (integer, for multi-tenant isolation)
- `company_id` (integer)
- `consultation_number` (string, auto-generated)
- `consultation_type` (string)
- `consultation_status` (enum)

## Frontend Changes Required

**None** - The frontend already sends the correct data format. The fix was entirely on the backend.

## API Request Format

### Create Consultation with New Patient:

```json
POST /api/api/Consultation
Authorization: Bearer {token}
Content-Type: application/json

{
  "is_new_patient": "true",
  "new_patient_first_name": "John",
  "new_patient_last_name": "Doe",
  "new_patient_email": "john.doe@example.com",
  "new_patient_phone": "+256700000001",
  "new_patient_address": "123 Test Street, Kampala",
  "new_patient_date_of_birth": "1990-01-01",
  "new_patient_gender": "Male",
  "consultation_type": "General Consultation",
  "chief_complaint": "Test complaint",
  "consultation_status": "Active",
  "priority_level": "Normal",
  "consultation_date": "2025-10-03",
  "medical_services": []
}
```

### Expected Response:

```json
{
  "code": 1,
  "message": "Record created successfully.",
  "data": {
    "id": 123,
    "consultation_number": "2025-10-03-1",
    "patient_id": 456,
    "patient_name": "John Doe",
    "patient_contact": "+256700000001",
    "enterprise_id": 1,
    "company_id": 1,
    "consultation_type": "General Consultation",
    "chief_complaint": "Test complaint",
    "consultation_status": "Active",
    "created_at": "2025-10-03T10:30:00.000000Z",
    "updated_at": "2025-10-03T10:30:00.000000Z"
  }
}
```

## Security Considerations

1. **Enterprise Isolation**: Every patient created gets the `enterprise_id` from the logged-in user, ensuring multi-tenant data isolation
2. **Default Password**: New patients get a default password (`patient123`) which should be changed on first login
3. **Card Status**: New patients get `card_status = 'Inactive'` for security until verified
4. **Validation**: Duplicate email/phone checks prevent data conflicts
5. **Authentication**: All operations require valid JWT token

## Maintenance

### If Issues Persist:

1. **Check Logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Verify Database**:
   - Check if `enterprise_id` column exists in both `users` and `consultations` tables
   - Verify logged-in user has valid `enterprise_id`

3. **Test Authentication**:
   ```bash
   curl -X GET "http://localhost:8888/api/users/me" \
     -H "Authorization: Bearer {token}"
   ```

4. **Check Model Events**:
   - Ensure `User::boot()` and `Consultation::boot()` are not being overridden
   - Verify no middleware is modifying the request data

## Summary

The fix ensures that when creating a consultation with `is_new_patient: true`:

✅ Patient is created FIRST with proper `enterprise_id`
✅ Patient record is validated and saved successfully
✅ Consultation is then linked to the newly created patient
✅ Comprehensive logging tracks every step
✅ Clear error messages guide users to fix issues
✅ Multi-tenant isolation is maintained
✅ Existing patient consultations continue to work as before

No frontend changes required - the backend now properly handles the workflow!
