<?php
session_start();
require_once 'includes/db.php';

// Traitement des actions (likes et commentaires)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    
    // Gestion des likes
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_like') {
        $article_id = (int)$_POST['article_id'];
        
        // Vérifier si l'utilisateur a déjà liké
        $check_like = $conn->prepare("SELECT id FROM likes WHERE utilisateur_id = ? AND article_id = ?");
        $check_like->execute([$user_id, $article_id]);
        
        if ($check_like->fetch()) {
            // Supprimer le like
            $delete_like = $conn->prepare("DELETE FROM likes WHERE utilisateur_id = ? AND article_id = ?");
            $delete_like->execute([$user_id, $article_id]);
        } else {
            // Ajouter le like
            $add_like = $conn->prepare("INSERT INTO likes (utilisateur_id, article_id) VALUES (?, ?)");
            $add_like->execute([$user_id, $article_id]);
        }
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

// Requête pour récupérer les articles avec infos auteur, likes et commentaires
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

// Pour chaque article, vérifier si l'utilisateur connecté a liké
if (isset($_SESSION['user_id'])) {
    foreach ($articles as &$article) {
        $check_user_like = $conn->prepare("SELECT id FROM likes WHERE utilisateur_id = ? AND article_id = ?");
        $check_user_like->execute([$_SESSION['user_id'], $article['id']]);
        $article['user_liked'] = $check_user_like->fetch() ? true : false;
    }
}
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

                            <!-- Interactions Section reste inchangée -->
                            <div class="product-interactions">
                                <div class="interactions-bar">
                                    <div class="like-section">
                                        <?php if (isset($_SESSION['user_id'])): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_like">
                                                <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                                <button type="submit" class="like-btn <?= isset($article['user_liked']) && $article['user_liked'] ? 'liked' : '' ?>">
                                                    <?= isset($article['user_liked']) && $article['user_liked'] ? '❤️' : '🤍' ?>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="like-btn">🤍</span>
                                        <?php endif; ?>
                                        <span class="like-count"><?= $article['likes_count'] ?> like<?= $article['likes_count'] > 1 ? 's' : '' ?></span>
                                    </div>
                                    <button class="comments-toggle" onclick="toggleComments(<?= $article['id'] ?>)">
                                        💬 <?= $article['comments_count'] ?> commentaire<?= $article['comments_count'] > 1 ? 's' : '' ?>
                                    </button>
                                </div>

                                <!-- Comments Section -->
                                <div class="comments-section" id="comments-<?= $article['id'] ?>">
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <form method="post" class="comment-form">
                                            <input type="hidden" name="action" value="add_comment">
                                            <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                            <textarea name="contenu" class="comment-input" placeholder="Écrivez votre commentaire..." required></textarea>
                                            <button type="submit" class="comment-submit">Commenter</button>
                                        </form>
                                    <?php endif; ?>

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
        // Toggle comments section
        function toggleComments(articleId) {
            const commentsSection = document.getElementById('comments-' + articleId);
            commentsSection.classList.toggle('show');
        }

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
