<?php
session_start();
require_once '../../config/config.php';
require_once '../../includes/helpers.php';
require_once '../../includes/authentification.php';

// Vérifie que l'utilisateur est connecté
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$userid = $_SESSION['user_id'];

// Utilise la bonne table
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute([':id' => $userid]);
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
// traitement du formulaire de mise a jour du profil
$success = '';
$errors = [];  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // on récupère les données du formulaire
    $email = trim($_POST['email'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Vérification CSRF
    if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $errors[] = "Token CSRF invalide";
    }

    // on vérifie si tous les champs sont remplis
    if (empty($email) || empty($nom) || empty($prenom) || empty($ville)) {
        $errors[] = 'Veuillez remplir tous les champs';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide";
    }

    // Si l'utilisateur souhaite changer son mot de passe
    $password_sql = '';
    $params = [
        ':email' => $email,
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':ville' => $ville,
        ':id' => $userid
    ];
    if (!empty($new_password) || !empty($confirm_password)) {
        if (strlen($new_password) < 8) {
            $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "Les mots de passe ne correspondent pas";
        }
        if (empty($errors)) {
            $password_sql = ", password = :password";
            $params[':password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }

    if (empty($errors)) {
        // on met à jour les informations de l'utilisateur dans la base de données
        $sql = "UPDATE users SET email = :email, nom = :nom, prenom = :prenom, ville = :ville $password_sql WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $success = "Profil mis à jour avec succès";
        // Recharge les infos utilisateur
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $userid]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
// Génère le token CSRF si besoin
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<?php if (!empty($success)): ?>
    <p style="color: green;"><?php echo e($success); ?></p>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <ul style="color: red;">
        <?php foreach ($errors as $error): ?>
            <li><?php echo e($error); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php require_once '../../includes/tete.php'; ?>
<?php require_once '../../templates/header.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="form-bg shadow-lg border border-3 border-success">
                <form action="" class="" method="post">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom :</label>
                        <input type="text" class="form-control" name="nom" id="nom" value="<?= e($userInfo['nom'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom :</label>
                        <input type="text" class="form-control" name="prenom" id="prenom" value="<?= e($userInfo['prenom'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="ville" class="form-label">Ville :</label>
                        <input type="text" class="form-control" name="ville" id="ville" value="<?= e($userInfo['ville'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email :</label>
                        <input type="email" class="form-control" name="email" id="email" value="<?= e($userInfo['email'] ?? '') ?>">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nouveau mot de passe :</label>
                        <input type="password" class="form-control" name="new_password" id="new_password">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe :</label>
                        <input type="password" class="form-control" name="confirm_password" id="confirm_password">
                    </div>
                    <small class="text-muted">Laisse les champs mot de passe vides si tu ne veux pas le changer.</small>
                    <br>
                    <div class="d-flex justify-content-center mt-3">
                        <button type="submit" class="btn btn-success">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
