<?php
session_start();
// on connecte la base de données
require_once '../config/config.php';
$errors = [];
$success = '';
// on verifie si le formulaire est soumis
$token = $_GET['token'] ?? '';
if (empty($token)) {
    $errors[] = "Lien de réinitialisation invalide.";
}
// traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    // on recupere les données du formulaire
    $token = $_POST['token'] ?? '';
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    // on verifie le token csrf
    if (empty($csrf_token)|| !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $errors[] = "Token CSRF invalide";
    }
    // on verifie si le mot de passe est valide
    if (empty($password) || strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    if (empty($errors)){
        $stmt = $pdo->prepare("SELECT email, expires_at FROM password_resets WHERE token = :token");
        $stmt->execute([':token' => $token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reset || strtotime($reset['expires_at']) < time()) {
            $errors[] = "Le lien de réinitialisation est invalide ou a expiré.";
        } else {
            // mise à jour du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            // Adaptation à ta base : table utilisateurs, champ mot_de_passe
            $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = :mot_de_passe WHERE email = :email");
            $stmt->execute([
                ':mot_de_passe' => $hashedPassword,
                ':email' => $reset['email']
            ]);
            // on supprime le token de réinitialisation
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = :token");
            $stmt->execute([':token' => $token]);
            $success = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
        }
    }
}
// on genere le token csrf pour le formulaire
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!-- formulaire -->
<?php if ($success): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
    <a href="login.php">Se connecter</a>
<?php else: ?>
    <?php foreach ($errors as $error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>
    <?php if (!empty($token)): ?>
    <form method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <label for="password">Nouveau mot de passe:</label>
        <input type="password" name="password" id="password" required>
        <label for="confirm_password">Confirmer le mot de passe:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
        <button type="submit">Réinitialiser le mot de passe</button>
    </form>
    <?php endif; ?>
<?php endif; ?>