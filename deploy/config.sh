#!/bin/bash

# Deployment configuration
# Change these values for different environments

# API base URL
API_BASE_URL="https://readqrcode.iceiy.com"

# Local development URL
LOCAL_API_URL="http://localhost:8000"

# Project name
PROJECT_NAME="Bank Slip Reader"

# Deployment directory
DEPLOY_DIR="deploy"

# API endpoints
API_ENDPOINTS=(
    "/api/test"
    "/api/health"
    "/api/"
    "/ocr.php"
)