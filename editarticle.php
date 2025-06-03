<?php
session_start();
require_once 'includes/db.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';

// Vérifier que l'ID de l'article est dans l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID d'article invalide.";
    exit;
}

$article_id = intval($_GET['id']);
$message = "";

// Récupérer l'article
$stmt = $conn->prepare("SELECT * FROM articles WHERE id = :id");
$stmt->execute([':id' => $article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    echo "Article non trouvé.";
    exit;
}

// Vérifier que l'utilisateur est l'auteur OU un admin
if ($user_role !== 'admin' && $article['auteur_id'] != $user_id) {
    echo "Vous n'avez pas l'autorisation de modifier cet article.";
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description']);
    $prix = floatval($_POST['prix']);

    $update = $conn->prepare("UPDATE articles SET nom = :nom, description = :description, prix = :prix WHERE id = :id");
    $update->execute([
        ':nom' => $nom,
        ':description' => $description,
        ':prix' => $prix,
        ':id' => $article_id
    ]);

    $message = "Article mis à jour avec succès !";

    // Recharger les données
    $stmt->execute([':id' => $article_id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un article</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: auto;
            padding: 40px;
        }
        .form-group { margin-bottom: 20px; }
        label, input, textarea { display: block; width: 100%; }
        input, textarea { padding: 10px; font-size: 16px; }
        .btn-submit {
            background-color: #F8582E;
            color: white;
            padding: 10px 20px;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
        .success-message { color: green; margin-bottom: 20px; }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007BFF;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="form-container">
    <a class="back-link" href="<?= ($user_role === 'admin') ? 'admin.php' : 'profile.php' ?>">← Retour</a>
    <h1>Modifier l'article #<?= $article_id ?></h1>

    <?php if ($message): ?>
        <div class="success-message"><?= $message ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label for="nom">Nom de l'article :</label>
            <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($article['nom']) ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description :</label>
            <textarea name="description" id="description" rows="5" required><?= htmlspecialchars($article['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="prix">Prix (€) :</label>
            <input type="number" step="0.01" name="prix" id="prix" value="<?= htmlspecialchars($article['prix']) ?>" required>
        </div>

        <button type="submit" class="btn-submit">Enregistrer</button>
    </form>
</div>
</body>
</html>
