<?php
session_start();
require_once 'includes/db.php';

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
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
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #eee;
        }
        .admin-section {
            padding: 20px;
            max-width: 1200px;
            margin: auto;
        }
        h2 {
            margin-top: 40px;
            margin-bottom: 10px;
        }
        a.edit-link {
            color: #007BFF;
            text-decoration: none;
            margin-right: 10px;
        }
        a.edit-link:hover {
            text-decoration: underline;
        }
        form.inline-form {
            display: inline;
        }
        button.delete-btn {
            background-color: transparent;
            color: red;
            border: none;
            cursor: pointer;
        }
        button.delete-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="admin-section">
        <h1>Tableau de bord Administrateur</h1>

        <!-- Liste des utilisateurs -->
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
                                <form method="POST" action="admin-delete-user.php" class="inline-form" onsubmit="return confirm('Supprimer ce compte ? Cette action est irréversible.')">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="delete-btn">🗑 Supprimer</button>
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
                            <a class="edit-link" href="editarticle.php?id=<?= $article['id'] ?>">✏️ Modifier</a>
                            <form method="POST" action="admin-delete-article.php" class="inline-form" onsubmit="return confirm('Supprimer cet article ? Cette action est irréversible.')">
                                <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                <button type="submit" class="delete-btn">🗑 Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
