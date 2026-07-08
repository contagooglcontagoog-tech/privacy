<?php
// pagamento/create_payment.php - SÓ GERA O PAGAMENTO E RETORNA JSON

header('Content-Type: application/json'); // Resposta sempre será em formato de dados

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método não permitido.']);
    exit;
}

// --- CONFIGURAÇÕES ---
require_once __DIR__ . '/config.php';
$pixgoUrl = $CONFIG_PIXGO['API_URL'] . '/payment/create';
$pixgoKey = $CONFIG_PIXGO['API_KEY'];
// --------------------

// 1. CRIAÇÃO DO PAGAMENTO NA PIXGO
$payload = [
    "amount"        => floatval($_POST['amount']),
    "description"   => $_POST['product_name'] ?? 'Produto',
    "customer_name" => $_POST['name'] ?? '',
    "customer_cpf"  => preg_replace('/\D/', '', $_POST['cpf'] ?? ''),
    "customer_email"=> $_POST['email'] ?? '',
    "external_id"   => $_POST['external_id'] ?? ''
];

$ch = curl_init($pixgoUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'X-API-Key: ' . $pixgoKey
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$paymentData = json_decode($response, true);

if ($httpCode !== 201 || !($paymentData['success'] ?? false)) {
    echo json_encode(['success' => false, 'error' => 'Erro ao gerar o PIX na PixGo: ' . ($paymentData['message'] ?? 'Erro desconhecido.')]);
    exit;
}

$qrCodeText = $paymentData['data']['qr_code'] ?? null;
$transactionId = $paymentData['data']['payment_id'] ?? '';

if (!$qrCodeText) {
    echo json_encode(['success' => false, 'error' => 'Pagamento gerado na PixGo, mas código PIX não encontrado na resposta.']);
    exit;
}

// SUCESSO! Retorna os dados para o JavaScript
echo json_encode([
    'success' => true,
    'qrCodeText' => $qrCodeText,
    'transactionId' => $transactionId
]);
