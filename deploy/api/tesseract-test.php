<?php
// Bank Slip Reader - Tesseract OCR Test API
// This endpoint tests if Tesseract OCR is properly installed and working

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $testResult = testTesseractInstallation();

    $response = [
        'status' => $testResult['success'] ? 'success' : 'warning',
        'message' => $testResult['message'],
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '1.0.0',
        'data' => [
            'tesseract_status' => $testResult['success'] ? 'working' : 'not_available',
            'test_details' => $testResult,
            'installation_guide' => getInstallationGuide(),
            'system_info' => getSystemInfo()
        ]
    ];

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    $errorResponse = [
        'status' => 'error',
        'message' => 'Tesseract test failed: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'tesseract_status' => 'error',
            'error_details' => $e->getMessage(),
            'installation_guide' => getInstallationGuide(),
            'system_info' => getSystemInfo()
        ]
    ];

    http_response_code(500);
    echo json_encode($errorResponse, JSON_PRETTY_PRINT);
}

/**
 * Test Tesseract OCR installation and functionality
 */
function testTesseractInstallation() {
    $result = [
        'success' => false,
        'message' => '',
        'details' => []
    ];

    // Test 1: Check if TesseractOCR class exists
    $tesseractClassExists = class_exists('TesseractOCR');
    $result['details']['php_class_available'] = $tesseractClassExists;

    if (!$tesseractClassExists) {
        $result['message'] = 'TesseractOCR PHP class not found. Install via Composer: composer require thiagoalessio/tesseract_ocr';
        return $result;
    }

    // Test 2: Check if tesseract binary is available
    $tesseractBinary = checkTesseractBinary();
    $result['details']['tesseract_binary'] = $tesseractBinary;

    if (!$tesseractBinary['available']) {
        $result['message'] = 'Tesseract binary not found. Install system package: ' . $tesseractBinary['install_command'];
        return $result;
    }

    // Test 3: Check available languages
    $languages = checkTesseractLanguages();
    $result['details']['available_languages'] = $languages;

    if (empty($languages)) {
        $result['message'] = 'No Tesseract languages found. Install Thai language pack.';
        return $result;
    }

    // Test 4: Try actual OCR processing
    $ocrTest = testOCRLive();
    $result['details']['ocr_test'] = $ocrTest;

    if (!$ocrTest['success']) {
        $result['message'] = 'OCR test failed: ' . $ocrTest['error'];
        return $result;
    }

    // All tests passed
    $result['success'] = true;
    $result['message'] = 'Tesseract OCR is working correctly! Ready for bank slip processing.';

    return $result;
}

/**
 * Check if tesseract binary is available
 */
function checkTesseractBinary() {
    $paths = ['/usr/bin/tesseract', '/usr/local/bin/tesseract', '/opt/homebrew/bin/tesseract'];

    foreach ($paths as $path) {
        if (file_exists($path) && is_executable($path)) {
            $version = shell_exec($path . ' --version 2>/dev/null');
            return [
                'available' => true,
                'path' => $path,
                'version' => trim($version ?: 'Unknown')
            ];
        }
    }

    // Try to find tesseract in PATH
    $version = shell_exec('tesseract --version 2>/dev/null');
    if ($version) {
        return [
            'available' => true,
            'path' => 'tesseract (in PATH)',
            'version' => trim($version)
        ];
    }

    return [
        'available' => false,
        'path' => 'Not found',
        'version' => 'Not available',
        'install_command' => 'sudo apt-get install tesseract-ocr  # Ubuntu/Debian'
    ];
}

/**
 * Check available Tesseract languages
 */
function checkTesseractLanguages() {
    $languages = [];

    // Try to get list of languages
    $output = shell_exec('tesseract --list-langs 2>/dev/null');
    if ($output) {
        $lines = explode("\n", trim($output));
        // First line is "List of available languages", rest are language codes
        $languages = array_slice($lines, 1);
        $languages = array_filter(array_map('trim', $languages));
    }

    return array_values($languages);
}

/**
 * Test OCR with actual processing
 */
function testOCRLive() {
    try {
        // Create a simple test image data (text)
        $testText = "ธนาคารไทยพาณิชย์ SCB\n23 ก.ย. 2568\nจำนวนเงิน: 50.00 บาท";

        // Create temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'ocr_test_');
        file_put_contents($tempFile . '.txt', $testText);

        // Try OCR processing
        $tesseract = new TesseractOCR($tempFile . '.txt');
        $tesseract->lang('tha+eng');
        $result = $tesseract->run();

        // Clean up
        unlink($tempFile . '.txt');

        return [
            'success' => true,
            'input_text' => $testText,
            'ocr_output' => trim($result),
            'accuracy' => calculateAccuracy($testText, $result)
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'input_text' => $testText ?? 'Test text'
        ];
    }
}

/**
 * Calculate OCR accuracy (simple comparison)
 */
function calculateAccuracy($original, $ocrResult) {
    $original = strtolower(trim(preg_replace('/\s+/', '', $original)));
    $ocrResult = strtolower(trim(preg_replace('/\s+/', '', $ocrResult)));

    if (empty($original)) return 0;

    $originalChars = str_split($original);
    $ocrChars = str_split($ocrResult);

    $matches = 0;
    $total = count($originalChars);

    foreach ($originalChars as $i => $char) {
        if (isset($ocrChars[$i]) && $ocrChars[$i] === $char) {
            $matches++;
        }
    }

    return round(($matches / $total) * 100, 2);
}

/**
 * Get system information
 */
function getSystemInfo() {
    return [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'operating_system' => php_uname(),
        'extensions_loaded' => get_loaded_extensions(),
        'composer_autoload' => file_exists('vendor/autoload.php') ? 'Available' : 'Not found'
    ];
}

/**
 * Get installation guide based on system
 */
function getInstallationGuide() {
    $os = php_uname();

    if (stripos($os, 'Linux') !== false) {
        if (stripos($os, 'Ubuntu') !== false || stripos($os, 'Debian') !== false) {
            return [
                'os' => 'Ubuntu/Debian Linux',
                'steps' => [
                    '1. Install Tesseract: sudo apt-get update && sudo apt-get install tesseract-ocr tesseract-ocr-tha',
                    '2. Install PHP library: composer require thiagoalessio/tesseract_ocr',
                    '3. Test: tesseract --version'
                ]
            ];
        } else {
            return [
                'os' => 'Linux (Other)',
                'steps' => [
                    '1. Install Tesseract: sudo yum install tesseract tesseract-langpack-tha  # CentOS/RHEL',
                    '2. Or: sudo apt-get install tesseract-ocr tesseract-ocr-tha  # Ubuntu/Debian',
                    '3. Install PHP library: composer require thiagoalessio/tesseract_ocr',
                    '4. Test: tesseract --version'
                ]
            ];
        }
    } elseif (stripos($os, 'Darwin') !== false) {
        return [
            'os' => 'macOS',
            'steps' => [
                '1. Install Tesseract: brew install tesseract tesseract-lang',
                '2. Install Thai language: brew install tesseract-lang',
                '3. Install PHP library: composer require thiagoalessio/tesseract_ocr',
                '4. Test: tesseract --version'
            ]
        ];
    } elseif (stripos($os, 'Windows') !== false) {
        return [
            'os' => 'Windows',
            'steps' => [
                '1. Download Tesseract from: https://github.com/UB-Mannheim/tesseract/wiki',
                '2. Install Thai language pack from: https://github.com/tesseract-ocr/tessdata',
                '3. Install PHP library: composer require thiagoalessio/tesseract_ocr',
                '4. Add tesseract to PATH environment variable',
                '5. Test: tesseract --version'
            ]
        ];
    }

    return [
        'os' => 'Unknown',
        'steps' => [
            '1. Install Tesseract OCR for your operating system',
            '2. Install PHP Tesseract library: composer require thiagoalessio/tesseract_ocr',
            '3. Test installation: tesseract --version'
        ]
    ];
}
?>
