#!/bin/bash

# Login und Token abrufen (exakt wie in Postman)
response=$(curl -s -X POST http://kvmdash.back/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"testuser","password":"test"}')

echo "Login Response: $response"

# Token extrahieren
token=$(echo "$response" | jq -r '.token')

if [ "$token" == "null" ]; then
  echo "Login fehlgeschlagen"
  exit 1
fi

echo "Token erhalten: $token"

# Test der authentifizierten Anfrage
auth_response=$(curl -s -X GET http://kvmdash.back/api/host/mem \
  -H "Authorization: Bearer $token")

echo "Authentifizierte Antwort: $auth_response"