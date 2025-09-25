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
