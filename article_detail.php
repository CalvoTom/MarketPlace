<?php
require_once 'includes/db.php';

if (!isset($_GET['id'])) {
    echo "Article non trouvé.";
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT a.*, u.nom, u.prenom FROM Article a LEFT JOIN utilisateurs u ON a.author_id = u.id WHERE a.id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    echo "Article non trouvé.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($article['name']) ?> - Détail de l'article</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #FBF9F5;
            margin: 0;
            padding: 0;
        }
        .detail-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            padding: 40px;
        }
        .detail-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 32px;
            background: #EFEFEF;
            display: block;
        }
        .detail-title {
            font-size: 40px;
            color: #F8582E;
            margin-bottom: 16px;
        }
        .detail-description {
            font-size: 18px;
            color: #222;
            margin-bottom: 24px;
        }
        .detail-price {
            font-size: 32px;
            color: #F8582E;
            font-weight: bold;
            margin-bottom: 16px;
        }
        .detail-meta {
            color: #888;
            font-size: 16px;
            margin-bottom: 32px;
        }
        .back-link {
            display: inline-block;
            margin-top: 24px;
            color: #F8582E;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="detail-container">
        <?php if (!empty($article['image_link'])): ?>
            <img src="<?= htmlspecialchars($article['image_link']) ?>" alt="<?= htmlspecialchars($article['name']) ?>" class="detail-image">
        <?php else: ?>
            <div class="detail-image" style="display:flex;align-items:center;justify-content:center;height:300px;font-size:32px;color:#aaa;">
                📷 Aucune image
            </div>
        <?php endif; ?>
        <h1 class="detail-title"><?= htmlspecialchars($article['name']) ?></h1>
        <div class="detail-price"><?= number_format($article['price'], 2, ',', ' ') ?> €</div>
        <div class="detail-meta">
            Par <?= htmlspecialchars($article['prenom'] . ' ' . $article['nom']) ?> |
            Publié le <?= date('d/m/Y H:i', strtotime($article['publication_date'])) ?>
        </div>
        <div class="detail-description"><?= nl2br(htmlspecialchars($article['description'])) ?></div>
        <a href="index.php" class="back-link">&larr; Retour à l'accueil</a>
    </div>
</body>
</html>
