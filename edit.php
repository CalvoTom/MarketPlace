<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$session_user_id = $_SESSION["user_id"];
$user_role = $_SESSION["role"] ?? "user";

// Déterminer quel profil est édité
$edit_user_id = $session_user_id; // par défaut on modifie son propre profil
if ($user_role === 'admin' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $edit_user_id = intval($_GET['id']);
}

// Si l'utilisateur n'est pas admin et tente d'éditer un autre profil -> redirection
if ($user_role !== 'admin' && $edit_user_id !== $session_user_id) {
    header("Location: login.php");
    exit();
}

// Si l'utilisateur n'est pas admin, vérifier qu'il a confirmé son mot de passe
if ($user_role !== 'admin' && !isset($_SESSION["profil_edit_authorized"])) {
    header("Location: checkPassword.php");
    exit();
}

// Récupération des infos actuelles du profil à éditer
$sql = "SELECT nom, prenom, email, profile_picture FROM utilisateurs WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $edit_user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Utilisateur introuvable.";
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = htmlspecialchars($_POST["nom"]);
    $prenom = htmlspecialchars($_POST["prenom"]);
    $email = htmlspecialchars($_POST["email"]);
    $photo = $_FILES["photo"]["tmp_name"];

    $params = [
        ':id' => $edit_user_id,
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':email' => $email,
    ];

    $sqlUpdate = "UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email";

    if (!empty($photo) && filesize($photo) > 0) {
        $sqlUpdate .= ", profile_picture = :profile_picture";
        $params[':profile_picture'] = file_get_contents($photo);
    }

    $sqlUpdate .= " WHERE id = :id";

    $stmt = $conn->prepare($sqlUpdate);
    $stmt->execute($params);

    // Mettre à jour les données en session uniquement si c'est le profil connecté
    if ($edit_user_id === $session_user_id) {
        $_SESSION["nom"] = $nom;
        $_SESSION["prenom"] = $prenom;
        $_SESSION["email"] = $email;
        unset($_SESSION["profil_edit_authorized"]);
    }

    // Recharger les données
    $stmt = $conn->prepare("SELECT nom, prenom, email, profile_picture FROM utilisateurs WHERE id = :id");
    $stmt->execute([':id' => $edit_user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $message = "Profil mis à jour avec succès !";

    // Si c'est pas l'admin, on peut rediriger directement vers profile.php
    if ($user_role !== 'admin') {
        header("Location: profile.php");
        exit();
    }
}

// Préparer l'image de profil pour affichage
$hasImageInDB = !empty($user['profile_picture']);
$profileImage = $hasImageInDB
    ? 'data:image/jpeg;base64,' . base64_encode($user['profile_picture'])
    : 'img/default.png';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>MarketPlace - <?= ($edit_user_id === $session_user_id) ? "Modifier mon profil" : "Modifier l'utilisateur #$edit_user_id" ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        .container { max-width: 900px; margin: auto; padding: 20px; font-family: 'Inter', sans-serif; }
        .edit-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        .edit-icon { font-size: 2rem; }
        .edit-title { font-weight: 700; font-size: 1.8rem; }
        .edit-subtitle { color: #555; }
        .edit-form { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-grid { display: flex; gap: 30px; flex-wrap: wrap; }
        .profile-photo-section { flex: 1 1 250px; text-align: center; }
        .current-photo { width: 200px; height: 200px; border-radius: 100px; object-fit: cover; border: 2px solid #f8582e; }
        .photo-upload { margin-top: 10px; }
        .photo-input { display: none; }
        .photo-button { background: #f8582e; border: none; padding: 8px 15px; color: #fff; cursor: pointer; border-radius: 4px; }
        .form-fields { flex: 2 1 400px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 600; margin-bottom: 5px; }
        .form-input { width: 100%; padding: 10px; font-size: 1rem; border: 1px solid #ccc; border-radius: 4px; }
        .password-section { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        .password-title { font-size: 1.2rem; margin-bottom: 5px; }
        .password-description { color: #666; margin-bottom: 10px; }
        .btn-password { display: inline-block; background: #f8582e; color: #fff; padding: 10px 15px; border-radius: 4px; text-decoration: none; font-weight: 600; }
        .form-actions { margin-top: 30px; display: flex; gap: 15px; }
        .btn-cancel { background: #ccc; padding: 10px 20px; border-radius: 4px; text-decoration: none; color: #333; font-weight: 600; }
        .btn-save { background: #f8582e; color: #fff; border: none; padding: 10px 20px; font-weight: 700; cursor: pointer; border-radius: 4px; }
        .message { margin-bottom: 20px; padding: 10px; border-radius: 4px; }
        .error-message { background-color: #fdd; color: #900; }
        .success-message { background-color: #dfd; color: #090; }
    </style>
</head>
<body>
    <div class="container">
        <div class="edit-header">
            <div class="edit-icon">✏️</div>
            <div>
                <h1 class="edit-title"><?= ($edit_user_id === $session_user_id) ? "Modifier mon profil" : "Modifier l'utilisateur $edit_user_id" ?></h1>
                <p class="edit-subtitle"><?= ($edit_user_id === $session_user_id) ? "Mettez à jour vos informations personnelles" : "Mettez à jour les informations de ce compte" ?></p>
            </div>
        </div>

        <div class="edit-form">
            <?php if ($message): ?>
                <div class="message success-message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="profile-photo-section">
                        <img src="<?= $profileImage ?>" alt="Photo de profil" class="current-photo" id="photoPreview" />
                        <div class="photo-upload">
                            <input type="file" name="photo" accept="image/*" class="photo-input" id="photoInput" />
                            <button type="button" class="photo-button" onclick="document.getElementById('photoInput').click()">
                                📷 Changer la photo
                            </button>
                        </div>
                        <p style="font-size: 12px; color: #666; margin-top: 8px;">
                            Formats acceptés: JPG, PNG, GIF<br />
                            Taille max: 5MB
                        </p>
                    </div>

                    <div class="form-fields">
                        <div class="form-group">
                            <label class="form-label" for="prenom">Prénom</label>
                            <input
                                type="text"
                                id="prenom"
                                name="prenom"
                                class="form-input"
                                value="<?= htmlspecialchars($user['prenom']) ?>"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="nom">Nom</label>
                            <input
                                type="text"
                                id="nom"
                                name="nom"
                                class="form-input"
                                value="<?= htmlspecialchars($user['nom']) ?>"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input"
                                value="<?= htmlspecialchars($user['email']) ?>"
                                required
                            />
                        </div>
                    </div>
                </div>

                <?php if ($edit_user_id === $session_user_id): ?>
                <div class="password-section">
                    <h3 class="password-title">🔒 Sécurité du compte</h3>
                    <p class="password-description">
                        Modifiez votre mot de passe pour renforcer la sécurité de votre compte
                    </p>
                    <a href="changePassword.php" class="btn-password">
                        <span>🔑</span>
                        Modifier le mot de passe
                    </a>
                </div>
                <?php endif; ?>

                <div class="form-actions">
                    <a href="profile.php" class="btn-cancel">Annuler</a>
                    <button type="submit" class="btn-save">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Photo preview only
        document.getElementById('photoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photoPreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Input focus effects only
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.style.transform = 'translateY(-2px)';
                this.parentNode.style.transition = 'transform 0.2s ease';
            });

            input.addEventListener('blur', function() {
                this.parentNode.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>
