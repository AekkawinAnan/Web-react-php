import { useState } from 'react';
import { createWorker } from 'tesseract.js';
import './App.css';

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
      const parsed = parseSlipFromText(text);
      setSlipData(parsed);
    } catch (error) {
      console.error('OCR Error:', error);
      setOcrText('Error processing image');
    } finally {
      setIsProcessing(false);
    }
  };

  const parseSlipFromText = (text: string) => {
    // Clean and normalize the text
    const cleanText = text.replace(/\s+/g, ' ').trim();

    // Enhanced patterns for multiple bank formats

    // Date patterns (support both BE and AD years)
    const datePattern1 = /(\d{1,2})\s*(ม\.ค\.|ก\.พ\.|มี\.ค\.|เม\.ย\.|พ\.ค\.|มิ\.ย\.|ก\.ค\.|ส\.ค\.|ก\.ย\.|ต\.ค\.|พ\.ย\.|ธ\.ค\.)\s*(\d{4})/i;
    const datePattern2 = /(\d{1,2})\s*(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s*(\d{4})/i;
    const timePattern = /(\d{1,2}):(\d{2})/;

    // Account number patterns (multiple formats)
    const accountPattern1 = /(\d{3}-\d{1}-\d{4}-\d{1})/g; // xxx-x-xxxx-x
    const accountPattern2 = /(\d{3}-\d{3}-\d{3}-\d{1})/g; // xxx-xxx-xxx-x
    const accountPattern3 = /(\d{3}-\d{8}-\d{1})/g; // xxx-xxxxxxxx-x
    const accountPattern4 = /(xxx-xxx\d{3}-\d{1})/g; // xxx-xxx###-#

    // Amount patterns
    const amountPattern1 = /(\d{1,3}(?:,\d{3})*(?:\.\d{2})?)\s*บาท/i;
    const amountPattern2 = /(\d{1,3}(?:,\d{3})*(?:\.\d{2})?)\s*THB/i;
    const amountPattern3 = /จำนวนเงิน\s*(\d{1,3}(?:,\d{3})*(?:\.\d{2})?)/i;

    // Reference number patterns (various lengths)
    const refPattern1 = /รหัสอ้างอิง[:\s]*([A-Za-z0-9]{15,})/i;
    const refPattern2 = /เลขอ้างอิง[:\s]*([A-Za-z0-9]{15,})/i;
    const refPattern3 = /รหัส[:\s]*([A-Za-z0-9]{10,})/i;
    const refPattern4 = /Ref[:\s]*([A-Za-z0-9]{10,})/i;
    const refPattern5 = /หมายเลข[:\s]*(\d{15,})/i;

    // Biller information patterns
    const billerPattern1 = /ร้านค้า[:\s]*([^(]+)/i;
    const billerPattern2 = /ผู้รับเงิน[:\s]*([^(]+)/i;
    const billerPattern3 = /Biller[:\s]*([^(]+)/i;
    const billerIdPattern = /Biller ID[:\s]*(\d+)/i;
    const serviceCodePattern = /รหัสบริการ[:\s]*([A-Za-z0-9]+)/i;

    // Bank name patterns
    const bankNamePattern = /(SCB|ธนาคารไทยพาณิชย์|กสิกรไทย|KBANK|BBL|KTB|TBANK|UOB)/i;

    // Find all matches
    const dateMatch1 = cleanText.match(datePattern1);
    const dateMatch2 = cleanText.match(datePattern2);
    const timeMatch = cleanText.match(timePattern);

    const accounts1 = cleanText.match(accountPattern1) || [];
    const accounts2 = cleanText.match(accountPattern2) || [];
    const accounts3 = cleanText.match(accountPattern3) || [];
    const accounts4 = cleanText.match(accountPattern4) || [];
    const allAccounts = [...accounts1, ...accounts2, ...accounts3, ...accounts4];

    const amountMatch1 = cleanText.match(amountPattern1);
    const amountMatch2 = cleanText.match(amountPattern2);
    const amountMatch3 = cleanText.match(amountPattern3);

    const refMatch1 = cleanText.match(refPattern1);
    const refMatch2 = cleanText.match(refPattern2);
    const refMatch3 = cleanText.match(refPattern3);
    const refMatch4 = cleanText.match(refPattern4);
    const refMatch5 = cleanText.match(refPattern5);

    const billerMatch1 = cleanText.match(billerPattern1);
    const billerMatch2 = cleanText.match(billerPattern2);
    const billerMatch3 = cleanText.match(billerPattern3);
    const billerIdMatch = cleanText.match(billerIdPattern);
    const serviceCodeMatch = cleanText.match(serviceCodePattern);

    const bankNameMatch = cleanText.match(bankNamePattern);

    // Determine best date match
    let dateMatch = dateMatch1 || dateMatch2;
    let date = null;
    if (dateMatch) {
      if (dateMatch[2] && dateMatch[2].length > 3) {
        // Thai month name
        date = `${dateMatch[1]} ${dateMatch[2]} ${dateMatch[3]}`;
      } else {
        // English month name
        date = `${dateMatch[1]} ${dateMatch[2]} ${dateMatch[3]}`;
      }
    }

    // Determine best amount match
    let amount = null;
    if (amountMatch1) {
      amount = parseFloat(amountMatch1[1].replace(/,/g, ''));
    } else if (amountMatch2) {
      amount = parseFloat(amountMatch2[1].replace(/,/g, ''));
    } else if (amountMatch3) {
      amount = parseFloat(amountMatch3[1].replace(/,/g, ''));
    }

    // Determine best reference match
    let reference = null;
    if (refMatch1) reference = refMatch1[1];
    else if (refMatch2) reference = refMatch2[1];
    else if (refMatch3) reference = refMatch3[1];
    else if (refMatch4) reference = refMatch4[1];
    else if (refMatch5) reference = refMatch5[1];

    // Determine biller information
    let biller = null;
    if (billerMatch1) biller = billerMatch1[1].trim();
    else if (billerMatch2) biller = billerMatch2[1].trim();
    else if (billerMatch3) biller = billerMatch3[1].trim();

    return {
      date: date,
      time: timeMatch ? `${timeMatch[1]}:${timeMatch[2]}` : null,
      fromAccount: allAccounts[0] || null,
      toAccount: allAccounts[1] || null,
      amount: amount,
      reference: reference,
      biller: biller,
      billerId: billerIdMatch ? billerIdMatch[1] : null,
      serviceCode: serviceCodeMatch ? serviceCodeMatch[1] : null,
      bankName: bankNameMatch ? bankNameMatch[1] : null,
      rawText: text
    };
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
              <span className="label">Bank:</span>
              <span className="value">{slipData.bankName || 'Not found'}</span>
            </div>
            <div className="info-row">
              <span className="label">Date:</span>
              <span className="value">{slipData.date || 'Not found'}</span>
            </div>
            <div className="info-row">
              <span className="label">Time:</span>
              <span className="value">{slipData.time || 'Not found'}</span>
            </div>
            <div className="info-row">
              <span className="label">From Account:</span>
              <span className="value">{slipData.fromAccount || 'Not found'}</span>
            </div>
            <div className="info-row">
              <span className="label">To/Biller:</span>
              <span className="value">{slipData.biller || slipData.toAccount || 'Not found'}</span>
            </div>
            <div className="info-row">
              <span className="label">Biller ID:</span>
              <span className="value">{slipData.billerId || 'Not found'}</span>
            </div>
            <div className="info-row">
              <span className="label">Service Code:</span>
              <span className="value">{slipData.serviceCode || 'Not found'}</span>
            </div>
            <div className="info-row">
              <span className="label">Amount:</span>
              <span className="value">{slipData.amount ? `${slipData.amount.toFixed(2)} THB` : 'Not found'}</span>
            </div>
            <div className="info-row">
              <span className="label">Reference:</span>
              <span className="value">{slipData.reference || 'Not found'}</span>
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
