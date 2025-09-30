<?php
// Bank Slip Reader - OCR Text Processing API
// This endpoint processes raw OCR text and extracts bank slip information

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle GET requests for API information
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response = [
        'status' => 'success',
        'message' => 'Bank Slip Reader API',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
        'description' => 'Backend API for Bank Slip Reader application',
        'endpoints' => [
            'GET /api/test' => 'Test endpoint - returns hello world',
            'GET /api/health' => 'Health check endpoint',
            'POST /api/verify-slip' => 'Verify slip data (JSON input)',
            'POST /api/parse-slip' => 'Parse slip image (multipart/form-data)',
            'POST /api/ocr' => 'Process image with OCR and extract slip data'
        ],
        'documentation' => 'See README.md for full API documentation'
    ];
    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit();
}

try {
    // Get raw input data
    $input = file_get_contents('php://input');
    $requestData = json_decode($input, true);

    // Check if we have JSON data or form data
    if ($requestData && isset($requestData['ocr_text'])) {
        $ocrText = $requestData['ocr_text'];
    } elseif (isset($_POST['ocr_text'])) {
        $ocrText = $_POST['ocr_text'];
    } else {
        throw new Exception('No OCR text provided - test. Please provide ocr_text in JSON body or form data', 400);
    }

    // Validate OCR text
    if (empty(trim($ocrText))) {
        throw new Exception('OCR text cannot be empty', 400);
    }

    // Process the OCR text
    $parsedData = parseSlipFromText($ocrText);

    // Prepare response
    $response = [
        'status' => 'success',
        'message' => 'OCR text processed successfully',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
        'data' => [
            'input_info' => [
                'text_length' => strlen($ocrText),
                'processed_at' => date('Y-m-d H:i:s')
            ],
            'ocr_result' => [
                'raw_text' => $ocrText,
                'confidence' => 1.0, // Since we're using provided text
                'processing_time' => 0.01 // Very fast since no OCR processing
            ],
            'parsed_data' => $parsedData
        ]
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    $errorResponse = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s'),
        'code' => $e->getCode() ?: 500
    ];

    http_response_code($e->getCode() ?: 500);
    echo json_encode($errorResponse, JSON_PRETTY_PRINT);
}

/**
 * Process image with OCR
 * This function can be enhanced with real OCR processing
 */
function processImageWithOCR($imagePath) {
    $startTime = microtime(true);

    try {
        // Option 1: Use Tesseract PHP library (if installed)
        if (class_exists('TesseractOCR')) {
            return processWithTesseract($imagePath, $startTime);
        }

        // Option 2: Use Google Cloud Vision API
        if (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            return processWithGoogleVision($imagePath, $startTime);
        }

        // Option 3: Use external OCR service
        if (getenv('OCR_API_KEY')) {
            return processWithExternalService($imagePath, $startTime);
        }

        // Option 4: Use PHP-OCR library
        if (file_exists('vendor/autoload.php')) {
            return processWithPhpOcr($imagePath, $startTime);
        }

        // Fallback: Use mock data for demonstration
        return processWithMockData($imagePath, $startTime);

    } catch (Exception $e) {
        throw new Exception('OCR processing failed: ' . $e->getMessage());
    }
}

/**
 * Process image with Tesseract OCR (PHP library)
 */
function processWithTesseract($imagePath, $startTime) {
    try {
        $tesseract = new TesseractOCR($imagePath);
        $tesseract->lang('tha+eng'); // Thai + English
        $tesseract->config('tessedit_char_whitelist', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyzกขคฆงจฉชซฌญฎฏฐฑฒณดตถทธนบปผฝพฟภมยรลวศษสหฬอฮะัาำิีึืุูเแโใไาำๆฯ.()- ');
        $text = $tesseract->run();

        $processingTime = microtime(true) - $startTime;

        return [
            'text' => $text,
            'confidence' => 0.85, // Tesseract doesn't provide confidence scores directly
            'processing_time' => round($processingTime, 2)
        ];
    } catch (Exception $e) {
        throw new Exception('Tesseract OCR failed: ' . $e->getMessage());
    }
}

/**
 * Process image with Google Cloud Vision API
 */
function processWithGoogleVision($imagePath, $startTime) {
    try {
        // Read image file
        $imageData = file_get_contents($imagePath);

        // Prepare request to Google Cloud Vision
        $requestData = [
            'requests' => [
                [
                    'image' => [
                        'content' => base64_encode($imageData)
                    ],
                    'features' => [
                        [
                            'type' => 'TEXT_DETECTION',
                            'maxResults' => 1
                        ]
                    ]
                ]
            ]
        ];

        $jsonData = json_encode($requestData);

        // Make API call
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $jsonData
            ]
        ]);

        $response = file_get_contents('https://vision.googleapis.com/v1/images:annotate?key=' . getenv('GOOGLE_VISION_API_KEY'), false, $context);

        if ($response === false) {
            throw new Exception('Failed to call Google Vision API');
        }

        $result = json_decode($response, true);

        if (isset($result['responses'][0]['textAnnotations'][0]['description'])) {
            $text = $result['responses'][0]['textAnnotations'][0]['description'];
            $confidence = $result['responses'][0]['textAnnotations'][0]['boundingPoly']['vertices'][0]['x'] ?? 0.9; // Mock confidence
        } else {
            $text = '';
            $confidence = 0.0;
        }

        $processingTime = microtime(true) - $startTime;

        return [
            'text' => $text,
            'confidence' => $confidence,
            'processing_time' => round($processingTime, 2)
        ];
    } catch (Exception $e) {
        throw new Exception('Google Vision API failed: ' . $e->getMessage());
    }
}

/**
 * Process image with external OCR service
 */
function processWithExternalService($imagePath, $startTime) {
    try {
        // Example: OCR.space API
        $apiKey = getenv('OCR_API_KEY');
        $url = 'https://api.ocr.space/parse/image';

        $imageData = file_get_contents($imagePath);
        $base64Image = base64_encode($imageData);

        $postData = [
            'apikey' => $apiKey,
            'base64Image' => $base64Image,
            'language' => 'tha+eng',
            'isOverlayRequired' => false
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($postData)
            ]
        ]);

        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            throw new Exception('Failed to call OCR service');
        }

        $result = json_decode($response, true);

        if (isset($result['ParsedResults'][0]['ParsedText'])) {
            $text = $result['ParsedResults'][0]['ParsedText'];
            $confidence = $result['ParsedResults'][0]['TextOverlay']['HasOverlay'] ? 0.9 : 0.7;
        } else {
            $text = '';
            $confidence = 0.0;
        }

        $processingTime = microtime(true) - $startTime;

        return [
            'text' => $text,
            'confidence' => $confidence,
            'processing_time' => round($processingTime, 2)
        ];
    } catch (Exception $e) {
        throw new Exception('External OCR service failed: ' . $e->getMessage());
    }
}

/**
 * Process image with PHP-OCR library
 */
function processWithPhpOcr($imagePath, $startTime) {
    try {
        // This would require installing a PHP OCR library
        // For now, return mock data
        return processWithMockData($imagePath, $startTime);
    } catch (Exception $e) {
        throw new Exception('PHP OCR library failed: ' . $e->getMessage());
    }
}

/**
 * Generate mock OCR text based on image filename
 * This is for demonstration purposes when no OCR service is available
 */
function processWithMockData($imagePath, $startTime) {
    $filename = basename($imagePath);

    // Mock OCR text based on common bank slip patterns
    $mockTexts = [
        'scb' => "ธนาคารไทยพาณิชย์ SCB\n23 ก.ย. 2568 - 12:33\nรหัสอ้างอิง: 2025092303qN9srV2J8UjWSbl\nจาก นาย เอกกวิน อนันตราคิติ\nเลขที่บัญชี: xxx-xxx998-9\nไปยัง ร้านค้าออนไลน์ มหาเจ๊ (ออน ฟสโม้)\nBiller ID: 099400016489130\nรหัสบริการ: MHG1\nจำนวนเงิน: 50.00 บาท",
        'kbank' => "ธนาคารกสิกรไทย\nวันที่ 23 ก.ย. 2568\nเวลา 12:33:45\nเลขที่อ้างอิง: 2025092303ABC123DEF456\nจากบัญชี: xxx-x-xxxxx-x\nไปยังบัญชี: xxx-x-xxxxx-x\nจำนวนเงิน: 1,250.00 บาท",
        'default' => "ธนาคารกรุงเทพ BBL\nวันที่ 23 กันยายน 2568\nเวลา 12:33\nเลขอ้างอิง: 2025092303XYZ789ABC\nจากบัญชี: xxx-xxx-xxxx-x\nไปยังบัญชี: xxx-xxx-xxxx-x\nจำนวนเงิน: 500.00 บาท"
    ];

    // Determine mock text based on filename
    $lowerFilename = strtolower($filename);

    if (strpos($lowerFilename, 'scb') !== false) {
        $text = $mockTexts['scb'];
    } elseif (strpos($lowerFilename, 'kbank') !== false || strpos($lowerFilename, 'kbank') !== false) {
        $text = $mockTexts['kbank'];
    } else {
        $text = $mockTexts['default'];
    }

    $processingTime = microtime(true) - $startTime;

    return [
        'text' => $text,
        'confidence' => 0.85, // Mock confidence score
        'processing_time' => round($processingTime, 2)
    ];
}

/**
 * Parse OCR text to extract bank slip information
 * This uses the same logic as the frontend
 */
function parseSlipFromText($text) {
    // Clean and normalize the text
    $cleanText = trim($text);

    // Date patterns (support both BE and AD years, 2 or 4 digit years)
    $datePattern1 = '/(\d{1,2})\s*(ม\.ค\.|ก\.พ\.|มี\.ค\.|เม\.ย\.|พ\.ค\.|มิ\.ย\.|ก\.ค\.|ส\.ค\.|ก\.ย\.|ก\.ข\.|ต\.ค\.|พ\.ย\.|ธ\.ค\.)\s*(\d{2,4})/i';
    $datePattern2 = '/(\d{1,2})\s*(ม\.ค\.|ก\.พ\.|มี\.ค\.|เม\.ย\.|พ\.ค\.|มิ\.ย\.|ก\.ค\.|ส\.ค\.|ก\.ย\.|ก\.ข\.|ต\.ค\.|พ\.ย\.|ธ\.ค\.)\s*(\d{4})/i';
    $datePattern3 = '/(\d{1,2})\s*(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s*(\d{2,4})/i';
    $datePattern4 = '/(\d{1,2})\s*(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s*(\d{4})/i';
    $timePattern = '/(\d{1,2}):(\d{2})/';

    // Account number patterns (multiple formats)
    $accountPattern1 = '/(\d{3}-\d{1}-\d{4}-\d{1})/'; // xxx-x-xxxx-x
    $accountPattern2 = '/(\d{3}-\d{3}-\d{3}-\d{1})/'; // xxx-xxx-xxx-x
    $accountPattern3 = '/(\d{3}-\d{8}-\d{1})/'; // xxx-xxxxxxxx-x
    $accountPattern4 = '/(xxx-xxx\d{3}-\d{1})/'; // xxx-xxx###-#

    // Amount patterns
    $amountPattern1 = '/(\d{1,3}(?:,\d{3})*(?:\.\d{2})?)\s*บาท/i';
    $amountPattern2 = '/(\d{1,3}(?:,\d{3})*(?:\.\d{2})?)\s*THB/i';
    $amountPattern3 = '/จำนวนเงิน\s*(\d{1,3}(?:,\d{3})*(?:\.\d{2})?)/i';
    $amountPattern4 = '/จํานวนเงิน\s+(\d+(?:\.\d{2})?)/i';

    // Reference number patterns (various lengths)
    $refPattern1 = '/รหัสอ้างอิง[:\s]*([A-Za-z0-9]{10,})/i';
    $refPattern2 = '/เลขอ้างอิง[:\s]*([A-Za-z0-9]{10,})/i';
    $refPattern3 = '/รหัส[:\s]*([A-Za-z0-9]{10,})/i';
    $refPattern4 = '/Ref[:\s]*([A-Za-z0-9]{10,})/i';
    $refPattern5 = '/หมายเลข[:\s]*(\d{10,})/i';

    // Biller information patterns
    $billerPattern1 = '/ร้านค้า[:\s]*([^(]+)/i';
    $billerPattern2 = '/ผู้รับเงิน[:\s]*([^(]+)/i';
    $billerPattern3 = '/Biller[:\s]*([^(]+)/i';
    $billerIdPattern = '/Biller ID[:\s]*(\d+)/i';
    $serviceCodePattern = '/รหัสบริการ[:\s]*([A-Za-z0-9]+)/i';

    // Sender and receiver name patterns
    $senderPattern1 = '/จาก[:\s]*([^\n\r]+)/i';
    $senderPattern2 = '/ผู้ส่ง[:\s]*([^\n\r]+)/i';
    $receiverPattern1 = '/ไปยัง[:\s]*([^\n\r]+)/i';
    $receiverPattern2 = '/ผู้รับ[:\s]*([^\n\r]+)/i';
    $receiverPattern3 = '/ถึง[:\s]*([^\n\r]+)/i';

    // Transaction type patterns
    $transactionTypePattern = '/(โอน|ชำระ|จ่าย|ถอน|ฝาก|Transfer|Payment|Withdraw|Deposit)/i';

    // Bank name patterns
    $bankNamePattern = '/(SCB|ธนาคารไทยพาณิชย์|กสิกรไทย|KBANK|BBL|KTB|TBANK|UOB|ธนาคารกรุงเทพ|ธนาคารกรุงไทย|ธนาคารทหารไทย|ธนาคารยูโอบี)/i';

    // Find all matches
    $dateMatch1 = preg_match($datePattern1, $cleanText, $dateMatches1) ? $dateMatches1 : null;
    $dateMatch2 = preg_match($datePattern2, $cleanText, $dateMatches2) ? $dateMatches2 : null;
    $dateMatch3 = preg_match($datePattern3, $cleanText, $dateMatches3) ? $dateMatches3 : null;
    $dateMatch4 = preg_match($datePattern4, $cleanText, $dateMatches4) ? $dateMatches4 : null;
    $timeMatch = preg_match($timePattern, $cleanText, $timeMatches) ? $timeMatches : null;

    preg_match_all($accountPattern1, $cleanText, $accounts1);
    preg_match_all($accountPattern2, $cleanText, $accounts2);
    preg_match_all($accountPattern3, $cleanText, $accounts3);
    preg_match_all($accountPattern4, $cleanText, $accounts4);
    $allAccounts = array_merge($accounts1[0], $accounts2[0], $accounts3[0], $accounts4[0]);

    $amountMatch1 = preg_match($amountPattern1, $cleanText, $amountMatches1) ? $amountMatches1 : null;
    $amountMatch2 = preg_match($amountPattern2, $cleanText, $amountMatches2) ? $amountMatches2 : null;
    $amountMatch3 = preg_match($amountPattern3, $cleanText, $amountMatches3) ? $amountMatches3 : null;
    $amountMatch4 = preg_match($amountPattern4, $cleanText, $amountMatches4) ? $amountMatches4 : null;

    $refMatch1 = preg_match($refPattern1, $cleanText, $refMatches1) ? $refMatches1 : null;
    $refMatch2 = preg_match($refPattern2, $cleanText, $refMatches2) ? $refMatches2 : null;
    $refMatch3 = preg_match($refPattern3, $cleanText, $refMatches3) ? $refMatches3 : null;
    $refMatch4 = preg_match($refPattern4, $cleanText, $refMatches4) ? $refMatches4 : null;
    $refMatch5 = preg_match($refPattern5, $cleanText, $refMatches5) ? $refMatches5 : null;

    $billerMatch1 = preg_match($billerPattern1, $cleanText, $billerMatches1) ? $billerMatches1 : null;
    $billerMatch2 = preg_match($billerPattern2, $cleanText, $billerMatches2) ? $billerMatches2 : null;
    $billerMatch3 = preg_match($billerPattern3, $cleanText, $billerMatches3) ? $billerMatches3 : null;
    $billerIdMatch = preg_match($billerIdPattern, $cleanText, $billerIdMatches) ? $billerIdMatches : null;
    $serviceCodeMatch = preg_match($serviceCodePattern, $cleanText, $serviceCodeMatches) ? $serviceCodeMatches : null;

    $senderMatch1 = preg_match($senderPattern1, $cleanText, $senderMatches1) ? $senderMatches1 : null;
    $senderMatch2 = preg_match($senderPattern2, $cleanText, $senderMatches2) ? $senderMatches2 : null;
    $receiverMatch1 = preg_match($receiverPattern1, $cleanText, $receiverMatches1) ? $receiverMatches1 : null;
    $receiverMatch2 = preg_match($receiverPattern2, $cleanText, $receiverMatches2) ? $receiverMatches2 : null;
    $receiverMatch3 = preg_match($receiverPattern3, $cleanText, $receiverMatches3) ? $receiverMatches3 : null;

    $transactionTypeMatch = preg_match($transactionTypePattern, $cleanText, $transactionTypeMatches) ? $transactionTypeMatches : null;

    $bankNameMatch = preg_match($bankNamePattern, $cleanText, $bankNameMatches) ? $bankNameMatches : null;

    // Determine best date match
    $dateMatch = $dateMatch1 ?: $dateMatch2 ?: $dateMatch3 ?: $dateMatch4;
    $date = null;
    if ($dateMatch) {
        $day = $dateMatch[1];
        $month = $dateMatch[2];
        $year = $dateMatch[3];

        // Convert 2-digit year to 4-digit
        if (strlen($year) == 2) {
            if (preg_match('/ม\.ค\.|ก\.พ\.|มี\.ค\.|เม\.ย\.|พ\.ค\.|มิ\.ย\.|ก\.ค\.|ส\.ค\.|ก\.ย\.|ต\.ค\.|พ\.ย\.|ธ\.ค\./i', $month)) {
                // Thai month, assume BE
                $year = '25' . $year;
            } else {
                // English month, assume AD
                $year = '20' . $year;
            }
        }

        // Normalize month abbreviations
        $month = str_replace('ก.ข.', 'ก.ย.', $month);

        $date = sprintf('%s %s %s', $day, $month, $year);
    }

    // Determine best amount match
    $amount = null;
    if ($amountMatch1) {
        $amount = floatval(str_replace(',', '', $amountMatch1[1]));
    } elseif ($amountMatch2) {
        $amount = floatval(str_replace(',', '', $amountMatch2[1]));
    } elseif ($amountMatch3) {
        $amount = floatval(str_replace(',', '', $amountMatch3[1]));
    } elseif ($amountMatch4) {
        $amount = floatval(str_replace(',', '', $amountMatch4[1]));
    }

    // Determine best reference match
    $reference = null;
    if ($refMatch1) $reference = $refMatch1[1];
    elseif ($refMatch2) $reference = $refMatch2[1];
    elseif ($refMatch3) $reference = $refMatch3[1];
    elseif ($refMatch4) $reference = $refMatch4[1];
    elseif ($refMatch5) $reference = $refMatch5[1];

    // Determine biller information
    $biller = null;
    if ($billerMatch1) $biller = trim($billerMatch1[1]);
    elseif ($billerMatch2) $biller = trim($billerMatch2[1]);
    elseif ($billerMatch3) $biller = trim($billerMatch3[1]);

    // Determine sender name
    $sender = null;
    if ($senderMatch1) $sender = trim($senderMatch1[1]);
    elseif ($senderMatch2) $sender = trim($senderMatch2[1]);

    // Determine receiver name
    $receiver = null;
    if ($receiverMatch1) $receiver = trim($receiverMatch1[1]);
    elseif ($receiverMatch2) $receiver = trim($receiverMatch2[1]);
    elseif ($receiverMatch3) $receiver = trim($receiverMatch3[1]);

    // Determine transaction type
    $transactionType = null;
    if ($transactionTypeMatch) {
        $type = trim($transactionTypeMatch[1]);
        // Normalize to English
        if (preg_match('/โอน|Transfer/i', $type)) $transactionType = 'Transfer';
        elseif (preg_match('/ชำระ|จ่าย|Payment/i', $type)) $transactionType = 'Payment';
        elseif (preg_match('/ถอน|Withdraw/i', $type)) $transactionType = 'Withdraw';
        elseif (preg_match('/ฝาก|Deposit/i', $type)) $transactionType = 'Deposit';
        else $transactionType = $type;
    }

    return [
        'date' => $date,
        'time' => $timeMatch ? sprintf('%s:%s', $timeMatch[1], $timeMatch[2]) : null,
        'fromAccount' => $allAccounts[0] ?? null,
        'toAccount' => $allAccounts[1] ?? null,
        'biller' => $biller,
        'amount' => $amount
    ];
}
?>
