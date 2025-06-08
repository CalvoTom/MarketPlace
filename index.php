<?php
session_start();
require_once 'includes/db.php';

$sql = "SELECT a.id, a.nom, a.description, a.prix, a.date_publication, a.auteur_id, a.image_url,
               u.nom AS auteur_nom, u.prenom AS auteur_prenom,
               COUNT(DISTINCT l.id) as likes_count,
               COUNT(DISTINCT c.id) as comments_count
        FROM articles a
        LEFT JOIN utilisateurs u ON a.auteur_id = u.id
        LEFT JOIN likes l ON a.id = l.article_id
        LEFT JOIN commentaires c ON a.id = c.article_id
        GROUP BY a.id
        ORDER BY a.date_publication DESC";

$stmt = $conn->query($sql);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlace - Accueil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav class="navbar">
            <a href="index.php" class="logo">MarketPlace</a>
            <div class="nav-links">
                <a href="index.php" class="nav-link active">HOME</a>
                <a href="articles.php" class="nav-link">ARTICLES</a>
                <a href="panier.php" class="nav-link">PANIER</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="nav-link">PROFILE</a>
                    <a href="articleLike.php" class="nav-link nav-heart">❤️</a>
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
                <img src="/img/image.png" alt="hero image">
            </div>
        </section>

        <!-- Tendances Section -->
        <section class="tendances">
            <h2 class="tendances-title">Nos Tendances</h2>
            <div class="products-grid">
                <?php if (count($articles) > 0): ?>
                    <?php foreach ($articles as $article): ?>
                        <div class="product-card">
                            <a href="articleDetail.php?id=<?= $article['id'] ?>" style="text-decoration: none; color: inherit;">
                                <?php if (!empty($article['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($article['image_url']) ?>" alt="<?= htmlspecialchars($article['nom']) ?>" class="product-image">
                                <?php else: ?>
                                    <div class="product-image" style="background: linear-gradient(135deg, #F8582E, #e04a26); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                                        📷 Aucune image
                                    </div>
                                <?php endif; ?>
                                <div class="product-info">
                                    <h3 class="product-title"><?= htmlspecialchars($article['nom']) ?></h3>
                                    <p class="product-description"><?= nl2br(htmlspecialchars($article['description'])) ?></p>
                                    <p class="product-price"><?= number_format($article['prix'], 2, ',', ' ') ?> €</p>
                                    <div class="product-meta">
                                        <span class="product-author">
                                            Par <?= htmlspecialchars($article['auteur_prenom'] . ' ' . $article['auteur_nom']) ?>
                                        </span>
                                        <span class="product-date">
                                            Publié le <?= date('d/m/Y H:i', strtotime($article['date_publication'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
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
        <!-- Footer -->
        <footer class="footer">
            <h2 class="footer-title">MARKETPLACE</h2>
        </footer>
    </div>

    <script>
        // Button hover effects
        document.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });
        });
    </script>
</body>
</html>
