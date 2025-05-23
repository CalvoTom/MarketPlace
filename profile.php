<?php
session_start();

// Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil utilisateur</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <h1>Mon Profil</h1>
</header>
<main>
    <p><strong>Nom :</strong> <?php echo htmlspecialchars($_SESSION["nom"]); ?></p>
    <p><strong>Prénom :</strong> <?php echo htmlspecialchars($_SESSION["prenom"]); ?></p>
    <p><strong>Email :</strong> <?php echo htmlspecialchars($_SESSION["email"]); ?></p>

    <form method="get" action="">
        <input type="submit" name="logout" value="Se déconnecter">
    </form>
</main>
</body>
</html>
