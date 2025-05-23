<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = $conn;

// Requête avec jointure pour récupérer les infos utilisateur de l'auteur
$sql = "SELECT a.id, a.name, a.description, a.price, a.publication_date, a.author_id, a.image_link,
        u.nom, u.prenom
        FROM Article a
        LEFT JOIN utilisateurs u ON a.author_id = u.id
        ORDER BY a.publication_date DESC";

$stmt = $pdo->query($sql);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Articles en Vente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .article {
            border: 1px solid #ccc;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
        }
        .article img {
            max-width: 200px;
            height: auto;
            display: block;
            margin-bottom: 10px;
        }
        .article h2 {
            margin: 0;
        }
        .article small {
            color: #555;
        }
        a {
            text-decoration: none;
            color: #337ab7;
            margin-right: 10px;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Articles en Vente</h1>
    <a href="vente.php">Vendre un article</a>

    <?php if (count($articles) > 0): ?>
        <?php foreach ($articles as $article): ?>
            <div class="article">
                <?php if (!empty($article['image_link'])): ?>
                    <img src="<?= htmlspecialchars($article['image_link']) ?>" alt="Image de l'article">
                <?php endif; ?>
                <h2><?= htmlspecialchars($article['name']) ?></h2>
                <p><?= nl2br(htmlspecialchars($article['description'])) ?></p>
                <p><strong>Prix :</strong> <?= number_format($article['price'], 2, ',', ' ') ?> €</p>
                <small>
                    Publié le <?= date('d/m/Y H:i', strtotime($article['publication_date'])) ?> |
                    Auteur : <?= htmlspecialchars($article['prenom'] . ' ' . $article['nom']) ?>
                </small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun article en vente pour le moment.</p>
    <?php endif; ?>
</body>
</html>
