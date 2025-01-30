#!/bin/bash

# Login und Token abrufen
response=$(curl -s -X POST http://kvmdash.back/api/login -H "Content-Type: application/json" -d '{"username": "testuser", "password": "password"}')

# Debugging-Ausgabe der Antwort
echo "Antwort: $response"

# Token extrahieren
token=$(echo $response | jq -r '.token')

# Überprüfen, ob der Token erfolgreich abgerufen wurde
if [ "$token" == "null" ]; then
  echo "Login fehlgeschlagen: $(echo $response | jq -r '.message')"
  exit 1
fi

echo "Token: $token"

# Authentifizierte Anfrage senden
auth_response=$(curl -s -X GET http://kvmdash.back/api/host/mem -H "Authorization: Bearer $token")

echo "Antwort: $auth_response"