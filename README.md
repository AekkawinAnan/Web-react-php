# Bank Slip Reader 📷

A modern web application for reading and verifying bank slip information using OCR (Optical Character Recognition) technology. Built with React.js and TypeScript.

![Build Status](https://img.shields.io/badge/build-passing-brightgreen)
![Version](https://img.shields.io/badge/version-1.0.0-blue)
![License](https://img.shields.io/badge/license-MIT-green)

## ✨ Features

- 📷 **OCR-based Reading**: Extract text from bank slip images using Tesseract.js
- 🔍 **Intelligent Parsing**: Automatically detect and parse multiple Thai bank slip formats
- 🏦 **Multi-Bank Support**: SCB, Kasikorn Bank, and other major Thai banks
- 💳 **Enhanced Recognition**: Biller IDs, service codes, and various account formats
- 📱 **Responsive Design**: Works perfectly on desktop and mobile devices
- 🌐 **Multi-language Support**: Thai and English text recognition
- 🚀 **Production Ready**: Optimized build with modern deployment options

## 🎯 Supported Information

- **Bank Name**: Automatic detection of SCB, KBank, and other banks
- **Date & Time**: Transaction date and time (Thai and English formats)
- **Account Numbers**: Multiple formats (xxx-x-xxxx-x, xxx-xxx-xxx-x, etc.)
- **Biller Information**: Biller names, IDs, and service codes
- **Transaction Amount**: Amount in THB with proper formatting
- **Reference Numbers**: Various reference number formats and lengths
- **Raw OCR Text**: Full extracted text for debugging and verification

## 🚀 Quick Start

### Prerequisites

- Node.js 18+
- npm or yarn

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd bank-slip-reader
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Start development server**
   ```bash
   npm run dev
   ```

4. **Open your browser**
   Navigate to `http://localhost:5173`

### Production Build

```bash
# Build for production
npm run build

# Preview production build
npm run preview

# Deploy using the deployment script
./deploy.sh
```

## 🐳 Docker Deployment

### Using Docker Compose

```yaml
version: '3.8'
services:
  bank-slip-reader:
    build: .
    ports:
      - "3000:3000"
    environment:
      - NODE_ENV=production
    restart: unless-stopped
```

```bash
docker-compose up -d
```

### Using Docker directly

```bash
# Build the image
docker build -t bank-slip-reader .

# Run the container
docker run -d -p 3000:3000 --name bank-slip-reader bank-slip-reader
```

## ☁️ Cloud Deployment

### Vercel (Recommended)

1. Connect your GitHub repository to Vercel
2. Set build command: `npm run build`
3. Set output directory: `dist`
4. Deploy automatically on push

### Netlify

1. Drag and drop the `dist` folder to Netlify
2. Or connect your GitHub repository
3. Set build command: `npm run build`
4. Set publish directory: `dist`

### GitHub Pages

The project includes GitHub Actions workflow for automatic deployment to GitHub Pages.

## 📋 Usage Guide

### 1. Upload Image
- Click "Choose Slip Image" to select a bank slip photo
- Supported formats: JPG, PNG, GIF, WebP

### 2. Extract Text
- Click "Extract Text" to process the image with OCR
- Wait for the AI to analyze and extract information

### 3. Review Results
- View extracted information in structured format:
  - Date and Time
  - Account numbers
  - Transaction amount
  - Reference number

### 4. Verify Data
- Enter expected amount and account number
- Click "Verify Slip" to validate the data
- Review verification results

## 🔧 Configuration

### Vite Configuration

The project uses optimized Vite configuration for production:

```typescript
// vite.config.ts
export default defineConfig({
  plugins: [react()],
  build: {
    minify: 'esbuild',
    sourcemap: false,
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['react', 'react-dom'],
          ocr: ['tesseract.js']
        }
      }
    }
  }
})
```

### Configuration

The application uses central configuration files for easy environment management:

#### Frontend Configuration (`src/config.ts`)
```typescript
export const API_CONFIG = {
  BASE_URL: 'http://localhost:8000', // Change for different environments
  ENDPOINTS: {
    OCR: '/ocr.php',
    TEST: '/api/test',
    HEALTH: '/api/health'
  }
};
```

#### Deployment Configuration (`deploy/config.sh`)
```bash
# API base URL - change for different environments
API_BASE_URL="https://readqrcode.iceiy.com"

# Local development
LOCAL_API_URL="http://localhost:8000"
```

#### Environment Variables (Optional)

Create a `.env` file for additional configuration:

```env
# Development
VITE_APP_TITLE="Bank Slip Reader"
VITE_API_URL="https://api.example.com"

# Production
NODE_ENV=production
PORT=3000
```

## 🧪 Testing

### Frontend Testing
```bash
# Run tests
npm test

# Run tests with coverage
npm run test:coverage

# Run linting
npm run lint

# Run type checking
npm run type-check
```

### API Testing
```bash
# Test API endpoints (requires PHP server)
php test-api.php

# Or test manually with curl
curl http://localhost:8000/api/test
curl http://localhost:8000/api/health
```

### OCR Processing API
The project includes a complete OCR processing API for bank slip images:

```bash
# Test OCR endpoint
curl -X POST http://localhost:8000/api/ocr \
  -F "slip_image=@your_bank_slip.jpg"

# Response includes:
# - Raw OCR text
# - Parsed bank slip data
# - Confidence scores
# - Processing time
```

### Setup Real OCR Processing
To use real OCR instead of mock data, see [OCR Setup Guide](api/OCR_SETUP.md) for:
- Tesseract PHP library setup
- Google Cloud Vision API integration
- OCR.space API configuration
- AWS Textract implementation


### Manual API Testing
If PHP is not available, you can test the API endpoints manually:

1. **Start PHP server** (if available):
   ```bash
   cd api
   php -S localhost:8000
   ```

2. **Test endpoints**:
   ```bash
   curl http://localhost:8000/api/test
   curl http://localhost:8000/api/health
   curl http://localhost:8000/api/
   ```

## 📁 Project Structure

```
bank-slip-reader/
├── public/                 # Static assets
├── src/                    # Source code
│   ├── components/         # React components
│   ├── assets/            # Images and icons
│   ├── App.tsx            # Main application
│   ├── main.tsx           # Application entry
│   └── index.css          # Global styles
├── dist/                   # Production build
├── deploy/                 # Deployment files
├── .github/               # GitHub workflows
├── Dockerfile             # Docker configuration
├── vite.config.ts         # Vite configuration
└── package.json           # Dependencies
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature-name`
3. Make your changes and test thoroughly
4. Commit your changes: `git commit -m 'Add feature'`
5. Push to the branch: `git push origin feature-name`
6. Submit a pull request

### Development Guidelines

- Follow TypeScript best practices
- Write meaningful commit messages
- Add tests for new features
- Update documentation as needed

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- [Tesseract.js](https://github.com/naptha/tesseract.js) for OCR functionality
- [React](https://reactjs.org/) for the UI framework
- [Vite](https://vitejs.dev/) for the build tool
- Thai banking community for format specifications

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/your-repo/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-repo/discussions)
- **Email**: support@bankslipreader.com

## 🗺️ Roadmap

### Version 1.1.0
- [ ] Batch processing for multiple slips
- [ ] Export to PDF/Excel
- [ ] API integration for bank verification
- [ ] Mobile app (React Native)

### Version 1.2.0
- [ ] Machine learning model for better accuracy
- [ ] Multi-bank format support
- [ ] Real-time slip verification
- [ ] Advanced analytics dashboard

---

**Made with ❤️ for the Thai banking community**

*For more information, visit our [documentation](docs/README.md) or [demo site](https://demo.bankslipreader.com)*
