<?php
session_start();
require_once '../../config/config.php';
require_once '../../includes/helpers.php';
require_once '../../includes/middleware.php';

adminOnly(); // Seul un admin peut accéder

// Définir le seuil d'une transaction "importante"
$seuil = 1000; // Exemple : toute transaction >= 1000€ doit être validée

// Validation d'une transaction par l'admin
if (isset($_POST['valider']) && isset($_POST['transaction_id'])) {
    $transaction_id = intval($_POST['transaction_id']);
    // Met à jour la transaction comme validée
    $stmt = $pdo->prepare("UPDATE transactions SET validee = 1 WHERE id = :id");
    $stmt->execute(['id' => $transaction_id]);
}

// Récupérer les transactions importantes non validées
$stmt = $pdo->prepare("
    SELECT t.*, u.email AS utilisateur_email
    FROM transactions t
    JOIN users u ON t.utilisateur_id = u.id
    WHERE t.montant >= :seuil AND (t.validee IS NULL OR t.validee = 0)
    ORDER BY t.date_operation DESC
");
$stmt->execute(['seuil' => $seuil]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../templates/header.php';
?>

<h2>Validation des transactions importantes (≥ <?= $seuil ?> €)</h2>

<?php if (empty($transactions)): ?>
    <p>Aucune transaction à valider.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Utilisateur</th>
            <th>Type</th>
            <th>Montant</th>
            <th>Action</th>
        </tr>
        <?php foreach ($transactions as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['id']) ?></td>
                <td><?= htmlspecialchars($t['date_operation']) ?></td>
                <td><?= htmlspecialchars($t['utilisateur_email']) ?></td>
                <td><?= ucfirst($t['type']) ?></td>
                <td><?= number_format($t['montant'], 2) ?> €</td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="transaction_id" value="<?= $t['id'] ?>">
                        <button type="submit" name="valider" onclick="return confirm('Valider cette transaction ?');">Valider</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php include '../../templates/footer.php'; ?>