<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Vérifie si l'utilisateur a confirmé son mot de passe
if (!isset($_SESSION["profil_edit_authorized"])) {
    header("Location: checkPassword.php");
    exit();
}

$message = "";

// Récupération des infos actuelles
$sql = "SELECT nom, prenom, email, profile_picture FROM utilisateurs WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = htmlspecialchars($_POST["nom"]);
    $prenom = htmlspecialchars($_POST["prenom"]);
    $email = htmlspecialchars($_POST["email"]);
    $photo = $_FILES["photo"]["tmp_name"];

    $params = [
        ':id' => $_SESSION["user_id"],
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':email' => $email,
    ];

    $sql = "UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email";

    // Si une nouvelle image est fournie
    if (!empty($photo)) {
        $sql .= ", profile_picture = :profile_picture";
        $params[':profile_picture'] = file_get_contents($photo);
    }

    $sql .= " WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    // Mise à jour des données en session
    $_SESSION["nom"] = $nom;
    $_SESSION["prenom"] = $prenom;
    $_SESSION["email"] = $email;

    // Supprimer l'autorisation une fois l'action terminée
    unset($_SESSION["profil_edit_authorized"]);

    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mon profil</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <h1>Modifier mon profil</h1>
</header>
<main>
    <?php if ($message): ?>
        <p style="color: red;"><?= $message ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label for="nom">Nom :</label><br>
        <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required><br><br>

        <label for="prenom">Prénom :</label><br>
        <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required><br><br>

        <label for="email">Email :</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

        <label for="photo">Photo de profil :</label><br>
        <input type="file" name="photo" accept="image/*"><br><br>

        <a href="changePassword.php" class="button">Modifier le mot de passe</a>

        <div>
            <input type="submit" value="Mettre à jour">
            <a href="profile.php" class="button button-cancel">Annuler</a>
        </div>
    </form>
</main>
</body>
</html>
