# Hospital Management System API Documentation

## Overview

The Hospital Management System API provides a comprehensive RESTful interface for managing hospital operations including patient management, consultations, medical services, and billing. The API is designed with enterprise multi-tenancy, security, and performance in mind.

## Base URL

```
https://your-hospital-domain.com/api/v1
```

## Authentication

The API uses JWT (JSON Web Token) authentication. Include the token in the Authorization header:

```
Authorization: Bearer {your-jwt-token}
```

### Authentication Endpoints

#### Login
```http
POST /auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "user_type": "doctor"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "expires_in": 3600
    }
}
```

#### Register
```http
POST /auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "user@example.com",
    "password": "password",
    "password_confirmation": "password",
    "user_type": "patient"
}
```

#### Refresh Token
```http
POST /auth/refresh
Authorization: Bearer {token}
```

#### Logout
```http
POST /auth/logout
Authorization: Bearer {token}
```

#### Get Current User
```http
GET /auth/me
Authorization: Bearer {token}
```

## Patient Management

### Get All Patients
```http
GET /patients
Authorization: Bearer {token}

Query Parameters:
- page: int (default: 1)
- per_page: int (default: 15, max: 100)
- search: string (searches name, email, phone)
- status: string (active|inactive)
- sort_by: string (default: created_at)
- sort_order: string (asc|desc, default: desc)
```

**Response:**
```json
{
    "success": true,
    "message": "Patients retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Jane Smith",
            "email": "jane@example.com",
            "phone": "+1234567890",
            "date_of_birth": "1990-01-15",
            "age": 34,
            "gender": "female",
            "blood_group": "O+",
            "is_active": true,
            "created_at": "2024-01-15T10:30:00Z",
            "updated_at": "2024-01-15T10:30:00Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7,
        "from": 1,
        "to": 15
    }
}
```

### Get Single Patient
```http
GET /patients/{id}
Authorization: Bearer {token}

Query Parameters:
- include: string (comma-separated: consultations,medical_records)
```

### Create Patient
```http
POST /patients
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1234567890",
    "password": "password",
    "date_of_birth": "1990-01-15",
    "gender": "female",
    "blood_group": "O+",
    "address": "123 Main St, City, State",
    "emergency_contact_name": "John Smith",
    "emergency_contact_phone": "+1234567891",
    "medical_history": "No significant medical history",
    "allergies": "None known",
    "current_medications": "None"
}
```

### Update Patient
```http
PUT /patients/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Jane Smith Updated",
    "phone": "+1234567892",
    "address": "456 New St, City, State"
}
```

### Get Patient Consultations
```http
GET /patients/{id}/consultations
Authorization: Bearer {token}

Query Parameters:
- page: int
- per_page: int
- status: string
- date_from: date (YYYY-MM-DD)
- date_to: date (YYYY-MM-DD)
```

### Get Patient Medical Records
```http
GET /patients/{id}/medical-records
Authorization: Bearer {token}

Query Parameters:
- page: int
- per_page: int
- record_type: string
- date_from: date
- date_to: date
```

## Consultation Management

### Get All Consultations
```http
GET /consultations
Authorization: Bearer {token}

Query Parameters:
- page: int
- per_page: int
- patient_id: int
- doctor_id: int
- status: string|array (scheduled,in_progress,completed,cancelled)
- search: string
- date_from: date
- date_to: date
- sort_by: string
- sort_order: string
```

### Get Single Consultation
```http
GET /consultations/{id}
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Consultation retrieved successfully",
    "data": {
        "consultation": {
            "id": 1,
            "patient": {
                "id": 1,
                "name": "Jane Smith",
                "email": "jane@example.com"
            },
            "doctor": {
                "id": 2,
                "name": "Dr. John Wilson",
                "email": "doctor@example.com"
            },
            "symptoms": "Fever and headache",
            "vital_signs": {
                "temperature": "38.5",
                "blood_pressure": "120/80",
                "heart_rate": "75"
            },
            "examination_notes": "Patient presents with flu-like symptoms",
            "diagnosis": "Viral infection",
            "treatment_plan": "Rest and fluids",
            "prescriptions": "Paracetamol 500mg, twice daily",
            "status": "completed",
            "follow_up_date": "2024-01-22",
            "medical_services": [
                {
                    "id": 1,
                    "service_name": "Blood Test",
                    "quantity": 1,
                    "notes": "Complete blood count"
                }
            ],
            "billing_items": [
                {
                    "id": 1,
                    "description": "Consultation fee",
                    "amount": 100.00,
                    "quantity": 1,
                    "total": 100.00
                }
            ],
            "created_at": "2024-01-15T10:30:00Z",
            "updated_at": "2024-01-15T10:30:00Z"
        }
    }
}
```

### Create Consultation
```http
POST /consultations
Authorization: Bearer {token}
Content-Type: application/json

{
    "patient_id": 1,
    "doctor_id": 2,
    "symptoms": "Fever and headache",
    "vital_signs": "{\"temperature\": \"38.5\", \"blood_pressure\": \"120/80\"}",
    "examination_notes": "Patient presents with flu-like symptoms",
    "diagnosis": "Viral infection",
    "treatment_plan": "Rest and fluids",
    "follow_up_date": "2024-01-22",
    "status": "scheduled",
    "medical_services": [
        {
            "service_id": 1,
            "quantity": 1,
            "notes": "Complete blood count"
        }
    ]
}
```

### Update Consultation
```http
PUT /consultations/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "diagnosis": "Viral infection confirmed",
    "prescriptions": "Paracetamol 500mg, twice daily for 3 days",
    "status": "completed"
}
```

### Add Medical Services to Consultation
```http
POST /consultations/{id}/medical-services
Authorization: Bearer {token}
Content-Type: application/json

{
    "medical_services": [
        {
            "service_id": 2,
            "quantity": 1,
            "notes": "X-ray chest"
        }
    ]
}
```

### Get Consultation Billing Summary
```http
GET /consultations/{id}/billing-summary
Authorization: Bearer {token}
```

## Medical Services Management

### Get Service Catalog
```http
GET /medical-services/items
Authorization: Bearer {token}

Query Parameters:
- page: int
- per_page: int
- category: string
- is_active: boolean
- search: string
- price_min: number
- price_max: number
- sort_by: string
- sort_order: string
```

**Response:**
```json
{
    "success": true,
    "message": "Medical service items retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Blood Test - Complete Blood Count",
            "code": "LAB001",
            "category": "Laboratory",
            "price": 50.00,
            "unit": "test",
            "is_active": true,
            "requires_approval": false
        }
    ],
    "pagination": {...}
}
```

### Get Service Categories
```http
GET /medical-services/categories
Authorization: Bearer {token}
```

### Create Service Item
```http
POST /medical-services/items
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Blood Test - Complete Blood Count",
    "code": "LAB001",
    "description": "Complete blood count analysis",
    "category": "Laboratory",
    "price": 50.00,
    "unit": "test",
    "is_active": true,
    "requires_approval": false,
    "preparation_instructions": "Fasting required",
    "post_service_instructions": "Results in 24 hours"
}
```

### Get Service Statistics
```http
GET /medical-services/statistics
Authorization: Bearer {token}

Query Parameters:
- date_from: date
- date_to: date
```

## Error Handling

The API uses standard HTTP status codes and returns consistent error responses:

```json
{
    "success": false,
    "message": "Validation failed",
    "error": {
        "code": "VALIDATION_ERROR",
        "details": {
            "email": ["The email field is required."],
            "password": ["The password must be at least 8 characters."]
        }
    },
    "meta": {
        "timestamp": "2024-01-15T10:30:00Z",
        "request_id": "req_123456789"
    }
}
```

### Common Error Codes

- `VALIDATION_ERROR` (400): Request validation failed
- `AUTHENTICATION_ERROR` (401): Invalid or missing authentication
- `AUTHORIZATION_ERROR` (403): Insufficient permissions
- `NOT_FOUND` (404): Resource not found
- `RATE_LIMIT_EXCEEDED` (429): Too many requests
- `INTERNAL_ERROR` (500): Server error

## Rate Limiting

The API implements rate limiting to ensure fair usage:

- **General endpoints**: 120 requests per minute
- **Resource endpoints**: 60 requests per minute
- **Authentication endpoints**: 10 requests per minute

Rate limit headers are included in responses:
- `X-RateLimit-Limit`: Request limit per window
- `X-RateLimit-Remaining`: Remaining requests in current window
- `X-RateLimit-Reset`: Timestamp when limit resets

## Security Features

- JWT authentication with configurable expiration
- Request validation and sanitization
- SQL injection and XSS protection
- CORS headers
- Security headers (HSTS, Content Security Policy, etc.)
- Request logging and monitoring
- Enterprise-level data isolation

## Pagination

List endpoints support pagination with the following parameters:

- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

Pagination info is included in the response meta:

```json
{
    "pagination": {
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7,
        "from": 1,
        "to": 15,
        "has_more": true
    }
}
```

## Sorting and Filtering

Most list endpoints support:

- **Sorting**: `sort_by` and `sort_order` parameters
- **Filtering**: Specific filters per endpoint
- **Search**: Text search across relevant fields

## SDK and Integration

For easier integration, consider using our official SDKs:

- **JavaScript/Node.js**: `npm install hospital-api-sdk`
- **PHP**: `composer require hospital/api-sdk`
- **Python**: `pip install hospital-api-sdk`

## Support

For API support, please contact:
- Email: api-support@hospital.com
- Documentation: https://docs.hospital.com/api
- GitHub: https://github.com/hospital/api-docs
