<?php
require_once 'includes/db.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Récupérer les articles dans le panier
$stmt = $conn->prepare("
    SELECT a.*, c.id as cart_id
    FROM cart c
    JOIN articles a ON c.article_id = a.id
    WHERE c.utilisateur_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

// Calculer le total
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['prix'];
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
        <h1>Mon Panier</h1>
        
        <?php if (empty($cartItems)): ?>
            <div class="cart-items">
                <div class="cart-item">
                    <p>Votre panier est vide.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>">
                        <div class="item-details">
                            <h3><?= htmlspecialchars($item['nom']) ?></h3>
                            <p><?= htmlspecialchars($item['description']) ?></p>
                            <p class="price"><?= number_format($item['prix'], 2) ?> €</p>
                            <form action="remove_from_cart.php" method="POST">
                                <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                <button type="submit">Supprimer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-total">
                <h3>Total: <?= number_format($total, 2) ?> €</h3>
                <form action="checkout.php" method="POST">
                    <button type="submit">Procéder au paiement</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
