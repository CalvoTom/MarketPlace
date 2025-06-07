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
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav class="navbar">
            <a href="index.php" class="logo">MarketPlace</a>
            <div class="nav-links">
                <a href="index.php" class="nav-link">HOME</a>
                <a href="articles.php" class="nav-link active">ARTICLES</a>
                <a href="Panier.php" class="nav-link">PANIER</a>
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
                        <div class="article-card" onclick="viewArticle(<?= $article['id'] ?>)">
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
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <script>
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