<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = $conn;

// Requête pour récupérer les articles avec infos auteur
$sql = "SELECT a.id, a.name, a.description, a.price, a.publication_date, a.author_id, a.image_link,
        u.nom, u.prenom
        FROM Article a
        LEFT JOIN utilisateurs u ON a.author_id = u.id
        ORDER BY a.publication_date DESC";

$stmt = $pdo->query($sql);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlace - Accueil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #FBF9F5;
            position: relative;
            width: 100%;
            min-height: 100vh;
        }

        .container {
            max-width: 1440px;
            margin: 0 auto;
            position: relative;
        }

        /* Navigation */
        .navbar {
            position: absolute;
            width: 1344px;
            height: 54px;
            left: 48px;
            top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }

        .logo {
            font-weight: 700;
            font-size: 48px;
            line-height: 58px;
            color: #000000;
        }

        .nav-links {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .nav-link {
            font-weight: 400;
            font-size: 16px;
            line-height: 19px;
            color: #1E1D19;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-link.active {
            color: #F8582E;
            font-weight: 600;
        }

        .nav-link:hover {
            color: #F8582E;
        }

        .nav-buttons {
            display: flex;
            gap: 24px;
        }

        .btn-secondary {
            width: 117px;
            height: 46px;
            border: 1px solid #F8582E;
            border-radius: 8px;
            background: transparent;
            color: #1E1D19;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-secondary:hover {
            background: #F8582E;
            color: #FBF9F5;
        }

        .btn-primary {
            width: 199px;
            height: 46px;
            background: #F8582E;
            border-radius: 8px;
            border: none;
            color: #FBF9F5;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary:hover {
            background: #e04a26;
        }

        /* Hero Section */
        .hero {
            position: absolute;
            width: 1344px;
            height: 592px;
            left: 48px;
            top: 159px;
            background: #F9F4EC;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 48px;
        }

        .hero-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 40px;
        }

        .hero-title {
            font-weight: 700;
            font-size: 114px;
            line-height: 138px;
            color: #F8582E;
            text-align: center;
            margin-bottom: 100px;
        }

        .hero-features {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .feature-btn {
            width: 214px;
            height: 46px;
            border: 1px solid #000000;
            border-radius: 12px;
            background: transparent;
            color: #000000;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .feature-btn:hover {
            background: #000000;
            color: #FBF9F5;
        }

        .hero-image {
            width: 573px;
            height: 400px;
            background: linear-gradient(135deg, #F8582E, #e04a26);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        /* Tendances Section */
        .tendances {
            position: absolute;
            top: 822px;
            left: 48px;
            width: 100%;
        }

        .tendances-title {
            font-weight: 700;
            font-size: 114px;
            line-height: 138px;
            color: #000000;
            margin-bottom: 80px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(406px, 1fr));
            gap: 40px;
            margin-top: 50px;
            max-width: 1344px;
        }

        .product-card {
            background: #F9F4EC;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            padding: 24px;
            height: auto;
            min-height: 451px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            width: 100%;
            height: 250px;
            border-radius: 8px;
            margin-bottom: 16px;
            object-fit: cover;
            background: #EFEFEF;
        }

        .product-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .product-title {
            font-size: 16px;
            line-height: 19px;
            color: #000000;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .product-description {
            font-size: 14px;
            line-height: 18px;
            color: #666;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            font-weight: 700;
            font-size: 32px;
            line-height: 40px;
            color: #F8582E;
            margin-bottom: 12px;
        }

        .product-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 12px;
            color: #888;
        }

        .product-author {
            font-weight: 500;
            color: #1E1D19;
        }

        .product-date {
            color: #999;
        }

        .no-articles {
            text-align: center;
            padding: 80px 20px;
            background: #F9F4EC;
            border-radius: 12px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.12);
        }

        .btn-sell {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #F8582E;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .btn-sell:hover {
            background: #e04a26;
        }

        .no-articles-icon {
            font-size: 64px;
            margin-bottom: 24px;
        }

        .no-articles-title {
            font-size: 24px;
            font-weight: 600;
            color: #000;
            margin-bottom: 12px;
        }

        .no-articles-text {
            font-size: 16px;
            color: #666;
            margin-bottom: 24px;
        }

        /* Philosophie Section */
        .philosophie {
            position: absolute;
            top: 1780px;
            left: 48px;
            width: 1344px;
            height: 1123px;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('/placeholder.svg?height=1123&width=1344');
            background-size: cover;
            background-position: center;
            border-radius: 12px;
            box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            align-items: flex-start;
            padding: 100px;
        }

        .philosophy-card {
            width: 452px;
            height: 275px;
            background: #FBF9F5;
            box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
            border-radius: 12px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .philosophy-icon {
            width: 80px;
            height: 71px;
            background: #1E1D19;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .philosophy-card:nth-child(2) {
            margin-left: auto;
        }

        /* Retours Section */
        .retours {
            position: absolute;
            top: 2988px;
            left: 48px;
            width: 1344px;
        }

        .retours-title {
            font-weight: 700;
            font-size: 114px;
            line-height: 138px;
            color: #000000;
            text-align: right;
            margin-bottom: 80px;
        }

        .testimonials {
            display: flex;
            gap: 48px;
            margin-top: 300px;
        }

        .testimonial-card {
            width: 649px;
            height: 451px;
            background: #F9F4EC;
            box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25);
            border-radius: 12px;
            padding: 48px;
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .testimonial-avatar {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, #F8582E, #e04a26);
            border-radius: 50%;
            flex-shrink: 0;
        }

        .testimonial-content {
            flex: 1;
        }

        /* Footer */
        .footer {
            position: absolute;
            top: 3835px;
            left: 0;
            width: 100%;
            height: 550px;
            background: #F8582E;
            box-shadow: 0px -4px 10px rgba(0, 0, 0, 0.12);
            border-radius: 25px 25px 0px 0px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .footer-title {
            font-weight: 700;
            font-size: 140px;
            line-height: 169px;
            color: #FBF9F5;
            opacity: 0.25;
        }

        /* Responsive */
        @media (max-width: 1440px) {
            .container {
                max-width: 100%;
                padding: 0 24px;
            }
            
            .navbar {
                width: calc(100% - 48px);
                left: 24px;
            }
            
            .hero {
                width: calc(100% - 48px);
                left: 24px;
            }
            
            .tendances {
                left: 24px;
                width: calc(100% - 48px);
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 24px;
            }
            
            .philosophie {
                left: 24px;
                width: calc(100% - 48px);
            }
            
            .retours {
                left: 24px;
                width: calc(100% - 48px);
            }
            
            .testimonials {
                flex-direction: column;
                gap: 24px;
            }
            
            .testimonial-card {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .hero {
                flex-direction: column;
                height: auto;
                padding: 48px 24px;
            }
            
            .hero-title {
                font-size: 64px;
                line-height: 80px;
            }
            
            .hero-image {
                width: 100%;
                height: 300px;
                margin-top: 32px;
            }
            
            .tendances-title,
            .retours-title {
                font-size: 64px;
                line-height: 80px;
            }
            
            .footer-title {
                font-size: 80px;
                line-height: 100px;
            }
            
            .navbar {
                flex-direction: column;
                height: auto;
                gap: 16px;
                padding: 16px;
            }
            
            .nav-links {
                order: 2;
            }
            
            .nav-buttons {
                order: 3;
            }

            .products-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .product-card {
                min-height: auto;
            }
        }

        /* Animation classes */
        .fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav class="navbar">
            <div class="logo">MarketPlace</div>
            <div class="nav-links">
                <a href="index.php" class="nav-link active">HOME</a>
                <a href="article" class="nav-link">ARTICLES</a>
                <a href="#" class="nav-link">PANIER</a>
                <a href="profile" class="nav-link">PROFILE</a>
            </div>
            <div class="nav-buttons">
                <a href="register.php" class="btn-secondary">S'inscrire</a>
                <a href="vente.php" class="btn-primary">Vends tes articles !</a>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1 class="hero-title">MARKETPLACE</h1>
                <div class="hero-features">
                    <button class="feature-btn">Vendre plus facilement</button>
                    <button class="feature-btn">Acheter en sécurité</button>
                    <button class="feature-btn">Communauté active</button>
                </div>
            </div>
            <div class="hero-image">
                Image Hero
            </div>
        </section>

        <!-- Tendances Section -->
        <section class="tendances">
            <h2 class="tendances-title">Nos Tendances</h2>
            <div class="products-grid">
                <?php if (count($articles) > 0): ?>
                    <?php foreach ($articles as $article): ?>
                        <div class="product-card fade-in">
                            <?php if (!empty($article['image_link'])): ?>
                                <img src="<?= htmlspecialchars($article['image_link']) ?>" alt="<?= htmlspecialchars($article['name']) ?>" class="product-image">
                            <?php else: ?>
                                <div class="product-image" style="background: linear-gradient(135deg, #F8582E, #e04a26); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                                    📷 Aucune image
                                </div>
                            <?php endif; ?>
                            <div class="product-info">
                                <h3 class="product-title"><?= htmlspecialchars($article['name']) ?></h3>
                                <p class="product-description"><?= nl2br(htmlspecialchars($article['description'])) ?></p>
                                <p class="product-price"><?= number_format($article['price'], 2, ',', ' ') ?> €</p>
                                <div class="product-meta">
                                    <span class="product-author">
                                        Par <?= htmlspecialchars($article['prenom'] . ' ' . $article['nom']) ?>
                                    </span>
                                    <span class="product-date">
                                        Publié le <?= date('d/m/Y H:i', strtotime($article['publication_date'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-articles">
                        <div class="no-articles-icon">📦</div>
                        <h2 class="no-articles-title">Aucun article disponible</h2>
                        <p class="no-articles-text">
                            Il n'y a pas encore d'articles en vente sur notre marketplace.<br>
                            Soyez le premier à publier un article !
                        </p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="vente.php" class="btn-sell">
                                <span>💰</span>
                                Vendre un article
                            </a>
                        <?php else: ?>
                            <a href="register.php" class="btn-sell">
                                <span>👤</span>
                                S'inscrire pour vendre
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Philosophie Section -->
        <section class="philosophie">
            <div class="philosophy-card">
                <div class="philosophy-icon"></div>
                <div class="philosophy-content">
                    <h3>Notre Vision</h3>
                    <p>Créer une plateforme de vente simple et accessible à tous, où chacun peut vendre et acheter en toute confiance.</p>
                </div>
            </div>
            <div class="philosophy-card">
                <div class="philosophy-icon"></div>
                <div class="philosophy-content">
                    <h3>Notre Mission</h3>
                    <p>Faciliter les échanges entre particuliers en offrant un environnement sécurisé et convivial pour tous.</p>
                </div>
            </div>
            <div class="philosophy-card">
                <div class="philosophy-icon"></div>
                <div class="philosophy-content">
                    <h3>Nos Valeurs</h3>
                    <p>Transparence, sécurité et simplicité sont au cœur de notre marketplace pour une expérience optimale.</p>
                </div>
            </div>
        </section>

        <!-- Retours Section -->
        <section class="retours">
            <h2 class="retours-title">LES RETOURS</h2>
            <div class="testimonials">
                <div class="testimonial-card">
                    <div class="testimonial-avatar"></div>
                    <div class="testimonial-content">
                        <h3>Marie Dubois</h3>
                        <p>"Excellente expérience sur cette plateforme ! J'ai pu vendre mes articles facilement et rapidement. L'interface est intuitive et le service client très réactif."</p>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-avatar"></div>
                    <div class="testimonial-content">
                        <h3>Pierre Martin</h3>
                        <p>"Je recommande vivement ce marketplace. Les transactions sont sécurisées et j'ai trouvé des articles uniques à des prix très intéressants."</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <h2 class="footer-title">MARKETPLACE</h2>
        </footer>
    </div>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    // Add smooth scrolling logic here if needed
                }
            });
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.product-card, .philosophy-card, .testimonial-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });

        // Product card hover effects
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function() {
                // Ici vous pouvez ajouter la redirection vers la page détail de l'article
                // window.location.href = 'article.php?id=' + articleId;
                console.log('Clic sur l\'article');
            });
        });

        // Button hover effects and interactions
        document.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', function() {
                // Add click animation
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });
        });

        // Parallax effect for hero section
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero');
            if (hero) {
                hero.style.transform = `translateY(${scrolled * 0.1}px)`;
            }
        });
    </script>
</body>
</html>