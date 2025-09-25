#!/bin/bash
echo "🧪 Testing Bank Slip Reader API"
echo "================================"
echo ""

# Test hello world endpoint
echo "1. Testing GET /api/hello.php"
curl -s https://readqrcode.iceiy.com/api/hello.php | head -5
echo ""
echo "✅ Expected: JSON response with 'Hello World from Bank Slip Reader!'"
echo ""

# Test API info endpoint
echo "2. Testing GET /api/index.php"
curl -s https://readqrcode.iceiy.com/api/index.php | head -5
echo ""
echo "✅ Expected: JSON response with API information"
echo ""

echo "🎉 API Testing Complete!"
echo ""
echo "📋 Available endpoints:"
echo "   GET  /api/hello.php    - Simple hello world"
echo "   GET  /api/index.php    - Full REST API"
echo "   POST /api/verify-slip  - Slip verification"
echo "   POST /api/parse-slip   - Slip parsing"
