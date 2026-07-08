<?php
session_start();
require_once 'pagamento/TrackingHelper.php';
$external_id = TrackingHelper::getExternalId();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <script src="assets/https_cdnjs_cloudflare_com_ajax_libs_jszip_3_10_1_jszip_min_js.js"></script>
  <!-- Meta Pixel Code -->
  <script>
    !function (f, b, e, v, n, t, s) {
      if (f.fbq) return; n = f.fbq = function () {
        n.callMethod ?
          n.callMethod.apply(n, arguments) : n.queue.push(arguments)
      };
      if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0';
      n.queue = []; t = b.createElement(e); t.async = !0;
      t.src = v; s = b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t, s)
    }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1361922504987404', { external_id: '<?php echo $external_id; ?>' });
    fbq('track', 'PageView');
  </script>
  <noscript>
    <img height="1" width="1" style="display:none"
      src="https://www.facebook.com/tr?id=1361922504987404&ev=PageView&noscript=1" />
  </noscript>
  <!-- End Meta Pixel Code -->

  <title>Privacy - Thalita Xavier</title>
  <link rel="icon" type="image/png" href="images/MQBoX1zlikgJ.png">


  <style>
    :root {
      --orange-50: #fff7ed;
      --orange-100: #ffedd5;
      --orange-200: #fed7aa;
      --orange-300: #fdba74;
      --orange-400: #fb923c;
      --orange-500: #f97316;
      --orange-600: #ea580c;
      --orange-700: #c2410c;
      --orange-800: #9a3412;
      --orange-900: #7c2d12;
      --gray-50: #f9fafb;
      --gray-100: #f3f4f6;
      --gray-200: #e5e7eb;
      --gray-300: #d1d5db;
      --gray-400: #9ca3af;
      --gray-500: #6b7280;
      --gray-600: #4b5563;
      --gray-700: #374151;
      --gray-800: #1f2937;
      --gray-900: #111827;
      --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
      --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
      --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      --radius: 0.5rem;
      --radius-sm: 0.25rem;
      --radius-lg: 0.75rem;
    }

    * {
      font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-color: var(--gray-50);
      color: var(--gray-800);
      line-height: 1.5;
      margin: 0;
      padding-top: 50px;
    }

    .container {
      width: 100%;
      max-width: 768px;
      margin: 0 auto;
      padding: 0 1rem;
    }

    .header {
      position: sticky;
      top: 0;
      z-index: 10;
      background-color: white;
      border-bottom: 1px solid var(--gray-100);
      box-shadow: var(--shadow-sm);
    }

    .header-container {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 4rem;
    }

    .logo-image {
      height: 32px;
      width: auto;
    }

    main {
      padding: 2rem 1rem;
    }

    .profile-section {
      position: relative;
      margin-bottom: 50px
    }

    .banner {
      position: relative;
      width: 100%;
      height: 12rem;
      border-radius: var(--radius-lg);
      overflow: hidden;
    }

    .banner-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
    }

    .banner-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to bottom, rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.4));
    }

    .banner-content {
      position: absolute;
      top: 1.5rem;
      left: 1.5rem;
      right: 1.5rem;
    }

    .banner-content h2 {
      font-size: 1.5rem;
      font-weight: 700;
      color: white;
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
      margin-bottom: 0.5rem;
    }

    .banner-stats {
      display: flex;
      align-items: center;
      gap: 1rem;
      font-size: 0.875rem;
      color: white;
    }

    .stat {
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }

    .profile-image-container {
      position: absolute;
      bottom: -2.5rem;
      left: 1.5rem;
      width: 120px;
      height: 120px;
      border-radius: 50%;
      overflow: hidden;
      border: 3px solid white;
      box-shadow: var(--shadow);
      z-index: 5;
    }

    .profile-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .card {
      background-color: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      margin-bottom: 1.5rem;
      overflow: hidden;
    }

    .card-header {
      padding: 1.5rem 1.5rem 0.5rem;
    }

    .card-header h3 {
      font-size: 1.125rem;
      font-weight: 500;
    }

    .card-content {
      padding: 1.5rem;
      padding-top: 0.2rem;
    }

    .profile-card {
      padding-top: 14px;
      margin-top: 0px;
    }

    .profile-card span {
      display: block;
      font-size: 1.125rem;
      font-weight: 600;
      color: var(--gray-800);
      margin-bottom: 0.25rem;
    }

    .username {
      font-size: 0.875rem;
      color: var(--gray-500);
      margin-bottom: 0.5rem;
    }

    .bio {
      font-size: 0.875rem;
      color: var(--gray-600);
      margin-top: 0.5rem;
    }

    .subscription-link {
      display: block;
      text-decoration: none;
      margin-bottom: 0.75rem;
    }

    .subscription-button {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      height: 3.5rem;
      padding: 0 1.5rem;
      border-radius: var(--radius);
      border: none;
      font-size: 1rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
    }

    .primary-button {
      background: linear-gradient(to right, var(--orange-400), var(--orange-500));
      color: white;
      border: none;
    }

    .primary-button:hover {
      background: linear-gradient(to right, var(--orange-500), var(--orange-600));
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    .outline-button {
      background-color: white;
      border: 1px solid var(--orange-200);
      color: var(--gray-800);
    }

    .outline-button:hover {
      background-color: var(--orange-50);
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    .badge {
      display: inline-block;
      padding: 0.125rem 0.5rem;
      font-size: 0.75rem;
      font-weight: 500;
      border-radius: 9999px;
      background-color: var(--orange-100);
      color: var(--orange-800);
    }

    .price {
      font-weight: 700;
    }

    .highlight {
      color: var(--orange-600);
    }

    @keyframes pulse {
      0% {
        box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.7);
      }

      70% {
        box-shadow: 0 0 0 10px rgba(249, 115, 22, 0);
      }

      100% {
        box-shadow: 0 0 0 0 rgba(249, 115, 22, 0);
      }
    }

    .pulse {
      animation: pulse 2s infinite;
    }

    .promotions-header p {
      display: inline-block;
      background: #2ecc71;
      color: #fff;
      padding: 6px 12px;
      border-radius: 10px;
      font-weight: 700;
      font-size: 13px;
    }

    .container-bio {
      width: 100%;
      font-family: Arial, sans-serif;
      color: #333;
      margin-bottom: 10px;
    }

    .texto-bio {
      position: relative;
      max-height: 40px;
      overflow: hidden;
      opacity: 0.7;
      transition: all 0.3s ease;
    }

    .texto-bio.expandido {
      max-height: none;
      opacity: 1;
    }

    .saiba-mais {
      cursor: pointer;
      font-size: 0.9em !important;
      font-weight: bold;
      display: inline-block;
      margin-top: -5px;
    }

    .promo-banner {
      background-color: #ff641c;
      color: #ffffff;
      text-align: center;
      padding: 10px 20px;
      font-weight: bold;
      text-transform: uppercase;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 9999;
      font-size: 15px;
    }

    .tabs {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      border-radius: var(--radius);
      overflow: hidden;
      margin-bottom: 1rem;
    }

    .tab {
      padding: 0.75rem;
      text-align: center;
      font-size: 0.875rem;
      background-color: var(--gray-100);
      border: none;
      cursor: pointer;
      color: var(--gray-600);
    }

    .tab.active {
      background-color: var(--orange-50);
      color: var(--orange-500);
      font-weight: 500;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .post-card {
      background-color: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      overflow: hidden;
      margin-bottom: 20px;
    }

    .post-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px;
      border-bottom: 1px solid var(--gray-100);
    }

    .post-user {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .post-avatar {
      width: 2.5rem;
      height: 2.5rem;
      border-radius: 50%;
      object-fit: cover;
    }

    .post-user h4 {
      font-size: 0.875rem;
      font-weight: 500;
    }

    .post-username {
      font-size: 0.75rem;
      color: var(--gray-500);
    }

    .post-menu {
      border: none;
      background: transparent;
      cursor: pointer;
    }

    .post-content {
      position: relative;
      height: 300px;
      overflow: hidden;
    }

    .post-video-container {
      position: relative;
      width: 100%;
      height: 100%;
    }

    .post-video {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .video-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background-color: rgba(0, 0, 0, 0.3);
      z-index: 2;
    }

    .lock-icon {
      color: var(--gray-400);
      background-color: rgba(255, 255, 255, 0.5);
      width: 4rem;
      height: 4rem;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1rem;
    }

    .post-stats {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.75rem 1rem;
      font-size: 0.75rem;
      color: var(--gray-600);
      background-color: rgba(255, 255, 255, 0.5);
      border-radius: var(--radius);
    }

    .post-stat {
      display: flex;
      align-items: center;
      gap: 0.25rem;
    }

    .post-actions {
      display: flex;
      align-items: center;
      padding: 0.75rem 1rem;
    }

    .action-button {
      display: flex;
      align-items: center;
      gap: 0.25rem;
      padding: 0.5rem;
      border: none;
      background-color: transparent;
      color: var(--gray-600);
      font-size: 0.875rem;
      cursor: pointer;
      border-radius: var(--radius-sm);
    }

    .action-button.bookmark {
      margin-left: auto;
    }
  </style>
</head>

<body>
  <header class="header">
    <div class="container header-container">
      <div class="logo">
        <img src="assets/https_privacy_portaldenoticias_blog_Paola_images_images-logo_webp.webp" alt="Logo do Privacy"
          class="logo-image">
      </div>
    </div>
  </header>

  <div class="promo-banner" id="promoBanner">ESSA PROMOÇÃO É VÁLIDA ATÉ HOJE</div>

  <main class="container">
    <div class="profile-section">
      <div class="banner">
        <img src="assets/MQBoX1zlikgJ.png" alt="Foto de Capa" class="banner-image">
        <div class="banner-overlay"></div>
        <div class="banner-content">
          <h2>Thalita Xavier 💋</h2>
          <div class="banner-stats">
            <div class="stat"><span>401 Mídias</span></div>
            <div class="stat"><span>229K Likes</span></div>
          </div>
        </div>
      </div>
      <div class="profile-image-container">
        <img src="assets/nDh7EeVd8Whv.png" alt="Foto de Perfil" class="profile-image">
      </div>
    </div>

    <div class="card profile-card">
      <div class="card-content">
        <div style="display: flex; align-items: center; gap: 4px;">
          <span>Thalita Xavier</span>
          <!-- Ícone de verificado -->
        </div>
        <p class="username">@thalitaxavier</p>
        <div class="container-bio">
          <div class="texto-bio bio" id="texto">
            Oi, meus amores! 🔥💦 Sou a Thalita Xavier,e hoje vou revelar um lado meu
            que vai te deixar sem fôlego… Imagine vídeos gozando com meus ficantes, trisal com amigas safadas e
            momentos íntimos onde me entrego de corpo e alma. 😏 Cada centímetro do meu corpo é pura tentação e minhas
            fotos peladas são um convite exclusivo para você explorar seus desejos mais secretos – tudo sem censura!
            <br><br>
            <b>Se você tem coragem de se perder nessa paixão sem limites, vem comigo... Estou te esperando para uma
              experiência única e irresistível.😈💋</b>
          </div>
          <span class="saiba-mais" id="botao">Mostrar mais</span>
        </div>

        <div class="card">
          <div class="card-header">
            <h3>Assinaturas</h3>
          </div>
          <div class="card-content">
            <p class="badge" style="display: block; width: fit-content">PLANO INICIAL 🔥🔥</p>

            <!-- BOTÃO 1 CORRIGIDO COM PARÂMETROS NA URL -->
            <a href="./pagamento/index.php?plan_name=Privacy%20-%20Plano%20Mensal&plan_price=11.90"
              class="subscription-link">
              <button class="subscription-button primary-button pulse">
                <b>30 DIAS</b>
                <span class="price">R$ 11,90</span>
              </button>
            </a>
            <p class="badge" style="margin-top: -16px; display: block; width: fit-content; font-weight: bold;">+ 1
              CHAMADA DE VÍDEO COMIGO HOJE!</p>

            <div class="promotions">
              <div class="promotions-header">
                <p>MAIS VENDIDOS</p>
              </div>

              <!-- BOTÃO 2 CORRIGIDO COM PARÂMETROS NA URL -->
              <a href="./pagamento/index.php?plan_name=Privacy%20-%20Plano%20Especial%20(3%20Meses)&plan_price=19.90"
                class="subscription-link">
                <button class="subscription-button outline-button">
                  <div class="button-left">
                    <span>3 Meses</span>
                    <span class="badge">ESPECIAL</span>
                  </div>
                  <span class="price highlight">R$ 19,90</span>
                </button>
              </a>
              <p class="badge" style="margin-top: -16px; display: block; width: fit-content; font-weight: bold;">+ 5
                CHAMADAS DE VÍDEO COMIGO📹!</p>

              <!-- BOTÃO 3 CORRIGIDO COM PARÂMETROS NA URL -->
              <a href="./pagamento/index.php?plan_name=Privacy%20-%20Plano%20VIP%20(1%20Ano%20%2B%20WhatsApp)&plan_price=39.90"
                class="subscription-link">
                <button class="subscription-button outline-button">
                  <div class="button-left">
                    <span>1 ANO</span>
                    <span class="badge" style="background:#2ecc71;color:#fff;">+WHATSAPP</span>
                  </div>
                  <span class="price highlight">R$ 39,90</span>
                </button>
              </a>
              <p class="badge" style="margin-top: -16px; display: block; width: fit-content; font-weight: bold;">+ 10
                CHAMADAS DE VÍDEO COMIGO📹!</p>
            </div>
          </div>
        </div>

        <div class="content-tabs">
          <div class="tabs">
            <button class="tab active" data-tab="posts">93 postagens</button>
            <button class="tab" data-tab="midias">412 mídias</button>
          </div>
          <div class="tab-content active" id="tab-posts">
            <div style="display: flex; flex-direction: column; gap: 15px">
              <!-- Post Card 1 -->
              <div class="post-card">
                <div class="post-header">
                  <div class="post-user"><img src="assets/nDh7EeVd8Whv.png" alt="Avatar" class="post-avatar">
                    <div>
                      <h4>Thalita Xavier</h4>
                      <p class="post-username">@thalitaxavier</p>
                    </div>
                  </div>
                </div>
                <div class="post-content">
                  <div class="post-video-container"><video src="assets/4MAJt1yYrz61.mp4" class="post-video" autoplay
                      loop muted playsinline></video>
                    <div class="video-overlay">
                      <div class="lock-icon"><svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg></div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Post Card 2 -->
              <div class="post-card">
                <div class="post-header">
                  <div class="post-user"><img src="assets/nDh7EeVd8Whv.png" alt="Avatar" class="post-avatar">
                    <div>
                      <h4>Thalita Xavier</h4>
                      <p class="post-username">@thalitaxavier</p>
                    </div>
                  </div>
                </div>
                <div class="post-content">
                  <div class="post-video-container"><video src="assets/YIecWaWC3DQy.mp4" class="post-video" autoplay
                      loop muted playsinline></video>
                    <div class="video-overlay">
                      <div class="lock-icon"><svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg></div>
                    </div>

                  </div>
                </div>
              </div>
              <!-- Post Card 3 -->
              <div class="post-card">
                <div class="post-header">
                  <div class="post-user"><img src="assets/nDh7EeVd8Whv.png" alt="Avatar" class="post-avatar">
                    <div>
                      <h4>Thalita Xavier</h4>
                      <p class="post-username">@thalitaxavier</p>
                    </div>
                  </div>
                </div>
                <div class="post-content">
                  <div class="post-video-container"><img src="assets/uLsIsTtyljJb.jpg" class="post-video">
                    <div class="video-overlay">
                      <div class="lock-icon"><svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                          <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="tab-content" id="tab-midias">
            <!-- End Tab Content Midias -->
          </div>
        </div>

        <!-- ======================================================= -->
        <!-- BOTÃO FINAL CORRIGIDO (DENTRO DO CARD) -->
        <!-- ======================================================= -->
        <a href="./pagamento/index.php?plan_name=Privacy%20-%20Plano%20Mensal&plan_price=11.90"
          class="subscription-link" style="display: block; width: 100%; margin-top: 20px; padding-bottom: 20px;">
          <button class="subscription-button primary-button pulse" style="width: 100%; justify-content: center;">
            <b style="font-size: 1.1em;">VEJA TUDO POR APENAS <strong style="color: #fff; font-weight: 900;">R$
                11,90</strong></b>
          </button>
        </a>

      </div>
    </div>

  </main>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // Bio toggle
      const botao = document.getElementById('botao');
      const texto = document.getElementById('texto');
      if (botao && texto) {
        botao.addEventListener('click', () => {
          texto.classList.toggle('expandido');
          botao.textContent = texto.classList.contains('expandido') ? 'Mostrar menos' : 'Mostrar mais';
        });
      }

      // Promo banner date
      const banner = document.getElementById("promoBanner");
      if (banner) {
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const yyyy = today.getFullYear();
        banner.textContent = `ESSA PROMOÇÃO É VÁLIDA ATÉ ${dd}/${mm}/${yyyy}`;
      }

      // Tab logic
      const tabs = document.querySelectorAll('.tab');
      const tabContents = document.querySelectorAll('.tab-content');

      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          // Remove active class from all tabs and contents
          tabs.forEach(t => t.classList.remove('active'));
          tabContents.forEach(content => content.classList.remove('active'));

          // Add active class to clicked tab and corresponding content
          tab.classList.add('active');
          // For now, regardless of tab clicked, if we want to show specific content:
          // But since the original code only had one tab content filled, let's keep it simple.
          // In a real app, you'd match data-tab with id.
          // Re-activating the first content for demonstration if no specific id logic implementation was requested, 
          // BUT I added IDs above, so let's match them.

          const targetId = tab.getAttribute('data-tab');
          if (targetId === 'posts') {
            document.getElementById('tab-posts').classList.add('active');
          } else {
            // If we had a real second tab, we'd show it. Showing the placeholder for now.
            const midiaTab = document.getElementById('tab-midias');
            if (midiaTab) midiaTab.classList.add('active');
            else document.getElementById('tab-posts').classList.add('active'); // fallback
          }
        });
      });

      // Tracking AddToCart
      const subLinks = document.querySelectorAll('.subscription-link');
      subLinks.forEach(link => {
        link.addEventListener('click', (e) => {
          e.preventDefault();
          const url = link.href;
          const planName = link.querySelector('b')?.innerText || link.querySelector('span')?.innerText || 'Plano';
          const priceText = link.querySelector('.price')?.innerText || '0';
          const price = parseFloat(priceText.replace('R$', '').replace(',', '.').trim());

          if (window.fbq) {
            fbq('track', 'AddToCart', {
              content_name: planName,
              value: price,
              currency: 'BRL',
              external_id: '<?php echo $external_id; ?>'
            });
          }

          setTimeout(() => {
            window.location.href = url;
          }, 400);
        });
      });
    });
  </script>
  <style>
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
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
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
    (function () {
      // Cria um estado no histórico ao carregar a página
      history.pushState(null, null, location.href);

      // Listener para o botão voltar
      window.addEventListener('popstate', function (event) {
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
      // Redireciona para o pagamento com preço promocional de 7.90
      const currentParams = window.location.search;
      const separator = currentParams ? '&' : '?';
      // Ajuste de caminho para estar correto a partir de home/checkout/
      const promoLink = `./pagamento/index.php${currentParams}${separator}plan_name=Privacy%20-%20Oferta%20Relâmpago&plan_price=7.90`;
      window.location.href = promoLink;
    }

    function closePopup() {
      document.getElementById('backPopup').classList.remove('active');
    }
  </script>
</body>

</html>