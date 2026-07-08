<?php
// pagamento/check_status.php - SÓ VERIFICA O STATUS E RETORNA JSON
// VERSÃO COM LOGGING PARA DEBUG

header('Content-Type: application/json');

require_once __DIR__ . '/utmfy.php';

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

// --- PROCESSAMENTO ---

// 1. CONFIGURAÇÕES PIXGO
require_once __DIR__ . '/config.php';
$pixgoKey = $CONFIG_PIXGO['API_KEY'];
$pixgoUrl = $CONFIG_PIXGO['API_URL'] . '/payment/' . $tid . '/status';

// 2. CONSULTA STATUS NA PIXGO
$statusCh = curl_init($pixgoUrl);
curl_setopt_array($statusCh, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'X-API-Key: ' . $pixgoKey
    ],
    CURLOPT_SSL_VERIFYPEER => false
]);
$statusResponse = curl_exec($statusCh);
$statusCode = curl_getinfo($statusCh, CURLINFO_HTTP_CODE);
$statusError = curl_error($statusCh);
curl_close($statusCh);

log_check("RESPOSTA STATUS PIXGO: HTTP $statusCode - Resp: $statusResponse");

if ($statusCode !== 200) {
    log_check("ERRO STATUS API PIXGO: Falha ao consultar status. CurlErr: $statusError");
}

// 3. SE O PAGAMENTO ESTIVER APROVADO, DISPARA UTMIFY IMEDIATAMENTE!
if ($statusResponse) {
    $respData = json_decode($statusResponse, true);
    $currentStatus = strtoupper($respData['status'] ?? '');
    
    if ($currentStatus === 'COMPLETED' || $currentStatus === 'PAID' || $currentStatus === 'APPROVED' || $currentStatus === 'SUCCEEDED') {
        $DIR_PAGAMENTOS = __DIR__ . '/pagamentos';
        $lockFile = $DIR_PAGAMENTOS . '/' . $tid . '_paid.lock';
        
        // Se ainda não disparamos o UTMify por aqui
        if (!file_exists($lockFile)) {
            // Cria o lock imediatamente para evitar duplicidade em requisições paralelas
            file_put_contents($lockFile, date('Y-m-d H:i:s'));
            
            wlog_utmfy("PAGAMENTO APROVADO detectado via check_status.php para transação: {$tid}");
            
            // Carrega os dados salvos pelo payment.php
            $metaFile = $DIR_PAGAMENTOS . '/' . $tid . '_meta.json';
            if (file_exists($metaFile)) {
                $meta = json_decode(file_get_contents($metaFile), true) ?? [];
                
                $amount      = floatval($meta['amount'] ?? 0);
                $customer    = $meta['customer'] ?? [];
                $tracking    = $meta['tracking'] ?? [];
                $productName = $meta['product_name'] ?? 'Privacy - Produto';
                $createdAt   = $meta['created_at_utc'] ?? gmdate('Y-m-d H:i:s');
                $approvedDate= gmdate('Y-m-d H:i:s');
                
                sendToUtmfy($tid, 'paid', $amount, $customer, $tracking, $productName, $createdAt, $approvedDate);
                
                // DISPARA O FACEBOOK CAPI IMEDIATAMENTE!
                require_once __DIR__ . '/FacebookCAPI.php';
                require_once __DIR__ . '/config.php';
                $fbCapi = new FacebookCAPI($CONFIG_FACEBOOK['PIXEL_ID'], $CONFIG_FACEBOOK['ACCESS_TOKEN']);
                
                $fbCapi->sendEvent('Purchase', [
                    'name'              => $customer['name']  ?? '',
                    'email'             => $customer['email'] ?? '',
                    'phone'             => $customer['phone'] ?? '',
                    'external_id'       => $meta['external_id'] ?? ($customer['cpf'] ?? ''),
                    'client_ip_address' => $customer['client_ip_address'] ?? null,
                    'country'           => 'br'
                ], [
                    'value'          => $amount,
                    'currency'       => 'BRL',
                    'content_name'   => $productName,
                    'transaction_id' => $tid,
                ], null, $tid);
            }
        }
    }
    echo $statusResponse;
} else {
    echo json_encode(['status' => 'ERROR', 'message' => 'Empty response from API']);
}
