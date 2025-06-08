<?php
session_start();
require_once 'includes/db.php';

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupération des articles likés par l'utilisateur
$sql = "SELECT a.*, u.nom AS auteur_nom, u.prenom AS auteur_prenom,
        COUNT(DISTINCT l2.id) AS likes_count
        FROM articles a
        JOIN likes l ON a.id = l.article_id
        JOIN utilisateurs u ON a.auteur_id = u.id
        LEFT JOIN likes l2 ON a.id = l2.article_id
        WHERE l.utilisateur_id = ?
        GROUP BY a.id
        ORDER BY l.date_like DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$liked_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement des actions (unlike et commentaires)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Gestion des likes (unlike)
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_like') {
        $article_id = (int)$_POST['article_id'];
        
        // Supprimer le like (puisqu'on est sur la page des articles likés)
        $delete_like = $conn->prepare("DELETE FROM likes WHERE utilisateur_id = ? AND article_id = ?");
        $delete_like->execute([$user_id, $article_id]);
    }
    
    // Gestion des commentaires
    if (isset($_POST['action']) && $_POST['action'] === 'add_comment') {
        $article_id = (int)$_POST['article_id'];
        $contenu = trim($_POST['contenu']);
        
        if (!empty($contenu)) {
            $add_comment = $conn->prepare("INSERT INTO commentaires (utilisateur_id, article_id, contenu) VALUES (?, ?, ?)");
            $add_comment->execute([$user_id, $article_id, $contenu]);
        }
    }
    
    // Redirection pour éviter la resoumission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlace</title>
    <link rel="icon" type="image/png" href="/img/favicon.png">
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
                <a href="articles.php" class="nav-link">ARTICLES</a>
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

        <!-- Liked Articles Section -->
        <section class="articles-section">
            <!-- Articles Header -->
            <div class="articles-header">
                <div class="header-content">
                    <div class="articles-icon">❤️</div>
                    <div>
                        <h1 class="articles-title">Mes articles favoris</h1>
                        <p class="articles-subtitle">Tous les articles que vous avez aimés</p>
                    </div>
                </div>
                <div class="articles-count">
                    <?= count($liked_articles) ?> article<?= count($liked_articles) > 1 ? 's' : '' ?>
                </div>
            </div>

            <!-- Articles Grid -->
            <?php if (empty($liked_articles)): ?>
                <div class="no-articles">
                    <div class="no-articles-icon">💔</div>
                    <h2 class="no-articles-title">Aucun article favori</h2>
                    <p class="no-articles-text">
                        Vous n'avez pas encore d'articles favoris.<br>
                        Explorez notre marketplace et likez les articles qui vous plaisent !
                    </p>
                    <a href="articles.php" class="btn-sell">
                        <span>🛍️</span>
                        Découvrir les articles
                    </a>
                </div>
            <?php else: ?>
                <div class="articles-grid">
                    <?php foreach ($liked_articles as $article): ?>
                        <div class="article-card">
                            <a href="articleDetail.php?id=<?= $article['id'] ?>" style="text-decoration: none; color: inherit;">
                                <?php if (!empty($article['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($article['image_url']) ?>" 
                                         alt="<?= htmlspecialchars($article['nom']) ?>" 
                                         class="article-image"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="article-image" style="display: none; background: linear-gradient(135deg, #F8582E, #e04a26); color: white; font-size: 18px;">
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
</body>
<script>
    // Toggle comments section
    function toggleComments(articleId) {
        const commentsSection = document.getElementById('comments-' + articleId);
        commentsSection.classList.toggle('show');
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
</html>
