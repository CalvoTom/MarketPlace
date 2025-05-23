<?php
    session_start();
    require_once 'includes/db.php';

    $success = $error = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nom = htmlspecialchars($_POST["nom"]);
        $prenom = htmlspecialchars($_POST["prenom"]);
        $email = htmlspecialchars($_POST["email"]);
        $mot_de_passe_brut = $_POST["mot_de_passe"];
        $mot_de_passe = password_hash($mot_de_passe_brut, PASSWORD_DEFAULT);

        $defaultImagePath = 'img/default.png';
        $profile_picture = null;

        if (file_exists($defaultImagePath)) {
            $profile_picture = file_get_contents($defaultImagePath);
        } else {
            $error = "Impossible de charger l'image par défaut.";
        }

        if (!empty($nom) && !empty($prenom) && !empty($email) && !empty($mot_de_passe_brut) && $profile_picture !== null) {
            $sql = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, profile_picture) 
                    VALUES (:nom, :prenom, :email, :mot_de_passe, :profile_picture)";
            $stmt = $conn->prepare($sql);
            try {
                $stmt->execute([
                    ':nom' => $nom,
                    ':prenom' => $prenom,
                    ':email' => $email,
                    ':mot_de_passe' => $mot_de_passe,
                    ':profile_picture' => $profile_picture
                ]);

                $user_id = $conn->lastInsertId();

                $_SESSION["user_id"] = $user_id;
                $_SESSION["email"] = $email;
                $_SESSION["nom"] = $nom;
                $_SESSION["prenom"] = $prenom;

                header("Location: home.php");
                exit();
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "Un compte avec cet email existe déjà.";
                } else {
                    $error = "Erreur : " . $e->getMessage();
                }
            }
        } else {
            if (empty($error)) {
                $error = "Tous les champs sont obligatoires.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <h1>Créer un compte</h1>
</header>
<main>
    <?php if ($success): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php elseif ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="nom">Nom :</label><br>
        <input type="text" id="nom" name="nom" required><br><br>

        <label for="prenom">Prénom :</label><br>
        <input type="text" id="prenom" name="prenom" required><br><br>

        <label for="email">Email :</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="mot_de_passe">Mot de passe :</label><br>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required><br><br>

        <input type="submit" value="Créer un compte">
    </form>
</main>
</body>
</html>
