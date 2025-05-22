<?php
session_start();
require_once 'includes/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = htmlspecialchars($_POST["email"]);
    $mot_de_passe = $_POST["mot_de_passe"];

    if (!empty($email) && !empty($mot_de_passe)) {
        $sql = "SELECT * FROM utilisateurs WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
            // Authentification réussie
            $_SESSION["user_id"] = $utilisateur["id"];
            $_SESSION["email"] = $utilisateur["email"];
            $_SESSION["nom"] = $utilisateur["nom"];
            $_SESSION["prenom"] = $utilisateur["prenom"];

            header("Location: home.php");
            exit();
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <h1>Connexion</h1>
</header>
<main>
    <?php if ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="email">Email :</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="mot_de_passe">Mot de passe :</label><br>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required><br><br>

        <input type="submit" value="Se connecter">
    </form>

    <p>Pas encore de compte ? <a href="register.php">Créer un compte</a></p>
</main>
</body>
</html>
