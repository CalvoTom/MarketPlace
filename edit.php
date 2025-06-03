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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlace - Modifier mon profil</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav class="navbar">
            <a href="index.php" class="logo">MarketPlace</a>
            <div class="nav-links">
                <a href="index.php" class="nav-link">HOME</a>
                <a href="articles.php" class="nav-link">ARTICLES</a>
                <a href="#" class="nav-link">PANIER</a>
                <a href="profile.php" class="nav-link active">PROFILE</a>
            </div>
            <div class="nav-buttons">
                <a href="register.php" class="btn-secondary">S'inscrire</a>
                <a href="vente.php" class="btn-primary">Vends tes articles !</a>
            </div>
        </nav>

        <!-- Edit Section -->
        <section class="edit-section">
            <!-- Edit Header -->
            <div class="edit-header">
                <div class="edit-icon">✏️</div>
                <div>
                    <h1 class="edit-title">Modifier mon profil</h1>
                    <p class="edit-subtitle">Mettez à jour vos informations personnelles</p>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="edit-form">
                <?php if ($message): ?>
                    <div class="message error-message">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="form-grid">
                        <!-- Photo Section -->
                        <div class="profile-photo-section">
                            <img src="<?= $profileImage ?>" alt="Photo de profil" class="current-photo" id="photoPreview">
                            <div class="photo-upload">
                                <input type="file" name="photo" accept="image/*" class="photo-input" id="photoInput">
                                <button type="button" class="photo-button" onclick="document.getElementById('photoInput').click()">
                                    📷 Changer la photo
                                </button>
                            </div>
                            <p style="font-size: 12px; color: #666; margin-top: 8px;">
                                Formats acceptés: JPG, PNG, GIF<br>
                                Taille max: 5MB
                            </p>
                        </div>

                        <!-- Form Fields -->
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
                                >
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
                                >
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
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Password Section -->
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

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="profile.php" class="btn-cancel">Annuler</a>
                        <button type="submit" class="btn-save">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </section>
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
