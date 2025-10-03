#!/bin/bash

# Test Script for New Patient + Consultation Creation
# This script simulates what the frontend sends when creating a consultation with a new patient

echo "🧪 Testing Consultation Creation with New Patient"
echo "=================================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Get the API base URL
API_URL="http://localhost:8888/api"
TOKEN=""

echo "${BLUE}📋 Step 1: Login to get authentication token${NC}"
echo ""

# Login first (you'll need to update credentials)
LOGIN_RESPONSE=$(curl -s -X POST "${API_URL}/users/login" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin@example.com",
    "password": "admin123"
  }')

# Extract token from response (adjust based on your API response structure)
TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
    echo "${RED}❌ Login failed. Please check credentials in the script.${NC}"
    echo "Response: $LOGIN_RESPONSE"
    exit 1
fi

echo "${GREEN}✅ Login successful${NC}"
echo "Token: ${TOKEN:0:20}..."
echo ""

echo "${BLUE}📋 Step 2: Create consultation with new patient${NC}"
echo ""

# Create consultation with new patient
CONSULTATION_RESPONSE=$(curl -s -X POST "${API_URL}/api/Consultation" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ${TOKEN}" \
  -d '{
    "is_new_patient": "true",
    "new_patient_first_name": "John",
    "new_patient_last_name": "Doe",
    "new_patient_email": "john.doe.test@example.com",
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
  }')

echo "Response:"
echo "$CONSULTATION_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$CONSULTATION_RESPONSE"
echo ""

# Check if successful
if echo "$CONSULTATION_RESPONSE" | grep -q '"code":1'; then
    echo "${GREEN}✅ Consultation created successfully!${NC}"
    
    # Extract patient_id and consultation_id
    PATIENT_ID=$(echo $CONSULTATION_RESPONSE | grep -o '"patient_id":[0-9]*' | cut -d':' -f2)
    CONSULTATION_ID=$(echo $CONSULTATION_RESPONSE | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    
    echo ""
    echo "Created:"
    echo "  - Patient ID: $PATIENT_ID"
    echo "  - Consultation ID: $CONSULTATION_ID"
    echo ""
    
    echo "${BLUE}📋 Step 3: Verify patient was created${NC}"
    echo ""
    
    # Verify patient
    PATIENT_RESPONSE=$(curl -s -X GET "${API_URL}/api/User/${PATIENT_ID}" \
      -H "Authorization: Bearer ${TOKEN}")
    
    echo "Patient Details:"
    echo "$PATIENT_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$PATIENT_RESPONSE"
    echo ""
    
    if echo "$PATIENT_RESPONSE" | grep -q '"user_type":"Patient"'; then
        echo "${GREEN}✅ Patient verified successfully!${NC}"
    else
        echo "${RED}❌ Patient verification failed${NC}"
    fi
    
else
    echo "${RED}❌ Consultation creation failed${NC}"
    echo ""
    echo "Please check the logs for details:"
    echo "  tail -f storage/logs/laravel.log"
fi

echo ""
echo "=================================================="
echo "🏁 Test Complete"
