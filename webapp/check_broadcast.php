<?php
// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// Get a neptune session
$session = App\Models\CsSession::where('scenario_key', 'neptune_strike')->latest()->first();
if (!$session) {
    echo "NO NEPTUNE SESSION FOUND\n";
    exit(1);
}
echo "Session code: " . $session->code . "\n";
echo "Moderator ID: " . ($session->moderator_id ?? 'NULL') . "\n";

// Test the broadcast endpoint with curl internally
$url = "http://localhost/neptune/{$session->code}/api/broadcast";

// Simulate request with no auth
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['message' => 'test', 'type' => 'info']));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-CSRF-TOKEN: test',
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

$headers = substr($result, 0, $headerSize);
$body = substr($result, $headerSize);

echo "\nHTTP Status: $httpCode\n";
echo "First 300 chars of response:\n";
echo substr($body, 0, 300) . "\n";

// Check if broadcast method exists in getModeratorSession
echo "\n--- getModeratorSession check ---\n";
$controller = $app->make(App\Http\Controllers\NeptuneApiController::class);
echo "Controller instantiated: OK\n";
echo "Method 'broadcast' exists: " . (method_exists($controller, 'broadcast') ? 'YES' : 'NO') . "\n";

// Check Auth::check
echo "Auth::check (no session): " . (Illuminate\Support\Facades\Auth::check() ? 'logged in' : 'NOT logged in') . "\n";

// Identify what getModeratorSession does with no auth
echo "\n--- isModerator logic ---\n";
$refClass = new ReflectionClass($controller);
$method = $refClass->getMethod('getModeratorSession');
$method->setAccessible(true);
try {
    $result2 = $method->invoke($controller, $session->code);
    echo "getModeratorSession returned: " . get_class($result2) . "\n";
} catch (\Exception $e) {
    echo "getModeratorSession threw: " . $e->getMessage() . "\n";
}
