<?php
// Lightweight GitHub deploy webhook.
// Verifies HMAC signature, then runs deploy.sh in background.
// Configure in GitHub: Settings → Webhooks → Add webhook
//   Payload URL: https://sutomoschool.com/deploy.php
//   Content type: application/json
//   Secret: (value of DEPLOY_SECRET env var below, or hardcoded)
//   Events: Just the push event.

$SECRET = getenv('DEPLOY_SECRET') ?: 'CHANGE_ME_TO_A_LONG_RANDOM_STRING';
$BRANCH = 'refs/heads/principal-module';
$DEPLOY_SCRIPT = '/home/sutomosc/sutomoschool/deploy.sh';
$LOG_FILE = '/home/sutomosc/sutomoschool/storage/logs/deploy.log';

http_response_code(202);
header('Content-Type: text/plain');

$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (!$sigHeader || !str_starts_with($sigHeader, 'sha256=')) {
    http_response_code(401);
    exit("missing signature\n");
}

$expected = 'sha256=' . hash_hmac('sha256', $payload, $SECRET);
if (!hash_equals($expected, $sigHeader)) {
    http_response_code(401);
    exit("bad signature\n");
}

$data = json_decode($payload, true);
if (($data['ref'] ?? '') !== $BRANCH) {
    exit("ignored (branch " . ($data['ref'] ?? 'unknown') . ")\n");
}

// Fire and forget — run deploy in background, fully detached from Apache.
// nohup + redirected stdin/out/err + & + disown ensures suPHP/Apache doesn't reap it.
@file_put_contents($LOG_FILE, "\n[" . date('c') . "] webhook trigger (push " . substr(($data['after'] ?? ''), 0, 7) . ")\n", FILE_APPEND);
$cmd = sprintf(
    'nohup /bin/bash %s >> %s 2>&1 < /dev/null &',
    escapeshellarg($DEPLOY_SCRIPT),
    escapeshellarg($LOG_FILE)
);
shell_exec($cmd);

echo "deploy triggered\n";
