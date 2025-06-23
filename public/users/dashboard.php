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

if (!$user) {
    // Déconnexion forcée si l'utilisateur n'existe plus
    session_destroy();
    header('Location: ../login.php');
    exit;
}

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
            WHEN type = 'virement' AND utilisateur_id = :id1 THEN -montant
            WHEN type = 'virement' AND destinataire_id = :id1 THEN montant
            ELSE 0
        END
    ) AS solde
    FROM transactions 
    WHERE utilisateur_id = :id1 OR destinataire_id = :id2
");
$stmt->execute(['id1' => $userId, 'id2' => $userId]);
$solde = $stmt->fetchColumn() ?? 0;

// Récupère les 5 dernières transactions
$stmt = $pdo->prepare("
    SELECT t.*, u.email AS destinataire_email
    FROM transactions t
    LEFT JOIN users u ON t.destinataire_id = u.id
    WHERE t.utilisateur_id = :id1 OR t.destinataire_id = :id2
    ORDER BY t.date_operation DESC
    LIMIT 5
");
$stmt->execute(['id1' => $userId, 'id2' => $userId]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fonction utilitaire pour afficher l’email de l’émetteur (pour virement reçu)
function getUserEmailById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetchColumn();
}

include '../../includes/tete.php';
include '../../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="form-bg mb-4">
                <h2 class="text-center mb-3 display-5 fw-bold">
                    Bienvenue, <?= htmlspecialchars($userPrenom . ' ' . $userNom) ?>
                </h2>
                <p class="text-center mb-2">Email : <strong><?= htmlspecialchars($userEmail) ?></strong></p>
                <p class="text-center mb-2">Ville : <strong><?= htmlspecialchars($userVille) ?></strong></p>
                <p class="text-center fs-4">
                    Votre solde actuel est de :
                    <span class="fw-bold text-success"><?= number_format($solde, 2) ?> €</span>
                </p>
                <nav class="d-flex flex-wrap justify-content-center gap-2 my-3">
                    <a href="../transaction.php" class="btn btn-success">
                        <i class="fas fa-exchange-alt me-2"></i>Effectuer une transaction
                    </a>
                    <a href="historique.php" class="btn btn-outline-dark">
                        <i class="fas fa-list me-2"></i>Historique des transactions
                    </a>
                    <a href="profil.php" class="btn btn-outline-success">
                        <i class="fas fa-user me-2"></i>Mon profil
                    </a>
                    <a href="logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                    </a>
                </nav>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3 class="mb-3 text-success fw-bold">Vos 5 dernières transactions</h3>
                    <?php if ($transactions): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle">
                                <thead class="table-success">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Montant</th>
                                        <th>Destinataire</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($transactions as $t): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($t['date_operation']) ?></td>
                                        <td><?= ucfirst($t['type']) ?></td>
                                        <td class="<?= ($t['type'] === 'depot' || $t['destinataire_id'] == $userId) ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format($t['montant'], 2) ?> €
                                        </td>
                                        <td>
                                            <?php
                                            if ($t['type'] === 'virement') {
                                                echo $t['utilisateur_id'] == $userId
                                                    ? 'Vers : ' . htmlspecialchars($t['destinataire_email'])
                                                    : 'De : ' . htmlspecialchars(getUserEmailById($pdo, $t['utilisateur_id']));
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">Aucune transaction récente.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="mb-3 text-dark fw-bold">Que souhaitez-vous faire ?</h3>
                    <ul>
                        <li>Effectuer un dépôt, retrait ou virement</li>
                        <li>Consulter votre historique complet</li>
                        <li>Modifier vos informations personnelles</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../../templates/footer.php';
?>
