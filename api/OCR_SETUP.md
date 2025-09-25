# OCR Setup Guide - Bank Slip Reader

This guide explains how to set up real OCR processing instead of using mock data.

## 🔧 Available OCR Options

### Option 1: Tesseract PHP Library (Recommended)

#### Installation:
```bash
# Install Tesseract OCR system library
sudo apt-get install tesseract-ocr tesseract-ocr-tha  # Ubuntu/Debian
# OR
sudo yum install tesseract tesseract-langpack-tha     # CentOS/RHEL

# Install PHP Tesseract library via Composer
composer require thiagoalessio/tesseract_ocr
```

#### Configuration:
```bash
# Download Thai language pack
sudo apt-get install tesseract-ocr-tha

# Test installation
tesseract --version
tesseract --list-langs
```

#### Usage:
The API will automatically detect and use Tesseract when available. No additional configuration needed.

---

### Option 2: Google Cloud Vision API

#### Setup:
1. **Create Google Cloud Project:**
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create new project or select existing one
   - Enable Vision API

2. **Create Service Account:**
   ```bash
   # Create service account key
   gcloud iam service-accounts create ocr-service-account
   gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
     --member="serviceAccount:ocr-service-account@YOUR_PROJECT_ID.iam.gserviceaccount.com" \
     --role="roles/serviceusage.serviceUsageConsumer"
   gcloud iam service-accounts keys create key.json \
     --iam-account=ocr-service-account@YOUR_PROJECT_ID.iam.gserviceaccount.com
   ```

3. **Set Environment Variables:**
   ```bash
   export GOOGLE_APPLICATION_CREDENTIALS="/path/to/key.json"
   export GOOGLE_VISION_API_KEY="your-api-key"
   ```

#### Usage:
The API will automatically use Google Vision API when credentials are available.

---

### Option 3: OCR.space API

#### Setup:
1. **Get API Key:**
   - Sign up at [OCR.space](https://ocr.space/ocrapi)
   - Get your free API key

2. **Set Environment Variable:**
   ```bash
   export OCR_API_KEY="your-ocr-space-api-key"
   ```

#### Usage:
The API will automatically use OCR.space when API key is provided.

---

### Option 4: AWS Textract

#### Setup:
1. **Install AWS CLI and configure credentials**
2. **Set Environment Variables:**
   ```bash
   export AWS_ACCESS_KEY_ID="your-access-key"
   export AWS_SECRET_ACCESS_KEY="your-secret-key"
   export AWS_REGION="us-east-1"
   ```

#### Implementation:
Add this function to `api/ocr.php`:
```php
function processWithAWSTextract($imagePath, $startTime) {
    // AWS Textract implementation
    // Requires AWS SDK for PHP
}
```

---

## 🚀 Quick Setup (Tesseract)

### For Ubuntu/Debian:
```bash
# Install system dependencies
sudo apt-get update
sudo apt-get install tesseract-ocr tesseract-ocr-tha

# Install PHP dependencies
composer require thiagoalessio/tesseract_ocr

# Test OCR
echo "สวัสดีครับ" | tesseract stdin stdout
```

### For macOS:
```bash
# Install Tesseract
brew install tesseract tesseract-lang

# Install PHP Tesseract
composer require thiagoalessio/tesseract_ocr
```

### For Windows:
```bash
# Download Tesseract installer from:
# https://github.com/UB-Mannheim/tesseract/wiki

# Install Thai language pack
# Download from: https://github.com/tesseract-ocr/tessdata

# Install via Composer
composer require thiagoalessio/tesseract_ocr
```

---

## 🔍 Testing OCR Setup

### Test Tesseract:
```bash
# Create test image
echo "ธนาคารไทยพาณิชย์ SCB
23 ก.ย. 2568 - 12:33
จำนวนเงิน: 50.00 บาท" > test.txt

# Test OCR
tesseract test.txt stdout
```

### Test API:
```bash
# Start PHP server
cd api
php -S localhost:8000

# Test with curl
curl -X POST http://localhost:8000/ocr \
  -F "slip_image=@test_image.jpg"
```

---

## 📊 Performance Comparison

| OCR Service | Accuracy | Speed | Cost | Setup Complexity |
|-------------|----------|-------|------|------------------|
| **Tesseract** | ⭐⭐⭐⭐ | ⭐⭐⭐ | Free | ⭐⭐ |
| **Google Vision** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | $1.50/1000 | ⭐⭐⭐ |
| **OCR.space** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ | $0.008/image | ⭐⭐ |
| **AWS Textract** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | $1.50/1000 | ⭐⭐⭐⭐ |

---

## 🛠️ Troubleshooting

### Tesseract Issues:
```bash
# Check if Tesseract is installed
tesseract --version

# Check available languages
tesseract --list-langs

# Install Thai language pack
sudo apt-get install tesseract-ocr-tha

# Test with image
tesseract image.jpg stdout
```

### Google Vision Issues:
```bash
# Check credentials
echo $GOOGLE_APPLICATION_CREDENTIALS

# Test API key
curl -X POST \
  "https://vision.googleapis.com/v1/images:annotate?key=YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"requests":[{"image":{"content":"base64_image"},"features":[{"type":"TEXT_DETECTION"}]}]}'
```

### OCR.space Issues:
```bash
# Test API key
curl -X POST https://api.ocr.space/parse/image \
  -F "apikey=YOUR_API_KEY" \
  -F "file=@test.jpg"
```

---

## 📈 Production Recommendations

### For Development:
- **Use Tesseract** - Free, works offline, good accuracy

### For Production:
- **Google Vision API** - Best accuracy, reliable, scalable
- **OCR.space** - Good balance of cost and performance
- **AWS Textract** - Enterprise-grade, handles complex documents

### Hybrid Approach:
```php
function processImageWithOCR($imagePath) {
    // Try Tesseract first (free, offline)
    if (class_exists('TesseractOCR')) {
        return processWithTesseract($imagePath);
    }

    // Fallback to cloud service
    if (getenv('GOOGLE_VISION_API_KEY')) {
        return processWithGoogleVision($imagePath);
    }

    // Final fallback to mock data
    return processWithMockData($imagePath);
}
```

---

## 🔧 Environment Configuration

Create `.env` file in your project root:
```env
# OCR Configuration
OCR_SERVICE=tesseract
GOOGLE_VISION_API_KEY=your-google-vision-key
OCR_API_KEY=your-ocr-space-key
AWS_ACCESS_KEY_ID=your-aws-key
AWS_SECRET_ACCESS_KEY=your-aws-secret
AWS_REGION=us-east-1

# Processing Options
MAX_FILE_SIZE=10MB
ALLOWED_FORMATS=jpg,jpeg,png,gif,webp
ENABLE_CONFIDENCE_SCORING=true
```

---

## 🎯 Next Steps

1. **Choose OCR service** based on your needs
2. **Install dependencies** for your chosen service
3. **Test with sample images** to verify setup
4. **Monitor performance** and accuracy
5. **Set up fallback options** for reliability

---

## 📞 Support

For issues with specific OCR services:
- **Tesseract:** [GitHub Issues](https://github.com/tesseract-ocr/tesseract/issues)
- **Google Vision:** [Google Cloud Support](https://cloud.google.com/support)
- **OCR.space:** [OCR.space Support](https://ocr.space/ocrapi/contact)

---

**Ready to process real bank slip images!** 🎉
