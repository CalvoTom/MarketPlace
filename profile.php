<?php
session_start();
require_once 'includes/db.php';

unset($_SESSION["profil_edit_authorized"]);

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Gestion des messages de suppression
$message = '';
if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $message = '<div class="message success-message">Article supprimé avec succès.</div>';
} elseif (isset($_GET['error'])) {
    if ($_GET['error'] == 'unauthorized') {
        $message = '<div class="message error-message">Vous n\'êtes pas autorisé à supprimer cet article.</div>';
    } elseif ($_GET['error'] == 'invalid') {
        $message = '<div class="message error-message">Requête invalide.</div>';
    }
}

// Récupération de la photo de profil
$sql = "SELECT profile_picture FROM utilisateurs WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupération du solde utilisateur
$sql_solde = "SELECT sold FROM utilisateurs WHERE id = :id";
$stmt_solde = $conn->prepare($sql_solde);
$stmt_solde->execute([':id' => $_SESSION["user_id"]]);
$user_solde = $stmt_solde->fetch(PDO::FETCH_ASSOC);

$solde = $user_solde ? $user_solde["sold"] : 0;

// Récupération des articles de l'utilisateur
$sql_articles = "SELECT a.*, COUNT(DISTINCT l.id) as likes_count, COUNT(DISTINCT c.id) as comments_count
                FROM articles a
                LEFT JOIN likes l ON a.id = l.article_id
                LEFT JOIN commentaires c ON a.id = c.article_id
                WHERE a.auteur_id = :user_id
                GROUP BY a.id
                ORDER BY a.date_publication DESC";
$stmt_articles = $conn->prepare($sql_articles);
$stmt_articles->execute([':user_id' => $_SESSION["user_id"]]);
$user_articles = $stmt_articles->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si une image existe
$hasImageInDB = !empty($user['profile_picture']);
$profileImage = $hasImageInDB
    ? 'data:image/jpeg;base64,' . base64_encode($user['profile_picture'])
    : 'img/default.png';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlace - Mon Profil</title>
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
                    <a href="profile.php" class="nav-link active">PROFILE</a>
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

        <!-- Profile Section -->
        <section class="profile-section fade-in">
            <?= $message ?>
            <!-- Profile Header -->
            <div class="profile-header">
                <img src="<?= $profileImage ?>" alt="Photo de profil" class="profile-avatar">
                <div class="profile-info">
                    <h1 class="profile-name"><?= htmlspecialchars($_SESSION["prenom"] . ' ' . $_SESSION["nom"]) ?></h1>
                    <p class="profile-email"><?= htmlspecialchars($_SESSION["email"]) ?></p>
                    <div class="profile-actions">
                        <a href="edit.php" class="btn-edit">
                            <span>✏️</span>
                            Modifier mon profil
                        </a>

                        <form method="get" class="btn-logout" action="">
                            <input style="all: unset;" type="submit" name="logout" value="🚪 Se déconnecter">
                        </form>
                    </div>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="profile-details">
                <!-- Informations personnelles -->
                <div class="detail-card">
                    <h3>Informations personnelles</h3>
                    <div class="detail-item">
                        <span class="detail-label">Prénom</span>
                        <span class="detail-value"><?= htmlspecialchars($_SESSION["prenom"]) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Nom</span>
                        <span class="detail-value"><?= htmlspecialchars($_SESSION["nom"]) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email</span>
                        <span class="detail-value"><?= htmlspecialchars($_SESSION["email"]) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Membre depuis</span>
                        <span class="detail-value">Janvier 2024</span>
                    </div>
                </div>

                <!-- Solde et finances -->
                <div class="detail-card">
                    <h3>Mon portefeuille</h3>
                    <div class="solde-display">
                        <div class="solde-amount">
                            <?= number_format($solde, 2, ',', ' ') ?> €
                        </div>
                        <div class="solde-label">Solde disponible</div>
                    </div>
                </div>
            </div>

            <!-- Mes Articles Section -->
            <div class="user-articles-section">
                <div class="articles-header">
                    <div class="header-content">
                        <div class="articles-icon">📝</div>
                        <div>
                            <h3 class="articles-title">Mes articles</h3>
                            <p class="articles-subtitle">Gérez vos articles en vente</p>
                        </div>
                    </div>
                    <div class="articles-count">
                        <?= count($user_articles) ?> article<?= count($user_articles) > 1 ? 's' : '' ?>
                    </div>
                </div>

                <?php if (empty($user_articles)): ?>
                    <div class="no-articles">
                        <div class="no-articles-icon">📦</div>
                        <h4 class="no-articles-title">Aucun article en vente</h4>
                        <p class="no-articles-text">
                            Vous n'avez pas encore mis d'articles en vente.<br>
                            Commencez dès maintenant !
                        </p>
                        <a href="vente.php" class="btn-sell">
                            <span>💰</span>
                            Vendre un article
                        </a>
                    </div>
                <?php else: ?>
                    <div class="user-articles-grid">
                        <?php foreach ($user_articles as $article): ?>
                            <div class="user-article-card">
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
                                        <h4 class="article-name"><?= htmlspecialchars($article['nom']) ?></h4>
                                        
                                        <?php if (!empty($article['description'])): ?>
                                            <p class="article-description"><?= htmlspecialchars($article['description']) ?></p>
                                        <?php endif; ?>
                                        
                                        <div class="article-price"><?= number_format($article['prix'], 2, ',', ' ') ?> €</div>
                                        
                                        <div class="article-meta">
                                            <span class="article-stats">
                                                ❤️ <?= $article['likes_count'] ?> | 💬 <?= $article['comments_count'] ?>
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
            </div>
        </section>
    </div>

    <script>
        // Animation d'entrée pour les cartes
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.detail-card, .stat-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Effet hover sur les cartes
        document.querySelectorAll('.detail-card, .stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.transition = 'transform 0.3s ease';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Animation du solde
        function animateNumber(element, target) {
            const start = 0;
            const duration = 1000;
            const startTime = performance.now();
            
            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const current = start + (target - start) * progress;
                
                element.textContent = current.toFixed(2).replace('.', ',') + ' €';
                
                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }
            
            requestAnimationFrame(update);
        }

        // Fonction de suppression d'article
        function deleteArticle(articleId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet article ? Cette action est irréversible.')) {
                // Créer un formulaire pour envoyer la requête de suppression
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete-article.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'article_id';
                input.value = articleId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
