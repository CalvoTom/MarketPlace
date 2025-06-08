<?php
session_start();
require_once 'includes/db.php';

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupération des articles likés par l'utilisateur
$sql = "SELECT a.*, u.nom, u.prenom,
        COUNT(DISTINCT l2.id) as likes_count,
        COUNT(DISTINCT c.id) as comments_count
        FROM articles a 
        JOIN likes l ON a.id = l.article_id
        JOIN utilisateurs u ON a.auteur_id = u.id 
        LEFT JOIN likes l2 ON a.id = l2.article_id
        LEFT JOIN commentaires c ON a.id = c.article_id
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
    <title>MarketPlace - Mes articles favoris</title>
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
                            <a href="article-detail.php?id=<?= $article['id'] ?>" style="text-decoration: none; color: inherit;">
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
                                            Par <?= htmlspecialchars($article['prenom']) ?> <?= htmlspecialchars($article['nom']) ?>
                                        </span>
                                        <span class="article-date">
                                            <?= date('d/m/Y', strtotime($article['date_publication'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </a>

                            <!-- Interactions Section -->
                            <div class="article-interactions">
                                <div class="interactions-bar">
                                    <div class="like-section">
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_like">
                                            <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                            <button type="submit" class="like-btn liked" title="Retirer des favoris">
                                                ❤️
                                            </button>
                                        </form>
                                        <span class="like-count"><?= $article['likes_count'] ?> like<?= $article['likes_count'] > 1 ? 's' : '' ?></span>
                                    </div>
                                    <button class="comments-toggle" onclick="toggleComments(<?= $article['id'] ?>)">
                                        💬 <?= $article['comments_count'] ?> commentaire<?= $article['comments_count'] > 1 ? 's' : '' ?>
                                    </button>
                                </div>

                                <!-- Comments Section -->
                                <div class="comments-section" id="comments-<?= $article['id'] ?>">
                                    <form method="post" class="comment-form">
                                        <input type="hidden" name="action" value="add_comment">
                                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                        <textarea name="contenu" class="comment-input" placeholder="Écrivez votre commentaire..." required></textarea>
                                        <button type="submit" class="comment-submit">Commenter</button>
                                    </form>

                                    <div class="comments-list">
                                        <?php
                                        // Récupérer les commentaires pour cet article
                                        $comments_sql = "SELECT c.contenu, c.date_commentaire, u.prenom, u.nom 
                                                        FROM commentaires c 
                                                        JOIN utilisateurs u ON c.utilisateur_id = u.id 
                                                        WHERE c.article_id = ? 
                                                        ORDER BY c.date_commentaire DESC 
                                                        LIMIT 5";
                                        $comments_stmt = $conn->prepare($comments_sql);
                                        $comments_stmt->execute([$article['id']]);
                                        $comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);
                                        ?>
                                        
                                        <?php if (empty($comments)): ?>
                                            <p style="font-size: 12px; color: #999; text-align: center; padding: 16px;">
                                                Aucun commentaire pour le moment
                                            </p>
                                        <?php else: ?>
                                            <?php foreach ($comments as $comment): ?>
                                                <div class="comment-item">
                                                    <div class="comment-author">
                                                        <?= htmlspecialchars($comment['prenom'] . ' ' . $comment['nom']) ?>
                                                    </div>
                                                    <div class="comment-content">
                                                        <?= nl2br(htmlspecialchars($comment['contenu'])) ?>
                                                    </div>
                                                    <div class="comment-date">
                                                        <?= date('d/m/Y à H:i', strtotime($comment['date_commentaire'])) ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

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
</body>
</html>
