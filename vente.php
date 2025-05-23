<?php
// Connexion à la base de données
$pdo = new PDO('mysql:host=localhost;dbname=mydb;charset=utf8', 'root', '');

// Ajouter un nouvel article si les données sont envoyées en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données POST
    $name = $_POST['name'] ?? null;
    $description = $_POST['description'] ?? null;
    $price = $_POST['price'] ?? null;
    $author_id = $_POST['author_id'] ?? null;
    $image_link = $_POST['image_link'] ?? null;

    // Vérifier les champs obligatoires
    if ($name && $price && $author_id) {
        $stmt = $pdo->prepare('INSERT INTO Article (name, description, price, author_id, image_link) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$name, $description, $price, $author_id, $image_link]);
        echo "Article ajouté avec succès.\n";
    } else {
        echo "Champs obligatoires manquants (name, price, author_id).\n";
    }
    exit;
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
    <form method="post" action="">
        <label for="name">Nom de l'article* :</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="description">Description :</label>
        <textarea id="description" name="description"></textarea><br><br>

        <label for="price">Prix* :</label>
        <input type="number" id="price" name="price" step="0.01" required><br><br>

        <label for="author_id">ID de l'auteur* :</label>
        <input type="number" id="author_id" name="author_id" required><br><br>

        <label for="image_link">Lien de l'image :</label>
        <input type="text" id="image_link" name="image_link"><br><br>

        <button type="submit">Ajouter</button>
    </form>
</body>
</html>
