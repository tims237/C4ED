<?php
session_start();
require_once '../../config/config.php';
require_once '../../includes/helpers.php';
require_once '../../includes/middleware.php';

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

// Récupère les 5 dernières transactions
$stmt = $pdo->prepare("
    SELECT t.*, u.email AS destinataire_email
    FROM transactions t
    LEFT JOIN users u ON t.destinataire_id = u.id
    WHERE t.utilisateur_id = :id OR t.destinataire_id = :id
    ORDER BY t.date_operation DESC
    LIMIT 5
");
$stmt->execute(['id' => $userId]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<h3>Vos 5 dernières transactions</h3>
<?php if ($transactions): ?>
    <table border="1" cellpadding="5">
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Montant</th>
            <th>Destinataire</th>
        </tr>
        <?php foreach ($transactions as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['date_operation']) ?></td>
                <td><?= ucfirst($t['type']) ?></td>
                <td style="color: <?= ($t['type'] === 'depot' || $t['destinataire_id'] == $userId) ? 'green' : 'red' ?>">
                    <?= number_format($t['montant'], 2) ?> €
                </td>
                <td>
                    <?php
                    if ($t['type'] === 'virement') {
                        echo $t['utilisateur_id'] == $userId
                            ? 'Vers : ' . htmlspecialchars($t['destinataire_email'])
                            : 'De : ' . getUserEmailById($pdo, $t['utilisateur_id']);
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>Aucune transaction récente.</p>
<?php endif; ?>

<h3>Que souhaitez-vous faire ?</h3>
<ul>
    <li>Effectuer un dépôt, retrait ou virement</li>
    <li>Consulter votre historique complet</li>
    <li>Modifier vos informations personnelles</li>
</ul>

<?php include '../templates/footer.php'; ?>

<?php
// Fonction utilitaire pour afficher l’email de l’émetteur (pour virement reçu)
function getUserEmailById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetchColumn();
}
?>
