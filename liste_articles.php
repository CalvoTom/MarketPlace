<?php
// Supposons que vous ayez déjà une connexion à la base de données établie dans un fichier inclus
// include('db_connection.php');

// Récupérer tous les articles de la base de données
$query = "SELECT id, titre, resume FROM articles";
$result = $db->query($query);
$articles = $result->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Articles</title>
</head>
<body>

<h1>Liste des Articles</h1>

<?php foreach ($articles as $article): ?>
    <div class="article">
        <h2>
            <a href="article_detail.php?id=<?php echo $article['id']; ?>">
                <?php echo htmlspecialchars($article['titre']); ?>
            </a>
        </h2>
        <p><?php echo htmlspecialchars($article['resume']); ?></p>
    </div>
<?php endforeach; ?>

</body>
</html>