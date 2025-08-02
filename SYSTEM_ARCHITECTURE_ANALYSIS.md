# Hospital Management System - Comprehensive Architecture Analysis
*Generated: August 2, 2025*

## 🏥 SYSTEM OVERVIEW

This is a **Laravel 8 Hospital Management System** with **SaaS Multi-tenancy Architecture** designed to handle the complete patient journey from consultation initiation to payment completion.

## 🔄 CORE PATIENT JOURNEY WORKFLOW

### 1. **Consultation Initialization** (Patient Record Creation)
```
Patient Registration → Consultation Creation → Enterprise Assignment
```
- **Model**: `Consultation` (acts as Patient Record)
- **Key Fields**: `patient_id`, `consultation_number`, `main_status`, `enterprise_id`
- **Statuses**: `Pending`, `Active`, `Completed`, `Cancelled`
- **Auto-Generation**: Consultation number format: `YYYY-MM-DD-COUNT`

### 2. **Medical Services Assignment** (Treatment Records)
```
Consultation → Medical Services Assignment → Specialist Assignment
```
- **Model**: `MedicalService` (acts as Treatment Record)
- **Service Types**: Laboratory, Radiology, Pharmacy, Physiotherapy, Surgery, Specialist, etc.
- **Key Fields**: `consultation_id`, `type`, `assigned_to_id`, `status`, `total_price`
- **Statuses**: `pending`, `in_progress`, `completed`, `cancelled`

### 3. **Medical Services Execution** (Treatment Delivery)
```
Service Assignment → Service Execution → Outcome Documentation
```
- **Execution**: Specialists update service status and outcomes
- **Documentation**: `specialist_outcome`, `instruction`, `remarks`
- **File Attachments**: Medical reports, images, documents
- **Related Models**: `MedicalServiceItem` for detailed service components

### 4. **Dosage Management** (Medication Prescription)
```
Consultation → Dose Items → Dose Item Records (Patient Tracking)
```
- **Model**: `DoseItem` - Prescription details
- **Fields**: `medicine`, `quantity`, `times_per_day`, `number_of_days`
- **Model**: `DoseItemRecord` - Patient medication tracking
- **Progress Tracking**: `dosage_progress`, `dosage_is_completed`

### 5. **Billing Generation** (Financial Management)
```
Medical Services → Billing Items → Invoice Processing
```
- **Model**: `BillingItem` - Individual charges
- **Types**: Consultation, Laboratory, Radiology, Pharmacy, Surgery, etc.
- **Calculation**: Automatic total calculation from medical services
- **Invoice Processing**: `invoice_processed`, `invoice_pdf`, `invoice_process_date`

### 6. **Payment Processing** (Financial Settlement)
```
Billing → Payment Records → Balance Management
```
- **Model**: `PaymentRecord` - Payment transactions
- **Methods**: Cash, Card, Mobile Money, Flutterwave
- **Card Integration**: `CardRecord` for card-based payments
- **Balance Tracking**: `amount_payable`, `amount_paid`, `balance`

### 7. **Medical Report Generation** (Documentation)
```
Completed Services → Medical Reports → Patient Records
```
- **Report Generation**: Automated PDF reports
- **Storage**: `report_link` field in consultations
- **Templates**: Laravel DomPDF integration
- **Comprehensive Data**: Consultation, services, medications, payments

## 🏗️ DATABASE ARCHITECTURE

### Core Tables Structure:
1. **`consultations`** - Main patient visitation records
2. **`medical_services`** - Individual treatments/services
3. **`medical_service_items`** - Detailed service components
4. **`billing_items`** - Financial charges
5. **`payment_records`** - Payment transactions
6. **`dose_items`** - Medication prescriptions
7. **`dose_item_records`** - Medication tracking
8. **`card_records`** - Card payment history
9. **`users`** - Patients, doctors, staff, administrators
10. **`companies`** - Hospital departments/sections
11. **`enterprises`** - Multi-tenant organizations

### Multi-Tenancy Implementation:
- **Enterprise ID**: All tables have `enterprise_id` column
- **Global Scoping**: `EnterpriseScopeTrait` automatically filters by enterprise
- **Automatic Assignment**: New records get `enterprise_id` from authenticated user
- **Scope Bypass**: Admin operations use `withoutGlobalScope('enterprise')`

## 🔧 MODEL ARCHITECTURE

### Standardized Model Features:
1. **EnterpriseScopeTrait** - Multi-tenant query scoping
2. **StandardBootTrait** - Unified model event handling
3. **Comprehensive Fillable Arrays** - Mass assignment protection
4. **Query Scopes** - Filtered data access (active, completed, pending, etc.)
5. **Relationship Methods** - CamelCase naming convention
6. **Accessor Methods** - Formatted data presentation
7. **Helper Methods** - Business logic encapsulation

### Key Model Relationships:
```php
Consultation hasMany MedicalServices
Consultation hasMany BillingItems  
Consultation hasMany PaymentRecords
Consultation hasMany DoseItems
MedicalService belongsTo Consultation
MedicalService hasMany MedicalServiceItems
DoseItem hasMany DoseItemRecords
PaymentRecord belongsTo Consultation
```

## 📊 SYSTEM STATISTICS (Current Data):
- **Total Consultations**: 16 patient records
- **Total Medical Services**: 31 treatment records
- **Total Billing Items**: 19 financial charges
- **Total Payment Records**: 19 transactions
- **Total Users**: 43 (patients, doctors, staff)
- **Total Companies**: 6 departments/sections

## 🔐 SECURITY & PERMISSIONS

### Authentication & Authorization:
- **Laravel Admin**: Backend administration interface
- **JWT Authentication**: Mobile API access
- **Enterprise Validation**: User access restricted to their enterprise
- **Role-Based Access**: Admin roles with specific permissions
- **API Middleware**: Token validation and enterprise scoping

## 🚀 API ARCHITECTURE

### Mobile API Endpoints:
1. **Authentication**: Login, register, profile management
2. **Consultations**: List, details, creation
3. **Medical Services**: Assignment, tracking, completion
4. **Dashboard**: Statistics, recent activities
5. **Push Notifications**: Device token management, notifications
6. **Offline Sync**: Data synchronization, conflict resolution
7. **Location Services**: GPS tracking, geofencing, facility discovery

### Admin API:
- **Resource Controllers**: CRUD operations for all models
- **Bulk Operations**: Mass updates and deletions
- **Report Generation**: PDF exports, analytics
- **Data Import/Export**: CSV handling

## 🎯 BUSINESS LOGIC WORKFLOWS

### Consultation Workflow States:
```
Pending → Active → Completed
                ↓ 
            Cancelled
```

### Medical Service Workflow:
```
Pending → In Progress → Completed
                     ↓
                 Cancelled  
```

### Payment Workflow:
```
Billing Generated → Payment Initiated → Payment Completed
                                     ↓
                                Payment Failed
```

### Billing Calculation Logic:
1. Medical services generate billing items
2. Consultation fee added automatically  
3. Discounts applied as negative billing items
4. Total calculated: `consultation_fee + medical_services - discounts`
5. Balance tracked: `total_charges - total_paid = total_due`

## 🔄 ENTERPRISE SCOPING DETAILS

### Automatic Enterprise Assignment:
```php
// When creating new records, enterprise_id is automatically set
$consultation = new Consultation($data); // Gets enterprise_id from Auth::user()
$medicalService = new MedicalService($data); // Same automatic assignment
```

### Query Filtering:
```php
// All queries automatically filtered by enterprise
Consultation::all(); // Only returns current enterprise's consultations
MedicalService::where('status', 'pending')->get(); // Scoped to enterprise
```

### Administrative Override:
```php
// Admin operations can bypass enterprise scoping
Consultation::withoutGlobalScope('enterprise')->get(); // All enterprises
User::withoutGlobalScope('enterprise')->find($id); // Cross-enterprise access
```

## 📱 MOBILE APPLICATION SUPPORT

### Comprehensive Mobile API Features:
- **Patient Portal**: View consultations, medical services, payments
- **Doctor Interface**: Manage assigned services, update outcomes
- **Push Notifications**: Real-time updates and reminders  
- **Offline Capability**: Sync data when connection restored
- **Location Services**: Emergency sharing, facility discovery
- **Secure Authentication**: JWT tokens with enterprise validation

## 🎨 LARAVEL ADMIN INTERFACE

### Administrative Features:
- **Dashboard**: Real-time statistics and analytics
- **CRUD Operations**: Full management of all entities
- **Advanced Filtering**: Search and filter capabilities
- **Bulk Operations**: Mass updates and deletions
- **Export/Import**: Data exchange capabilities
- **Custom Forms**: Complex form handling with validation
- **File Management**: Document and image uploads

## 🔍 KEY INSIGHTS

### System Design Philosophy:
1. **Consultation-Centric**: Everything revolves around patient consultations
2. **Service-Based**: Medical services are independent, trackable units
3. **Financial Transparency**: Complete billing and payment audit trail
4. **Multi-Tenant Ready**: Enterprise isolation with admin override capability
5. **Mobile-First API**: Comprehensive mobile application support
6. **Standardized Architecture**: Consistent patterns across all models

### Nomenclature Clarification:
- **"Patient Records"** = **Consultations** (complete patient journey)
- **"Treatment Records"** = **Medical Services** (individual treatments)
- **Legacy Models**: `PatientRecord` and `TreatmentRecord` models exist but are unused
- **Active Architecture**: System uses Consultation → MedicalService pattern

This architecture provides a robust, scalable foundation for hospital management with complete patient journey tracking, financial management, and multi-tenant SaaS capabilities.
