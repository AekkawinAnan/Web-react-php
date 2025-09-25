<?php
echo "🧪 Testing Bank Slip Reader API\n";
echo "================================\n\n";

// Test 1: Test endpoint
echo "1. Testing GET /api/test\n";
$ch = curl_init('http://localhost:8000/api/test');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Status: $http_code (Success)\n";
    $data = json_decode($response, true);
    echo "📝 Message: " . $data['message'] . "\n";
    echo "🕒 Timestamp: " . $data['timestamp'] . "\n";
    echo "🔖 Version: " . $data['version'] . "\n";
} else {
    echo "❌ Status: $http_code (Failed)\n";
    echo "Response: $response\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test 2: Health endpoint
echo "2. Testing GET /api/health\n";
$ch = curl_init('http://localhost:8000/api/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Status: $http_code (Success)\n";
    $data = json_decode($response, true);
    echo "💚 Health: " . $data['status'] . "\n";
    echo "📝 Message: " . $data['message'] . "\n";
    echo "🕒 Timestamp: " . $data['timestamp'] . "\n";
    echo "🐘 PHP Version: " . $data['server_info']['php_version'] . "\n";
} else {
    echo "❌ Status: $http_code (Failed)\n";
    echo "Response: $response\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test 3: Root API endpoint
echo "3. Testing GET /api/\n";
$ch = curl_init('http://localhost:8000/api/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "✅ Status: $http_code (Success)\n";
    $data = json_decode($response, true);
    echo "📝 Message: " . $data['message'] . "\n";
    echo "🔖 Version: " . $data['version'] . "\n";
    echo "📋 Available endpoints:\n";
    foreach ($data['endpoints'] as $endpoint => $description) {
        echo "   • $endpoint - $description\n";
    }
} else {
    echo "❌ Status: $http_code (Failed)\n";
    echo "Response: $response\n";
}

echo "\n" . str_repeat("-", 50) . "\n\n";

// Test 4: Invalid endpoint
echo "4. Testing invalid endpoint GET /api/invalid\n";
$ch = curl_init('http://localhost:8000/api/invalid');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 404) {
    echo "✅ Status: $http_code (Expected - Not Found)\n";
    $data = json_decode($response, true);
    echo "📝 Message: " . $data['message'] . "\n";
} else {
    echo "❌ Status: $http_code (Unexpected)\n";
    echo "Response: $response\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎉 API Testing Complete!\n";
echo "📚 To start the API server, run:\n";
echo "   php -S localhost:8000 -t api/\n";
echo "📱 Then test at: http://localhost:8000/api/test\n";
?>
