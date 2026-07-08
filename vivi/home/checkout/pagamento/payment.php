<?php
// paolla/home/checkout/pagamento/payment.php
// VERSÃO FINAL - COM UI/UX PREMIUM E LOGICA VERIFICADA
require_once 'config.php'; // Carrega configurações (Dice + Facebook + UTMify)

// Desativar a exibição de erros em produção
error_reporting(0);
ini_set('display_errors', 0);

// --- 1. CONFIGURAÇÕES GERAIS ---

// PREÇOS DOS ORDER BUMPS (DEFINIDOS NO SERVIDOR PARA SEGURANÇA MÁXIMA)
define('BUMP_MAISA_PRICE', 7.90);
define('BUMP_MELODY_PRICE', 8.90);
define('BUMP_NICOLLE_PRICE', 8.90);

// CONFIGURAÇÕES DA API DICE
$clientId     = $CONFIG_DICE['CLIENT_ID'];
$clientSecret = $CONFIG_DICE['CLIENT_SECRET'];
$baseUrl      = 'https://api.use-dice.com';
$thankYouUrl  = './obrigado/index.php';

// CONFIGURAÇÕES DO FACEBOOK CAPI
require_once __DIR__ . '/FacebookCAPI.php';
$fbPixelId      = $CONFIG_FACEBOOK['PIXEL_ID'];
$fbAccessToken  = $CONFIG_FACEBOOK['ACCESS_TOKEN'];
$fbCapi         = new FacebookCAPI($fbPixelId, $fbAccessToken);

// DIRETÓRIO PARA SALVAR DADOS DAS TRANSAÇÕES (UTMs + cliente)
$DIR_TRANSACOES = __DIR__ . '/pagamentos';
if (!is_dir($DIR_TRANSACOES)) {
    mkdir($DIR_TRANSACOES, 0775, true);
}

// Protege contra acesso direto ao arquivo sem dados do formulário
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// --- 2. CÁLCULO SEGURO DO VALOR TOTAL NO SERVIDOR ---
$base_product_price = isset($_POST['base_product_price']) ? floatval($_POST['base_product_price']) : 9.90;
$base_product_name  = isset($_POST['base_product_name'])  ? $_POST['base_product_name'] : 'Privacy - Plano Mensal';

$totalAmount = $base_product_price;
$productName = $base_product_name;
$addedBumps  = [];

if (isset($_POST['bump_maisa'])  && $_POST['bump_maisa']  == '1') { $totalAmount += BUMP_MAISA_PRICE;   $addedBumps[] = "Maisa Silva"; }
if (isset($_POST['bump_melody']) && $_POST['bump_melody'] == '1') { $totalAmount += BUMP_MELODY_PRICE;  $addedBumps[] = "MC Melody"; }
if (isset($_POST['bump_nicolle'])&& $_POST['bump_nicolle']== '1') { $totalAmount += BUMP_NICOLLE_PRICE; $addedBumps[] = "Nicolle Ex do Gordão"; }

if (count($addedBumps) > 0) {
    $productName .= " + " . implode(" + ", $addedBumps);
}

// --- 3. CAPTURA DOS DADOS DO CLIENTE E UTMs ---
$customerData = [
    'name'  => trim($_POST['name']  ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'phone' => preg_replace('/\D/', '', $_POST['phone'] ?? ''),
    'cpf'   => preg_replace('/\D/', '', $_POST['cpf']   ?? ''),
    'client_ip_address' => $_POST['client_ip'] ?? null,
];

if (strlen($customerData['phone']) <= 11 && strpos($customerData['phone'], '55') !== 0) {
    $customerData['phone'] = '55' . $customerData['phone'];
}

$external_id = $_COOKIE['external_id'] ?? $customerData['cpf'];

$trackingParams = [
    'src'          => ($_POST['src']          ?? '') ?: null,
    'sck'          => ($_POST['sck']          ?? '') ?: null,
    'utm_source'   => ($_POST['utm_source']   ?? '') ?: null,
    'utm_campaign' => ($_POST['utm_campaign'] ?? '') ?: null,
    'utm_medium'   => ($_POST['utm_medium']   ?? '') ?: null,
    'utm_content'  => ($_POST['utm_content']  ?? '') ?: null,
    'utm_term'     => ($_POST['utm_term']     ?? '') ?: null,
];

// --- 4. CARREGA FUNÇÃO UTMIFY ---
require_once __DIR__ . '/utmfy.php';

// --- 5. PROCESSAMENTO DO PAGAMENTO VIA API PIXGO ---
$errorMsg      = null;
$qrCodeText    = null;
$transactionId = null;
$createdAt_utc = gmdate('Y-m-d H:i:s');

$pixgoUrl = $CONFIG_PIXGO['API_URL'] . '/payment/create';
$pixgoKey = $CONFIG_PIXGO['API_KEY'];

$payload = [
    "amount"           => round($totalAmount, 2),
    "description"      => $productName,
    "customer_name"    => $customerData['name'],
    "customer_cpf"     => $customerData['cpf'],
    "customer_email"   => $customerData['email'],
    "customer_phone"   => $customerData['phone'],
    "external_id"      => (string)$external_id,
];

// Se tivermos um webhook_url configurado, podemos adicionar aqui
// $payload['webhook_url'] = "https://seusite.com/pagamento/webhook.php";

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

if ($httpCode === 201) {
    $paymentData = json_decode($response, true);
    
    if (($paymentData['success'] ?? false) && isset($paymentData['data'])) {
        $qrCodeText    = $paymentData['data']['qr_code'] ?? null;
        $transactionId = $paymentData['data']['payment_id'] ?? '';

        if ($qrCodeText && $transactionId) {
            $tidClean = preg_replace('/[^a-zA-Z0-9_\-]/', '', $transactionId);
            $transactionMeta = [
                'transaction_id'  => $transactionId,
                'external_id'     => $external_id,
                'amount'          => round($totalAmount, 2),
                'product_name'    => $productName,
                'created_at_utc'  => $createdAt_utc,
                'customer'        => $customerData,
                'tracking'        => $trackingParams,
            ];
            file_put_contents($DIR_TRANSACOES . '/' . $tidClean . '_meta.json', json_encode($transactionMeta, JSON_UNESCAPED_UNICODE));
            
            // Log local para PixGo
            file_put_contents(__DIR__ . '/pixgo_api.log', "[" . date('Y-m-d H:i:s') . "] Pagamento criado: $transactionId - Valor: $totalAmount" . PHP_EOL, FILE_APPEND);
            
            sendToUtmfy($transactionId, 'waiting_payment', $totalAmount, $customerData, $trackingParams, $productName, $createdAt_utc, null, $CONFIG_UTMFY);
        }

        if (!$qrCodeText) { 
            $errorMsg = "PIX gerado, mas código QR não encontrado na resposta."; 
        }

        // Facebook CAPI InitiateCheckout
        $checkoutEventId = $_POST['event_id'] ?? null;
        $fbCapi->sendEvent('InitiateCheckout', [
            'email'             => $customerData['email'],
            'name'              => $customerData['name'],
            'phone'             => $customerData['phone'],
            'external_id'       => $external_id,
            'country'           => 'br',
            'client_ip_address' => $customerData['client_ip_address']
        ], [
            'value'        => round($totalAmount, 2),
            'currency'     => 'BRL',
            'content_name' => $productName
        ], null, $checkoutEventId);

    } else {
        $errorMsg = "Erro na resposta da PixGo: " . ($paymentData['message'] ?? 'Erro desconhecido');
    }
} else {
    $errorMsg = "Erro ao gerar PIX na PixGo (Status HTTP: $httpCode). " . $response;
}

$redirectUrlQuery = http_build_query([
    'transaction_id' => $transactionId,
    'amount'         => $totalAmount,
    'product_name'   => urlencode($productName),
    'name'           => urlencode($customerData['name']),
    'email'          => urlencode($customerData['email']),
    'cpf'            => urlencode($customerData['cpf']),
]);
$redirectUrl = $thankYouUrl . '?' . $redirectUrlQuery;

function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Finalizar Inscrição - PIX</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #FF6B35;
            --primary-dark: #E55A2B;
            --success-color: #10B981;
            --warning-color: #F59E0B;
            --white: #FFFFFF;
            --light-gray: #F8F9FA;
            --medium-gray: #E9ECEF;
            --dark-gray: #6C757D;
            --text-color: #333333;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --border-radius: 16px;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: var(--light-gray); color: var(--text-color); line-height: 1.6; display: flex; flex-direction: column; align-items: center; min-height: 100vh; padding: 0; }
        
        /* Cabeçalho Premium */
        .checkout-header { width: 100%; background-color: var(--white); display: flex; flex-direction: column; align-items: center; padding: 20px 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-bottom: 1px solid var(--medium-gray); z-index: 10; }
        .logo-img { height: 32px; width: auto; margin-bottom: 5px; }
        .security-badge { font-size: 12px; color: var(--dark-gray); display: flex; align-items: center; gap: 6px; font-weight: 500; }
        .security-badge i { color: var(--success-color); }

        .container { background: white; width: 100%; max-width: 500px; margin: 25px 15px; border-radius: var(--border-radius); box-shadow: var(--shadow); overflow: hidden; border: 1px solid var(--medium-gray); }
        
        /* Seção do Perfil/Banner */
        .model-header { position: relative; width: 100%; height: 160px; background-color: #eee; }
        .model-banner { width: 100%; height: 100%; object-fit: cover; }
        .model-profile-overlay { position: absolute; bottom: -40px; left: 25px; display: flex; align-items: flex-end; gap: 15px; }
        .model-photo { width: 85px; height: 85px; border-radius: 50%; border: 4px solid var(--white); box-shadow: 0 4px 10px rgba(0,0,0,0.1); background-color: var(--white); }
        .model-name { margin-bottom: 10px; }
        .model-name h2 { font-size: 18px; font-weight: 700; color: var(--text-color); margin: 0; text-shadow: 0 2px 4px rgba(255,255,255,0.8); }
        .verified-badge { color: #0095f6; font-size: 14px; }

        .summary { padding: 60px 25px 25px 25px; border-bottom: 1px dashed var(--medium-gray); background: #fdfdfd; }
        .product-info-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
        .product-name-label { font-weight: 500; font-size: 14px; color: var(--dark-gray); }
        .total-row { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
        .total-label { font-weight: 700; font-size: 16px; }
        .total-price { font-size: 28px; font-weight: 800; color: var(--primary-color); }

        .payment-area { padding: 25px; text-align: center; }
        .payment-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; color: var(--text-color); }
        
        /* Instruções de Pagamento */
        .instructions-steps { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 25px; text-align: center; }
        .step { display: flex; flex-direction: column; align-items: center; gap: 8px; }
        .step-icon { width: 32px; height: 32px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; box-shadow: 0 4px 8px rgba(255,107,53,0.3); }
        .step-text { font-size: 11px; font-weight: 600; color: var(--dark-gray); line-height: 1.2; }

        .qr-frame { display: flex; justify-content: center; align-items: center; padding: 15px; border: 2px solid var(--primary-color); border-radius: 12px; background: white; margin: 0 auto 20px auto; width: fit-content; box-shadow: 0 10px 25px rgba(255,107,53,0.1); }
        
        .copy-info { font-size: 14px; color: var(--dark-gray); margin-bottom: 10px; font-weight: 500; }
        .copy-field { background: var(--light-gray); border: 1px solid var(--medium-gray); border-radius: 10px; padding: 14px; font-family: 'Courier New', monospace; font-size: 12px; word-break: break-all; margin-bottom: 15px; width: 100%; box-sizing: border-box; text-align: center; cursor: pointer; transition: var(--transition); color: var(--text-color); }
        .copy-field:hover { border-color: var(--primary-color); background-color: #fff; }
        
        .btn { width: 100%; padding: 18px; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer; border: none; display: flex; justify-content: center; align-items: center; gap: 10px; margin-bottom: 15px; background: var(--primary-color); color: white; transition: var(--transition); box-shadow: 0 6px 15px rgba(255,107,53,0.3); }
        .btn:hover { background: var(--primary-dark); transform: translateY(-2px); }
        .btn:active { transform: translateY(0); }
        
        .timer-msg { font-size: 13px; color: var(--dark-gray); margin-top: 15px; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 500; }
        .timer-msg i { color: var(--warning-color); }

        .paid-section { display: none; padding: 40px 20px; text-align: center; }
        .paid-icon { font-size: 64px; margin-bottom: 20px; color: var(--success-color); animation: bounceIn 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55); }
        @keyframes bounceIn { 0% { transform: scale(0); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        
        .error-box { padding: 40px 20px; text-align: center; }
        .error-icon { font-size: 48px; color: #d93025; margin-bottom: 20px; }
        .error-title { font-size: 22px; font-weight: 700; color: #d93025; margin-bottom: 15px; }
        
        @media (max-width: 480px) {
            .container { margin: 15px 10px; }
            .model-header { height: 130px; }
            .model-profile-overlay { bottom: -35px; left: 15px; }
            .model-photo { width: 75px; height: 75px; }
            .total-price { font-size: 24px; }
        }
    </style>
</head>
<body>
    <header class="checkout-header">
        <img src="logo.png" alt="Privacy Logo" class="logo-img">
        <div class="security-badge"><i class="fas fa-shield-alt"></i> Checkout 100% Seguro e Criptografado</div>
    </header>

    <?php if ($qrCodeText): ?>
        <div class="container">
            <!-- Banner e Foto da Modelo -->
            <div class="model-header">
                <img src="https://privacys-br.shop/vivi/home/checkout/assets/MQBoX1zlikgJ.png" alt="Banner Modelo" class="model-banner">
                <div class="model-profile-overlay">
                    <img src="https://privacys-br.shop/vivi/home/checkout/assets/nDh7EeVd8Whv.png" alt="Foto Modelo" class="model-photo">
                    <div class="model-name">
                        <h2>Concluir Inscrição <i class="fas fa-check-circle verified-badge"></i></h2>
                    </div>
                </div>
            </div>

            <div class="summary">
                <div class="product-info-row">
                    <span class="product-name-label">Pedido:</span>
                    <span style="font-weight: 600; font-size: 14px; text-align: right;"><?= e($productName) ?></span>
                </div>
                <div class="total-row">
                    <span class="total-label">Total a pagar:</span>
                    <span class="total-price">R$ <?= e(number_format($totalAmount, 2, ',', '.')) ?></span>
                </div>
            </div>

            <div class="payment-area">
                <div id="pending-section">
                    <h2 class="payment-title">Pague com PIX para liberar seu acesso</h2>
                    
                    <!-- Instruções Passo a Passo -->
                    <div class="instructions-steps">
                        <div class="step">
                            <div class="step-icon">1</div>
                            <span class="step-text">Abra o app do seu banco</span>
                        </div>
                        <div class="step">
                            <div class="step-icon">2</div>
                            <span class="step-text">Escaneie o QR Code</span>
                        </div>
                        <div class="step">
                            <div class="step-icon">3</div>
                            <span class="step-text">Acesso imediato!</span>
                        </div>
                    </div>

                    <div class="qr-frame"><div id="qrcode-container"></div></div>
                    
                    <p class="copy-info"><i class="fas fa-copy"></i> Ou use o PIX Copia e Cola:</p>
                    <div class="copy-field" onclick="navigator.clipboard.writeText('<?= e($qrCodeText) ?>').then(() => alert('Código PIX Copiado!'))"><?= e($qrCodeText) ?></div>
                    
                    <button class="btn" onclick="navigator.clipboard.writeText('<?= e($qrCodeText) ?>').then(() => alert('Código PIX Copiado!'))"><i class="fas fa-paste"></i> COPIAR CÓDIGO PIX</button>
                    
                    <div class="timer-msg">
                        <i class="fas fa-sync-alt fa-spin"></i> Aguardando confirmação do pagamento...
                    </div>
                </div>
                <!-- Seção de Sucesso -->
                <div id="completed-section" class="paid-section">
                    <div class="paid-icon"><i class="fas fa-check-circle"></i></div>
                    <h1 class="payment-title" style="color:var(--success-color); font-size: 24px;">Pagamento Aprovado!</h1>
                    <p style="font-weight: 500; color: var(--dark-gray);">Seu acesso foi liberado. Estamos te redirecionando para o conteúdo exclusivo...</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="container">
            <div class="error-box">
                <div class="error-icon"><i class="fas fa-exclamation-circle"></i></div>
                <h1 class="error-title">Ocorreu um Erro</h1>
                <p style="margin-bottom:25px; color: var(--dark-gray); font-weight: 500;"><?= e($errorMsg) ?></p>
                <a href="index.php" class="btn" style="text-decoration:none;"><i class="fas fa-redo"></i> TENTAR NOVAMENTE</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($qrCodeText): ?>
    <script>
        // Gera o QR Code visual
        new QRCode(document.getElementById("qrcode-container"), { text: '<?= $qrCodeText ?>', width: 220, height: 220 });
        
        const transactionId = '<?= $transactionId ?>';
        const redirectUrl = '<?= $redirectUrl ?>'; 
        
        // Verifica o status do pagamento a cada 1 segundo (conforme solicitado)
        const statusInterval = setInterval(() => {
            fetch(`check_status.php?tid=${transactionId}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.status) {
                        const s = data.status.toLowerCase();
                        if (s === 'completed' || s === 'paid' || s === 'approved' || s === 'succeeded') { 
                            clearInterval(statusInterval);
                            
                            // Dispara o Pixel de Compra (com deduplicação)
                            if (window.fbq) {
                                fbq('track', 'Purchase', {
                                    value: <?= floatval($totalAmount) ?>,
                                    currency: 'BRL',
                                    content_name: '<?= e($productName) ?>',
                                    order_id: transactionId
                                }, {eventID: transactionId});
                            }

                            document.getElementById('pending-section').style.display = 'none';
                            document.getElementById('completed-section').style.display = 'block';
                            
                            // Redireciona para a página de obrigado
                            setTimeout(() => { window.location.href = redirectUrl; }, 2500);
                        }
                    }
                }).catch(err => console.error("Erro ao verificar status:", err));
        }, 1000);
    </script>
    <?php endif; ?>
</body>
</html>