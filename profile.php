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

// Gestion de l'ajout d'argent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_money'])) {
    $addAmount = 100.00;

    $sql_update_solde = "UPDATE utilisateurs SET sold = sold + :amount WHERE id = :id";
    $stmt_update_solde = $conn->prepare($sql_update_solde);
    $stmt_update_solde->execute([
        ':amount' => $addAmount,
        ':id' => $_SESSION["user_id"]
    ]);
}


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

$stmt_articles = $conn->prepare("
    SELECT * FROM invoice
    WHERE invoice.utilisateur_id = :user_id
");
$stmt_articles->execute([':user_id' => $_SESSION["user_id"]]);
$invoice= $stmt_articles->fetchAll(PDO::FETCH_ASSOC);

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

        <!-- Profile Section -->
        <section class="profile-section">
            <?= $message ?>
            <!-- Profile Header -->
            <div class="profile-header">
                <img src="<?= $profileImage ?>" alt="Photo de profil" class="profile-avatar">
                <div class="profile-info">
                    <h1 class="profile-name"><?= htmlspecialchars($_SESSION["prenom"] . ' ' . $_SESSION["nom"]) ?></h1>
                    <p class="profile-email"><?= htmlspecialchars($_SESSION["email"]) ?></p>
                    <div class="profile-actions">
                        <a href="edit.php?id=<?= $_SESSION["user_id"] ?>" class="btn-edit">
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
                        <span class="detail-value"><?=htmlspecialchars($_SESSION["creation"])?></span>
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
                    <form method="post" style="display: inline;">
                        <button type="submit" name="add_money" class="btn-money">Ajouter</button>
                    </form>
                </div>
            </div>

           <!-- Section Articles-->
            <div class="user-articles-section">
                <div class="articles-header">
                    <div class="header-content">
                        <div class="articles-icon">📝</div>
                        <div>
                            <h3 class="articles-title">Mes articles</h3>
                            <p class="articles-subtitle">Gérez vos articles</p>
                        </div>
                    </div>
                    <div class="articles-toggle">
                        <button onclick="toggleArticles('sale')" class="btn btn-primary" id="btn-sale">Mes articles</button>
                        <button onclick="toggleArticles('buy')" class="btn btn-secondary" id="btn-buy">Mes factures</button>
                    </div>
                </div>

                <!-- Articles en vente -->
                <div id="articles-sale" class="articles-container">
                    <?php if (empty($user_articles)): ?>
                        <div class="no-articles">
                            <div class="no-articles-icon">📦</div>
                            <h4 class="no-articles-title">Aucun article en vente</h4>
                            <p class="no-articles-text">Vous n'avez pas encore mis d'articles en vente.<br>Commencez dès maintenant !</p>
                            <a href="vente.php" class="btn-sell"><span>💰</span>Vendre un article</a>
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
                                            <div class="article-image" style="display: none; background: #ccc; color: white;">📷 Image non disponible</div>
                                        <?php else: ?>
                                            <div class="article-image" style="background: #ccc; display: flex; align-items: center; justify-content: center;">📷 Aucune image</div>
                                        <?php endif; ?>

                                        <div class="article-content">
                                            <h4 class="article-name"><?= htmlspecialchars($article['nom']) ?></h4>
                                            <p class="article-description"><?= htmlspecialchars($article['description']) ?></p>
                                            <div class="article-price"><?= number_format($article['prix'], 2, ',', ' ') ?> €</div>
                                            <div class="article-meta">
                                                ❤️ <?= $article['likes_count'] ?> | 💬 <?= $article['comments_count'] ?> |
                                                <span><?= date('d/m/Y', strtotime($article['date_publication'])) ?></span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Articles achetés -->
                <div id="articles-buy" class="articles-container" style="display: none;">
                    <?php if (empty($invoice)): ?>
                        <div class="no-articles">
                            <div class="no-articles-icon">🧾</div>
                            <h4 class="no-articles-title">Aucune facture trouvée</h4>
                            <p class="no-articles-text">Vous n'avez encore effectué aucun achat.</p>
                            <a href="articles.php" class="btn-sell"><span>🛍️</span> Acheter un article</a>
                        </div>
                    <?php else: ?>
                        <div class="user-articles-grid">
                            <?php foreach ($invoice as $facture): ?>
                                <div class="user-article-card">
                                    <div class="article-content">
                                        <h4 class="article-name">Facture #<?= htmlspecialchars($facture['id']) ?></h4>
                                        <p class="article-description">Date : <?= date('d/m/Y H:i', strtotime($facture['date_transaction'])) ?></p>
                                        <p class="article-description">Montant : <?= number_format($facture['montant'], 2, ',', ' ') ?> €</p>
                                        <p class="article-description">
                                            Adresse : <?= htmlspecialchars($facture['adresse_facturation']) ?>,
                                            <?= htmlspecialchars($facture['ville_facturation']) ?> <?= htmlspecialchars($facture['code_postal_facturation']) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <h2 class="footer-title">MARKETPLACE</h2>
    </footer>
</body>
<script>
    function toggleArticles(view) {
        const saleSection = document.getElementById('articles-sale');
        const buySection = document.getElementById('articles-buy');
        const btnSale = document.getElementById('btn-sale');
        const btnBuy = document.getElementById('btn-buy');

        if (view === 'sale') {
            saleSection.style.display = 'block';
            buySection.style.display = 'none';
            btnSale.classList.add('btn-primary');
            btnSale.classList.remove('btn-secondary');
            btnBuy.classList.remove('btn-primary');
            btnBuy.classList.add('btn-secondary');
        } else {
            saleSection.style.display = 'none';
            buySection.style.display = 'block';
            btnSale.classList.remove('btn-primary');
            btnSale.classList.add('btn-secondary');
            btnBuy.classList.add('btn-primary');
            btnBuy.classList.remove('btn-secondary');
        }
    }
</script>
</html>
