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
            

            header("Location: index.php");
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
    <title>MarketPlace - Inscription</title>
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
                <a href="panier.php" class="nav-link">PANIER</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin"): ?>
                    <a href="admin.php" class="nav-link">DASHBOARD</a>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="articleLike.php" class="nav-link nav-heart">❤️</a>
                <?php endif; ?>
            </div>

            <div class="nav-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="btn-secondary">Mon Profil</a>
                    <a href="vente.php" class="btn-primary">Vends tes articles !</a>
                <?php else: ?>
                    <a href="register.php" class="btn-secondary">S'inscrire</a>
                    <a href="login.php" class="btn-primary">Se connecter</a>
                <?php endif; ?>
            </div>
        </nav>

        <!-- Register Section -->
        <section class="register-section fade-in">
            <div class="register-illustration">
                <img class="img-cta" src="/img/cta.png" alt="image cta">
            </div>
            
            <div class="register-form-container">
                <a href="index.php" class="back-link">back to website</a>
                
                <h1 class="welcome-title">Welcome</h1>

                <?php if ($error): ?>
                    <div class="message error-message shake">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="message success-message">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <form id="registerForm" class="register-form" method="post" action="">
                    <div class="form-group">
                        <label class="form-label" for="prenom">Prénom</label>
                        <input 
                            type="text" 
                            id="prenom" 
                            name="prenom" 
                            class="form-input" 
                            placeholder="Entrez votre prénom"
                            value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>"
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
                            placeholder="Entrez votre nom"
                            value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>"
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
                            placeholder="Entrez votre email"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="mot_de_passe">Mot de passe</label>
                        <input 
                            type="password" 
                            id="mot_de_passe" 
                            name="mot_de_passe" 
                            class="form-input" 
                            placeholder="Entrez votre mot de passe"
                            required
                        >
                    </div>
                    
                    <button type="submit" class="register-button">Créer un compte</button>
                </form>
                
                <a href="login.php" class="login-link">already an account</a>
            </div>
        </section>
    </div>

    <script>
        // Form validation
        function validateForm(form) {
            const prenom = form.querySelector('input[name="prenom"]').value.trim();
            const nom = form.querySelector('input[name="nom"]').value.trim();
            const email = form.querySelector('input[name="email"]').value.trim();
            const motDePasse = form.querySelector('input[name="mot_de_passe"]').value;

            // Prénom validation
            if (prenom.length < 2) {
                showClientError('Le prénom doit contenir au moins 2 caractères');
                return false;
            }

            // Nom validation
            if (nom.length < 2) {
                showClientError('Le nom doit contenir au moins 2 caractères');
                return false;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showClientError('Veuillez entrer une adresse email valide');
                return false;
            }

            // Password validation
            if (motDePasse.length < 6) {
                showClientError('Le mot de passe doit contenir au moins 6 caractères');
                return false;
            }

            return true;
        }

        // Client-side error display
        function showClientError(message) {
            // Remove existing error messages
            const existingError = document.querySelector('.client-error-message');
            if (existingError) {
                existingError.remove();
            }

            // Create error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'message error-message client-error-message shake';
            errorDiv.textContent = message;

            // Insert error message
            const form = document.querySelector('.register-form');
            form.insertBefore(errorDiv, form.firstChild);

            // Remove error after 5 seconds
            setTimeout(() => {
                if (errorDiv.parentNode) {
                    errorDiv.remove();
                }
            }, 5000);
        }

        // Form submission handler
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }

            // Show loading state
            const button = this.querySelector('.register-button');
            const originalText = button.textContent;
            button.textContent = 'Création du compte...';
            button.disabled = true;

            // If validation passes, let the form submit normally
            // The PHP will handle the server-side processing
        });

        // Input focus effects
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.style.transform = 'translateY(-2px)';
                this.parentNode.style.transition = 'transform 0.2s ease';
            });

            input.addEventListener('blur', function() {
                this.parentNode.style.transform = 'translateY(0)';
            });

            // Real-time validation feedback
            input.addEventListener('input', function() {
                this.style.borderColor = '#F8582E';
                
                // Remove client error messages when user starts typing
                const clientError = document.querySelector('.client-error-message');
                if (clientError) {
                    clientError.remove();
                }
            });
        });

        // Auto-hide server messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.message:not(.client-error-message)');
            messages.forEach(message => {
                setTimeout(() => {
                    if (message.parentNode) {
                        message.style.opacity = '0';
                        message.style.transform = 'translateY(-10px)';
                        setTimeout(() => {
                            message.remove();
                        }, 300);
                    }
                }, 5000);
            });
        });

        // Button hover effects
        document.querySelectorAll('button, .btn-primary, .btn-secondary').forEach(button => {
            button.addEventListener('click', function() {
                if (!this.disabled) {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                }
            });
        });
    </script>
</body>
</html>
