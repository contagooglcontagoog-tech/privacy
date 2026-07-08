<?php
// future_safe_page.php - Conteúdo otimizado para um blog profissional
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Notícias - Um Blog Moderno</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Lora:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --background-color: #f4f4f4;
            --text-color: #333;
            --light-gray: #ecf0f1;
            --white: #fff;
            --shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--background-color);
            margin: 0;
            padding: 0;
            color: var(--text-color);
            line-height: 1.7;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Cabeçalho e Navegação */
        .main-header {
            background: var(--white);
            padding: 15px 30px;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Lora', serif;
            font-size: 1.8em;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .main-nav ul {
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
        }

        .main-nav ul li {
            margin-left: 25px;
        }

        .main-nav ul li a {
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .main-nav ul li a:hover,
        .main-nav ul li a.active {
            color: var(--secondary-color);
        }

        /* Layout Principal */
        .main-content {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 40px;
            margin-top: 30px;
        }

        /* Artigos do Blog */
        .blog-posts {
            display: flex;
            flex-direction: column;
        }

        .post-card {
            background: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .post-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .post-content {
            padding: 30px;
        }
        
        .post-category {
            background-color: var(--secondary-color);
            color: var(--white);
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.8em;
            text-transform: uppercase;
            text-decoration: none;
        }
        
        .post-content h2 {
            font-family: 'Lora', serif;
            margin: 20px 0 10px;
            font-size: 2em;
        }
        
        .post-content h2 a {
            text-decoration: none;
            color: var(--primary-color);
        }
        
        .post-meta {
            font-size: 0.9em;
            color: #777;
            margin-bottom: 20px;
        }

        .post-excerpt {
            color: #555;
            margin-bottom: 20px;
        }
        
        .read-more {
            text-decoration: none;
            color: var(--secondary-color);
            font-weight: 700;
        }

        /* Barra Lateral */
        .sidebar .widget {
            background: var(--white);
            padding: 25px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .sidebar .widget-title {
            font-family: 'Lora', serif;
            border-bottom: 2px solid var(--light-gray);
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .sidebar .widget ul {
            list-style: none;
            padding: 0;
        }
        
        .sidebar .widget ul li {
            padding: 8px 0;
            border-bottom: 1px solid var(--light-gray);
        }
        
        .sidebar .widget ul li:last-child {
            border-bottom: none;
        }

        .sidebar .widget ul a {
            text-decoration: none;
            color: var(--text-color);
        }
        
        .sidebar .widget ul a:hover {
            color: var(--secondary-color);
        }

        /* Rodapé */
        .main-footer {
            background: var(--primary-color);
            color: var(--white);
            padding: 40px 20px;
            margin-top: 40px;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            text-align: left;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .footer-section {
            flex: 1;
            padding: 20px;
            min-width: 220px;
        }
        
        .footer-section h3 {
            font-family: 'Lora', serif;
            border-bottom: 1px solid var(--secondary-color);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section a {
            text-decoration: none;
            color: var(--light-gray);
            transition: color 0.3s;
            display: block;
            margin-bottom: 8px;
        }

        .footer-section a:hover {
            color: var(--secondary-color);
        }

        .social-icons a {
            margin-right: 15px;
            font-size: 1.5em;
        }
        
        .footer-bottom {
            text-align: center;
            padding: 20px;
            border-top: 1px solid #4a627a;
            margin-top: 20px;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .main-header {
                flex-direction: column;
                padding: 15px;
            }

            .main-nav ul {
                margin-top: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }
            .main-nav ul li {
                margin: 5px 10px;
            }

            .post-image {
                height: 200px;
            }
        }
    </style>
</head>
<body>

    <header class="main-header">
        <a href="#" class="logo">Portal de Notícias</a>
        <nav class="main-nav">
            <ul>
                <li><a href="#" class="active">Home</a></li>
                <li><a href="#">Tecnologia</a></li>
                <li><a href="#">Saúde</a></li>
                <li><a href="#">Entretenimento</a></li>
                <li><a href="#">Contato</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <main class="main-content">
            <section class="blog-posts">
                <article class="post-card">
                    <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=1170" alt="Imagem de tecnologia e IA" class="post-image">
                    <div class="post-content">
                        <a href="#" class="post-category">Tecnologia</a>
                        <h2><a href="#">Novas tendências de Inteligência Artificial para 2026</a></h2>
                        <div class="post-meta">
                            <span>Por João Silva</span> | <span>28 de Dezembro, 2025</span>
                        </div>
                        <p class="post-excerpt">Especialistas apontam que a inteligência artificial continuará sendo o foco principal das empresas no próximo ano, com avanços em aprendizado de máquina e automação...</p>
                        <a href="#" class="read-more">Leia Mais &rarr;</a>
                    </div>
                </article>

                <article class="post-card">
                    <img src="https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?q=80&w=1220" alt="Pessoa praticando yoga em casa" class="post-image">
                    <div class="post-content">
                        <a href="#" class="post-category">Saúde</a>
                        <h2><a href="#">Saúde e bem-estar em casa: Guia para o home office</a></h2>
                        <div class="post-meta">
                            <span>Por Maria Oliveira</span> | <span>27 de Dezembro, 2025</span>
                        </div>
                        <p class="post-excerpt">Dicas simples de como manter uma rotina saudável mesmo trabalhando remotamente, incluindo exercícios, alimentação e cuidados com a saúde mental...</p>
                        <a href="#" class="read-more">Leia Mais &rarr;</a>
                    </div>
                </article>
                
                 <article class="post-card">
                    <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?q=80&w=1160" alt="Vários smartphones mostrando aplicativos" class="post-image">
                    <div class="post-content">
                        <a href="#" class="post-category">Entretenimento</a>
                        <h2><a href="#">Os melhores apps de streaming para maratonar em 2026</a></h2>
                        <div class="post-meta">
                            <span>Por Ana Costa</span> | <span>26 de Dezembro, 2025</span>
                        </div>
                        <p class="post-excerpt">Uma análise completa dos serviços de streaming disponíveis, suas bibliotecas de conteúdo, preços e funcionalidades para você escolher o melhor para o seu lazer...</p>
                        <a href="#" class="read-more">Leia Mais &rarr;</a>
                    </div>
                </article>
            </section>

            <aside class="sidebar">
                <div class="widget">
                    <h3 class="widget-title">Sobre o Portal</h3>
                    <p>Seu hub diário de notícias sobre tecnologia, saúde e entretenimento. Trazemos as últimas novidades para mantê-lo sempre informado.</p>
                </div>
                <div class="widget">
                    <h3 class="widget-title">Posts Recentes</h3>
                    <ul>
                        <li><a href="#">Novas tendências de IA para 2026</a></li>
                        <li><a href="#">Guia de bem-estar para home office</a></li>
                        <li><a href="#">Os melhores apps de streaming</a></li>
                        <li><a href="#">Avanços na medicina remota</a></li>
                    </ul>
                </div>
                <div class="widget">
                    <h3 class="widget-title">Categorias</h3>
                    <ul>
                        <li><a href="#">Tecnologia</a></li>
                        <li><a href="#">Saúde</a></li>
                        <li><a href="#">Entretenimento</a></li>
                        <li><a href="#">Ciência</a></li>
                        <li><a href="#">Cultura</a></li>
                    </ul>
                </div>
            </aside>
        </main>
    </div>

    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Sobre Nós</h3>
                <p>O Portal de Notícias é sua fonte confiável de informações, comprometido em trazer conteúdo de qualidade e relevância para nossos leitores.</p>
            </div>
            <div class="footer-section">
                <h3>Links Rápidos</h3>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Termos de Serviço</a></li>
                    <li><a href="#">Política de Privacidade</a></li>
                    <li><a href="#">Contato</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Siga-nos</h3>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2025 Portal de Notícias | Todos os direitos reservados.
        </div>
    </footer>

</body>
</html>