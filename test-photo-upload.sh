#!/bin/bash

# Script de test pour la nouvelle route d'upload de photo
# Usage: bash test-photo-upload.sh [userId] [jwtToken] [photoPath]

USER_ID=${1:-1}
JWT_TOKEN=${2:-"your-jwt-token"}
PHOTO_PATH=${3:-"./test-photo.jpg"}
API_URL="http://localhost:8000/api"

echo "=========================================="
echo "Test Photo Upload - User #$USER_ID"
echo "=========================================="

if [ ! -f "$PHOTO_PATH" ]; then
    echo "❌ Photo file not found: $PHOTO_PATH"
    echo ""
    echo "Create a test image first:"
    echo "  convert -size 100x100 xc:blue test-photo.jpg  # ImageMagick"
    echo "  # or use any existing image file"
    exit 1
fi

echo ""
echo "📤 Uploading photo with other fields..."
echo "   User ID: $USER_ID"
echo "   Photo: $PHOTO_PATH"
echo ""

curl -X PUT "$API_URL/utilisateurs/$USER_ID/profile" \
  -H "Authorization: Bearer $JWT_TOKEN" \
  -H "Accept: application/json" \
  -F "photo_profil=@$PHOTO_PATH" \
  -F "nom=TestNom" \
  -F "prenom=TestPrenom" \
  -F "telephone=+33123456789" \
  -F "bio=Updated bio from test script" \
  -v

echo ""
echo ""
echo "=========================================="
echo "✅ Test completed!"
echo "=========================================="
