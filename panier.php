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

    // Nouvelle action pour modifier la quantité
    if (isset($_POST['action']) && $_POST['action'] === 'update_quantity') {
        $cart_id = $_POST['cart_id'] ?? null;
        $new_quantity = (int)($_POST['new_quantity'] ?? 0);

        if ($cart_id && $new_quantity > 0) {
            // Récupérer les informations actuelles du panier
            $stmt = $conn->prepare('SELECT article_id, quantite FROM cart WHERE id = ?');
            $stmt->execute([$cart_id]);
            $cart_item = $stmt->fetch();

            if ($cart_item) {
                // Vérifier le stock disponible total (stock actuel + quantité dans le panier)
                $stmt = $conn->prepare('SELECT quantite FROM stock WHERE article_id = ?');
                $stmt->execute([$cart_item['article_id']]);
                $stock_info = $stmt->fetch();
                
                $stock_total_disponible = $stock_info['quantite'] + $cart_item['quantite'];

                if ($new_quantity <= $stock_total_disponible) {
                    $conn->beginTransaction();
                    try {
                        // Calculer la différence de quantité
                        $quantity_diff = $new_quantity - $cart_item['quantite'];

                        // Mettre à jour la quantité dans le panier
                        $stmt = $conn->prepare('UPDATE cart SET quantite = ? WHERE id = ?');
                        $stmt->execute([$new_quantity, $cart_id]);

                        // Ajuster le stock (si on augmente la quantité, on diminue le stock et vice versa)
                        $stmt = $conn->prepare('UPDATE stock SET quantite = quantite - ? WHERE article_id = ?');
                        $stmt->execute([$quantity_diff, $cart_item['article_id']]);

                        $conn->commit();
                        $message = "Quantité mise à jour avec succès.";
                    } catch (Exception $e) {
                        $conn->rollBack();
                        $message = "Une erreur est survenue lors de la mise à jour.";
                    }
                } else {
                    $message = "Stock insuffisant. Stock disponible : " . $stock_total_disponible;
                }
            }
        }
    }

    // Redirection pour éviter la resoumission du formulaire
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Récupérer les articles dans le panier avec le stock disponible
$stmt = $conn->prepare("
    SELECT a.*, c.id as cart_id, c.quantite as quantity,
           COALESCE(s.quantite, 0) as stock_disponible
    FROM cart c
    JOIN articles a ON c.article_id = a.id
    LEFT JOIN stock s ON a.id = s.article_id
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
                <a href="panier.php" class="nav-link active">PANIER</a>
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
                <div class="articles-header">
                    <div class="header-content">
                        <div class="articles-icon">🛍️</div>
                        <div>
                            <h1 class="articles-title">Mon Panier</h1>
                        </div>
                    </div>
                    <div class="articles-count">
                        <?= count($cartItems) ?> article<?= count($cartItems) > 1 ? 's' : '' ?>
                    </div>
                </div>

                <?php if (isset($message)): ?>
                    <div class="alert" style="padding: 10px; margin: 10px 0; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px;">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

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
                                
                                <div class="item-actions">
                                    <!-- Contrôles de quantité -->
                                    <form method="POST" class="quantity-controls" id="form_<?= $item['cart_id'] ?>">
                                        <input type="hidden" name="action" value="update_quantity">
                                        <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                        <select name="new_quantity" id="quantity_<?= $item['cart_id'] ?>" class="form-select" onchange="this.form.submit()">
                                            <?php 
                                            $max_quantity = $item['stock_disponible'] + $item['quantity'];
                                            for($i = 1; $i <= $max_quantity; $i++): 
                                            ?>
                                                <option value="<?= $i ?>" <?= ($i == $item['quantity']) ? 'selected' : '' ?>>
                                                    <?= $i ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </form>
                                    
                                    <!-- Bouton supprimer -->
                                    <form method="POST">
                                        <input type="hidden" name="action" value="remove_for_cart">
                                        <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                        <button type="submit" class="btn-secondary">Supprimer</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-total">
                    <h3>Total: <?= number_format($total, 2) ?> €</h3>
                    <form action="checkout.php" method="GET">
                        <button type="submit" class="btn-primary">Procéder au paiement</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <h2 class="footer-title">MARKETPLACE</h2>
    </footer>
</body>
</html>