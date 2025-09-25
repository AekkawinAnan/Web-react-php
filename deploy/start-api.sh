#!/bin/bash
echo "🚀 Starting Bank Slip Reader API..."
echo "📍 API will be available at: http://localhost:8000"
echo "📋 Available endpoints:"
echo "   GET  /api/test        - Hello World test"
echo "   GET  /api/health      - Health check"
echo "   GET  /api/            - API information"
echo "   POST /api/verify-slip - Slip verification"
echo "   POST /api/parse-slip  - Slip parsing"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

# Start PHP built-in server
php -S localhost:8000 -t api/
