## Quick Fix Applied: Patient ID Validation for New Patients

### Problem
When creating a consultation with `is_new_patient: true`, the system was throwing error:
```
"Patient not found. Please select a valid patient."
```

This happened because the `do_prepare()` method was checking for patient_id even when the patient hadn't been created yet.

### Solution
Modified `app/Models/Consultation.php` in the `do_prepare()` method:

**OLD LOGIC:**
```php
if (!$model->is_new_patient) {
    // Validate patient_id exists
    $patient = User::find($model->patient_id);
    if ($patient == null) {
        throw new \Exception('Patient not found...');
    }
}
```

**NEW LOGIC:**
```php
if ($model->patient_id) {
    // We have patient_id - validate it (works for both new and existing)
    $patient = User::find($model->patient_id);
    if ($patient == null) {
        throw new \Exception('Patient not found...');
    }
} elseif (!$model->is_new_patient) {
    // No patient_id but existing patient selected - ERROR
    throw new \Exception('Patient selection is required...');
}
// If is_new_patient=true and no patient_id yet - OK, will be created
```

### How It Works Now

**Scenario 1: New Patient**
1. Frontend sends: `is_new_patient: "true"`, `patient_id: null`, new patient fields filled
2. `do_prepare()` sees no `patient_id` but `is_new_patient = true` → **No error, continues**
3. `handle_new_patient_creation()` creates patient → sets `patient_id`
4. `do_prepare()` called again, now has `patient_id` → Loads patient data
5. ✅ Success!

**Scenario 2: Existing Patient**
1. Frontend sends: `is_new_patient: "false"`, `patient_id: 123`
2. `do_prepare()` sees `patient_id = 123` → Validates and loads patient
3. ✅ Success!

**Scenario 3: Existing Patient but No Selection**
1. Frontend sends: `is_new_patient: "false"`, `patient_id: null`
2. `do_prepare()` sees no `patient_id` and `is_new_patient = false`
3. ❌ Error: "Patient selection is required"

### Testing

**Test in Frontend:**
1. Go to **Admin → Consultations → Create**
2. Select **"Is this a new patient?" → Yes**
3. Fill in new patient information
4. Fill in consultation details
5. Click **"Create Consultation"**
6. ✅ **Should work now!**

**Check Logs:**
```bash
tail -f storage/logs/laravel.log | grep -E "🔵|✅|❌"
```

You should see:
```
[INFO] 🔵 Consultation::creating - Starting consultation creation
[INFO] 🟢 New patient flag detected - Creating new patient first
[INFO] ✅ Patient created successfully
[INFO] 🔵 Loading patient data for patient_id: 123
[INFO] ✅ Patient data loaded successfully
[INFO] ✅ Consultation prepared successfully
```

### Files Changed
- `app/Models/Consultation.php` - Fixed `do_prepare()` method logic

The fix is minimal and surgical - only changes the validation logic to properly handle the case when `is_new_patient = true` and `patient_id` is not yet set.
