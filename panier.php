<?php
session_start();
require_once 'includes/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];

    if (isset($_POST['action']) && $_POST['action'] === 'remove_for_cart') {
        $cart_id = $_POST['cart_id'] ?? null;

        if ($cart_id) {
            // Récupérer la quantité du panier et l'article_id avant la suppression
            $stmt = $conn->prepare('SELECT article_id, quantite FROM cart WHERE id = ?');
            $stmt->execute([$cart_id]);
            $cart_item = $stmt->fetch();

            if ($cart_item) {
                $conn->beginTransaction();
                try {
                    // Supprimer l'article du panier
                    $stmt = $conn->prepare('DELETE FROM cart WHERE id = ?');
                    $stmt->execute([$cart_id]);

                    // Restaurer le stock
                    $stmt = $conn->prepare('UPDATE stock SET quantite = quantite + ? WHERE article_id = ?');
                    $stmt->execute([$cart_item['quantite'], $cart_item['article_id']]);

                    $conn->commit();
                    $message = "Article supprimé du panier avec succès.";
                } catch (Exception $e) {
                    $conn->rollBack();
                    $message = "Une erreur est survenue lors de la suppression.";
                }
            }
        }
    }
}

// Récupérer les articles dans le panier
$stmt = $conn->prepare("
    SELECT a.*, c.id as cart_id, c.quantite as quantity
    FROM cart c
    JOIN articles a ON c.article_id = a.id
    WHERE c.utilisateur_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

// Calculer le total
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['prix'] * $item['quantity'];
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">  
    <title>Mon Panier</title>
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
                <a href="panier.php" class="nav-link active">PANIER</a>
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

        <div class="cart-container">        
            <?php if (empty($cartItems)): ?>
                <div class="no-articles">
                    <div class="no-articles-icon">📦</div>
                    <h2 class="no-articles-title">Aucun article dans le panier</h2>
                    <p class="no-articles-text">
                        Il n'y a pas encore d'articles dans votre panier.<br>
                        Aller faire un tour pour trouver votre bonheur !
                    </p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="articles.php" class="btn-sell">
                            <span>🪑</span>
                            Trouver un article
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn-sell">
                            <span>👤</span>
                            S'inscrire pour acheter
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>">
                            <div class="item-details">
                                <h3><?= htmlspecialchars($item['nom']) ?></h3>
                                <p><?= htmlspecialchars($item['description']) ?></p>
                                <p class="price">
                                    <?= number_format($item['prix'], 2) ?> € x <?= $item['quantity'] ?> 
                                    = <?= number_format($item['prix'] * $item['quantity'], 2) ?> €
                                </p>
                                <form method="POST">
                                    <input type="hidden" name="action" value="remove_for_cart">
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                    <button type="submit" class="btn-primary">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-total">
                    <h3>Total: <?= number_format($total, 2) ?> €</h3>
                    <form action="checkout" method="POST">
                        <button type="submit" class="btn-primary">Procéder au paiement</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
