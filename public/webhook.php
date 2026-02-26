<?php
/**
 * Auto Deploy Webhook Handler - Absensi Frontend
 * 
 * Webhook ini akan dipanggil oleh GitHub setiap kali ada push ke branch main
 * URL: https://presensi.globalintermedia.online/webhook.php
 */

// Load Laravel bootstrap for environment variables
require_once __DIR__ . '/../vendor/autoload.php';

// Configuration
$secret = getenv('WEBHOOK_SECRET') ?: 'koekontol';  // Default secret
$branch = 'refs/heads/main';
$deployScript = __DIR__ . '/../deploy.sh';

// Log file
$logFile = __DIR__ . '/../storage/logs/deployment.log';

/**
 * Write to log file
 */
function writeLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

/**
 * Verify GitHub signature
 */
function verifyGitHubSignature($payload, $signature) {
    global $secret;
    
    if (empty($signature)) {
        return false;
    }
    
    $hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);
    return hash_equals($hash, $signature);
}

// Get request info
$headers = getallheaders();
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

writeLog("=== New Webhook Request ===");
writeLog("IP: " . $_SERVER['REMOTE_ADDR']);
writeLog("User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'));

// Detect webhook source
$isGitHub = isset($headers['X-GitHub-Event']);

if (!$isGitHub) {
    writeLog("ERROR: Unknown webhook source");
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Unknown webhook source']);
    exit;
}

// Verify signature
$signature = $headers['X-Hub-Signature-256'] ?? '';
$verified = verifyGitHubSignature($payload, $signature);
$event = $headers['X-GitHub-Event'];
$ref = $data['ref'] ?? '';

writeLog("GitHub webhook detected - Event: $event, Ref: $ref");

// Only process push events to main branch
if ($event !== 'push' || $ref !== $branch) {
    writeLog("SKIP: Not a push to main branch");
    echo json_encode(['status' => 'skipped', 'message' => 'Not a push to main branch']);
    exit;
}

if (!$verified) {
    writeLog("ERROR: Invalid signature/token");
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
    exit;
}

// Execute deployment script
if (!file_exists($deployScript)) {
    writeLog("ERROR: Deployment script not found: $deployScript");
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Deployment script not found']);
    exit;
}

writeLog("Starting deployment...");

// Execute deploy script in background
$output = [];
$returnVar = 0;

// Change to script directory
chdir(dirname($deployScript));

// Execute script
exec("bash deploy.sh >> $logFile 2>&1 &", $output, $returnVar);

if ($returnVar === 0) {
    writeLog("SUCCESS: Deployment script executed");
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Deployment started',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    writeLog("ERROR: Deployment script failed with code $returnVar");
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Deployment failed',
        'code' => $returnVar
    ]);
}

writeLog("=== End Webhook Request ===\n");
