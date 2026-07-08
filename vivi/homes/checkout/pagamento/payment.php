<?php
session_start();
require_once 'config.php'; // Carrega configurações (Dice + Facebook)
require_once 'TrackingHelper.php';

$external_id = TrackingHelper::getExternalId();

// Desativar a exibição de erros em um ambiente de produção
error_reporting(0);
ini_set('display_errors', 0);

// --- 1. CONFIGURAÇÕES GERAIS ---

// PREÇOS DOS ORDER BUMPS (DEFINIDOS NO SERVIDOR PARA SEGURANÇA MÁXIMA)
define('BUMP_MAISA_PRICE', 7.90);
define('BUMP_MELODY_PRICE', 8.90);
define('BUMP_NICOLLE_PRICE', 8.90);

// CONFIGURAÇÕES DA API DICE
$clientId = 'dice_live_804dfb1c44f9fbe9d335e5c2452e5b3f';
$clientSecret = 'dicesk_live_6d659cda9cf2a36a363b0157b4cdd0e9ae6bead7adb5884b';
$baseUrl = "https://api.use-dice.com";
$thankYouUrl = "./obrigado/index.php"; // Página do primeiro upsell

// CONFIGURAÇÕES DO FACEBOOK CAPI
require_once __DIR__ . '/FacebookCAPI.php';
$fbPixelId = $CONFIG_FACEBOOK['PIXEL_ID'];
$fbAccessToken = $CONFIG_FACEBOOK['ACCESS_TOKEN'];
$fbCapi = new FacebookCAPI($fbPixelId, $fbAccessToken);

// Protege contra acesso direto ao arquivo sem dados do formulário
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php"); 
    exit;
}

// --- 2. CÁLCULO SEGURO DO VALOR TOTAL NO SERVIDOR ---

// Pega os dados base do plano selecionado, enviados pelo formulário
$base_product_price = isset($_POST['base_product_price']) ? floatval($_POST['base_product_price']) : 9.90; // Valor padrão de segurança
$base_product_name = isset($_POST['base_product_name']) ? $_POST['base_product_name'] : 'Privacy - Plano Mensal'; // Nome padrão de segurança

// Inicia o cálculo com os valores base
$totalAmount = $base_product_price;
$productName = $base_product_name;
$addedBumps = [];

// Verifica se os order bumps foram selecionados e adiciona seus valores (definidos no servidor)
if (isset($_POST['bump_maisa']) && $_POST['bump_maisa'] == '1') {
    $totalAmount += BUMP_MAISA_PRICE;
    $addedBumps[] = "Maisa Silva";
}
if (isset($_POST['bump_melody']) && $_POST['bump_melody'] == '1') {
    $totalAmount += BUMP_MELODY_PRICE;
    $addedBumps[] = "MC Melody";
}
if (isset($_POST['bump_nicolle']) && $_POST['bump_nicolle'] == '1') {
    $totalAmount += BUMP_NICOLLE_PRICE;
    $addedBumps[] = "Nicolle Ex do Gordão";
}

// Constrói o nome final do produto para exibir no checkout e na fatura
if (count($addedBumps) > 0) {
    $productName .= " + " . implode(" + ", $addedBumps);
}

// --- 3. PROCESSAMENTO DO PAGAMENTO VIA API ---

$errorMsg = null;
$qrCodeText = null;
$transactionId = null;
$customerData = [
    'name' => $_POST['name'] ?? '',
    'email' => $_POST['email'] ?? '',
    'phone' => $_POST['phone'] ?? '',
    'cpf' => $_POST['cpf'] ?? ''
];

// 3.1. Autenticação na API
$authCh = curl_init($baseUrl . '/api/v1/auth/login');
curl_setopt_array($authCh, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_POSTFIELDS => json_encode(['client_id' => $clientId, 'client_secret' => $clientSecret]), CURLOPT_SSL_VERIFYPEER => false]);
$authResponse = curl_exec($authCh);
$authCode = curl_getinfo($authCh, CURLINFO_HTTP_CODE);
curl_close($authCh);

if ($authCode === 200) {
    $token = json_decode($authResponse, true)['token'] ?? json_decode($authResponse, true)['access_token'] ?? null;
    if ($token) {
        $payload = [
            "product_name" => $productName,
            "amount" => round($totalAmount, 2), // USA O VALOR FINAL SEGURO
            "payer" => [
                "name" => $customerData['name'],
                "email" => $customerData['email'],
                "document" => preg_replace('/\D/', '', $customerData['cpf'])
            ]
        ];
        
        // 3.2. Criação do Pagamento PIX
        $ch = curl_init($baseUrl . '/api/v2/payments/deposit');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_HTTPHEADER => ["Authorization: Bearer $token", "Content-Type: application/json"], CURLOPT_POSTFIELDS => json_encode($payload), CURLOPT_SSL_VERIFYPEER => false]);
        $paymentResponse = curl_exec($ch); $paymentCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        $paymentData = json_decode($paymentResponse, true);

        if ($paymentCode === 200 || $paymentCode === 201) {
            $qrCodeText = $paymentData['pix']['payload'] ?? $paymentData['qr_code_text'] ?? null;
            $transactionId = $paymentData['transaction_id'] ?? '';
            if (!$qrCodeText) { $errorMsg = "PIX gerado, mas código não encontrado na resposta."; }
            
            // Envio para o Facebook CAPI (InitiateCheckout)
            $fbCapi->sendEvent('InitiateCheckout', [
                'email' => $customerData['email'],
                'name' => $customerData['name'],
                'phone' => $customerData['phone'] ?? '',
                'external_id' => $external_id
            ], [
                'value' => round($totalAmount, 2),
                'currency' => 'BRL',
                'content_name' => $productName
            ]);

            // Salva Contexto para o Webhook (Purchase)
            TrackingHelper::saveContext($transactionId, $customerData);

        } else { $errorMsg = "Erro ao gerar PIX (Código: $paymentCode). Tente novamente."; }
    } else { $errorMsg = "Token de autorização não encontrado."; }
} else { $errorMsg = "Falha na autenticação (Código: $authCode)."; }

// Constrói a URL de redirecionamento com todos os dados para os próximos passos
$redirectUrlQuery = http_build_query([
    'transaction_id' => $transactionId,
    'amount' => $totalAmount,
    'product_name' => urlencode($productName),
    'name' => urlencode($customerData['name']),
    'email' => urlencode($customerData['email']),
    'cpf' => urlencode($customerData['cpf']),
]);
$redirectUrl = $thankYouUrl . '?' . $redirectUrlQuery;

function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><title>Pagamento PIX</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Cole aqui o CSS da sua página de pagamento PIX (qr-code, etc) */
        :root{--primary-color:#FF6B35;--light-gray:#f8f9fa;--medium-gray:#e9ecef;--text-color:#333;}
        body{font-family:'Inter',sans-serif;background:var(--light-gray);margin:0;display:flex;flex-direction:column;align-items:center;min-height:100vh;padding:20px;}
        .container{background:white;width:100%;max-width:480px;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.08);overflow:hidden;}
        .summary{padding:25px;border-bottom:1px solid var(--medium-gray);background:#fdfdfd;}
        .product-name{font-weight:600;font-size:16px;margin-bottom:10px;color:var(--text-color);}
        .total-row{display:flex;justify-content:space-between;align-items:baseline;margin-top:10px;}
        .total-label{font-weight:600;}
        .total-price{font-size:24px;font-weight:700;color:var(--primary-color);}
        .payment-area{padding:25px;text-align:center;}
        .title{font-size:18px;font-weight:600;margin-bottom:15px;}
        .qr-frame{display:flex;justify-content:center;align-items:center;padding:10px;border:1px solid var(--medium-gray);border-radius:8px;background:white;margin:20px 0;}
        .copy-field{background:var(--light-gray);border:1px solid var(--medium-gray);border-radius:8px;padding:12px;font-family:monospace;font-size:13px;word-break:break-all;margin-bottom:15px;width:100%;box-sizing:border-box;text-align:center;}
        .btn{width:100%;padding:15px;border-radius:8px;font-weight:700;font-size:14px;cursor:pointer;border:none;display:flex;justify-content:center;align-items:center;gap:8px;margin-bottom:15px;background:var(--primary-color);color:white;}
        .paid-section{display:none;color:#16a34a;padding:30px;}.paid-icon{font-size:48px;margin-bottom:15px;}
        .error-box{padding:30px;text-align:center;}.error-title{font-size:20px;color:#d93025;margin-bottom:15px;}
    </style>
</head>
<body>
    <?php if ($qrCodeText): ?>
        <div class="container">
            <div class="summary">
                <div class="product-name"><?= e($productName) ?></div>
                <div class="total-row">
                    <span class="total-label">Total a pagar:</span>
                    <span class="total-price">R$ <?= e(number_format($totalAmount, 2, ',', '.')) ?></span>
                </div>
            </div>
            <div class="payment-area">
                <div id="pending-section">
                    <h2 class="title">Pague com PIX para liberar seu acesso</h2>
                    <div class="qr-frame"><div id="qrcode-container"></div></div>
                    <p style="font-size:14px;color:#555;">Ou use o PIX Copia e Cola:</p>
                    <input type="text" class="copy-field" value="<?= e($qrCodeText) ?>" readonly>
                    <button class="btn" onclick="navigator.clipboard.writeText('<?= e($qrCodeText) ?>').then(() => alert('Código PIX Copiado!'))">📋 COPIAR CÓDIGO</button>
                </div>
                <div id="completed-section" class="paid-section">
                    <div class="paid-icon">✅</div>
                    <h1 class="title" style="color:#16a34a;">Pagamento Aprovado!</h1>
                    <p>Seu acesso foi liberado. Estamos te redirecionando...</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="container">
            <div class="error-box">
                <h1 class="error-title">Ocorreu um Erro</h1>
                <p style="margin-bottom:25px;"><?= e($errorMsg) ?></p>
                <a href="index.php" class="btn" style="text-decoration:none;">Tentar Novamente</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($qrCodeText): ?>
    <script>
        new QRCode(document.getElementById("qrcode-container"), { text: '<?= $qrCodeText ?>', width: 220, height: 220 });
        
        const transactionId = '<?= $transactionId ?>';
        const redirectUrl = '<?= $redirectUrl ?>'; // URL já contém todos os dados
        
        const statusInterval = setInterval(() => {
            fetch(`check_status.php?tid=${transactionId}`)
                .then(res => res.json())
                .then(data => {
                    console.log("Status Check Response:", data); // Log para debug no console
                    
                    if (data && data.status) {
                        const s = data.status.toLowerCase();
                        // Lista ampliada de status de sucesso para garantir redirecionamento
                        if (s === 'completed' || s === 'paid' || s === 'approved' || s === 'succeeded') { 
                            clearInterval(statusInterval);
                            console.log("Pagamento Aprovado! Redirecionando...");
                            
                            // Dispara o Pixel de Compra
                            if (window.fbq) {
                                fbq('track', 'Purchase', {
                                    value: <?= floatval($totalAmount) ?>,
                                    currency: 'BRL',
                                    content_name: '<?= e($productName) ?>',
                                    order_id: transactionId
                                });
                            }

                            document.getElementById('pending-section').style.display = 'none';
                            document.getElementById('completed-section').style.display = 'block';
                            
                            // Redireciona para a próxima página do funil
                            setTimeout(() => { window.location.href = redirectUrl; }, 3000);
                        }
                    }
                }).catch(err => console.error("Erro ao verificar status:", err));
        }, 4000); // Verifica a cada 4 segundos
    </script>
    <?php endif; ?>
</body>
</html>