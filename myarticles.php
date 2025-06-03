<?php
session_start();
require_once 'includes/db.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les articles de l'utilisateur
$stmt = $conn->prepare("SELECT * FROM articles WHERE auteur_id = :id ORDER BY date_publication DESC");
$stmt->execute([':id' => $user_id]);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes articles</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container {
            max-width: 960px;
            margin: auto;
            padding: 40px 20px;
        }
        h1 {
            margin-bottom: 30px;
        }
        .article-card {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .article-title {
            font-size: 1.3em;
            font-weight: bold;
        }
        .article-desc {
            margin: 10px 0;
        }
        .btn-edit {
            background-color: #F8582E;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 6px;
        }
        .btn-edit:hover {
            background-color: #e14a1f;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="profile.php" class="btn-edit" style="background:#555;margin-bottom:20px;display:inline-block;">← Retour au profil</a>
    <h1>Mes articles</h1>

    <?php if (empty($articles)): ?>
        <p>Vous n'avez publié aucun article pour l'instant.</p>
    <?php else: ?>
        <?php foreach ($articles as $article): ?>
            <div class="article-card">
                <div class="article-title"><?= htmlspecialchars($article['nom']) ?></div>
                <div class="article-desc"><?= nl2br(htmlspecialchars($article['description'])) ?></div>
                <div>Prix : <?= number_format($article['prix'], 2) ?> €</div>
                <div>Date : <?= date("d/m/Y H:i", strtotime($article['date_publication'])) ?></div>
                <a class="btn-edit" href="editarticle.php?id=<?= $article['id'] ?>">Modifier</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
