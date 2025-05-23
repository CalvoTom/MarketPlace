<?php
session_start();
require_once 'includes/db.php';

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Gestion de la déconnexion
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Récupération de la photo de profil
$sql = "SELECT profile_picture FROM utilisateurs WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si une image existe
$hasImageInDB = !empty($user['profile_picture']);
$profileImage = $hasImageInDB
    ? 'data:image/jpeg;base64,' . base64_encode($user['profile_picture'])
    : 'img/default.png';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil utilisateur</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <h1>Mon Profil</h1>
</header>
<main>
    <p><strong>Nom :</strong> <?= htmlspecialchars($_SESSION["nom"]) ?></p>
    <p><strong>Prénom :</strong> <?= htmlspecialchars($_SESSION["prenom"]) ?></p>
    <p><strong>Email :</strong> <?= htmlspecialchars($_SESSION["email"]) ?></p>

    <?php include 'includes/solde.php'; ?>

    <p><strong>Photo de profil :</strong><br>
        <img src="<?= $profileImage ?>" alt="Photo de profil" width="150" height="150"
             style="border-radius: 50%; object-fit: cover;">
    </p>

    <form method="get" action="">
        <input type="submit" name="logout" value="Se déconnecter">
    </form>

    <br>
    <a href="edit.php">Modifier mon profil</a>
</main>
</body>
</html>
