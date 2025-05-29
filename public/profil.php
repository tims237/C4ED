<?php
session_start();
// on connecte la base de données
require_once '../config/config.php';
require_once '../includes/middleware.php';
require_once '../includes/helpers.php';
require_once '../includes/authentification.php';
// on charge les informations de l'utilisateur
$userid = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = :id");
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

    if (empty($errors)) {
        // on met à jour les informations de l'utilisateur dans la base de données
        $stmt = $pdo->prepare("UPDATE utilisateurs SET email = :email, nom = :nom, prenom = :prenom, ville = :ville WHERE id = :id");
        $stmt->execute([
            ':email' => $email,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':ville' => $ville,
            ':id' => $userid
        ]);
        $success = "Profil mis à jour avec succès";
        // Recharge les infos utilisateur
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = :id");
        $stmt->execute([':id' => $userid]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    }
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
    
   
    <button type="submit">Mettre à jour</button>
</form>