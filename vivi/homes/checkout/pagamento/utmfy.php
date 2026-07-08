<?php
// paolla/home/checkout/pagamento/utmfy.php
require_once __DIR__ . '/config.php';

function wlog_utmfy(string $msg): void {
    $logFile = __DIR__ . '/utmfy_api.log';
    file_put_contents($logFile, '[' . gmdate('Y-m-d H:i:s') . ' UTC] ' . $msg . PHP_EOL, FILE_APPEND);
}

function sendToUtmfy(
    string  $orderId,
    string  $status,
    float   $amount,
    array   $customer,
    array   $tracking,
    string  $productName,
    string  $createdAt,
    ?string $approvedDate
): void {
    global $CONFIG_UTMFY, $CONFIG_PRODUCT;
    
    if (empty($CONFIG_UTMFY['API_TOKEN'])) {
        wlog_utmfy('UTMIFY: API Token não configurado. Envio ignorado.');
        return;
    }

    $amountInCents = (int) round($amount * 100);
    $gatewayFee  = (int) round((1.00 + $amount * 0.03) * 100);
    $userComm    = max(0, $amountInCents - $gatewayFee);

    $payload = [
        'orderId'       => (string) $orderId,
        'platform'      => 'Dice',
        'paymentMethod' => 'pix',
        'status'        => $status,
        'createdAt'     => $createdAt,
        'approvedDate'  => $approvedDate,
        'refundedAt'    => null,
        'customer'      => [
            'name'     => (!empty($customer['name']))  ? (string) $customer['name']  : 'Cliente Desconhecido',
            'email'    => (!empty($customer['email'])) ? (string) $customer['email'] : 'sem@email.com',
            'phone'    => (!empty($customer['phone'])) ? (string) $customer['phone'] : null,
            'document' => (!empty($customer['cpf']))   ? (string) $customer['cpf']   : null,
            'country'  => 'BR',
        ],
        'products' => [[
            'id'           => $CONFIG_PRODUCT['ID'] ?? 'privado_mensal9',
            'name'         => $productName,
            'planId'       => null,
            'planName'     => null,
            'quantity'     => 1,
            'priceInCents' => $amountInCents,
        ]],
        'trackingParameters' => [
            'src'          => (!empty($tracking['src']))          ? $tracking['src']          : null,
            'sck'          => (!empty($tracking['sck']))          ? $tracking['sck']          : null,
            'utm_source'   => (!empty($tracking['utm_source']))   ? $tracking['utm_source']   : null,
            'utm_campaign' => (!empty($tracking['utm_campaign'])) ? $tracking['utm_campaign'] : null,
            'utm_medium'   => (!empty($tracking['utm_medium']))   ? $tracking['utm_medium']   : null,
            'utm_content'  => (!empty($tracking['utm_content']))  ? $tracking['utm_content']  : null,
            'utm_term'     => (!empty($tracking['utm_term']))     ? $tracking['utm_term']     : null,
        ],
        'commission' => [
            'totalPriceInCents'     => $amountInCents,
            'gatewayFeeInCents'     => $gatewayFee,
            'userCommissionInCents' => $userComm,
            'currency'              => 'BRL',
        ],
        'isTest' => false
    ];

    wlog_utmfy("Enviando status='{$status}' para orderId='{$orderId}'. Payload: " . json_encode($payload, JSON_UNESCAPED_UNICODE));

    $ch = curl_init($CONFIG_UTMFY['API_URL']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'x-api-token: ' . $CONFIG_UTMFY['API_TOKEN'],
        ],
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err      = curl_error($ch);
    curl_close($ch);

    wlog_utmfy("Resposta HTTP {$httpCode}: {$response}" . ($err ? " | CurlError: {$err}" : ""));
}
