<?php
// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=mydb;charset=utf8', 'root', '');

// Requête pour récupérer tous les articles, les plus récents en premier
$stmt = $pdo->query('SELECT id, name, description, price, publication_date, author_id, image_link FROM Article ORDER BY publication_date DESC');
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
    </style>
</head>
<body>
    <h1>Articles en Vente</h1>

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
                    Auteur ID : <?= htmlspecialchars($article['author_id']) ?> |
                    Article ID : <?= htmlspecialchars($article['id']) ?>
                </small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun article en vente pour le moment.</p>
    <?php endif; ?>
</body>
</html>
