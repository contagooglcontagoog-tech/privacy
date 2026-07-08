<?php
session_start();
require_once '../checkout/pagamento/TrackingHelper.php';
$external_id = TrackingHelper::getExternalId();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <title>Oferta Especial - Privacy</title>
    <link rel="icon" type="image/png" href="../checkout/images/MQBoX1zlikgJ.png">

    <style>
        /* STYLES COPIED FROM CHECKOUT/INDEX.HTML */
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
            border: 2px solid var(--orange-500);
            /* Highlight border for offer */
        }

        .card-content {
            padding: 1.5rem;
            text-align: center;
        }

        .subscription-link {
            display: block;
            text-decoration: none;
            margin-top: 1rem;
        }

        .subscription-button {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 3.5rem;
            padding: 0 1.5rem;
            border-radius: var(--radius);
            border: none;
            font-size: 1.1rem;
            font-weight: 700;
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

        .offer-title {
            color: var(--orange-600);
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .offer-subtitle {
            color: var(--gray-600);
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }

        .old-price {
            text-decoration: line-through;
            color: var(--gray-400);
            font-size: 1rem;
        }

        .new-price {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--gray-900);
            display: block;
            margin: 0.5rem 0;
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="container header-container">
            <div class="logo">
                <img src="../checkout/assets/https_privacy_portaldenoticias_blog_Paola_images_images-logo_webp.webp"
                    alt="Logo do Privacy" class="logo-image">
            </div>
        </div>
    </header>

    <main class="container">
        <div class="profile-section">
            <div class="banner">
                <img src="../checkout/assets/MQBoX1zlikgJ.png" alt="Foto de Capa" class="banner-image">
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
                <img src="../checkout/assets/nDh7EeVd8Whv.png" alt="Foto de Perfil" class="profile-image">
            </div>
        </div>

        <!-- OFFER CARD -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-content">
                <div class="offer-title">ESPERE! NÃO VÁ! 😱</div>
                <p class="offer-subtitle">Você ganhou um <b>acesso secreto</b> com desconto exclusivo.</p>

                <div
                    style="background: var(--orange-50); padding: 1rem; border-radius: var(--radius); margin-bottom: 1rem;">
                    <p style="font-weight: 600; color: var(--orange-800); margin-bottom: 0.5rem;">PLANO TRIMESTRAL (3
                        MESES)</p>
                    <span class="old-price">De R$ 29,90</span>
                    <span class="new-price">Por R$ 7,90</span>
                    <p style="font-size: 0.875rem; color: var(--red-600); font-weight: bold;">⚠️ OFERTA VÁLIDA POR 2
                        MINUTOS</p>
                </div>

                <a href="../checkout/pagamento/index.php?plan_name=Privacy%20-%20Oferta%20Relampago%20(3%20Meses)&plan_price=7.90"
                    class="subscription-link">
                    <button class="subscription-button primary-button pulse">
                        QUERO APROVEITAR AGORA
                    </button>
                </a>
            </div>
        </div>

    </main>
</body>

</html>