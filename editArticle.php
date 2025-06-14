<?php
session_start();
require_once 'includes/db.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';

// Vérifier que l'ID de l'article est dans l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID d'article invalide.";
    exit;
}

$article_id = intval($_GET['id']);
$message = "";

// Récupérer l'article
$stmt = $conn->prepare("SELECT * FROM articles WHERE id = :id");
$stmt->execute([':id' => $article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtStock = $conn->prepare("SELECT quantite FROM stock WHERE article_id = :article_id");
$stmtStock->execute([':article_id' => $article_id]);
$stock = $stmtStock->fetch(PDO::FETCH_ASSOC);
$quantite = $stock ? $stock['quantite'] : 0;

if (!$article) {
    echo "Article non trouvé.";
    exit;
}

// Vérifier que l'utilisateur est l'auteur OU un admin
if ($user_role !== 'admin' && $article['auteur_id'] != $user_id) {
    echo "Vous n'avez pas l'autorisation de modifier cet article.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description']);
    $prix = floatval($_POST['prix']);
    $quantite = intval($_POST['quantite']);

    // Mise à jour de l'article
    $update = $conn->prepare("UPDATE articles SET nom = :nom, description = :description, prix = :prix WHERE id = :id");
    $update->execute([
        ':nom' => $nom,
        ':description' => $description,
        ':prix' => $prix,
        ':id' => $article_id
    ]);

    // Met à jour ou insère la quantité
    $stmtCheck = $conn->prepare("SELECT id FROM stock WHERE article_id = :article_id");
    $stmtCheck->execute([':article_id' => $article_id]);

    if ($stmtCheck->fetch()) {
        $updateStock = $conn->prepare("UPDATE stock SET quantite = :quantite WHERE article_id = :article_id");
        $updateStock->execute([
            ':quantite' => $quantite,
            ':article_id' => $article_id
        ]);
    } else {
        $insertStock = $conn->prepare("INSERT INTO stock (article_id, quantite) VALUES (:article_id, :quantite)");
        $insertStock->execute([
            ':article_id' => $article_id,
            ':quantite' => $quantite
        ]);
    }

    // Redirection vers la page de détail de l'article
    header("Location: articleDetail.php?id=" . $article_id);
    exit;
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

        <div class="admin-section">
            <h1>Modifier l'article n°<?= $article_id ?></h1>

            <?php if ($message): ?>
                <div class="success-message"><?= $message ?></div>
            <?php endif; ?>

            <br>
            <form method="post">
                <div class="form-group">
                    <label for="nom" class="form-label">Nom de l'article :</label>
                    <input type="text" name="nom" id="nom" class="form-input" value="<?= htmlspecialchars($article['nom']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description :</label>
                    <textarea name="description" class="form-textarea" id="description" rows="5" required><?= htmlspecialchars($article['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="quantite" class="form-label">Quantité :</label>
                    <input type="number" name="quantite" id="quantite" class="form-input" min="0" value="<?= htmlspecialchars($quantite) ?>" required>
                </div>

                <div class="form-group">
                    <label for="prix" class="form-label">Prix (€) :</label>
                    <div class="price-input">
                        <input type="number" class="form-input" step="0.01" name="prix" id="prix" value="<?= htmlspecialchars($article['prix']) ?>" required>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="javascript:history.back()" class="btn-cancel">Annuler</a>
                    <button type="submit" class="btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <h2 class="footer-title">MARKETPLACE</h2>
    </footer>
</body>
</html>