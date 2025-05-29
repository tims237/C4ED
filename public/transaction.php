<?php
session_start();
require_once '../config/config.php';
require_once '../includes/helpers.php';
require_once '../includes/middleware.php';

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Récupère les infos utilisateur
$stmt = $pdo->prepare("SELECT email, prenom, nom, ville FROM users WHERE id = :id");
$stmt->execute(['id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$userEmail = $user['email'];
$userPrenom = $user['prenom'] ?? '';
$userNom = $user['nom'] ?? '';
$userVille = $user['ville'] ?? '';

// Calcule le solde
$stmt = $pdo->prepare("
    SELECT SUM(
        CASE 
            WHEN type = 'depot' THEN montant
            WHEN type = 'retrait' THEN -montant
            WHEN type = 'virement' AND utilisateur_id = :id THEN -montant
            WHEN type = 'virement' AND destinataire_id = :id THEN montant
            ELSE 0
        END
    ) AS solde
    FROM transactions 
    WHERE utilisateur_id = :id OR destinataire_id = :id
");
$stmt->execute(['id' => $userId]);
$solde = $stmt->fetchColumn() ?? 0;
?>

<?php include '../templates/header.php'; ?>

<h2>Bienvenue, <?= htmlspecialchars($userPrenom . ' ' . $userNom) ?> (<?= htmlspecialchars($userEmail) ?>)</h2>
<p>Ville : <strong><?= htmlspecialchars($userVille) ?></strong></p>
<p>Votre solde actuel est de : <strong><?= number_format($solde, 2) ?> €</strong></p>

<nav style="margin: 20px 0;">
    <a href="../transaction.php">💸 Effectuer une transaction</a> |
    <a href="historique.php">📄 Historique des transactions</a> |
    <a href="profil.php">👤 Mon profil</a> |
    <a href="logout.php" style="color: red;">🚪 Déconnexion</a>
</nav>

<h3>Que souhaitez-vous faire ?</h3>
<ul>
    <li>Effectuer un dépôt, retrait ou virement</li>
    <li>Consulter votre historique complet</li>
    <li>Modifier vos informations personnelles</li>
</ul>

<?php include '../templates/footer.php'; ?>
