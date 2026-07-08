<?php
// Obrigado/upsell2.php - PÁGINA DO SEGUNDO UPSELL (R$ 49,90)

// Dados do cliente vindos da etapa anterior
$customer_name = htmlspecialchars(urldecode($_GET['name'] ?? ''));
$customer_email = htmlspecialchars(urldecode($_GET['email'] ?? ''));
$customer_cpf = htmlspecialchars(urldecode($_GET['cpf'] ?? ''));

// Dados deste produto de UPSELL
$upsell_product_name = "Acesso VIP Vitalício";
$upsell_amount = 49.90;

// URL para a página final caso o cliente RECUSE a oferta
$final_page_url = "final.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><title>Última Oferta | Privacy</title>
    <!-- Cole aqui o seu <head> completo com CSS, fontes e Pixel (apenas PageView) -->
</head>
<body>
    <div class="container">
        <!-- Adapte o HTML para a oferta de 49,90 -->
        <div class="thank-you">
            <h1>Sua compra está quase finalizada!</h1>
            <p>Temos uma última oportunidade exclusiva para você se tornar um membro VIP.</p>
        </div>

        <div class="offer">
            <h2><?= $upsell_product_name ?></h2>
            <p>Acesso ilimitado, conteúdos diários e contato direto via WhatsApp.</p>
            <div class="price">R$ <?= number_format($upsell_amount, 2, ',', '.') ?></div>
            <div class="unique">PAGAMENTO ÚNICO</div>
        </div>
        
        <div class="btn-container">
            <!-- Formulário para ACEITAR o upsell e ir para o payment.php -->
            <form action="../pagamento/payment.php" method="POST" style="margin-bottom: 15px;">
                <input type="hidden" name="product_name" value="<?= htmlspecialchars($upsell_product_name) ?>">
                <input type="hidden" name="amount" value="<?= $upsell_amount ?>">
                <input type="hidden" name="name" value="<?= $customer_name ?>">
                <input type="hidden" name="email" value="<?= $customer_email ?>">
                <input type="hidden" name="cpf" value="<?= $customer_cpf ?>">
                <button type="submit" class="pulse-btn"><i class="fas fa-star"></i> SIM, QUERO SER VIP!</button>
            </form>
            
            <!-- Link para RECUSAR e ir para a página final -->
            <a href="<?= $final_page_url ?>" style="font-size: 14px; color: #777; display: block;">Não, obrigado. Finalizar minha compra.</a>
        </div>
    </div>
</body>
</html>