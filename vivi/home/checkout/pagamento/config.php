<?php
declare(strict_types=1);
date_default_timezone_set('America/Recife');

// Arquivo de log para depuração. Se algo der errado na comunicação com a API,
// o erro exato será gravado aqui.
define('DICE_LOG_FILE', __DIR__ . '/dice_api_error.log');

/* =================== CONFIGURAÇÕES DA DICE API (LEGACY) =================== */
$CONFIG_DICE = [
    'API_BASE'      => 'https://api.use-dice.com/api',
    'CLIENT_ID'     => 'dice_live_804dfb1c44f9fbe9d335e5c2452e5b3f',
    'CLIENT_SECRET' => 'dicesk_live_6d659cda9cf2a36a363b0157b4cdd0e9ae6bead7adb5884b',
];

/* =================== CONFIGURAÇÕES DA PIXGO API =================== */
$CONFIG_PIXGO = [
    'API_URL' => 'https://pixgo.org/api/v1',
    'API_KEY' => 'pk_1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef', // <-- COLOQUE SUA X-API-Key DA PIXGO AQUI
];

/* =================== CONFIGURAÇÕES DO FACEBOOK =================== */
$CONFIG_FACEBOOK = [
    'PIXEL_ID'     => '1472294161134125',
    'ACCESS_TOKEN' => 'EAANDIM0yYGgBQrBfIWJeEyiAuVdMHMN9YOATdxlvSOaUtzP7xxBkLGy3PfQqga7W9b8inSphNExftyRwqu6Ohnd6UjLH7wYCRSPVLxZAReboheuNHsRBfZCpp3hsfXRSwBbRJQq6NEkZBArS8W5dAOCyRTz0nTHZCLwYapGO9G3Cd1d1c9rfkTfjS5UOzAtZCZBwZDZD'
];

/* =================== CONFIGURAÇÕES DO PRODUTO =================== */
$CONFIG_PRODUCT = [
    'NAME'   => 'Privacy - paola',
    'ID'     => 'privado_mensal9',
    'AMOUNT' => 9.90,
];

/* =================== CONFIGURAÇÕES DA PÁGINA DE OBRIGADO =================== */
$CONFIG_THANKYOU = [
    'enabled' => true,
    'url'     => 'https://privacy.portaldenoticias.blog/Obrigado/',
    'delay'   => 5,
];

/* =================== CONFIGURAÇÕES UTMFY =================== */
$CONFIG_UTMFY = [
    'API_TOKEN' => 'bdh0chNh9M4HgbdW4NjlC3BAmTk0L6XAFHfu',
    'API_URL'   => 'https://api.utmify.com.br/api-credentials/orders'
];