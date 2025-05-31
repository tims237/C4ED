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

// Récupère le solde de l'utilisateur
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

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $montant = floatval($_POST['montant'] ?? 0);
    $destinataire_email = trim($_POST['destinataire_email'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Vérification CSRF
    if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $errors[] = "Token CSRF invalide";
    }
    if (!in_array($type, ['depot', 'retrait', 'virement'])) {
        $errors[] = "Type d'opération invalide";
    }
    if ($montant <= 0) {
        $errors[] = "Le montant doit être supérieur à 0";
    }

    // Vérifie le solde pour un retrait ou un virement
    if (($type === 'retrait' || $type === 'virement') && $montant > $solde) {
        $errors[] = "Solde insuffisant pour cette opération. Votre solde est de " . number_format($solde, 2) . " €.";
    }

    $destinataire_id = null;
    if ($type === 'virement') {
        if (empty($destinataire_email)) {
            $errors[] = "Veuillez saisir l'email du destinataire";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $destinataire_email]);
            $destinataire_id = $stmt->fetchColumn();
            if (!$destinataire_id) {
                $errors[] = "Destinataire introuvable";
            }
            if ($destinataire_id == $userId) {
                $errors[] = "Vous ne pouvez pas faire un virement vers vous-même";
            }
        }
    }

    if (empty($errors)) {
        $sql = "INSERT INTO transactions (utilisateur_id, type, montant, destinataire_id, date_operation) VALUES (:uid, :type, :montant, :dest, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':uid' => $userId,
            ':type' => $type,
            ':montant' => $montant,
            ':dest' => $type === 'virement' ? $destinataire_id : null
        ]);
        $success = "Opération réalisée avec succès.";
        // Met à jour le solde après opération
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
    }
}

// Génère le token CSRF si besoin
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include '../templates/header.php';
?>

<h2>Nouvelle opération</h2>
<p>Votre solde actuel : <strong><?= number_format($solde, 2) ?> €</strong></p>

<?php if ($success): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <ul style="color:red">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <label>Type :</label>
    <select name="type" id="type" required onchange="document.getElementById('destinataire').style.display = this.value === 'virement' ? 'inline' : 'none';">
        <option value="depot">Dépôt</option>
        <option value="retrait">Retrait</option>
        <option value="virement">Virement</option>
    </select>
    <span id="destinataire" style="display:none;">
        <label>Email destinataire :</label>
        <input type="email" name="destinataire_email">
    </span>
    <label>Montant :</label>
    <input type="number" step="0.01" name="montant" required>
    <button type="submit">Valider</button>
</form>

<p><a href="users/dashboard.php">⬅ Retour au tableau de bord</a></p>

<?php include '../templates/footer.php'; ?>

<script>
document.getElementById('type').addEventListener('change', function() {
    document.getElementById('destinataire').style.display = this.value === 'virement' ? 'inline' : 'none';
});
</script>
