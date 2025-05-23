<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? null;
    $description = $_POST['description'] ?? null;
    $price = $_POST['price'] ?? null;
    $image_link = $_POST['image_link'] ?? null;
    $author_id = $_SESSION['user_id']; // ID de l'utilisateur connecté

    if ($name && $price) {
        $stmt = $conn->prepare('INSERT INTO Article (name, description, price, author_id, image_link, publication_date) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$name, $description, $price, $author_id, $image_link]);
        $message = "Article ajouté avec succès.";
    } else {
        $message = "Les champs nom et prix sont obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Article</title>
</head>
<body>
    <h1>Ajouter un Article</h1>
    <p>Connecté en tant que : <?= htmlspecialchars($_SESSION['nom'] ?? 'Utilisateur') ?></p>

    <?php if ($message): ?>
        <p style="color: <?= strpos($message, 'succès') !== false ? 'green' : 'red' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="name">Nom de l'article* :</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="description">Description :</label>
        <textarea id="description" name="description"></textarea><br><br>

        <label for="price">Prix* :</label>
        <input type="number" id="price" name="price" step="0.01" required><br><br>

        <label for="image_link">Lien de l'image :</label>
        <input type="text" id="image_link" name="image_link"><br><br>

        <button type="submit">Ajouter</button>
        <a href="articles.php" style="margin-left: 15px;">Annuler</a>
    </form>
</body>
</html>
