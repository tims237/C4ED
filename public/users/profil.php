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
<form action="" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
    <div>
        <label for="nom">Nom :</label>
        <input type="text" name="nom" id="nom" value="<?= e($userInfo['nom'] ?? '') ?>">
    </div>
     <div>
        <label for="prenom">Prénom :</label>
        <input type="text" name="prenom" id="prenom" value="<?= e($userInfo['prenom'] ?? '') ?>">
    </div>
    <div>
        <label for="ville">Ville :</label>
        <input type="text" name="ville" id="ville" value="<?= e($userInfo['ville'] ?? '') ?>">
    </div>
    <div>
        <label for="email">Email :</label>
        <input type="email" name="email" id="email" value="<?= e($userInfo['email'] ?? '') ?>">
    </div>
    <hr>
    <div>
        <label for="new_password">Nouveau mot de passe :</label>
        <input type="password" name="new_password" id="new_password">
    </div>
    <div>
        <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
        <input type="password" name="confirm_password" id="confirm_password">
    </div>
    <small>Laisse les champs mot de passe vides si tu ne veux pas le changer.</small>
    <br>
    <button type="submit">Mettre à jour</button>
</form>