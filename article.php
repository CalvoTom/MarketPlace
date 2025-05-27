<?php
session_start();
require_once 'includes/db.php';

// Récupération de tous les articles
$sql = "SELECT a.*, u.nom, u.prenom 
        FROM Article a 
        JOIN utilisateurs u ON a.author_id = u.id 
        ORDER BY a.publication_date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlace - Tous les articles</title>
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
            text-decoration: none;
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

        .nav-link:hover {
            color: #F8582E;
        }

        .nav-link.active {
            color: #F8582E;
            font-weight: 600;
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

        /* Articles Section */
        .articles-section {
            position: absolute;
            top: 134px;
            left: 48px;
            width: 1344px;
            min-height: 600px;
        }

        .articles-header {
            background: #F9F4EC;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            padding: 32px 48px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .articles-icon {
            width: 60px;
            height: 60px;
            background: #F8582E;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            flex-shrink: 0;
        }

        .articles-title {
            font-weight: 700;
            font-size: 32px;
            line-height: 40px;
            color: #000000;
        }

        .articles-subtitle {
            font-size: 16px;
            color: #666;
            margin-top: 8px;
        }

        .articles-count {
            background: #F8582E;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 48px;
        }

        .article-card {
            background: #FFFFFF;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .article-card:hover {
            transform: translateY(-4px);
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.15);
        }

        .article-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 14px;
        }

        .article-content {
            padding: 20px;
        }

        .article-name {
            font-weight: 600;
            font-size: 18px;
            color: #000000;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .article-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 12px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .article-price {
            font-weight: 700;
            font-size: 20px;
            color: #F8582E;
            margin-bottom: 12px;
        }

        .article-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #999;
        }

        .article-author {
            font-weight: 500;
        }

        .article-date {
            font-style: italic;
        }

        .no-articles {
            text-align: center;
            padding: 80px 20px;
            background: #F9F4EC;
            border-radius: 12px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.12);
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
            
            .articles-section {
                left: 24px;
                width: calc(100% - 48px);
            }
        }

        @media (max-width: 1024px) {
            .articles-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
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
                flex-direction: column;
                width: 100%;
                gap: 12px;
            }
            
            .btn-secondary,
            .btn-primary {
                width: 100%;
            }
            
            .articles-section {
                top: 200px;
            }

            .articles-header {
                padding: 24px;
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }

            .header-content {
                flex-direction: column;
                text-align: center;
            }

            .articles-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .articles-title {
                font-size: 24px;
                line-height: 32px;
            }
        }

        @media (max-width: 480px) {
            .articles-grid {
                grid-template-columns: 1fr;
            }
            
            .article-card {
                margin: 0 auto;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav class="navbar">
            <a href="index.php" class="logo">MarketPlace</a>
            <div class="nav-links">
                <a href="index.php" class="nav-link">HOME</a>
                <a href="articles.php" class="nav-link active">ARTICLES</a>
                <a href="#" class="nav-link">PANIER</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="nav-link">PROFILE</a>
                <?php endif; ?>
            </div>
            <div class="nav-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="btn-secondary">Mon Profil</a>
                    <a href="vente.php" class="btn-primary">Vends tes articles !</a>
                <?php else: ?>
                    <a href="register.php" class="btn-secondary">S'inscrire</a>
                    <a href="login.php" class="btn-primary">Se connecter</a>
                <?php endif; ?>
            </div>
        </nav>

        <!-- Articles Section -->
        <section class="articles-section">
            <!-- Articles Header -->
            <div class="articles-header">
                <div class="header-content">
                    <div class="articles-icon">🛍️</div>
                    <div>
                        <h1 class="articles-title">Tous les articles</h1>
                        <p class="articles-subtitle">Découvrez tous les articles disponibles sur notre marketplace</p>
                    </div>
                </div>
                <div class="articles-count">
                    <?= count($articles) ?> article<?= count($articles) > 1 ? 's' : '' ?>
                </div>
            </div>

            <!-- Articles Grid -->
            <?php if (empty($articles)): ?>
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
            <?php else: ?>
                <div class="articles-grid">
                    <?php foreach ($articles as $article): ?>
                        <a href="article_detail.php?id=<?= $article['id'] ?>" style="text-decoration: none; color: inherit;">
                            <div class="article-card">
                                <?php if (!empty($article['image_link'])): ?>
                                    <img src="<?= htmlspecialchars($article['image_link']) ?>" 
                                         alt="<?= htmlspecialchars($article['name']) ?>" 
                                         class="article-image"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <?php else: ?>
                                    <div class="article-image" style="background: linear-gradient(135deg, #F8582E, #e04a26); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                                        📷 Aucune image
                                    </div>
                                <?php endif; ?>
                                
                                <div class="article-content">
                                    <h3 class="article-name"><?= htmlspecialchars($article['name']) ?></h3>
                                    
                                    <?php if (!empty($article['description'])): ?>
                                        <p class="article-description"><?= htmlspecialchars($article['description']) ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="article-price"><?= number_format($article['price'], 2) ?> €</div>
                                    
                                    <div class="article-meta">
                                        <span class="article-author">
                                            Par <?= htmlspecialchars($article['prenom']) ?> <?= htmlspecialchars($article['nom']) ?>
                                        </span>
                                        <span class="article-date">
                                            <?= date('d/m/Y', strtotime($article['publication_date'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <script>
        function viewArticle(id) {
            window.location.href = 'article_detail.php?id=' + id;
        }
        
        // Hover effects for article cards
        document.querySelectorAll('.article-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-4px)';
                this.style.boxShadow = '0px 8px 16px rgba(0, 0, 0, 0.15)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0px 4px 8px rgba(0, 0, 0, 0.1)';
            });
        });

        // Image error handling
        document.querySelectorAll('.article-image img').forEach(img => {
            img.addEventListener('error', function() {
                this.style.display = 'none';
                const placeholder = this.nextElementSibling;
                if (placeholder) {
                    placeholder.style.display = 'flex';
                }
            });
        });
    </script>
</body>
</html>