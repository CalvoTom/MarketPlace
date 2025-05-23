<?php
session_start();
require_once 'includes/db.php';

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";

// Récupération des infos actuelles
$sql = "SELECT nom, prenom, email, mot_de_passe FROM utilisateurs WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = htmlspecialchars($_POST["nom"]);
    $prenom = htmlspecialchars($_POST["prenom"]);
    $email = htmlspecialchars($_POST["email"]);
    $ancien_mdp = $_POST["ancien_mdp"];
    $nouveau_mdp = $_POST["nouveau_mdp"];
    $photo = $_FILES["photo"]["tmp_name"];

    // Vérification de l'ancien mot de passe
    if (!password_verify($ancien_mdp, $user["mot_de_passe"])) {
        $message = "Ancien mot de passe incorrect.";
    } else {
        // Mise à jour
        $params = [
            ':id' => $_SESSION["user_id"],
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
        ];

        $sql = "UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email";

        // Si un nouveau mot de passe est fourni
        if (!empty($nouveau_mdp)) {
            $sql .= ", mot_de_passe = :mot_de_passe";
            $params[':mot_de_passe'] = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
        }

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

        header("Location: profile.php");
        exit();
    }
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
        <p style="color: red;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label for="nom">Nom :</label><br>
        <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required><br><br>

        <label for="prenom">Prénom :</label><br>
        <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required><br><br>

        <label for="email">Email :</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

        <label for="ancien_mdp">Ancien mot de passe :</label><br>
        <input type="password" name="ancien_mdp" required><br><br>

        <label for="nouveau_mdp">Nouveau mot de passe (laisser vide pour ne pas changer) :</label><br>
        <input type="password" name="nouveau_mdp"><br><br>

        <label for="photo">Photo de profil :</label><br>
        <input type="file" name="photo" accept="image/*"><br><br>

        <input type="submit" value="Mettre à jour">
    </form>
</main>
</body>
</html>
