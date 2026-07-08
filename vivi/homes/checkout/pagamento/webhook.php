<?php
// webhook.php - VERSÃO COMPLETA E PRONTA PARA DICE API

declare(strict_types=1);
date_default_timezone_set('America/Recife');

/* =================== CONFIGURAÇÕES =================== */

// Diretório para salvar os arquivos de confirmação de pagamento.
$DIR_PAGAMENTOS = __DIR__ . '/pagamentos';

// Arquivo de log para registrar todas as requisições do webhook. ESSENCIAL PARA DEBUG.
$LOG_FILE = __DIR__ . '/webhook_dice.log';

/* CONFIG UTMFY (OPCIONAL) */
$UTMFY_CONFIG = [
    'API_TOKEN' => '', // <-- COLOQUE SEU TOKEN DA UTMFY AQUI (SE USAR)
    'API_URL'   => 'https://api.utmify.com.br/api-credentials/orders'
];

/* CONFIG FACEBOOK CAPI */
require_once __DIR__ . '/FacebookCAPI.php';
require_once __DIR__ . '/TrackingHelper.php';
$fbPixelId = '1361922504987404';
$fbAccessToken = 'EAAI6i9o9NFoBQTOMwGRtADYHaOsBIBa6c8Ng9v8vYPN1DDhak1VUIijzMTxYjsh0RXolpdzYYwMPEjZArkXPj97p37sLZCfsVyoEtCtjXYWsar2YmxD3Lo7bBwafljzWvh132fGHVZBz658S6YjPc4VMmgVHcedGOc3nuU8h08S8ZB1CDICfB5H4HE25jrZCNKgZDZD';
$fbCapi = new FacebookCAPI($fbPixelId, $fbAccessToken);

/* CONFIG DICE API (PARA VERIFICAÇÃO) */
$DICE_CONFIG = [
    'CLIENT_ID' => 'dice_live_a4295cf941e7c10798a06dffecd9c84e',
    'CLIENT_SECRET' => 'dicesk_live_38d6a46a6f5de7523a0d4e70daccd6686eaa74c53e5ce3d1',
    'BASE_URL' => 'https://api.use-dice.com'
];

/* ================= FIM DAS CONFIGURAÇÕES ================= */


// Garante que o diretório de pagamentos exista.
if (!is_dir($DIR_PAGAMENTOS)) {
    mkdir($DIR_PAGAMENTOS, 0775, true);
}

/**
 * Função de Log. Registra uma mensagem com data e hora no arquivo de log.
 * @param string $msg A mensagem a ser registrada.
 */
function wlog(string $msg): void {
    global $LOG_FILE;
    file_put_contents($LOG_FILE, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
}

/**
 * Envia a ATUALIZAÇÃO de compra (status 'paid') para a API da Utmfy.
 * @param array $diceWebhookData Dados recebidos no webhook da Dice.
 */
function sendPurchaseToUtmfy(array $diceWebhookData, array $utmfyConfig): void {
    if (empty($utmfyConfig['API_TOKEN'])) {
        wlog('UTMFY: API Token não configurado. Envio ignorado.');
        return;
    }

    $transactionId = $diceWebhookData['transaction_id'] ?? null;
    if (!$transactionId) {
        wlog('UTMFY ERRO: Transaction ID não encontrado no webhook da Dice.');
        return;
    }

    wlog("UTMFY: Iniciando envio 'paid' para transação {$transactionId}.");
    
    $amountInCents = (int) (($diceWebhookData['amount'] ?? 0) * 100);
    $payer = $diceWebhookData['payer'] ?? [];
    $now_utc = gmdate('Y-m-d H:i:s');

    $payload = [
        'orderId' => (string) $transactionId,
        'platform' => 'Dice',
        'paymentMethod' => 'pix',
        'status' => 'paid',
        'approvedDate' => $now_utc,
        'customer' => [
            'name' => $payer['name'] ?? null,
            'email' => $payer['email'] ?? null,
            'document' => $payer['document'] ?? null,
        ],
        'products' => [[
            'id' => 'privado_mensal9',
            'planId' => 'privado_mensal9',
            'name' => $diceWebhookData['product_name'] ?? 'Privacy - paola',
            'quantity' => 1,
            'priceInCents' => $amountInCents
        ]],
        'commission' => ['totalPriceInCents' => $amountInCents, 'currency' => 'BRL'],
    ];

    $ch = curl_init($utmfyConfig['API_URL']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json', 'x-api-token: ' . $utmfyConfig['API_TOKEN']],
        CURLOPT_TIMEOUT => 20,
    ]);
    $response = curl_exec($ch); $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    wlog("UTMFY: Resposta do envio 'paid' - HTTP $httpCode: " . $response);
}

/* =========================================================
   INÍCIO DA LÓGICA DO WEBHOOK - NÃO EDITAR ABAIXO
   ========================================================= */

// 1. Loga a requisição inicial
wlog('--- Nova Requisição de Webhook Recebida ---');
wlog('Método: ' . ($_SERVER['REQUEST_METHOD'] ?? 'N/A'));

// 2. Valida o método da requisição (deve ser POST)
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    wlog('ERRO: Método inválido. Respondendo com 405.');
    http_response_code(405); // Method Not Allowed
    echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
    exit;
}

// 3. Pega o corpo da requisição (payload)
$raw = file_get_contents('php://input');
if (!$raw || trim($raw) === '') {
    wlog('ERRO: Corpo da requisição vazio. Respondendo com 400.');
    http_response_code(400); // Bad Request
    echo json_encode(['ok' => false, 'error' => 'Empty body.']);
    exit;
}
wlog('RAW Payload: ' . $raw);

// 4. Decodifica o JSON
$data = json_decode($raw, true);
if (!is_array($data)) {
    wlog('ERRO: Payload não é um JSON válido. Respondendo com 400.');
    http_response_code(400); // Bad Request
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON.']);
    exit;
}

// 5. Extrai os dados importantes: status e ID da transação
$status = $data['status'] ?? null;
$tid = $data['transaction_id'] ?? null;

// 6. LÓGICA PRINCIPAL: Só executa a ação se o status for 'COMPLETED'
if ($status !== 'COMPLETED') {
    wlog("Status ignorado: '{$status}' para a transação '{$tid}'. Nenhuma ação necessária. Respondendo com 200.");
    http_response_code(200); // OK, recebemos, mas não faremos nada.
    echo json_encode(['ok' => true, 'message' => 'Status not completed, ignored.']);
    exit;
}

// 7. Se chegamos aqui, o pagamento foi anunciado como COMPLETED pelo webhook.
// Vamos RE-VERIFICAR via API para segurança extra, conforme solicitado.
wlog("Webhook anunciou status '{$status}' para a transação '{$tid}'. Iniciando verificação oficial...");

if (empty($tid)) {
    wlog("ERRO: 'transaction_id' vazio no payload.");
    http_response_code(400);
    exit;
}

// 7.1 AUTENTICAÇÃO NA DICE PARA VERIFICAR STATUS
$authUrl = $DICE_CONFIG['BASE_URL'] . '/api/v1/auth/login';
$authCh = curl_init($authUrl);
curl_setopt_array($authCh, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode([
        'client_id' => $DICE_CONFIG['CLIENT_ID'],
        'client_secret' => $DICE_CONFIG['CLIENT_SECRET']
    ]),
    CURLOPT_SSL_VERIFYPEER => false
]);
$authResp = curl_exec($authCh);
$authCode = curl_getinfo($authCh, CURLINFO_HTTP_CODE);
curl_close($authCh);

if ($authCode !== 200) {
    wlog("ERRO: Falha na autenticação Dice (HTTP {$authCode}). Abortando.");
    exit;
}

$token = json_decode($authResp, true)['token'] ?? json_decode($authResp, true)['access_token'] ?? null;
if (!$token) {
    wlog("ERRO: Token não encontrado na resposta de autenticação.");
    exit;
}

// 7.2 CONSULTA O STATUS REAL DA TRANSAÇÃO
$statusUrl = $DICE_CONFIG['BASE_URL'] . '/api/v1/transactions/getStatusTransac/' . $tid;
$statusCh = curl_init($statusUrl);
curl_setopt_array($statusCh, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Authorization: Bearer $token"],
    CURLOPT_SSL_VERIFYPEER => false
]);
$statusResp = curl_exec($statusCh);
$statusHttp = curl_getinfo($statusCh, CURLINFO_HTTP_CODE);
curl_close($statusCh);

$statusData = json_decode($statusResp, true);
$realStatus = $statusData['status'] ?? null;

wlog("Dice API retornou status real: '{$realStatus}' (HTTP {$statusHttp})");

if ($realStatus !== 'COMPLETED') {
    wlog("ALERTA: Status real '{$realStatus}' não é COMPLETED. Ignorando evento de Purchase.");
    http_response_code(200);
    exit;
}

// 8. Se chegamos aqui, o pagamento foi CONFIRMADO pela API.
wlog("PAGAMENTO VERIFICADO COM SUCESSO! Transação: '{$tid}'.");


// AÇÃO 1: Enviar notificação para Utmfy (se configurado)
sendPurchaseToUtmfy($data, $UTMFY_CONFIG);

// AÇÃO 1.1: Enviar notificação para Facebook CAPI
$payer = $data['payer'] ?? [];
$trackingContext = TrackingHelper::loadContext($tid) ?? [];

$fbCapi->sendEvent('Purchase', [
    'name' => $payer['name'] ?? ($trackingContext['name'] ?? ''),
    'email' => $payer['email'] ?? ($trackingContext['email'] ?? ''),
    'phone' => $payer['document'] ?? ($trackingContext['phone'] ?? ''), // Usando documento como fallback
    'external_id' => $trackingContext['external_id'] ?? null,
    'fbp' => $trackingContext['fbp'] ?? null,
    'fbc' => $trackingContext['fbc'] ?? null,
    'client_ip_address' => $trackingContext['ip'] ?? null,
    'client_user_agent' => $trackingContext['ua'] ?? null,
], [
    'value' => floatval($data['amount'] ?? 0),
    'currency' => 'BRL',
    'content_name' => $data['product_name'] ?? 'Plano Privacy',
    'transaction_id' => $tid
]);

// AÇÃO 2: Salva o arquivo localmente para confirmar o pagamento
$tidClean = preg_replace('/[^a-zA-Z0-9_\-]/', '', $tid);
if ($tidClean === '') {
    wlog("ERRO: 'transaction_id' inválido após limpeza: '{$tid}'.");
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid transaction_id format.']);
    exit;
}

$path = $DIR_PAGAMENTOS . '/' . $tidClean . '.json';
file_put_contents($path, $raw);
wlog("SUCESSO: Arquivo de confirmação salvo em: " . $path);

// 8. Responde para a Dice que o webhook foi processado com sucesso.
http_response_code(200);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true, 'message' => 'Webhook received and processed successfully.']);

exit;