import { useState } from 'react';
import { createWorker } from 'tesseract.js';
import './App.css';
import { getOcrUrl } from './config';

function App() {
  const [selectedImage, setSelectedImage] = useState<string | null>(null);
  const [ocrText, setOcrText] = useState<string>('');
  const [isProcessing, setIsProcessing] = useState<boolean>(false);
  const [slipData, setSlipData] = useState<any>(null);

  const resetAll = () => {
    setSelectedImage(null);
    setOcrText('');
    setSlipData(null);
    // Reset file input
    const fileInput = document.getElementById('imageInput') as HTMLInputElement;
    if (fileInput) {
      fileInput.value = '';
    }
  };

  const handleImageUpload = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (e) => {
        setSelectedImage(e.target?.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  const processOCR = async () => {
    if (!selectedImage) return;

    setIsProcessing(true);
    try {
      const worker = await createWorker('tha+eng');
      const { data: { text } } = await worker.recognize(selectedImage);
      await worker.terminate();

      setOcrText(text);

      // Send OCR text to API for parsing
      const response = await fetch(getOcrUrl(), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ocr_text: text }),
      });

      const result = await response.json();

      if (result.status === 'success') {
        setSlipData(result.data.parsed_data);
      } else {
        throw new Error(result.message);
      }
    } catch (error) {
      console.error('OCR Error:', error);
      setOcrText('Error processing image');
      setSlipData(null);
    } finally {
      setIsProcessing(false);
    }
  };


  return (
    <div className="App">
      <h1>Bank Slip Reader</h1>

      {/* Image Upload Section */}
      <div className="image-section">
        <h2>Upload Bank Slip Image</h2>
        <div className="image-upload">
          <input
            type="file"
            accept="image/*"
            onChange={handleImageUpload}
            id="imageInput"
          />
          <label htmlFor="imageInput" className="upload-button">
            Choose Slip Image
          </label>
          {selectedImage && (
            <button onClick={processOCR} disabled={isProcessing}>
              {isProcessing ? 'Processing...' : 'Extract Text'}
            </button>
          )}
        </div>

        {selectedImage && (
          <div className="image-preview">
            <img src={selectedImage} alt="Selected bank slip" />
          </div>
        )}

        <div className="action-buttons">
          <button onClick={resetAll} className="reset-button">
            Reset
          </button>
          <button
            onClick={() => window.location.href = '/test-api'}
            className="test-api-button"
          >
            🧪 Test API
          </button>
        </div>
      </div>



      {/* OCR Results */}
      {slipData && (
        <div className="result">
          <h2>Extracted Slip Information:</h2>
          <div className="slip-info">
            <div className="info-row">
              <span className="label">Date:</span>
              <span className="value">{slipData.Date || 'Not found'}</span>
            </div>
            <div className="info-row">
              <span className="label">To Account:</span>
              <span className="value">{slipData.toAccount || 'Not found'}</span>
            </div>
            <div className="info-row">
              <span className="label">Biller:</span>
              <span className="value">{slipData.biller || 'Not found'}</span>
            </div>
            <div className="info-row">
              <span className="label">Amount:</span>
              <span className="value">{slipData.amount ? `${slipData.amount.toFixed(2)} THB` : 'Not found'}</span>
            </div>
          </div>
        </div>
      )}

      {/* Raw OCR Text */}
      {ocrText && (
        <div className="result">
          <h2>Raw OCR Text:</h2>
          <pre>{ocrText}</pre>
        </div>
      )}


    </div>
  );
}

export default App;
