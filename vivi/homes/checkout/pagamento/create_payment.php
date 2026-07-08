<?php
// pagamento/create_payment.php - SÓ GERA O PAGAMENTO E RETORNA JSON

header('Content-Type: application/json'); // Resposta sempre será em formato de dados

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método não permitido.']);
    exit;
}

// --- CONFIGURAÇÕES ---
$clientId = 'dice_live_804dfb1c44f9fbe9d335e5c2452e5b3f';
$clientSecret = 'dicesk_live_6d659cda9cf2a36a363b0157b4cdd0e9ae6bead7adb5884b';
$baseUrl = "https://api.use-dice.com";
// --------------------

// 1. AUTENTICAÇÃO
$ch = curl_init($baseUrl . '/api/v1/auth/login');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_POSTFIELDS => json_encode(['client_id' => $clientId, 'client_secret' => $clientSecret]), CURLOPT_SSL_VERIFYPEER => false]);
$authResponse = curl_exec($ch); $authCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);

if ($authCode !== 200) {
    echo json_encode(['success' => false, 'error' => 'Falha na autenticação com a API.']);
    exit;
}

$token = json_decode($authResponse, true)['token'] ?? json_decode($authResponse, true)['access_token'] ?? null;
if (!$token) {
    echo json_encode(['success' => false, 'error' => 'Token de autorização não encontrado.']);
    exit;
}

// 2. CRIAÇÃO DO PAGAMENTO
$payload = [
    "product_name" => $_POST['product_name'],
    "amount" => floatval($_POST['amount']),
    "payer" => ["name" => $_POST['name'], "email" => $_POST['email'], "document" => preg_replace('/\D/', '', $_POST['cpf'])]
];

$ch = curl_init($baseUrl . '/api/v2/payments/deposit');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_HTTPHEADER => ["Authorization: Bearer $token", "Content-Type: application/json"], CURLOPT_POSTFIELDS => json_encode($payload), CURLOPT_SSL_VERIFYPEER => false]);
$paymentResponse = curl_exec($ch);
$paymentCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$paymentData = json_decode($paymentResponse, true);

if ($paymentCode !== 200 && $paymentCode !== 201) {
    echo json_encode(['success' => false, 'error' => 'Erro ao gerar o PIX: ' . ($paymentData['message'] ?? 'Erro desconhecido.')]);
    exit;
}

$qrCodeText = $paymentData['pix']['payload'] ?? $paymentData['qr_code_text'] ?? null;
$transactionId = $paymentData['transaction_id'] ?? '';

if (!$qrCodeText) {
    echo json_encode(['success' => false, 'error' => 'Pagamento gerado, mas código PIX não encontrado na resposta.']);
    exit;
}

// SUCESSO! Retorna os dados para o JavaScript
echo json_encode([
    'success' => true,
    'qrCodeText' => $qrCodeText,
    'transactionId' => $transactionId
]);