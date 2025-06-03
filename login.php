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

            header("Location: index.php");
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
    <title>MarketPlace - Connexion</title>
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
                <a href="profile.php" class="nav-link">PROFILE</a>
            </div>
            <div class="nav-buttons">
                <a href="register.php" class="btn-secondary active">S'inscrire</a>
                <a href="vente.php" class="btn-primary">Vends tes articles !</a>
            </div>
        </nav>

        <!-- Login Section -->
        <section class="login-section fade-in">
            <div class="login-illustration">
                <div>Connectez-vous à votre compte</div>
            </div>
            
            <div class="login-form-container">
                <a href="index.php" class="back-link">back to website</a>
                
                <h1 class="welcome-title">Welcome</h1>
                
                <?php if ($error): ?>
                    <div class="message error-message shake">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form id="loginForm" class="login-form" method="post" action="">
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
                        <label class="form-label" for="mot_de_passe">Password</label>
                        <input 
                            type="password" 
                            id="mot_de_passe" 
                            name="mot_de_passe" 
                            class="form-input" 
                            placeholder="Entrez votre mot de passe"
                            required
                        >
                    </div>
                    
                    <a href="#" class="forgot-password-link" onclick="forgotPassword()">Forgot password ?</a>
                    
                    <button type="submit" class="login-button">Login</button>
                </form>

                <a href="register.php" class="create-account-link">Create a free account</a>
            </div>
        </section>
    </div>

    <script>
        // Form validation
        function validateForm(form) {
            const email = form.querySelector('input[name="email"]').value.trim();
            const motDePasse = form.querySelector('input[name="mot_de_passe"]').value;

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showClientError('Veuillez entrer une adresse email valide');
                return false;
            }

            // Password validation
            if (motDePasse.length === 0) {
                showClientError('Le mot de passe est obligatoire');
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
            const form = document.querySelector('.login-form');
            form.insertBefore(errorDiv, form.firstChild);

            // Remove error after 5 seconds
            setTimeout(() => {
                if (errorDiv.parentNode) {
                    errorDiv.remove();
                }
            }, 5000);
        }

        // Form submission handler
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                return false;
            }

            // Show loading state
            const button = this.querySelector('.login-button');
            const originalText = button.textContent;
            button.textContent = 'Connexion...';
            button.disabled = true;

            // If validation passes, let the form submit normally
            // The PHP will handle the server-side processing
        });

        // Forgot password function
        function forgotPassword() {
            alert('Fonctionnalité de récupération de mot de passe à venir...');
            // Here you would redirect to forgot password page
            // window.location.href = 'forgot-password.php';
        }

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

        // Enter key submission
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && document.activeElement.tagName === 'INPUT') {
                document.getElementById('loginForm').dispatchEvent(new Event('submit'));
            }
        });

        // Auto-focus on email field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>
