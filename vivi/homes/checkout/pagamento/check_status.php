<?php
// pagamento/check_status.php - SÓ VERIFICA O STATUS E RETORNA JSON
// VERSÃO COM LOGGING PARA DEBUG

header('Content-Type: application/json');

// Função de log para debug
function log_check($msg) {
    $logFile = __DIR__ . '/check_status.log';
    file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
}

if (!isset($_GET['tid'])) {
    $msg = 'ID da transação não fornecido.';
    log_check("ERRO: $msg");
    echo json_encode(['status' => 'ERROR', 'message' => $msg]);
    exit;
}

$tid = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['tid']);
log_check("Iniciando verificação para Transaction ID: $tid");

// --- CONFIGURAÇÕES ---
$clientId = 'dice_live_804dfb1c44f9fbe9d335e5c2452e5b3f';
$clientSecret = 'dicesk_live_6d659cda9cf2a36a363b0157b4cdd0e9ae6bead7adb5884b';
$baseUrl = "https://api.use-dice.com";
// --------------------

// 1. AUTENTICAÇÃO
$authCh = curl_init($baseUrl . '/api/v1/auth/login');
curl_setopt_array($authCh, [
    CURLOPT_RETURNTRANSFER => true, 
    CURLOPT_POST => true, 
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'], 
    CURLOPT_POSTFIELDS => json_encode(['client_id' => $clientId, 'client_secret' => $clientSecret]), 
    CURLOPT_SSL_VERIFYPEER => false
]);
$authResponse = curl_exec($authCh);
$authCode = curl_getinfo($authCh, CURLINFO_HTTP_CODE);
$authError = curl_error($authCh);
curl_close($authCh);

if ($authCode !== 200) {
    log_check("ERRO AUTH: HTTP $authCode - Resp: $authResponse - CurlErr: $authError");
    echo json_encode(['status' => 'ERROR', 'message' => 'Auth failed', 'debug_http' => $authCode]);
    exit;
}

$tokenData = json_decode($authResponse, true);
$token = $tokenData['token'] ?? $tokenData['access_token'] ?? null;

if (!$token) {
    log_check("ERRO TOKEN: Token não encontrado na resposta: $authResponse");
    echo json_encode(['status' => 'ERROR', 'message' => 'Token not found']);
    exit;
}

// 2. CONSULTA STATUS
$statusUrl = $baseUrl . '/api/v1/transactions/getStatusTransac/' . $tid;
$statusCh = curl_init($statusUrl);
curl_setopt_array($statusCh, [
    CURLOPT_RETURNTRANSFER => true, 
    CURLOPT_HTTPHEADER => ["Authorization: Bearer $token"], 
    CURLOPT_SSL_VERIFYPEER => false
]);
$statusResponse = curl_exec($statusCh);
$statusCode = curl_getinfo($statusCh, CURLINFO_HTTP_CODE);
$statusError = curl_error($statusCh);
curl_close($statusCh);

log_check("RESPOSTA STATUS: HTTP $statusCode - Resp: $statusResponse");

if ($statusCode !== 200) {
    log_check("ERRO STATUS API: Falha ao consultar status. CurlErr: $statusError");
}

// Retorna a resposta da API (que já deve ser JSON) ou um erro formatado
if ($statusResponse) {
    echo $statusResponse;
} else {
    echo json_encode(['status' => 'ERROR', 'message' => 'Empty response from API']);
}
