#!/bin/bash

# Test script for API endpoints
BASE_URL="http://127.0.0.1:8080/api"

echo "Testing Hospital Management System API"
echo "======================================"

# Test admin login
echo "1. Testing admin login..."
LOGIN_RESPONSE=$(curl -s -X POST "${BASE_URL}/admin/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}')

echo "Login Response: $LOGIN_RESPONSE"

# Extract token from response (assuming JSON format)
TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"token":"[^"]*"' | cut -d'"' -f4)

if [ -n "$TOKEN" ]; then
    echo "✓ Login successful, token received"
    
    # Test events endpoint
    echo ""
    echo "2. Testing events listing..."
    EVENTS_RESPONSE=$(curl -s -X GET "${BASE_URL}/admin/events" \
      -H "Authorization: Bearer $TOKEN" \
      -H "Content-Type: application/json")
    
    echo "Events Response: $EVENTS_RESPONSE"
    
    # Test user profile
    echo ""
    echo "3. Testing user profile..."
    PROFILE_RESPONSE=$(curl -s -X GET "${BASE_URL}/admin/me" \
      -H "Authorization: Bearer $TOKEN" \
      -H "Content-Type: application/json")
    
    echo "Profile Response: $PROFILE_RESPONSE"
    
else
    echo "✗ Login failed - no token received"
fi

echo ""
echo "API testing completed!"
