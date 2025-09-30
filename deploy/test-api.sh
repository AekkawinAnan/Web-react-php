#!/bin/bash

# Source configuration
source deploy/config.sh

echo "🧪 Testing $PROJECT_NAME API"
echo "================================"
echo ""

echo "✅ Expected: JSON response with 'Hello World from Bank Slip Reader!'"
echo ""

# Test API info endpoint
echo "2. Testing GET /api/index.php"
curl -s $API_BASE_URL/api/index.php | head -5
echo ""
echo "✅ Expected: JSON response with API information"
echo ""

echo "🎉 API Testing Complete!"
echo ""
echo "📋 Available endpoints:"
for endpoint in "${API_ENDPOINTS[@]}"; do
    echo "   GET  $endpoint"
done
