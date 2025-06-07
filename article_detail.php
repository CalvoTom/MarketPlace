<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlace - Détail de l'article</title>
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
            position: fixed;
            width: 1344px;
            height: 54px;
            left: 50%;
            transform: translateX(-50%);
            top: 20px; 
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            padding: 20px;
            background: transparent; 
            box-shadow: none; 
        }

        .logo {
            font-weight: 700;
            font-size: 48px;
            line-height: 58px;
            color: #000000;
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

        .nav-link.active {
            color: #F8582E;
            font-weight: 600;
        }

        .nav-link:hover {
            color: #F8582E;
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
        .detail-container {
            display: flex;
            gap: 40px;
            max-width: 1200px;
            margin: 140px auto 40px auto; 
            padding: 40px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        }
        .detail-image-container {
            flex: 1;
            max-width: 50%;
        }
        .detail-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 12px;
            background: #EFEFEF;
        }
        .detail-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .detail-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 24px;
        }
        .detail-title {
            font-size: 32px;
            color: #1E1D19;
            margin-bottom: 16px;
        }
        .detail-price {
            font-size: 40px;
            color: #F8582E;
            font-weight: bold;
        }
        .detail-meta {
            color: #666;
            font-size: 14px;
            padding: 24px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-description {
            font-size: 16px;
            line-height: 1.6;
            color: #333;
        }
        .detail-actions {
            display: flex;
            gap: 16px;
            margin-top: auto;
        }
        .btn-cart {
            flex: 1;
            height: 50px;
            background: #F8582E;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-cart:hover {
            background: #e04a26;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link:hover {
            color: #F8582E;
        }
        @media (max-width: 1024px) {
            .detail-container {
                flex-direction: column;
                max-width: 800px;
            }
            
            .detail-image-container {
                max-width: 100%;
                height: 400px;
            }
        }
        @media (max-width: 768px) {
            .navbar { flex-direction: column; height: auto; gap: 16px; padding: 16px; }
            .logo { font-size: 32px; }
            .detail-container { padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <nav class="navbar">
            <div class="logo">MarketPlace</div>
            <div class="nav-links">
                <a href="index.php" class="nav-link">HOME</a>
                <a href="article" class="nav-link active">ARTICLES</a>
                <a href="Panier.php" class="nav-link">PANIER</a>
                <a href="profile" class="nav-link">PROFILE</a>
            </div>
            <div class="nav-buttons">
                <a href="register.php" class="btn-secondary">S'inscrire</a>
                <a href="vente.php" class="btn-primary">Vends tes articles !</a>
            </div>
        </nav>

        <?php
        require_once 'includes/db.php';

        if (!isset($_GET['id'])) {
            echo "Article non trouvé.";
            exit;
        }

        $id = intval($_GET['id']);
        $stmt = $conn->prepare("SELECT a.*, u.nom, u.prenom FROM Article a LEFT JOIN utilisateurs u ON a.author_id = u.id WHERE a.id = ?");
        $stmt->execute([$id]);
        $article = $stmt->fetch();

        if (!$article) {
            echo "Article non trouvé.";
            exit;
        }
        ?>

        <div class="detail-container">
            <div class="detail-image-container">
                <?php if (!empty($article['image_link'])): ?>
                    <img src="<?= htmlspecialchars($article['image_link']) ?>" alt="<?= htmlspecialchars($article['name']) ?>" class="detail-image">
                <?php else: ?>
                    <div class="detail-image" style="display:flex;align-items:center;justify-content:center;font-size:32px;color:#aaa;">
                        📷 Aucune image
                    </div>
                <?php endif; ?>
            </div>
            <div class="detail-content">
                <div class="detail-header">
                    <h1 class="detail-title"><?= htmlspecialchars($article['name']) ?></h1>
                    <div class="detail-price"><?= number_format($article['price'], 2, ',', ' ') ?> €</div>
                </div>
                
                <div class="detail-meta">
                    Vendu par <?= htmlspecialchars($article['prenom'] . ' ' . $article['nom']) ?><br>
                    Publié le <?= date('d/m/Y à H:i', strtotime($article['publication_date'])) ?>
                </div>
                
                <div class="detail-description">
                    <?= nl2br(htmlspecialchars($article['description'])) ?>
                </div>

                <div class="detail-actions">
                    <a href="index.php" class="back-link">
                        ← Retour aux articles
                    </a>
                    <button class="btn-cart">Ajouter au panier</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
