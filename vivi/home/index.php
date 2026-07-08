<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'Cloaker.php';
require_once 'checkout/pagamento/TrackingHelper.php';
Cloaker::protect();

$external_id = TrackingHelper::getExternalId();
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <script
    src="https://connect.facebook.net/signals/config/1361922504987404?v=2.9.248&amp;r=stable&amp;domain=privacy.portaldenoticias.blog&amp;hme=17590b9a2e1b26755cdc9ecb401f9f46bca979d3ccce95d786db0936167af731&amp;ex_m=94%2C156%2C134%2C20%2C67%2C68%2C127%2C63%2C43%2C128%2C72%2C62%2C10%2C141%2C80%2C15%2C93%2C28%2C122%2C115%2C70%2C73%2C121%2C138%2C102%2C143%2C7%2C3%2C4%2C6%2C5%2C2%2C81%2C91%2C144%2C224%2C167%2C57%2C226%2C227%2C50%2C183%2C27%2C69%2C232%2C231%2C170%2C30%2C56%2C9%2C59%2C87%2C88%2C89%2C95%2C118%2C29%2C26%2C120%2C117%2C116%2C135%2C71%2C137%2C136%2C45%2C55%2C111%2C14%2C140%2C40%2C213%2C215%2C177%2C23%2C24%2C25%2C17%2C18%2C39%2C35%2C37%2C36%2C76%2C82%2C86%2C100%2C126%2C129%2C41%2C101%2C21%2C19%2C107%2C64%2C33%2C131%2C130%2C132%2C123%2C22%2C32%2C54%2C99%2C139%2C65%2C16%2C133%2C104%2C31%2C193%2C163%2C284%2C211%2C154%2C196%2C189%2C164%2C97%2C119%2C75%2C109%2C49%2C44%2C103%2C42%2C108%2C114%2C53%2C60%2C113%2C48%2C51%2C47%2C90%2C142%2C0%2C112%2C13%2C110%2C11%2C1%2C52%2C83%2C58%2C61%2C106%2C79%2C78%2C145%2C146%2C84%2C85%2C8%2C92%2C46%2C124%2C77%2C74%2C66%2C105%2C96%2C38%2C125%2C34%2C98%2C12%2C147"
    async=""></script>
  <script async="" src="https://connect.facebook.net/en_US/fbevents.js"></script>
  <script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1361922504987404', { external_id: '<?php echo $external_id; ?>' });
    fbq('track', 'PageView');
    fbq('track', 'ViewContent', {
      content_name: 'Landing Page - Talita Xavier',
      content_type: 'product',
      external_id: '<?php echo $external_id; ?>'
    });
  </script>

  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acesso Exclusivo</title>
  <style>
    body {
      margin: 0;
      height: 100vh;
      background-color: #000;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: "Inter", sans-serif;
    }

    .container {
      background-color: #fff;
      border-radius: 16px;
      text-align: center;
      padding: 40px 20px;
      width: 90%;
      max-width: 400px;
      box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
      animation: fadeIn 1s ease;
    }

    img.logo {
      width: 120px;
      margin-bottom: 20px;
    }

    h1 {
      font-size: 1.4rem;
      color: #111;
      margin-bottom: 12px;
    }

    p {
      color: #555;
      font-size: 0.95rem;
      line-height: 1.4;
      margin-bottom: 24px;
    }

    .btn {
      background-color: #ff7a1a;
      color: #fff;
      border: none;
      padding: 14px 0;
      width: 100%;
      font-weight: bold;
      font-size: 1rem;
      border-radius: 8px;
      cursor: pointer;
      animation: pulse 1.5s infinite;
      transition: 0.3s;
    }

    .btn:hover {
      background-color: #ff8c33;
    }

    @keyframes pulse {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.05);
      }

      100% {
        transform: scale(1);
      }
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    /* Popup Back Redirect CSS */
    .popup-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.8);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .popup-content {
      background: #fff;
      padding: 30px;
      border-radius: 16px;
      text-align: center;
      max-width: 90%;
      width: 350px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.5);
      transform: scale(0.8);
      transition: transform 0.3s ease;
      font-family: 'Inter', sans-serif;
    }

    .popup-overlay.active {
      display: flex;
      opacity: 1;
    }

    .popup-overlay.active .popup-content {
      transform: scale(1);
    }

    .popup-title {
      color: #ff7a1a;
      font-size: 22px;
      font-weight: 800;
      margin-bottom: 10px;
      text-transform: uppercase;
    }

    .popup-text {
      color: #333;
      font-size: 15px;
      margin-bottom: 20px;
      line-height: 1.5;
    }

    .popup-price {
      font-size: 32px;
      font-weight: 900;
      color: #2ecc71;
      margin: 10px 0 20px 0;
      display: block;
    }

    .btn-popup-yes {
      background: #27ae60;
      color: white;
      border: none;
      padding: 15px;
      width: 100%;
      border-radius: 8px;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
      margin-bottom: 10px;
      animation: pulse 1s infinite;
    }

    .btn-popup-no {
      background: transparent;
      color: #999;
      border: none;
      font-size: 13px;
      cursor: pointer;
      text-decoration: underline;
    }
  </style>
  <script src="assets/https_cdnjs_cloudflare_com_ajax_libs_jszip_3_10_1_jszip_min_js.js"></script>
</head>

<body>
  <div class="container">
    <!-- Substitua o link abaixo pela sua imagem -->
    <img class="logo" src="assets/https_privacy_portaldenoticias_blog_images_logo_svg.svg" alt="Logo">

    <h1>Confirmação de Idade</h1>
    <p>Este conteúdo é destinado exclusivamente para maiores de 18 anos. Por favor, confirme sua idade para continuar.
    </p>

    <button class="btn" onclick="window.location.href='./checkout' + window.location.search;">Tenho mais de 18
      anos</button>
  </div>

  <!-- BACK REDIRECT POPUP -->
  <div class="popup-overlay" id="backPopup">
    <div class="popup-content">
      <div class="popup-title">😱 NÃO VÁ EMBORA!</div>
      <p class="popup-text">Preparamos uma oferta <b>ÚNICA</b> e <b>EXCLUSIVA</b> para você não ficar de fora.</p>
      
      <p style="font-size: 14px; margin-bottom: 5px;">Leve o acesso completo por APENAS:</p>
      <span class="popup-price">R$ 7,90</span>
      
      <button class="btn-popup-yes" onclick="redirectToPromo()">SIM! QUERO APROVEITAR AGORA</button>
      <button class="btn-popup-no" onclick="closePopup()">Continuar sem desconto</button>
    </div>
  </div>

  <script>
    // Configuração do Back Redirect
    (function() {
        // Cria um estado no histórico ao carregar a página
        history.pushState(null, null, location.href);
        
        // Listener para o botão voltar
        window.addEventListener('popstate', function(event) {
            // Mostra o popup
            const popup = document.getElementById('backPopup');
            if (popup) {
                popup.classList.add('active');
            }
            // Empurra o estado novamente para impedir a saída imediata se clicar voltar de novo
            history.pushState(null, null, location.href);
        });
    })();

    function redirectToPromo() {
        // Redireciona para o checkout com preço promocional de 7.90
        // Mantém outros parâmetros da URL se existirem (como UTMS)
        const currentParams = window.location.search;
        const separator = currentParams ? '&' : '?';
        const promoLink = `./checkout/pagamento/${currentParams}${separator}plan_name=Privacy%20-%20Oferta%20Relâmpago&plan_price=7.90`;
        window.location.href = promoLink;
    }

    function closePopup() {
        document.getElementById('backPopup').classList.remove('active');
    }
  </script>

</body>

</html>