#!/bin/bash

# Bank Slip Reader - Deployment Script
echo "🚀 Deploying Bank Slip Reader..."

# Build the project
echo "📦 Building production version..."
npm run build

# Check if build was successful
if [ $? -eq 0 ]; then
    echo "✅ Build successful!"

    # Create deployment directory
    mkdir -p deploy

    # Copy built files
    cp -r dist/* deploy/

    # Create a simple server file
    cat > deploy/server.js << 'EOF'
const express = require('express');
const path = require('path');
const app = express();
const port = process.env.PORT || 3000;

// Serve static files
app.use(express.static(path.join(__dirname)));

// Handle React Router
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});

app.listen(port, () => {
  console.log(`🚀 Bank Slip Reader running on port ${port}`);
  console.log(`📱 Access at: http://localhost:${port}`);
});
EOF

    # Create package.json for deployment
    cat > deploy/package.json << 'EOF'
{
  "name": "bank-slip-reader",
  "version": "1.0.0",
  "description": "OCR-based bank slip reader application",
  "main": "server.js",
  "scripts": {
    "start": "node server.js",
    "dev": "npm run serve"
  },
  "dependencies": {
    "express": "^4.18.2"
  },
  "keywords": ["ocr", "bank", "slip", "reader", "react"],
  "author": "Bank Slip Reader Team",
  "license": "MIT"
}
EOF

    # Create README for deployment
    cat > deploy/README.md << 'EOF'
# Bank Slip Reader - Deployment

This is a production build of the Bank Slip Reader application.

## Features

- 📷 OCR-based bank slip reading
- 🔍 Intelligent text extraction (Thai & English)
- ✅ Slip verification system
- 📱 Responsive web interface

## Quick Start

### Option 1: Node.js Server (Recommended)

```bash
cd deploy
npm install
npm start
```

Access at: http://localhost:3000

### Option 2: Static File Server

```bash
cd deploy
npx serve .
```

### Option 3: Direct File Access

Simply open `index.html` in your browser.

## Production Deployment

### Vercel/Netlify (Recommended)

1. Upload the `deploy` folder to your hosting provider
2. Set the build command to: `npm run build`
3. Set the output directory to: `dist`

### Docker Deployment

```dockerfile
FROM node:18-alpine
WORKDIR /app
COPY deploy/ .
RUN npm install
EXPOSE 3000
CMD ["npm", "start"]
```

## Environment Variables

- `PORT`: Server port (default: 3000)

## Browser Support

- Chrome/Chromium 88+
- Firefox 85+
- Safari 14+
- Edge 88+

## License

MIT
EOF

    # Create .gitignore for deployment
    cat > deploy/.gitignore << 'EOF'
node_modules/
*.log
.env
.env.local
.DS_Store
EOF

    # Copy API files to deployment
    mkdir -p deploy/api
    cp api/index.php deploy/api/
    cp api/hello.php deploy/api/
    cp api/ocr.php deploy/api/
    cp api/tesseract-test.php deploy/api/
    cp test-api.php deploy/
    cp .htaccess deploy/
    cp nginx.conf deploy/

    # Update .htaccess for routing
    cat > deploy/.htaccess << 'EOF'
# Bank Slip Reader API - Apache Configuration
# Place this file in the root directory (public_html or www)

RewriteEngine On

# CORS headers for all requests
<IfModule mod_headers.c>
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization, Accept"
    Header always set Access-Control-Max-Age "86400"
</IfModule>

# Handle preflight OPTIONS requests
RewriteCond %{REQUEST_METHOD} OPTIONS
RewriteRule ^(.*)$ $1 [R=200,L]

# API Routing - Route all /api/* requests to PHP files
RewriteRule ^api/(.*)$ api/$1 [QSA,L]

# Handle direct PHP file access
RewriteCond %{REQUEST_FILENAME} \.php$
RewriteRule ^(.*)$ $1 [QSA,L]

# Force PHP execution for all .php files
<Files "*.php">
    SetHandler application/x-httpd-php
</Files>

# Serve static files (CSS, JS, images) directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.html [QSA,L]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Prevent access to sensitive files
<FilesMatch "\.(htaccess|htpasswd|ini|log|sh|inc|bak)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
</IfModule>
EOF

    # Create simple test script
    cat > deploy/test-api.sh << 'EOF'
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
EOF

    chmod +x deploy/test-api.sh

    # Create API documentation
    cat > deploy/API.md << 'EOF'
# Bank Slip Reader API Documentation

## Overview

The Bank Slip Reader API provides REST endpoints for OCR-based bank slip processing and verification.

## Base URL

```
http://localhost:8000/api/
```

## Authentication

Currently, no authentication is required. CORS is enabled for all origins.

## Endpoints

### GET /api/test

**Hello World test endpoint**

Returns basic API information and status.

**Response:**
```json
{
  "status": "success",
  "message": "Hello World from Bank Slip Reader API!",
  "timestamp": "2024-01-01 12:00:00",
  "version": "1.0.0",
  "data": {
    "service": "Bank Slip Reader Backend API",
    "description": "OCR-based bank slip reading service",
    "endpoints": {
      "GET /api/test": "Test endpoint - returns hello world",
      "POST /api/verify-slip": "Verify slip data (planned)",
      "POST /api/parse-slip": "Parse slip image (planned)",
      "POST /api/ocr": "Process OCR text and extract slip data"
    }
  }
}
```

### GET /api/health

**Health check endpoint**

Returns server status and system information.

**Response:**
```json
{
  "status": "healthy",
  "message": "API is running correctly",
  "timestamp": "2024-01-01 12:00:00",
  "server_info": {
    "php_version": "8.2.0",
    "server_software": "Apache/2.4.54",
    "request_method": "GET",
    "request_uri": "/api/health"
  }
}
```

### GET /api/

**API information endpoint**

Returns comprehensive API documentation and available endpoints.

**Response:**
```json
{
  "status": "success",
  "message": "Bank Slip Reader API",
  "timestamp": "2024-01-01 12:00:00",
  "version": "1.0.0",
  "description": "Backend API for Bank Slip Reader application",
  "endpoints": {
    "GET /api/test": "Test endpoint - returns hello world",
    "GET /api/health": "Health check endpoint",
    "POST /api/verify-slip": "Verify slip data (JSON input)",
    "POST /api/parse-slip": "Parse slip image (multipart/form-data)",
    "POST /api/ocr": "Process OCR text and extract slip data"
  },
  "documentation": "See README.md for full API documentation"
}
```

### POST /api/verify-slip

**Slip verification endpoint**

Accepts JSON data for slip verification (placeholder implementation).

**Request Body:**
```json
{
  "amount": 50.00,
  "account": "xxx-xxx998-9",
  "bank": "SCB"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Slip verification endpoint ready",
  "timestamp": "2024-01-01 12:00:00",
  "data": {
    "note": "This is a placeholder endpoint. OCR processing will be implemented here.",
    "received_data": {
      "amount": 50.00,
      "account": "xxx-xxx998-9",
      "bank": "SCB"
    }
  }
}
```

### POST /api/parse-slip

**Slip parsing endpoint**

Accepts multipart/form-data for image processing (placeholder implementation).

**Request:** FormData with image file
- Field name: `slip_image`
- Supported formats: jpg, jpeg, png, gif, webp
- Max size: 10MB

**Response:**
```json
{
  "status": "success",
  "message": "Slip parsing endpoint ready",
  "timestamp": "2024-01-01 12:00:00",
  "data": {
    "note": "This is a placeholder endpoint. Image processing will be implemented here.",
    "supported_formats": ["jpg", "jpeg", "png", "gif", "webp"],
    "max_file_size": "10MB"
  }
}
```

### POST /api/ocr

**OCR text processing endpoint**

Accepts raw OCR text and extracts bank slip information.

**Request Body (JSON):**
```json
{
  "ocr_text": "ธนาคารไทยพาณิชย์ SCB\n23 ก.ย. 2568 - 12:33\nรหัสอ้างอิง: 2025092303qN9srV2J8UjWSbl\nจำนวนเงิน: 50.00 บาท"
}
```

**Request Body (Form Data):**
```
ocr_text=ธนาคารไทยพาณิชย์ SCB\n23 ก.ย. 2568 - 12:33\nรหัสอ้างอิง: 2025092303qN9srV2J8UjWSbl\nจำนวนเงิน: 50.00 บาท
```

**Response:**
```json
{
  "status": "success",
  "message": "OCR text processed successfully",
  "timestamp": "2024-01-01 12:00:00",
  "version": "1.0.0",
  "data": {
    "input_info": {
      "text_length": 156,
      "processed_at": "2024-01-01 12:00:00"
    },
    "ocr_result": {
      "raw_text": "ธนาคารไทยพาณิชย์ SCB\n23 ก.ย. 2568 - 12:33\nรหัสอ้างอิง: 2025092303qN9srV2J8UjWSbl\nจำนวนเงิน: 50.00 บาท",
      "confidence": 1.0,
      "processing_time": 0.01
    },
    "parsed_data": {
      "date": "23 ก.ย. 2568",
      "time": "12:33",
      "amount": 50.00,
      "reference": "2025092303qN9srV2J8UjWSbl",
      "bankName": "SCB",
      "rawText": "..."
    }
  }
}
```

## Error Responses

### 404 Not Found
```json
{
  "status": "error",
  "message": "Endpoint not found",
  "timestamp": "2024-01-01 12:00:00",
  "path": "/api/invalid",
  "method": "GET"
}
```

### 405 Method Not Allowed
```json
{
  "status": "error",
  "message": "Method not allowed",
  "timestamp": "2024-01-01 12:00:00",
  "path": "/api/test",
  "method": "POST"
}
```

### 400 Bad Request
```json
{
  "status": "error",
  "message": "No OCR text provided. Please provide ocr_text in JSON body or form data",
  "timestamp": "2024-01-01 12:00:00",
  "path": "/api/ocr",
  "method": "POST"
}
```

## Testing

### Using the test script
```bash
php test-api.php
```

### Using curl
```bash
# Test hello world
curl http://localhost:8000/api/test

# Health check
curl http://localhost:8000/api/health

# API info
curl http://localhost:8000/api/

# OCR text processing (JSON)
curl -X POST http://localhost:8000/api/ocr \
  -H "Content-Type: application/json" \
  -d '{"ocr_text": "ธนาคารไทยพาณิชย์ SCB\n23 ก.ย. 2568\nจำนวนเงิน: 50.00 บาท"}'

# OCR text processing (Form data)
curl -X POST http://localhost:8000/api/ocr \
  -d "ocr_text=ธนาคารไทยพาณิชย์ SCB\n23 ก.ย. 2568\nจำนวนเงิน: 50.00 บาท"
```

## Starting the API Server

### Development
```bash
cd api
php -S localhost:8000
```

### Production
```bash
# Using the deployment script
./start-api.sh

# Or manually
php -S localhost:8000 -t api/
```

## Notes

- All endpoints return JSON responses
- CORS is enabled for cross-origin requests
- Timestamps are in Y-m-d H:i:s format
- Error responses include detailed information for debugging
- OCR endpoint accepts raw text input for processing
EOF

    # Create API startup script
    cat > deploy/start-api.sh << 'EOF'
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
EOF

    chmod +x deploy/start-api.sh

    # Create combined startup script
    cat > deploy/start-all.sh << 'EOF'
#!/bin/bash
echo "🚀 Starting Bank Slip Reader (Frontend + Backend)"
echo "================================================="
echo ""
echo "📱 Frontend (React App): http://localhost:3000"
echo "🔧 Backend API: http://localhost:8000"
echo ""
echo "Starting both services..."
echo ""

# Function to cleanup on exit
cleanup() {
    echo ""
    echo "🛑 Shutting down services..."
    kill $frontend_pid $backend_pid 2>/dev/null
    exit
}

# Trap SIGINT (Ctrl+C)
trap cleanup SIGINT

# Start backend API
echo "📡 Starting Backend API..."
php -S localhost:8000 -t api/ > api.log 2>&1 &
backend_pid=$!

# Wait a moment for backend to start
sleep 2

# Start frontend
echo "🌐 Starting Frontend App..."
cd ..
serve -s dist -p 3000 > frontend.log 2>&1 &
frontend_pid=$!

echo ""
echo "✅ Both services started successfully!"
echo "📱 Frontend: http://localhost:3000"
echo "🔧 Backend:  http://localhost:8000"
echo ""
echo "Press Ctrl+C to stop both services"
echo ""

# Wait for processes
wait
EOF

    chmod +x deploy/start-all.sh

    # Update deployment README
    cat > deploy/README.md << 'EOF'
# Bank Slip Reader - Deployment Package

This is a complete deployment package for the Bank Slip Reader application with both frontend and backend.

## 📁 Package Contents

- `index.html` - Main React application
- `assets/` - Optimized CSS and JavaScript files
- `server.js` - Express server for production
- `api/index.php` - PHP REST API backend
- `start-api.sh` - Script to start only the API
- `start-all.sh` - Script to start both frontend and backend
- `test-api.php` - API testing script

## 🚀 Quick Start Options

### Option 1: Start Everything (Recommended)
```bash
./start-all.sh
```
- Starts both frontend (port 3000) and backend (port 8000)
- Perfect for development and testing

### Option 2: API Only
```bash
./start-api.sh
```
- Starts only the PHP API on port 8000
- Use with your own frontend

### Option 3: Frontend Only
```bash
# From the deploy directory
serve -s . -p 3000
```
- Serves the static frontend files

## 🔧 API Endpoints

### GET Endpoints
- `GET /api/test` - Hello World test endpoint
- `GET /api/health` - Health check
- `GET /api/` - API information

### POST Endpoints
- `POST /api/verify-slip` - Slip verification (JSON)
- `POST /api/parse-slip` - Slip parsing (multipart/form-data)

## 🧪 Testing the API

```bash
# Test all API endpoints
php test-api.php

# Or test manually with curl
curl http://localhost:8000/api/test
curl http://localhost:8000/api/health
```

## 🌐 Production Deployment

### With Apache/Nginx
1. Copy all files to your web server
2. Configure your web server to:
   - Serve static files from root
   - Route API calls to `api/index.php`
3. Set proper permissions

### With Docker
```bash
# Build the image
docker build -t bank-slip-reader .

# Run the container
docker run -d -p 3000:3000 -p 8000:8000 bank-slip-reader
```

## 📊 API Response Examples

### Hello World Test
```json
{
  "status": "success",
  "message": "Hello World from Bank Slip Reader API!",
  "timestamp": "2024-01-01 12:00:00",
  "version": "1.0.0"
}
```

### Health Check
```json
{
  "status": "healthy",
  "message": "API is running correctly",
  "timestamp": "2024-01-01 12:00:00",
  "server_info": {
    "php_version": "8.2.0",
    "server_software": "Apache/2.4.54"
  }
}
```

## 🔒 Security Notes

- CORS is enabled for all origins (configure in production)
- Error details are shown in development
- Consider adding authentication for production use

## 📞 Support

For issues or questions:
- Check the logs: `api.log` and `frontend.log`
- Test endpoints with: `php test-api.php`
- Review API documentation in `api/index.php`
EOF

    echo "✅ Deployment files created successfully!"
    echo ""
    echo "📁 Deployment files created in: ./deploy/"
    echo "📋 Files included:"
    echo "  - index.html (main app)"
    echo "  - assets/ (CSS & JS files)"
    echo "  - server.js (Express server)"
    echo "  - api/index.php (PHP REST API)"
    echo "  - start-api.sh (API startup script)"
    echo "  - start-all.sh (combined startup)"
    echo "  - test-api.php (API testing)"
    echo "  - package.json (dependencies)"
    echo "  - README.md (deployment guide)"
    echo ""
    echo "🚀 Ready for deployment!"

else
    echo "❌ Build failed!"
    exit 1
fi
