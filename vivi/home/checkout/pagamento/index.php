<?php
// paolla/home/checkout/pagamento/index.php
session_start();
require_once 'config.php'; // Carrega configurações (Dice + Facebook)

// Gera/recupera external_id via cookie (sem TrackingHelper)
$external_id = $_COOKIE['external_id'] ?? hash('sha256', uniqid((string)mt_rand(), true));
if (!isset($_COOKIE['external_id'])) {
    setcookie('external_id', $external_id, time() + (86400 * 30), "/");
}

// --- LÊ OS DADOS DO PLANO DA URL ---
$main_product_name = isset($_GET['plan_name']) ? htmlspecialchars(urldecode($_GET['plan_name'])) : 'Privacy - Plano Mensal';
$main_product_price = isset($_GET['plan_price']) ? floatval($_GET['plan_price']) : 9.90;

// --- PREÇOS FIXOS DOS ORDER BUMPS ---
define('BUMP_MAISA_PRICE', 7.90);
define('BUMP_MELODY_PRICE', 8.90);

function format_price($price) {
    return 'R$ ' . number_format($price, 2, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout PRIVADO - Thalita Xavier</title>
    <link rel="icon" href="foto.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '<?php echo $CONFIG_FACEBOOK['PIXEL_ID']; ?>', { external_id: '<?php echo $external_id; ?>' });
    fbq('track', 'PageView');
    fbq('track', 'InitiateCheckout', {
        content_name: '<?php echo $main_product_name; ?>',
        value: <?php echo $main_product_price; ?>,
        currency: 'BRL',
        external_id: '<?php echo $external_id; ?>'
    });
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo $CONFIG_FACEBOOK['PIXEL_ID']; ?>&ev=PageView&noscript=1"/></noscript>

    <style>
        :root {
            --primary-color: #FF6B35; --primary-dark: #E55A2B; --success-color: #10B981;
            --warning-color: #F59E0B; --white: #FFFFFF; --light-gray: #F8F9FA; 
            --medium-gray: #E9ECEF; --dark-gray: #6C757D; --text-color: #333333; 
            --shadow: rgba(0, 0, 0, 0.08); --border-radius: 16px; --transition: all 0.3s ease; 
            --green-selected: #10B981; --green-light: rgba(16, 185, 129, 0.1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: var(--white); color: var(--text-color); line-height: 1.6; }
        .checkout-container { width: 100%; max-width: 900px; margin: 0 auto; }
        .security-badge { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: white; text-align: center; padding: 14px 15px; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .checkout-header { background-color: var(--white); display: flex; justify-content: center; align-items: center; padding: 25px 15px; }
        .logo-img { height: 35px; width: auto; }
        
        /* --- CSS CORRIGIDO PARA O BANNER --- */
        .banner-container {
            display: flex;
            width: 100%;
            max-height: 250px; /* Altura máxima para a colagem no desktop */
            overflow: hidden;
            background-color: var(--medium-gray); /* Cor de fundo caso as imagens não carreguem */
        }
        .banner-img {
            width: 25%; /* 4 imagens, cada uma ocupa 1/4 do espaço */
            object-fit: cover; /* Garante que a imagem cubra o espaço sem distorcer */
            display: block;
        }
        /* --- FIM DA CORREÇÃO DO BANNER --- */

        .info-bar { background-color: var(--primary-color); color: white; text-align: center; padding: 12px 15px; font-size: 14px; font-weight: 500;}
        .checkout-content { display: grid; grid-template-columns: 1fr; gap: 25px; padding: 25px 15px; background-color: var(--light-gray); }
        .checkout-form, .order-summary { background-color: var(--white); border-radius: var(--border-radius); padding: 25px; box-shadow: 0 2px 15px rgba(0,0,0,.08); border: 1px solid var(--medium-gray); height: fit-content; }
        .section-title { font-size: 20px; font-weight: 600; margin-bottom: 25px; color: var(--text-color); position: relative; padding-left: 18px; display: flex; align-items: center; gap: 10px; }
        .section-title::before { content: ''; width: 6px; height: 24px; background: var(--primary-color); border-radius: 3px; position: absolute; left: 0; top: 50%; transform: translateY(-50%); }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 8px; color: var(--dark-gray); }
        .input-with-flag { position: relative; }
        .flag-container { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); display: flex; align-items: center; gap: 8px; pointer-events: none; }
        .flag-img { width: 24px; height: auto; border-radius: 2px; }
        .country-code { color: var(--dark-gray); font-weight: 500; }
        .form-input { width: 100%; padding: 16px; border: 1px solid var(--medium-gray); border-radius: 12px; font-size: 16px; transition: var(--transition); }
        .form-input.with-flag { padding-left: 85px; }
        .form-input:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(255,107,53,.1); }
        .form-input.input-error { border-color: #e53e3e !important; box-shadow: 0 0 0 3px rgba(229,62,62,.15) !important; }
        .field-error-msg { display: none; color: #e53e3e; font-size: 13px; font-weight: 500; margin-top: 6px; }
        .field-error-msg.visible { display: block; }
        .order-bumps-section { margin: 30px 0; }
        .order-bump-title { font-size: 18px; font-weight: 600; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .order-bump-title i { color: var(--primary-color); font-size: 20px; }
        .order-bump-item { background: var(--white); border: 2px solid var(--medium-gray); border-radius: 12px; padding: 15px; transition: var(--transition); cursor: pointer; margin-bottom: 15px; }
        .order-bump-item.selected { border-color: var(--green-selected); background: var(--green-light); }
        .order-bump-header { display: flex; align-items: center; gap: 15px; }
        .order-bump-image img { width: 70px; height: 70px; border-radius: 10px; object-fit: cover; }
        .order-bump-info { flex: 1; }
        .order-bump-name-price { display: flex; justify-content: space-between; align-items: flex-start; }
        .order-bump-name { font-size: 16px; font-weight: 600; }
        .order-bump-price { font-size: 16px; font-weight: 700; color: var(--green-selected); white-space: nowrap; }
        .order-bump-description { font-size: 13px; color: var(--dark-gray); margin: 4px 0; }
        .order-bump-old-price-save { display: flex; align-items: center; gap: 10px; margin-top: 5px; }
        .order-bump-old-price { font-size: 13px; color: var(--dark-gray); text-decoration: line-through; }
        .order-bump-save-badge { background: var(--warning-color); color: white; padding: 2px 8px; border-radius: 8px; font-size: 11px; font-weight: 600; }
        .order-bump-checkbox-container { display: flex; align-items: center; gap: 10px; margin-top: 15px; }
        .order-bump-checkbox { width: 18px; height: 18px; accent-color: var(--primary-color); }
        .checkbox-label { font-size: 14px; font-weight: 500; color: var(--text-color); }
        .product-item { display: flex; gap: 15px; align-items: center; }
        .product-image-container img { width: 80px; height: 80px; border-radius: 10px; border: 1px solid var(--medium-gray); object-fit: cover;}
        .product-name { font-size: 18px; font-weight: 600; }
        .product-description { font-size: 14px; color: var(--dark-gray); }
        .product-price { font-size: 22px; font-weight: 700; color: var(--primary-color); }
        .bumps-added { margin-top: 20px; display: none; }
        .bump-added-item { display: flex; justify-content: space-between; align-items: center; font-size: 14px; margin-bottom: 8px; color: var(--dark-gray); }
        .bump-added-price { font-weight: 500; }
        .order-total { border-top: 1px solid var(--medium-gray); padding-top: 20px; margin-top: 20px; display: flex; justify-content: space-between; align-items: center; }
        .total-label { font-size: 18px; font-weight: 600; }
        .total-price { font-size: 28px; font-weight: 700; color: var(--primary-color); }
        .submit-button { width: 100%; padding: 18px; background: var(--primary-color); color: white; border: none; border-radius: 12px; font-size: 17px; font-weight: 600; cursor: pointer; margin-top: 20px; display: flex; justify-content: center; align-items: center; gap: 10px; }
        .secure-checkout { text-align: center; margin-top: 25px; font-size: 14px; color: var(--dark-gray); display: flex; justify-content: center; align-items: center; gap: 8px; }
        .footer { text-align: center; padding: 25px 15px; border-top: 1px solid var(--medium-gray); background-color: var(--white); }
        .footer-secure { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; padding: 8px 16px; border-radius: 20px; border: 1px solid var(--medium-gray); margin-bottom: 15px; }
        .loading-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,.9); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .loading-overlay.active { display: flex; }
        .loading-spinner { width: 50px; height: 50px; border: 4px solid var(--medium-gray); border-top: 4px solid var(--primary-color); border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0%{ transform:rotate(0deg) } 100%{ transform:rotate(360deg) } }
        
        @media (max-width: 767px) { 
            .checkout-content { grid-template-columns: 1fr; } 
            .checkout-form { order: 2; margin-top: 20px; } 
            .order-summary { order: 1; }
            
            
            /* --- CSS PARA O BANNER RESPONSIVO --- */
            .banner-container {
                max-height: 180px; /* Altura ajustada para celular */
            }
            .banner-img {
                width: 100%; /* A primeira imagem ocupa todo o espaço */
            }
            .hide-on-mobile {
                display: none; /* As outras 3 imagens são escondidas */
            }
            /* --- FIM DO CSS RESPONSIVO --- */
        }
    </style>
</head>
<body>

    <div class="checkout-container">
       
        <!-- HTML DO BANNER CORRIGIDO -->
        <!-- No Desktop: Mostra 4 imagens. No Celular: Mostra apenas a primeira. -->
        <div class="banner-container">
            <img src="2jermqv3ku0ma9svt8qkuf5mw.jpg" alt="Banner Principal" class="banner-img">
        </div>

        <div class="info-bar">O acesso será enviado pelo WhatsApp e também pelo e-mail de forma totalmente privada</div>
        
        <div class="checkout-content">
            <div class="checkout-form">
                <h2 class="section-title">Seus dados</h2>
                <form id="checkoutForm" action="payment.php" method="POST">
                    <input type="hidden" name="base_product_name" value="<?php echo htmlspecialchars($main_product_name); ?>">
                    <input type="hidden" name="base_product_price" value="<?php echo $main_product_price; ?>">
                    
                    <div class="form-group"><label class="form-label" for="name">Nome completo</label><input type="text" id="name" name="name" class="form-input" placeholder="Digite seu nome completo" autocomplete="name"><span class="field-error-msg" id="error-name">⚠ Por favor, informe seu nome completo.</span></div>
                    <div class="form-group"><label class="form-label" for="email">E-mail</label><input type="email" id="email" name="email" class="form-input" placeholder="seuemail@exemplo.com" autocomplete="email"><span class="field-error-msg" id="error-email">⚠ Por favor, informe um e-mail válido.</span></div>
                    <div class="form-group"><label class="form-label" for="phone">WhatsApp</label><div class="input-with-flag"><div class="flag-container"><img src="bandeira.png" alt="Bandeira do Brasil" class="flag-img"> <span class="country-code">+55</span></div><input type="tel" id="phone" name="phone" class="form-input with-flag" placeholder="(11) 99999-9999" autocomplete="tel"></div><span class="field-error-msg" id="error-phone">⚠ Por favor, informe seu WhatsApp com DDD.</span></div>
                    <input type="hidden" name="cpf" value="13432030908">
                    
                    <div class="order-bumps-section">
                        <div class="order-bump-title"><i class="fas fa-gift"></i><span>ADICIONAIS EXCLUSIVOS (OPCIONAL)</span></div>
                        <p>Marque os produtos que deseja adicionar:</p>
                        <div class="order-bump-item" id="bump-maisa-container">
                            <div class="order-bump-header">
                                <div class="order-bump-image"><img src="pkt0cabb7irkyg3xmmkiolln7.jpg" alt="Privacy Maisa Silva"></div>
                                <div class="order-bump-info">
                                    <div class="order-bump-name-price"><span class="order-bump-name">Privacy Mel Maia</span><span class="order-bump-price"><?php echo format_price(BUMP_MAISA_PRICE); ?></span></div>
                                    <p class="order-bump-description">Acesso completo ao conteúdo exclusivo da Mel Maia.</p>
                                    <div class="order-bump-old-price-save"><span class="order-bump-old-price">R$ 19,90</span><span class="order-bump-save-badge">ECONOMIZE 60%</span></div>
                                </div>
                            </div>
                            <div class="order-bump-checkbox-container">
                                <input type="checkbox" id="bump_maisa" name="bump_maisa" value="1" class="order-bump-checkbox">
                                <label for="bump_maisa" class="checkbox-label">Adicionar "Privacy Mel Maia" ao meu pedido por <?php echo format_price(BUMP_MAISA_PRICE); ?></label>
                            </div>
                        </div>
                        <div class="order-bump-item" id="bump-melody-container">
                            <div class="order-bump-header">
                                <div class="order-bump-image"><img src="lbzs6ucczallplhvvmtm0mu5r.png" alt="Privacy MC Melody"></div>
                                <div class="order-bump-info">
                                    <div class="order-bump-name-price"><span class="order-bump-name">Privacy Kamilinha</span><span class="order-bump-price"><?php echo format_price(BUMP_MELODY_PRICE); ?></span></div>
                                    <p class="order-bump-description">Conteúdo premium da Kamilinha - Vídeos e fotos exclusivas.</p>
                                    <div class="order-bump-old-price-save"><span class="order-bump-old-price">R$ 19,90</span><span class="order-bump-save-badge">ECONOMIZE 55%</span></div>
                                </div>
                            </div>
                            <div class="order-bump-checkbox-container">
                                <input type="checkbox" id="bump_melody" name="bump_melody" value="1" class="order-bump-checkbox">
                                <label for="bump_melody" class="checkbox-label">Adicionar "Privacy Kamilinha" ao meu pedido por <?php echo format_price(BUMP_MELODY_PRICE); ?></label>
                            </div>
                        </div>    
                            <div class="order-bump-item" id="bump-nicolle-container">
                            <div class="order-bump-header">
                                <div class="order-bump-image"><img src="bump3.jpg" alt="Privacy MC Melody"></div>
                                <div class="order-bump-info">
                                    <div class="order-bump-name-price"><span class="order-bump-name">Privacy Nicolle Ex do Gordão da xj</span><span class="order-bump-price"><?php echo format_price(BUMP_MELODY_PRICE); ?></span></div>
                                    <p class="order-bump-description">Conteúdo premium da Nicolle Ex do Gordão da xj - Vídeos e fotos exclusivas.</p>
                                    <div class="order-bump-old-price-save"><span class="order-bump-old-price">R$ 19,90</span><span class="order-bump-save-badge">ECONOMIZE 55%</span></div>
                                </div>
                            </div>
                            <div class="order-bump-checkbox-container">
                                <input type="checkbox" id="bump_nicolle" name="bump_nicolle" value="1" class="order-bump-checkbox">
                                <label for="bump_nicolle" class="checkbox-label">Adicionar "Privacy Nicolle Ex do Gordão da xj" ao meu pedido por <?php echo format_price(BUMP_MELODY_PRICE); ?></label>
                            </div>
                        </div>
                    </div>
                </form>
                <button type="submit" form="checkoutForm" id="submitButton" class="submit-button"><i class="fas fa-lock"></i> <span id="buttonText">FINALIZAR PEDIDO - <?php echo format_price($main_product_price); ?></span></button>
            </div>
            
            <div class="order-summary">
                <h2 class="section-title">Seu pedido</h2>
                <div class="product-item">
                    <div class="product-image-container"><img src="nDh7EeVd8Whv.png" alt="Produto Principal"></div>
                    <div class="product-info">
                        <div class="product-name"><?php echo htmlspecialchars($main_product_name); ?></div>
                        <div class="product-price"><?php echo format_price($main_product_price); ?></div>
                    </div>
                </div>
                <div id="bumpsAdded" class="bumps-added"></div>
                <div class="order-total">
                    <div class="total-label">Total</div>
                    <div id="totalPrice" class="total-price"><?php echo format_price($main_product_price); ?></div>
                </div>
                <div class="secure-checkout"><i class="fas fa-shield-alt"></i> Checkout 100% seguro e criptografado</div>
            </div>
        </div>
        
        <footer class="footer">
            <div class="footer-secure"><i class="fas fa-shield-alt"></i> Checkout Seguro</div>
            <p>Checkout PRIVADO © 2025</p>
        </footer>
    </div>
    
    <div class="loading-overlay" id="loadingOverlay"><div class="loading-spinner"></div></div>

    <script>
        const MAIN_PRODUCT_PRICE = <?php echo $main_product_price; ?>;
        const BUMP_MAISA_PRICE = <?php echo BUMP_MAISA_PRICE; ?>;
        const BUMP_MELODY_PRICE = <?php echo BUMP_MELODY_PRICE; ?>;
        const BUMP_NICOLLE_PRICE = 8.90;

        document.addEventListener('DOMContentLoaded', () => {
            const maisaCheckbox = document.getElementById('bump_maisa');
            const melodyCheckbox = document.getElementById('bump_melody');
            const nicolleCheckbox = document.getElementById('bump_nicolle');
            const maisaContainer = document.getElementById('bump-maisa-container');
            const melodyContainer = document.getElementById('bump-melody-container');
            const nicolleContainer = document.getElementById('bump-nicolle-container');

            // --- Restaura dados salvos (caso a página tenha recarregado) ---
            const SS_KEY = 'checkout_form_data';
            const saved = JSON.parse(sessionStorage.getItem(SS_KEY) || '{}');
            if (saved.name)  document.getElementById('name').value  = saved.name;
            if (saved.email) document.getElementById('email').value = saved.email;
            if (saved.phone) document.getElementById('phone').value = saved.phone;

            // --- Salva no sessionStorage a cada digitação ---
            function saveField(field) {
                const data = JSON.parse(sessionStorage.getItem(SS_KEY) || '{}');
                data[field.id] = field.value;
                sessionStorage.setItem(SS_KEY, JSON.stringify(data));
            }
            ['name','email','phone'].forEach(id => {
                const el = document.getElementById(id);
                el.addEventListener('input', () => saveField(el));
            });

            // --- Remove erro visual ao corrigir o campo ---
            const emailRegexInline = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            document.getElementById('name').addEventListener('blur', () => {
                const f = document.getElementById('name');
                if (f.value.trim().split(' ').filter(p => p).length >= 2) {
                    f.classList.remove('input-error');
                    document.getElementById('error-name').classList.remove('visible');
                }
            });
            document.getElementById('email').addEventListener('blur', () => {
                const f = document.getElementById('email');
                if (emailRegexInline.test(f.value.trim())) {
                    f.classList.remove('input-error');
                    document.getElementById('error-email').classList.remove('visible');
                }
            });
            document.getElementById('phone').addEventListener('input', (e) => {
                let v = e.target.value.replace(/\D/g,'').slice(0,11);
                if(v.length>=7){v=v.replace(/^(\d{2})(\d{5})(\d{0,4}).*/,'($1) $2-$3');}
                else if(v.length>=3){v=v.replace(/^(\d{2})(\d{0,5}).*/,'($1) $2');}
                else if(v){v=v.replace(/^(\d*)/,'($1');}
                e.target.value = v;
                if (v.replace(/\D/g,'').length >= 10) {
                    e.target.classList.remove('input-error');
                    document.getElementById('error-phone').classList.remove('visible');
                }
                saveField(e.target);
            });

            const updateUICallback = () => updateUI();
            maisaCheckbox.addEventListener('change', updateUICallback);
            melodyCheckbox.addEventListener('change', updateUICallback);
            nicolleCheckbox.addEventListener('change', updateUICallback);
            maisaContainer.addEventListener('click', (e) => { if(e.target.type !== 'checkbox') { maisaCheckbox.checked = !maisaCheckbox.checked; updateUI(); } });
            melodyContainer.addEventListener('click', (e) => { if(e.target.type !== 'checkbox') { melodyCheckbox.checked = !melodyCheckbox.checked; updateUI(); } });
            nicolleContainer.addEventListener('click', (e) => { if(e.target.type !== 'checkbox') { nicolleCheckbox.checked = !nicolleCheckbox.checked; updateUI(); } });

            document.getElementById('checkoutForm').addEventListener('submit', (e) => {
                e.preventDefault();

                const nameField  = document.getElementById('name');
                const emailField = document.getElementById('email');
                const phoneField = document.getElementById('phone');

                let valid = true;

                // --- Valida Nome ---
                const nameVal = nameField.value.trim();
                const nameErr = document.getElementById('error-name');
                if (!nameVal || nameVal.split(' ').filter(p => p).length < 2) {
                    nameField.classList.add('input-error');
                    nameErr.classList.add('visible');
                    valid = false;
                } else {
                    nameField.classList.remove('input-error');
                    nameErr.classList.remove('visible');
                }

                // --- Valida E-mail ---
                const emailVal = emailField.value.trim();
                const emailErr = document.getElementById('error-email');
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailVal || !emailRegex.test(emailVal)) {
                    emailField.classList.add('input-error');
                    emailErr.classList.add('visible');
                    valid = false;
                } else {
                    emailField.classList.remove('input-error');
                    emailErr.classList.remove('visible');
                }

                // --- Valida Telefone ---
                const phoneVal = phoneField.value.replace(/\D/g, '');
                const phoneErr = document.getElementById('error-phone');
                if (!phoneVal || phoneVal.length < 10) {
                    phoneField.classList.add('input-error');
                    phoneErr.classList.add('visible');
                    valid = false;
                } else {
                    phoneField.classList.remove('input-error');
                    phoneErr.classList.remove('visible');
                }

                if (!valid) return; // Bloqueia envio se inválido

                sessionStorage.removeItem('checkout_form_data'); // Limpa ao enviar com sucesso
                document.getElementById('loadingOverlay').classList.add('active');
                document.getElementById('submitButton').disabled = true;
                if (window.fbq) { fbq('track', 'InitiateCheckout'); }
                e.target.submit();
            });

            updateUI();
        });

        function updateUI() {
            let total = MAIN_PRODUCT_PRICE;
            const bumpsContainer = document.getElementById('bumpsAdded');
            bumpsContainer.innerHTML = '';
            bumpsContainer.style.display = 'none';

            const maisaChecked = document.getElementById('bump_maisa').checked;
            const melodyChecked = document.getElementById('bump_melody').checked;
            const nicolleChecked = document.getElementById('bump_nicolle').checked;
            
            document.getElementById('bump-maisa-container').classList.toggle('selected', maisaChecked);
            document.getElementById('bump-melody-container').classList.toggle('selected', melodyChecked);
            document.getElementById('bump-nicolle-container').classList.toggle('selected', nicolleChecked);

            if (maisaChecked) { total += BUMP_MAISA_PRICE; addBumpToSummary('Privacy Maisa Silva', BUMP_MAISA_PRICE); }
            if (melodyChecked) { total += BUMP_MELODY_PRICE; addBumpToSummary('Privacy MC Melody', BUMP_MELODY_PRICE); }
            if (nicolleChecked) { total += BUMP_NICOLLE_PRICE; addBumpToSummary('Privacy Nicolle Ex do Gordão da xj', BUMP_NICOLLE_PRICE); }
            if (maisaChecked || melodyChecked || nicolleChecked) { bumpsContainer.style.display = 'block'; }

            const formattedTotal = 'R$ ' + total.toFixed(2).replace('.', ',');
            document.getElementById('totalPrice').textContent = formattedTotal;
            document.getElementById('buttonText').textContent = 'FINALIZAR PEDIDO - ' + formattedTotal;
        }

        function addBumpToSummary(name, price) {
            const container = document.getElementById('bumpsAdded');
            container.innerHTML += `<div class="bump-added-item"><span>${name}</span><span class="bump-added-price">+ R$ ${price.toFixed(2).replace('.', ',')}</span></div>`;
        }
    </script>
</body>
</html>