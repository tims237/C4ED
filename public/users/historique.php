<?php
session_start();
require_once '../config/config.php';
require_once '../includes/helpers.php';
require_once '../includes/middleware.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Récupère toutes les transactions (entrantes et sortantes)
$stmt = $pdo->prepare("
    SELECT t.*, u.email AS destinataire_email
    FROM transactions t
    LEFT JOIN users u ON t.destinataire_id = u.id
    WHERE t.utilisateur_id = :id OR t.destinataire_id = :id
    ORDER BY t.date_operation DESC
");
$stmt->execute(['id' => $userId]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../templates/header.php';
?>

<h2>Historique complet de vos transactions</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Montant</th>
        <th>Destinataire</th>
    </tr>
    <?php if ($transactions): ?>
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
    <?php else: ?>
        <tr><td colspan="4">Aucune transaction enregistrée.</td></tr>
    <?php endif; ?>
</table>

<?php include '../templates/footer.php'; ?>

<?php
function getUserEmailById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetchColumn();
}
?>