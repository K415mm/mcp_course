<?php

use App\Models\User;
use App\Models\CsSession;
use App\Http\Controllers\NeptuneApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Request::capture());

// Retrieve session
$sessionCode = 'ZRITQT';
$session = CsSession::where('code', $sessionCode)->first();
if (!$session) {
    echo "Session not found\n";
    exit(1);
}

// Log in as admin
$user = User::where('role', 'admin')->first();
if (!$user) {
    echo "Admin user not found\n";
    exit(1);
}
Auth::login($user);

// Execute state endpoint
try {
    $ctrl = app(NeptuneApiController::class);
    $resp = $ctrl->state(request(), $sessionCode);
    echo "STATUS CODE: " . $resp->getStatusCode() . "\n";
    $data = json_decode($resp->getContent(), true);
    echo "KEYS: " . implode(", ", array_keys($data)) . "\n";
    echo "DECISIONS COUNT: " . count($data['decisions'] ?? []) . "\n";
    echo "SUCCESS!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
