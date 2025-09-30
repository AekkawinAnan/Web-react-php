import React, { useState } from 'react';
import './ApiTest.css';

interface ApiResponse {
  status: string;
  message: string;
  timestamp: string;
  version?: string;
  data?: any;
  server_info?: any;
}

const ApiTest: React.FC = () => {
  const [responses, setResponses] = useState<{[key: string]: ApiResponse | null}>({
    hello: null,
    test: null,
    health: null,
    root: null,
    tesseract: null
  });
  const [loading, setLoading] = useState<{[key: string]: boolean}>({
    hello: false,
    test: false,
    health: false,
    root: false,
    tesseract: false
  });
  const [error, setError] = useState<string | null>(null);

  const API_BASE_URL = 'http://localhost:8000';

  const testEndpoint = async (endpoint: string, key: string) => {
    setLoading(prev => ({ ...prev, [key]: true }));
    setError(null);

    try {
      const url = endpoint === 'root' ? `${API_BASE_URL}/` : `${API_BASE_URL}/${endpoint}`;
      const response = await fetch(url, {
        method: endpoint === 'root' ? 'GET' : 'GET',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      setResponses(prev => ({ ...prev, [key]: data }));
    } catch (err) {
      setError(`Error testing ${endpoint}: ${err instanceof Error ? err.message : 'Unknown error'}`);
      setResponses(prev => ({ ...prev, [key]: null }));
    } finally {
      setLoading(prev => ({ ...prev, [key]: false }));
    }
  };

  const formatJson = (obj: any): string => {
    return JSON.stringify(obj, null, 2);
  };

  const getStatusColor = (status: string): string => {
    switch (status?.toLowerCase()) {
      case 'success': return '#28a745';
      case 'healthy': return '#17a2b8';
      case 'error': return '#dc3545';
      default: return '#6c757d';
    }
  };

  return (
    <div className="api-test-container">
      <div className="api-test-header">
        <h1>🧪 API Testing Dashboard</h1>
        <p>Test all Bank Slip Reader API endpoints</p>
        <button
          className="back-button"
          onClick={() => window.location.href = '/'}
        >
          ← Back to Main App
        </button>
      </div>

      {error && (
        <div className="error-message">
          <strong>❌ Error:</strong> {error}
        </div>
      )}

      <div className="api-test-grid">
        {/* Hello World Test */}
        <div className="api-test-card">
          <div className="card-header">
            <h3>🌟 Simple Hello World</h3>
            <code>GET /api/hello.php</code>
          </div>
          <div className="card-actions">
            <button
              onClick={() => testEndpoint('hello.php', 'hello')}
              disabled={loading.hello}
              className="test-button"
            >
              {loading.hello ? 'Testing...' : 'Test API'}
            </button>
          </div>
          {responses.hello && (
            <div className="response-container">
              <div className="response-status">
                <span
                  className="status-badge"
                  style={{ backgroundColor: getStatusColor(responses.hello.status) }}
                >
                  {responses.hello.status}
                </span>
                <span className="timestamp">{responses.hello.timestamp}</span>
              </div>
              <pre className="json-response">{formatJson(responses.hello)}</pre>
            </div>
          )}
        </div>

        {/* API Test */}
        <div className="api-test-card">
          <div className="card-header">
            <h3>🔧 REST API Test</h3>
            <code>GET /api/test</code>
          </div>
          <div className="card-actions">
            <button
              onClick={() => testEndpoint('test', 'test')}
              disabled={loading.test}
              className="test-button"
            >
              {loading.test ? 'Testing...' : 'Test API'}
            </button>
          </div>
          {responses.test && (
            <div className="response-container">
              <div className="response-status">
                <span
                  className="status-badge"
                  style={{ backgroundColor: getStatusColor(responses.test.status) }}
                >
                  {responses.test.status}
                </span>
                <span className="timestamp">{responses.test.timestamp}</span>
              </div>
              <pre className="json-response">{formatJson(responses.test)}</pre>
            </div>
          )}
        </div>

        {/* Health Check */}
        <div className="api-test-card">
          <div className="card-header">
            <h3>💚 Health Check</h3>
            <code>GET /api/health</code>
          </div>
          <div className="card-actions">
            <button
              onClick={() => testEndpoint('health', 'health')}
              disabled={loading.health}
              className="test-button"
            >
              {loading.health ? 'Testing...' : 'Test API'}
            </button>
          </div>
          {responses.health && (
            <div className="response-container">
              <div className="response-status">
                <span
                  className="status-badge"
                  style={{ backgroundColor: getStatusColor(responses.health.status) }}
                >
                  {responses.health.status}
                </span>
                <span className="timestamp">{responses.health.timestamp}</span>
              </div>
              <pre className="json-response">{formatJson(responses.health)}</pre>
            </div>
          )}
        </div>

        {/* API Root */}
        <div className="api-test-card">
          <div className="card-header">
            <h3>📋 API Information</h3>
            <code>GET /api/</code>
          </div>
          <div className="card-actions">
            <button
              onClick={() => testEndpoint('', 'root')}
              disabled={loading.root}
              className="test-button"
            >
              {loading.root ? 'Testing...' : 'Test API'}
            </button>
          </div>
          {responses.root && (
            <div className="response-container">
              <div className="response-status">
                <span
                  className="status-badge"
                  style={{ backgroundColor: getStatusColor(responses.root.status) }}
                >
                  {responses.root.status}
                </span>
                <span className="timestamp">{responses.root.timestamp}</span>
              </div>
              <pre className="json-response">{formatJson(responses.root)}</pre>
            </div>
          )}
        </div>

        {/* Tesseract Test */}
        <div className="api-test-card">
          <div className="card-header">
            <h3>🔍 Tesseract OCR Test</h3>
            <code>GET /api/tesseract-test.php</code>
          </div>
          <div className="card-actions">
            <button
              onClick={() => testEndpoint('tesseract-test.php', 'tesseract')}
              disabled={loading.tesseract}
              className="test-button"
            >
              {loading.tesseract ? 'Testing...' : 'Test OCR'}
            </button>
          </div>
          {responses.tesseract && (
            <div className="response-container">
              <div className="response-status">
                <span
                  className="status-badge"
                  style={{ backgroundColor: getStatusColor(responses.tesseract.status) }}
                >
                  {responses.tesseract.data?.tesseract_status === 'working' ? 'Working' : 'Not Available'}
                </span>
                <span className="timestamp">{responses.tesseract.timestamp}</span>
              </div>
              <pre className="json-response">{formatJson(responses.tesseract)}</pre>
            </div>
          )}
        </div>
      </div>

      <div className="api-info">
        <h3>📊 API Endpoints Available:</h3>
        <div className="endpoints-list">
          <div className="endpoint-item">
            <code>GET /api/hello.php</code>
            <span>Simple hello world endpoint</span>
          </div>
          <div className="endpoint-item">
            <code>GET /api/test</code>
            <span>REST API test endpoint</span>
          </div>
          <div className="endpoint-item">
            <code>GET /api/health</code>
            <span>Health check endpoint</span>
          </div>
          <div className="endpoint-item">
            <code>GET /api/</code>
            <span>API information and documentation</span>
          </div>
          <div className="endpoint-item">
            <code>POST /api/verify-slip</code>
            <span>Slip verification (placeholder)</span>
          </div>
          <div className="endpoint-item">
            <code>POST /api/parse-slip</code>
            <span>Slip parsing (placeholder)</span>
          </div>
          <div className="endpoint-item">
            <code>GET /api/tesseract-test.php</code>
            <span>Test Tesseract OCR installation</span>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ApiTest;
