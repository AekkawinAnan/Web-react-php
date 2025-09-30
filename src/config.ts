// Central configuration for API endpoints and settings

export const API_CONFIG = {
  // API base URL - change this for different environments
  BASE_URL: 'http://localhost:8000',

  // API endpoints
  ENDPOINTS: {
    OCR: '/ocr.php',
    TEST: '/api/test',
    HEALTH: '/api/health',
    INFO: '/api/',
    VERIFY_SLIP: '/api/verify-slip',
    PARSE_SLIP: '/api/parse-slip'
  },

  // Request timeouts (in milliseconds)
  TIMEOUTS: {
    DEFAULT: 10000,
    OCR: 30000
  }
} as const;

// Helper function to get full API URL
export const getApiUrl = (endpoint: string): string => {
  return `${API_CONFIG.BASE_URL}${endpoint}`;
};

// Helper function to get OCR API URL (without /api prefix)
export const getOcrUrl = (): string => {
  return `${API_CONFIG.BASE_URL}${API_CONFIG.ENDPOINTS.OCR}`;
};