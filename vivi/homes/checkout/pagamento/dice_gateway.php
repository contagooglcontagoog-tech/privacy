<?php
declare(strict_types=1);
require_once 'config.php';

/**
 * Grava uma mensagem de erro no arquivo de log.
 * @param string $message A mensagem a ser registrada.
 */
function log_dice_error(string $message): void {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents(DICE_LOG_FILE, "[$timestamp] " . $message . PHP_EOL, FILE_APPEND);
}

/**
 * Obtém o token de autenticação da Dice API.
 * @return string|null O token de acesso ou null em caso de falha.
 */
function getDiceAuthToken(): ?string {
    global $CONFIG_DICE;

    $url = rtrim($CONFIG_DICE['API_BASE'], '/') . '/v1/auth/login';
    $payload = json_encode([
        'client_id' => $CONFIG_DICE['CLIENT_ID'],
        'client_secret' => $CONFIG_DICE['CLIENT_SECRET'],
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_TIMEOUT => 25,
    ]);
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        log_dice_error('CURL Error (Auth): ' . curl_error($ch));
        curl_close($ch);
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($response, true);

    if ($httpCode === 200 && !empty($data['token'])) {
        return $data['token'];
    } else {
        log_dice_error("Auth Failed - HTTP Status: $httpCode - Response: $response");
        return null;
    }
}

/**
 * Cria uma nova cobrança PIX na Dice API.
 * @param string $token O token de autenticação.
 * @param array $paymentData Os dados do pagamento (produto, valor, pagador).
 * @return array|null Os dados da transação ou null em caso de falha.
 */
function createDicePayment(string $token, array $paymentData): ?array {
    global $CONFIG_DICE;
    
    $url = rtrim($CONFIG_DICE['API_BASE'], '/') . '/v2/payments/deposit';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($paymentData),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        log_dice_error('CURL Error (Payment): ' . curl_error($ch));
        curl_close($ch);
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300 && !empty($data['transaction_id'])) {
        return $data;
    } else {
        log_dice_error("Payment Failed - HTTP Status: $httpCode - Response: $response");
        return null;
    }
}