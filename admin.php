<?php
session_start();
require_once 'includes/db.php';

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Suppression article 
    if (isset($_POST['action']) && $_POST['action'] === 'delete_article') {
        $article_id = intval($_POST['article_id']);

        if ($article_id){
            $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
            $stmt->execute([$article_id]);
        }
    }

    // Suppression utilisateurs 
    if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        $user_id = intval($_POST['user_id']);

        if ($user_id && $_SESSION['user_id'] != $user_id) {
            $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
            $stmt->execute([$user_id]);
        }
    }
}


// Récupérer tous les utilisateurs
$stmtUsers = $conn->prepare("SELECT id, nom, prenom, email, sold, role, date_creation FROM utilisateurs");
$stmtUsers->execute();
$utilisateurs = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les articles
$stmtArticles = $conn->prepare("
    SELECT a.*, u.nom AS auteur_nom, u.prenom AS auteur_prenom 
    FROM articles a 
    JOIN utilisateurs u ON a.auteur_id = u.id
    ORDER BY a.date_publication DESC
");
$stmtArticles->execute();
$articles = $stmtArticles->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Tableau de bord</title>
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
                    <a href="admin.php" class="nav-link active">DASHBOARD</a>
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
            <h2>Utilisateurs</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Solde (€)</th>
                        <th>Rôle</th>
                        <th>Date de création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['nom']) ?></td>
                            <td><?= htmlspecialchars($user['prenom']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= number_format($user['sold'], 2, ',', ' ') ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= $user['date_creation'] ?></td>
                            <td>
                                <?php if ($_SESSION['user_id'] != $user['id']): ?>
                                    <a class="btn-secondary" href="edit.php?id=<?= $user['id'] ?>">✏️ Modifier</a>
                                    <form method="POST" class="inline-form">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn-secondary">🗑 Supprimer</button>
                                    </form>
                                <?php else: ?>
                                    <em>(vous)</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Liste des articles -->
            <h2>Articles</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Prix (€)</th>
                        <th>Auteur</th>
                        <th>Date publication</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td><?= $article['id'] ?></td>
                            <td><?= htmlspecialchars($article['nom']) ?></td>
                            <td><?= htmlspecialchars(substr($article['description'], 0, 50)) ?>...</td>
                            <td><?= number_format($article['prix'], 2, ',', ' ') ?></td>
                            <td><?= htmlspecialchars($article['auteur_prenom']) . ' ' . htmlspecialchars($article['auteur_nom']) ?></td>
                            <td><?= $article['date_publication'] ?></td>
                            <td>
                                <a class="btn-secondary" href="editArticle.php?id=<?= $article['id'] ?>">✏️ Modifier</a>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="action" value="delete_article">
                                    <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                    <button type="submit" class="btn-secondary">🗑 Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Footer -->
    <footer class="footer">
        <h2 class="footer-title">MARKETPLACE</h2>
    </footer>
</body>
</html>