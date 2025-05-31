<?php
session_start();
// on connecte la base de données
require_once '../../config/config.php';
$success = '';
$errors = [];

// on verifie si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ON RECUPERE LES DONNEES DU FORMULAIRE
    $email = trim($_POST['email'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    // on verifie le token csrf
    if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $errors[] = "Token CSRF invalide";
    }
    // on verifie si l'email est valide
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide";
    }
    if (empty($errors)) {
        // on verifie l'email dans la base de donnees 
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $success = "Un email de réinitialisation a été envoyé à $email si cet email est associé à un compte.";
        if ($user) {
            // on genere le token de réinitialisation
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            // on insere le token dans la base de données
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)");
            $stmt->execute([
                ':email' => $email,
                ':token' => $token,
                ':expires_at' => $expires_at
            ]);
            // on envoie l'email de réinitialisation
            $reset_link = "http://localhost/C4ED/public/reset_password.php?token=$token";
            $subject = "Réinitialisation de mot de passe";
            $message = "Cliquez sur le lien suivant pour réinitialiser votre mot de passe : $reset_link";
            $headers = "From: no-reply@yourdomain.com";
            // mail($email, $subject, $message, $headers); // Décommente pour envoyer l'email
        }
    }
}
// on genere le token csrf
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
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
    <label for="email">Votre email :</label>
    <input type="email" name="email" id="email" required>
    <button type="submit">Réinitialiser</button>
</form>