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
