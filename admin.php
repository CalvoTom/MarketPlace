<?php
session_start();
require_once 'includes/db.php';



// Vérification que l'utilisateur est admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Suppression utilisateur
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit;
}

// Suppression article
if (isset($_GET['delete_article'])) {
    $id = intval($_GET['delete_article']);
    $stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit;
}

// Récupérer tous les utilisateurs
$utilisateurs = $conn->query("SELECT id, nom, prenom, email, role, sold, date_creation FROM utilisateurs")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les articles avec leurs auteurs
$articles = $conn->query("
    SELECT a.id, a.nom, a.description, a.prix, a.date_publication, a.image_url, u.nom AS auteur_nom, u.prenom AS auteur_prenom
    FROM articles a
    JOIN utilisateurs u ON a.auteur_id = u.id
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f8f9fa; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 40px; background: #fff; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f2f2f2; }
        a.delete { color: red; text-decoration: none; font-weight: bold; }
        a.delete:hover { text-decoration: underline; }
    </style>
</head>
<body>

<h1>Panneau d'administration</h1>

<h2>Utilisateurs</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Solde (€)</th>
            <th>Date création</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($utilisateurs as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['nom']) ?></td>
            <td><?= htmlspecialchars($u['prenom']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['role']) ?></td>
            <td><?= number_format($u['sold'], 2, ',', ' ') ?></td>
            <td><?= $u['date_creation'] ?></td>
            <td><a class="delete" href="?delete_user=<?= $u['id'] ?>" onclick="return confirm('Supprimer cet utilisateur ?')">🗑️</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Articles</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Prix (€)</th>
            <th>Auteur</th>
            <th>Date publication</th>
            <th>Image</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($articles as $a): ?>
        <tr>
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['nom']) ?></td>
            <td><?= htmlspecialchars(substr($a['description'], 0, 50)) ?>...</td>
            <td><?= number_format($a['prix'], 2, ',', ' ') ?></td>
            <td><?= htmlspecialchars($a['auteur_prenom'] . ' ' . $a['auteur_nom']) ?></td>
            <td><?= $a['date_publication'] ?></td>
            <td>
                <?php if ($a['image_url']): ?>
                    <img src="<?= htmlspecialchars($a['image_url']) ?>" alt="image" width="50">
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </td>
            <td><a class="delete" href="?delete_article=<?= $a['id'] ?>" onclick="return confirm('Supprimer cet article ?')">🗑️</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
