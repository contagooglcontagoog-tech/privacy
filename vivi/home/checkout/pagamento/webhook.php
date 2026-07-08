<?php
// webhook.php - VERSÃO COMPLETA COM UTMIFY CORRETO

declare(strict_types=1);
date_default_timezone_set('America/Recife');

/* =================== CONFIGURAÇÕES =================== */

// Diretório onde o payment.php salva os arquivos de transação e confirmação
$DIR_PAGAMENTOS = __DIR__ . '/pagamentos';

// Arquivo de log para registrar todas as requisições do webhook
$LOG_FILE = __DIR__ . '/webhook_pixgo.log';

/* CONFIG UTMIFY */
require_once __DIR__ . '/utmfy.php';

/* CONFIG FACEBOOK CAPI */

wlog('--- Nova Requisição de Webhook Recebida ---');
wlog('Método: ' . ($_SERVER['REQUEST_METHOD'] ?? 'N/A'));

// 1. Valida método POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    wlog('ERRO: Método inválido. Respondendo com 405.');
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed.']);
    exit;
}

// 2. Lê o corpo da requisição
$raw = file_get_contents('php://input');
if (!$raw || trim($raw) === '') {
    wlog('ERRO: Corpo da requisição vazio. Respondendo com 400.');
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Empty body.']);
    exit;
}
wlog('RAW Payload: ' . $raw);

// 3. Decodifica JSON
$data = json_decode($raw, true);
if (!is_array($data)) {
    wlog('ERRO: Payload não é JSON válido. Respondendo com 400.');
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON.']);
    exit;
}

// 4. Extrai o evento e dados
$event = $data['event'] ?? null;
$pixData = $data['data'] ?? [];
$tid    = $pixData['payment_id'] ?? null;
$external_id_fb = $pixData['external_id'] ?? null;

// 5. Só processa evento payment.completed (pago)
if ($event !== 'payment.completed') {
    wlog("Evento ignorado: '{$event}' para a transação '{$tid}'. Respondendo com 200.");
    http_response_code(200);
    echo json_encode(['ok' => true, 'message' => 'Event not payment.completed, ignored.']);
    exit;
}

wlog("Webhook payment.completed recebido da PixGo para transação: '{$tid}'.");

// 6. Sanitiza o ID da transação para uso em nomes de arquivo
$tidClean = preg_replace('/[^a-zA-Z0-9_\-]/', '', $tid ?? '');
if ($tidClean === '') {
    wlog("ERRO: 'transaction_id' inválido após limpeza: '{$tid}'.");
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid transaction_id format.']);
    exit;
}

// 7. Carrega os metadados salvos pelo payment.php (UTMs + cliente + createdAt)
$metaFile   = $DIR_PAGAMENTOS . '/' . $tidClean . '_meta.json';
$meta       = [];
$customer   = [];
$tracking   = [];
$createdAt  = gmdate('Y-m-d H:i:s'); // fallback: data atual se o meta não existir
$amount     = floatval($data['amount'] ?? 0);
$productName= $data['product_name'] ?? 'Privacy - paola';

if (file_exists($metaFile)) {
    $meta = json_decode(file_get_contents($metaFile), true) ?? [];
    $customer    = $meta['customer']       ?? [];
    $tracking    = $meta['tracking']       ?? [];
    $createdAt   = $meta['created_at_utc'] ?? $createdAt;
    $amount      = floatval($meta['amount']       ?? $amount);
    $productName = $meta['product_name']   ?? $productName;
    wlog("META carregado com sucesso do arquivo: {$metaFile}");
} else {
    // Fallback: usa os dados que vieram no próprio webhook da PixGo
    wlog("AVISO: Arquivo meta não encontrado ({$metaFile}). Usando dados do webhook da PixGo como fallback.");
    $customer = [
        'name'  => $pixData['payer_name']  ?? '',
        'email' => $pixData['payer_email'] ?? '',
        'phone' => $pixData['payer_phone'] ?? '',
        'cpf'   => $pixData['payer_cpf']   ?? '',
    ];
    $tracking = [
        'src' => null, 'sck' => null,
        'utm_source' => null, 'utm_campaign' => null,
        'utm_medium' => null, 'utm_content' => null, 'utm_term' => null,
    ];
}

$approvedDate = gmdate('Y-m-d H:i:s'); // Momento exato do pagamento (UTC)

// 8. AÇÃO 1: Envia evento "PIX PAGO" para a Utmify (SE AINDA NÃO FOI ENVIADO)
$lockFile = $DIR_PAGAMENTOS . '/' . $tidClean . '_paid.lock';
if (!file_exists($lockFile)) {
    file_put_contents($lockFile, $approvedDate);
    sendToUtmfy($tid, 'paid', $amount, $customer, $tracking, $productName, $createdAt, $approvedDate);
    wlog("UTMIFY: Evento PAID disparado via webhook.");
} else {
    wlog("UTMIFY: Evento PAID ignorado no webhook, pois já foi disparado pelo check_status.php.");
}

// 9. AÇÃO 2: Envia evento Purchase para o Facebook CAPI (SE AINDA NÃO FOI ENVIADO)
$fbLockFile = $DIR_PAGAMENTOS . '/' . $tidClean . '_fb_purchase.lock';
if (!file_exists($fbLockFile)) {
    file_put_contents($fbLockFile, $approvedDate);
    
    // Inicializa CAPI (precisa das configs de index.php ou config.php)
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/FacebookCAPI.php';
    $fbCapi = new FacebookCAPI($CONFIG_FACEBOOK['PIXEL_ID'], $CONFIG_FACEBOOK['ACCESS_TOKEN']);

    $fbCapi->sendEvent('Purchase', [
        'name'              => $customer['name']  ?? '',
        'email'             => $customer['email'] ?? '',
        'phone'             => $customer['phone'] ?? '',
        'external_id'       => $meta['external_id'] ?? ($customer['cpf'] ?? ''),
        'client_ip_address' => $customer['client_ip_address'] ?? null,
        'country'           => 'br'
    ], [
        'value'        => $amount,
        'currency'     => 'BRL',
        'content_name' => $productName
    ], null, $tid);
    
    wlog("FACEBOOK CAPI: Evento PURCHASE disparado via webhook.");
} else {
    wlog("FACEBOOK CAPI: Evento PURCHASE ignorado no webhook, já disparado anteriormente.");
}

// 10. AÇÃO 3: Salva arquivo de confirmação de pagamento (para check_status.php)
$confirmPath = $DIR_PAGAMENTOS . '/' . $tidClean . '.json';
file_put_contents($confirmPath, $raw);
wlog("SUCESSO: Arquivo de confirmação salvo em: " . $confirmPath);

// 11. Responde para a PixGo com 200 OK
http_response_code(200);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true, 'message' => 'Webhook PixGo received and processed successfully.']);

exit;