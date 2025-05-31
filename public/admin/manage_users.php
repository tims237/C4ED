<?php
session_start();
require_once '../../config/config.php';
require_once '../../includes/helpers.php';
// require_once '../../includes/middleware.php';

// adminOnly(); // Protection admin

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = intval($_POST['id'] ?? 0);
    $csrf = $_POST['csrf_token'] ?? '';

    if ($csrf !== $_SESSION['csrf_token']) {
        exit("Token CSRF invalide");
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
    }

    // Définir le seuil de dépense
    if ($action === 'set_seuil') {
        $seuil = floatval($_POST['seuil'] ?? 0);
        $stmt = $pdo->prepare("UPDATE users SET seuil_depense = :seuil WHERE id = :id");
        $stmt->execute(['seuil' => $seuil, 'id' => $userId]);
    }

    // Ajouter un dépôt direct (solde)
    if ($action === 'add_depot') {
        $montant = floatval($_POST['montant'] ?? 0);
        if ($montant > 0) {
            $stmt = $pdo->prepare("INSERT INTO transactions (utilisateur_id, type, montant, date_operation, validee) VALUES (:id, 'depot', :montant, NOW(), 1)");
            $stmt->execute(['id' => $userId, 'montant' => $montant]);
        }
    }
}

// Récupération de la liste des utilisateurs (ajoute le champ seuil_depense si besoin)
$stmt = $pdo->query("SELECT id, nom, prenom, email, role, seuil_depense FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../templates/header.php';
?>

<h2>Gestion des utilisateurs</h2>
<p><a href="add_users.php">+ Ajouter un utilisateur</a></p>

<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Seuil de dépense (€)</th>
            <th>Dépôt direct</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['nom']) ?></td>
                <td><?= htmlspecialchars($user['prenom']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="set_seuil">
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="number" step="0.01" name="seuil" value="<?= htmlspecialchars($user['seuil_depense'] ?? 0) ?>" style="width:80px;">
                        <button type="submit">OK</button>
                    </form>
                </td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="add_depot">
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="number" step="0.01" name="montant" placeholder="Montant" style="width:80px;">
                        <button type="submit">Déposer</button>
                    </form>
                </td>
                <td>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" style="color:red;">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../../templates/footer.php'; ?>
