<?php
session_start();
require_once 'includes/db.php';

if (!isset($_GET['id'])) {
    header("Location: articles.php");
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT a.*, a.id as articles_id, a.nom AS article_nom, 
                        u.nom AS user_nom, u.prenom,
                        COALESCE(s.quantite, 0) as stock_disponible
                        FROM articles a 
                        LEFT JOIN utilisateurs u ON a.auteur_id = u.id 
                        LEFT JOIN stock s ON a.id = s.article_id
                        WHERE a.id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    header("Location: articles.php");
    exit;
}

// Récupérer le nombre de likes
$likes_stmt = $conn->prepare("SELECT COUNT(*) as likes_count FROM likes WHERE article_id = ?");
$likes_stmt->execute([$id]);
$likes = $likes_stmt->fetch();
$likes_count = $likes ? $likes['likes_count'] : 0;

// Vérifier si l'utilisateur connecté a liké
$user_liked = false;
if (isset($_SESSION['user_id'])) {
    $check_like = $conn->prepare("SELECT id FROM likes WHERE utilisateur_id = ? AND article_id = ?");
    $check_like->execute([$_SESSION['user_id'], $id]);
    $user_liked = $check_like->fetch() ? true : false;
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];

    if (isset($_POST['action']) && $_POST['action'] === 'toggle_like') {
        $check_like = $conn->prepare("SELECT id FROM likes WHERE utilisateur_id = ? AND article_id = ?");
        $check_like->execute([$user_id, $id]);

        if ($check_like->fetch()) {
            $delete_like = $conn->prepare("DELETE FROM likes WHERE utilisateur_id = ? AND article_id = ?");
            $delete_like->execute([$user_id, $id]);
            $user_liked = false;
            $likes_count--;
        } else {
            $add_like = $conn->prepare("INSERT INTO likes (utilisateur_id, article_id) VALUES (?, ?)");
            $add_like->execute([$user_id, $id]);
            $user_liked = true;
            $likes_count++;
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'add_comment') {
        $contenu = trim($_POST['contenu']);
        if (!empty($contenu)) {
            $add_comment = $conn->prepare("INSERT INTO commentaires (utilisateur_id, article_id, contenu) VALUES (?, ?, ?)");
            $add_comment->execute([$user_id, $id, $contenu]);
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'add_article') {
        $article_id = $_POST['articles_id'] ?? null;
        $user_id = $_POST['user_id'] ?? null;
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($article_id && $user_id && $quantity > 0) {
            // Vérifier le stock disponible
            $check_stock = $conn->prepare('SELECT quantite FROM stock WHERE article_id = ?');
            $check_stock->execute([$article_id]);
            $stock = $check_stock->fetch();

            if (!$stock || $stock['quantite'] < $quantity) {
                $message = "Désolé, stock insuffisant.";
            } else {
                // Vérifier si l'article est déjà dans le panier
                $check_cart = $conn->prepare('SELECT COUNT(*) as count FROM cart WHERE utilisateur_id = ? AND article_id = ?');
                $check_cart->execute([$user_id, $article_id]);
                $in_cart = $check_cart->fetch();

                if ($in_cart['count'] > 0) {
                    $message = "Cet article est déjà dans votre panier.";
                } else {
                    // Ajouter au panier et diminuer le stock
                    $conn->beginTransaction();
                    try {
                        $stmt = $conn->prepare('INSERT INTO cart (utilisateur_id, article_id, quantite) VALUES (?, ?, ?)');
                        $stmt->execute([$user_id, $article_id, $quantity]);

                        $update_stock = $conn->prepare('UPDATE stock SET quantite = quantite - ? WHERE article_id = ?');
                        $update_stock->execute([$quantity, $article_id]);

                        $conn->commit();
                        header("Location: Panier.php");
                        exit();
                    } catch (Exception $e) {
                        $conn->rollBack();
                        $message = "Une erreur est survenue lors de l'ajout au panier.";
                    }
                }
            }
        }
    }

    header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $id);
    exit();
}

// Récupérer les commentaires
$comments_stmt = $conn->prepare("SELECT c.contenu, c.date_commentaire, u.prenom, u.nom 
                                 FROM commentaires c 
                                 JOIN utilisateurs u ON c.utilisateur_id = u.id 
                                 WHERE c.article_id = ? 
                                 ORDER BY c.date_commentaire DESC");
$comments_stmt->execute([$id]);
$comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);
$comments_count = count($comments);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlace - <?= htmlspecialchars($article['article_nom']) ?></title>
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

        <section class="articles-section">
            <div class="articles-header">
                <div class="header-content">
                    <div class="articles-icon">📖</div>
                    <div>
                        <h1 class="articles-title"><?= htmlspecialchars($article['article_nom']) ?></h1>
                        <p class="articles-subtitle">Détails de l'article</p>
                    </div>
                </div>
                <div class="articles-count">
                    <?= number_format($article['prix'], 2, ',', ' ') ?> €
                </div>
            </div>

            <div class="detail-container">
                <div class="detail-image-container">
                    <?php if (!empty($article['image_url'])): ?>
                        <img src="<?= htmlspecialchars($article['image_url']) ?>" 
                             alt="<?= htmlspecialchars($article['article_nom']) ?>" 
                             class="detail-image"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="detail-image" style="display: none; background: linear-gradient(135deg, #F8582E, #e04a26); color: white; font-size: 18px; align-items: center; justify-content: center;">
                            📷 Image non disponible
                        </div>
                    <?php else: ?>
                        <div class="detail-image" style="background: linear-gradient(135deg, #F8582E, #e04a26); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                            📷 Aucune image
                        </div>
                    <?php endif; ?>
                </div>
                <div class="detail-content">
                    <div class="detail-header">
                        <h1 class="detail-title"><?= htmlspecialchars($article['article_nom']) ?></h1>
                        <div class="detail-price"><?= number_format($article['prix'], 2, ',', ' ') ?> €</div>
                    </div>
                    
                    <div class="detail-meta">
                        Vendu par <?= htmlspecialchars($article['prenom'] . ' ' . $article['user_nom']) ?><br>
                        Publié le <?= date('d/m/Y à H:i', strtotime($article['date_publication'])) ?>
                    </div>
                    
                    <div class="detail-description">
                        <?= nl2br(htmlspecialchars($article['description'])) ?>
                    </div>

                    <div class="product-interactions">
                        <div class="interactions-bar">
                            <div class="like-section">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_like">
                                        <button type="submit" class="like-btn <?= $user_liked ? 'liked' : '' ?>">
                                            <?= $user_liked ? '❤️' : '🤍' ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="like-btn">🤍</span>
                                <?php endif; ?>
                                <span class="like-count"><?= $likes_count ?> like<?= $likes_count > 1 ? 's' : '' ?></span>
                            </div>
                            <button class="comments-toggle" onclick="toggleComments()">
                                💬 <?= $comments_count ?> commentaire<?= $comments_count > 1 ? 's' : '' ?>
                            </button>
                        </div>

                        <div class="comments-section show" id="comments-section">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form method="post" class="comment-form">
                                    <input type="hidden" name="action" value="add_comment">
                                    <textarea name="contenu" class="comment-input" placeholder="Écrivez votre commentaire..." required></textarea>
                                    <button type="submit" class="comment-submit">Commenter</button>
                                </form>
                            <?php endif; ?>

                            <div class="comments-list">
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

                    <div class="stock-info">
                        <?php if ($article['stock_disponible'] > 0): ?>
                            <p class="stock-available">Stock disponible : <?= $article['stock_disponible'] ?></p>
                        <?php else: ?>
                            <p class="stock-unavailable">Article épuisé</p>
                        <?php endif; ?>
                    </div>

                    <div class="detail-actions">
                        <a href="javascript:history.back()" class="back-link">Retour aux articles</a>
                        <form method="post" class="cart-form">
                            <input type="hidden" name="action" value="add_article">
                            <input type="hidden" name="articles_id" value="<?= $article['articles_id'] ?>">
                            <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?? '' ?>">
                            
                            <div class="quantity-selector">
                                <select name="quantity" class="form-select" <?= ($article['stock_disponible'] <= 0) ? 'disabled' : '' ?>>
                                    <?php for($i = 1; $i <= $article['stock_disponible']; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                                <button type="submit" class="btn-cart" <?= ($article['stock_disponible'] <= 0) ? 'disabled' : '' ?>>
                                    <?= ($article['stock_disponible'] > 0) ? 'Ajouter au panier' : 'Article épuisé' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        function toggleComments() {
            const commentsSection = document.getElementById('comments-section');
            commentsSection.classList.toggle('show');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const detailImage = document.querySelector('.detail-image-container img');
            if (detailImage) {
                detailImage.addEventListener('error', function () {
                    this.style.display = 'none';
                    const placeholder = this.nextElementSibling;
                    if (placeholder) {
                        placeholder.style.display = 'flex';
                    }
                });
            }
        });
    </script>
</body>
</html>
