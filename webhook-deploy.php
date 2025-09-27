<?php
/**
 * GitHub Webhook Deployment Handler
 * 
 * This script handles GitHub webhook payloads and triggers deployments
 * for the Sky Education Portal application.
 * 
 * Setup:
 * 1. Place this file in a web-accessible directory
 * 2. Configure GitHub webhook to point to this file
 * 3. Set the webhook secret in your environment
 * 4. Ensure proper file permissions and security
 */

// Configuration
define('WEBHOOK_SECRET', $_ENV['GITHUB_WEBHOOK_SECRET'] ?? 'your-webhook-secret-here');
define('DEPLOY_SCRIPT', '/var/www/sky-portal/deploy-from-github.sh');
define('LOG_FILE', '/var/log/webhook-deployment.log');
define('ALLOWED_BRANCHES', ['main', 'production']);
define('LOCK_FILE', '/tmp/webhook-deploy.lock');

// Headers for JSON response
header('Content-Type: application/json');

/**
 * Log messages with timestamp
 */
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents(LOG_FILE, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

/**
 * Send JSON response and exit
 */
function sendResponse($status, $message, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'timestamp' => date('c')
    ]);
    exit;
}

/**
 * Verify GitHub webhook signature
 */
function verifySignature($payload, $signature) {
    if (empty(WEBHOOK_SECRET)) {
        return true; // Skip verification if no secret is set (not recommended for production)
    }
    
    $calculatedSignature = 'sha256=' . hash_hmac('sha256', $payload, WEBHOOK_SECRET);
    return hash_equals($calculatedSignature, $signature);
}

/**
 * Check if deployment is already running
 */
function isDeploymentRunning() {
    if (file_exists(LOCK_FILE)) {
        $lockTime = filemtime(LOCK_FILE);
        $currentTime = time();
        
        // If lock file is older than 30 minutes, consider it stale
        if (($currentTime - $lockTime) > 1800) {
            unlink(LOCK_FILE);
            return false;
        }
        return true;
    }
    return false;
}

/**
 * Execute deployment script
 */
function executeDeploy($branch) {
    if (isDeploymentRunning()) {
        sendResponse('error', 'Deployment already in progress', 409);
    }
    
    // Create lock file
    file_put_contents(LOCK_FILE, getmypid());
    
    $command = escapeshellcmd(DEPLOY_SCRIPT) . ' ' . escapeshellarg($branch);
    $output = [];
    $returnCode = 0;
    
    logMessage("Executing deployment command: $command");
    
    // Execute deployment in background
    exec("$command > /tmp/deploy-output.log 2>&1 & echo $!", $output, $returnCode);
    
    if ($returnCode === 0 && !empty($output[0])) {
        $pid = $output[0];
        logMessage("Deployment started with PID: $pid");
        
        // Remove lock file after a short delay (deployment script will create its own)
        register_shutdown_function(function() {
            sleep(5);
            if (file_exists(LOCK_FILE)) {
                unlink(LOCK_FILE);
            }
        });
        
        return [
            'success' => true,
            'pid' => $pid,
            'message' => 'Deployment started successfully'
        ];
    } else {
        // Remove lock file on failure
        if (file_exists(LOCK_FILE)) {
            unlink(LOCK_FILE);
        }
        
        logMessage("Failed to start deployment. Return code: $returnCode");
        return [
            'success' => false,
            'error' => 'Failed to start deployment process'
        ];
    }
}

// Main execution starts here
try {
    // Check if this is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse('error', 'Only POST requests are allowed', 405);
    }
    
    // Get the payload
    $payload = file_get_contents('php://input');
    if (empty($payload)) {
        sendResponse('error', 'Empty payload received', 400);
    }
    
    // Verify signature if secret is configured
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
    if (!verifySignature($payload, $signature)) {
        logMessage('Invalid signature received');
        sendResponse('error', 'Invalid signature', 403);
    }
    
    // Parse JSON payload
    $data = json_decode($payload, true);
    if (!$data) {
        sendResponse('error', 'Invalid JSON payload', 400);
    }
    
    // Check if this is a push event
    $event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
    if ($event !== 'push') {
        logMessage("Ignoring non-push event: $event");
        sendResponse('ignored', "Event '$event' ignored");
    }
    
    // Extract branch name
    $ref = $data['ref'] ?? '';
    if (!preg_match('/^refs\/heads\/(.+)$/', $ref, $matches)) {
        sendResponse('ignored', 'Not a branch push');
    }
    
    $branch = $matches[1];
    
    // Check if this branch should trigger deployment
    if (!in_array($branch, ALLOWED_BRANCHES)) {
        logMessage("Ignoring push to non-deployable branch: $branch");
        sendResponse('ignored', "Branch '$branch' not configured for deployment");
    }
    
    // Log the deployment request
    $pusher = $data['pusher']['name'] ?? 'unknown';
    $commit = substr($data['head_commit']['id'] ?? 'unknown', 0, 8);
    $message = $data['head_commit']['message'] ?? 'No message';
    
    logMessage("Deployment requested - Branch: $branch, Pusher: $pusher, Commit: $commit");
    logMessage("Commit message: $message");
    
    // Security check: Verify the deploy script exists and is executable
    if (!file_exists(DEPLOY_SCRIPT)) {
        logMessage("Deploy script not found: " . DEPLOY_SCRIPT);
        sendResponse('error', 'Deploy script not found', 500);
    }
    
    if (!is_executable(DEPLOY_SCRIPT)) {
        logMessage("Deploy script not executable: " . DEPLOY_SCRIPT);
        sendResponse('error', 'Deploy script not executable', 500);
    }
    
    // Execute deployment
    $result = executeDeploy($branch);
    
    if ($result['success']) {
        logMessage("Deployment initiated successfully for branch: $branch");
        sendResponse('success', $result['message'], 200);
    } else {
        logMessage("Deployment failed: " . $result['error']);
        sendResponse('error', $result['error'], 500);
    }
    
} catch (Exception $e) {
    logMessage("Exception occurred: " . $e->getMessage());
    sendResponse('error', 'Internal server error', 500);
}
?>
