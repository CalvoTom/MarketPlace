<?php
session_start();
require_once 'includes/db.php';

// Récupération de tous les articles avec likes et commentaires
$sql = "SELECT 
    a.id, a.nom, a.description, a.prix, a.date_publication, a.auteur_id, a.image_url,
    u.nom AS auteur_nom, u.prenom AS auteur_prenom,
    COALESCE(l.likes_count, 0) AS likes_count,
    COALESCE(c.comments_count, 0) AS comments_count
FROM articles a
LEFT JOIN utilisateurs u ON a.auteur_id = u.id
LEFT JOIN (
    SELECT article_id, COUNT(*) AS likes_count
    FROM likes
    GROUP BY article_id
) l ON a.id = l.article_id
LEFT JOIN (
    SELECT article_id, COUNT(*) AS comments_count
    FROM commentaires
    GROUP BY article_id
) c ON a.id = c.article_id
ORDER BY a.date_publication DESC";
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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
         <!-- Navigation -->
        <nav class="navbar">
            <a href="index.php" class="logo">MarketPlace</a>
            <div class="nav-links">
                <a href="index.php" class="nav-link">HOME</a>
                <a href="articles.php" class="nav-link active">ARTICLES</a>
                <a href="panier.php" class="nav-link">PANIER</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin"): ?>
                    <a href="admin.php" class="nav-link">DASHBOARD</a>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
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
                        <div class="article-card">
                            <a href="articleDetail.php?id=<?= $article['id'] ?>" style="text-decoration: none; color: inherit;">
                                <?php
                                    $safe_image_url = htmlspecialchars($article['image_url']);
                                    $unique_image_url = $safe_image_url . '?article_id=' . $article['id'];
                                ?>
                                <?php if (!empty($article['image_url'])): ?>
                                    <img src="<?= $unique_image_url ?>"
                                        alt="<?= htmlspecialchars($article['nom']) ?>"
                                        class="article-image"
                                        onerror="handleImageError(this)">
                                    <div class="article-image-placeholder" style="display: none; background: linear-gradient(135deg, #F8582E, #e04a26); color: white; font-size: 18px; align-items: center; justify-content: center;">
                                        📷 Image non disponible
                                    </div>
                                <?php else: ?>
                                    <div class="article-image" style="background: linear-gradient(135deg, #F8582E, #e04a26); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                                        📷 Aucune image
                                    </div>
                                <?php endif; ?>

                                <div class="article-content">
                                    <h3 class="article-name"><?= htmlspecialchars($article['nom']) ?></h3>
                                    <?php if (!empty($article['description'])): ?>
                                        <p class="article-description"><?= htmlspecialchars($article['description']) ?></p>
                                    <?php endif; ?>
                                    <div class="article-price"><?= number_format($article['prix'], 2) ?> €</div>
                                    <div class="article-meta">
                                        <span class="article-author">
                                            Par <?= htmlspecialchars($article['auteur_prenom']) ?> <?= htmlspecialchars($article['auteur_nom']) ?>
                                        </span>
                                        <span class="article-date">
                                            <?= date('d/m/Y', strtotime($article['date_publication'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
    <!-- Footer -->
    <footer class="footer">
        <h2 class="footer-title">MARKETPLACE</h2>
    </footer>
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
