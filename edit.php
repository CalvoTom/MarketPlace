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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #FBF9F5;
            position: relative;
            width: 100%;
            min-height: 100vh;
        }

        .container {
            max-width: 1440px;
            margin: 0 auto;
            position: relative;
        }

        /* Navigation */
        .navbar {
            position: absolute;
            width: 1344px;
            height: 54px;
            left: 48px;
            top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }

        .logo {
            font-weight: 700;
            font-size: 48px;
            line-height: 58px;
            color: #000000;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .nav-link {
            font-weight: 400;
            font-size: 16px;
            line-height: 19px;
            color: #1E1D19;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #F8582E;
        }

        .nav-link.active {
            color: #F8582E;
            font-weight: 600;
        }

        .nav-buttons {
            display: flex;
            gap: 24px;
        }

        .btn-secondary {
            width: 117px;
            height: 46px;
            border: 1px solid #F8582E;
            border-radius: 8px;
            background: transparent;
            color: #1E1D19;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-secondary:hover {
            background: #F8582E;
            color: #FBF9F5;
        }

        .btn-primary {
            width: 199px;
            height: 46px;
            background: #F8582E;
            border-radius: 8px;
            border: none;
            color: #FBF9F5;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary:hover {
            background: #e04a26;
        }

        /* Edit Section */
        .edit-section {
            position: absolute;
            top: 134px;
            left: 48px;
            width: 1344px;
            min-height: 600px;
        }

        .edit-header {
            background: #F9F4EC;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            padding: 32px 48px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .edit-icon {
            width: 60px;
            height: 60px;
            background: #F8582E;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            flex-shrink: 0;
        }

        .edit-title {
            font-weight: 700;
            font-size: 32px;
            line-height: 40px;
            color: #000000;
        }

        .edit-subtitle {
            font-size: 16px;
            color: #666;
            margin-top: 8px;
        }

        .edit-form {
            background: #F9F4EC;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.12);
            border-radius: 12px;
            padding: 48px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 48px;
            margin-bottom: 32px;
        }

        .profile-photo-section {
            text-align: center;
        }

        .current-photo {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #F8582E;
            margin-bottom: 24px;
        }

        .photo-upload {
            position: relative;
            display: inline-block;
        }

        .photo-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .photo-button {
            padding: 12px 24px;
            background: #F8582E;
            color: #FBF9F5;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .photo-button:hover {
            background: #e04a26;
        }

        .form-fields {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-label {
            font-size: 16px;
            line-height: 19px;
            color: #000000;
            font-weight: 600;
        }

        .form-input {
            width: 100%;
            height: 46px;
            background: #FFFFFF;
            border: 1px solid #F8582E;
            border-radius: 8px;
            padding: 0 16px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #e04a26;
            box-shadow: 0 0 0 3px rgba(248, 88, 46, 0.1);
        }

        .password-section {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 24px;
            margin: 24px 0;
        }

        .password-title {
            font-size: 18px;
            font-weight: 600;
            color: #000;
            margin-bottom: 8px;
        }

        .password-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 16px;
        }

        .btn-password {
            padding: 12px 24px;
            background: transparent;
            color: #F8582E;
            border: 1px solid #F8582E;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-password:hover {
            background: #F8582E;
            color: #FBF9F5;
        }

        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #e0e0e0;
        }

        .btn-save {
            padding: 12px 32px;
            background: #F8582E;
            color: #FBF9F5;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn-save:hover {
            background: #e04a26;
        }

        .btn-cancel {
            padding: 12px 32px;
            background: transparent;
            color: #666;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-cancel:hover {
            background: #f5f5f5;
            border-color: #999;
        }

        /* Messages */
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
        }

        .error-message {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        /* Responsive */
        @media (max-width: 1440px) {
            .container {
                max-width: 100%;
                padding: 0 24px;
            }
            
            .navbar {
                width: calc(100% - 48px);
                left: 24px;
            }
            
            .edit-section {
                left: 24px;
                width: calc(100% - 48px);
            }
        }

        @media (max-width: 1024px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }
            
            .profile-photo-section {
                order: -1;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                height: auto;
                gap: 16px;
                padding: 16px;
            }
            
            .nav-links {
                order: 2;
            }
            
            .nav-buttons {
                order: 3;
                flex-direction: column;
                width: 100%;
                gap: 12px;
            }
            
            .btn-secondary,
            .btn-primary {
                width: 100%;
            }
            
            .edit-section {
                top: 200px;
            }

            .edit-form {
                padding: 24px;
            }

            .edit-header {
                padding: 24px;
                flex-direction: column;
                text-align: center;
            }

            .form-actions {
                flex-direction: column;
            }

            .current-photo {
                width: 150px;
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav class="navbar">
            <a href="index.php" class="logo">MarketPlace</a>
            <div class="nav-links">
                <a href="index.php" class="nav-link">HOME</a>
                <a href="#" class="nav-link">ARTICLES</a>
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