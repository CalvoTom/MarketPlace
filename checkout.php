<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user's current balance
$stmt = $conn->prepare("SELECT sold FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$current_balance = $user['sold'];

// Get cart total
$stmt = $conn->prepare("
    SELECT SUM(a.prix * c.quantite) as total
    FROM cart c
    JOIN articles a ON c.article_id = a.id
    WHERE c.utilisateur_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$result = $stmt->fetch();
$total = $result['total'] ?? 0;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adresse = $_POST['adresse'] ?? '';
    $ville = $_POST['ville'] ?? '';
    $code_postal = $_POST['code_postal'] ?? '';

    if (empty($adresse) || empty($ville) || empty($code_postal)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($current_balance < $total) {
        $error = "Solde insuffisant pour effectuer cet achat.";
    } else {
        try {
            $conn->beginTransaction();

            // Créer la facture
            $stmt = $conn->prepare("INSERT INTO invoice (utilisateur_id, montant, adresse_facturation, ville_facturation, code_postal_facturation) 
                                    VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $total, $adresse, $ville, $code_postal]);

            // Transfert d'argent aux vendeurs
            $stmt = $conn->prepare("
                SELECT a.auteur_id, a.prix, c.quantite
                FROM cart c
                JOIN articles a ON c.article_id = a.id
                WHERE c.utilisateur_id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $items = $stmt->fetchAll();

            // Regrouper les montants par vendeur
            $vendeurs = [];
            foreach ($items as $item) {
                $montant = $item['prix'] * $item['quantite'];
                if (!isset($vendeurs[$item['auteur_id']])) {
                    $vendeurs[$item['auteur_id']] = 0;
                }
                $vendeurs[$item['auteur_id']] += $montant;
            }

            // Crédite le compte de chaque vendeur
            foreach ($vendeurs as $auteur_id => $montant) {
                $stmt = $conn->prepare("UPDATE utilisateurs SET sold = sold + ? WHERE id = ?");
                $stmt->execute([$montant, $auteur_id]);
            }


            // Débit de l'acheteur
            $stmt = $conn->prepare("UPDATE utilisateurs SET sold = sold - ? WHERE id = ?");
            $stmt->execute([$total, $_SESSION['user_id']]);

            // Vider le panier
            $stmt = $conn->prepare("DELETE FROM cart WHERE utilisateur_id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            $conn->commit();
            $success = "Paiement effectué avec succès !";
            header("refresh:2;url=profile.php");

        } catch (Exception $e) {
            $conn->rollBack();
            $error = "Une erreur est survenue lors du paiement.";
        }
    }
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

        <section class="checkout-section">
            <div class="checkout-header">
                <div class="checkout-icon">💳</div>
                <div>
                    <h1 class="checkout-title">Finaliser votre achat</h1>
                    <p class="checkout-subtitle">Montant total : <?= number_format($total, 2) ?> €</p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="message error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="message success-message"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="checkout-form">
                <div class="balance-info">
                    <p>Votre solde actuel: <strong><?= number_format($current_balance, 2) ?> €</strong></p>
                    <p>Montant de la commande: <strong><?= number_format($total, 2) ?> €</strong></p>
                    <p>Solde après achat: <strong><?= number_format($current_balance - $total, 2) ?> €</strong></p>
                </div>

                <form method="post" action="">
                    <div class="form-group">
                        <label for="adresse">Adresse de facturation</label>
                        <input type="text" id="adresse" name="adresse" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="code_postal">Code postal</label>
                        <input type="text" id="code_postal" name="code_postal" class="form-input" required>
                    </div>

                    <div class="form-actions">
                        <a href="panier.php" class="btn-cancel">Retour au panier</a>
                        <button type="submit" class="btn-primary" <?= $current_balance < $total ? 'disabled' : '' ?>>
                            Confirmer le paiement
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <h2 class="footer-title">MARKETPLACE</h2>
    </footer>
</body>
</html>
