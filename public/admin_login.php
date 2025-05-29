<?php
session_start();
// on connecte la base de données
require_once '../includes/middleware.php';
require_once '../config/config.php';
require_once '../includes/authentification.php';
require_once '../includes/helpers.php';

// on vérifie si l'utilisateur est déjà connecté
if (is_logged_in() && $_SESSION['user_role'] === 'admin') {
    redirect('admin_panel.php');
}

// on vérifie si le token CSRF existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // on récupère les données du formulaire
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';

    // on vérifie le token CSRF
    if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $errors[] = "Token CSRF invalide";
    }

    // on vérifie si tous les champs sont remplis
    if (empty($email) || empty($password)) {
        $errors[] = 'Veuillez remplir tous les champs';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide";
    }

    // on recherche l'email de l'utilisateur dans la base de données
    if (empty($errors)) {
        // Adaptation à ta base : table = users, champ password
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // si l'utilisateur existe, est admin, et que le mot de passe est correct
        if ($user && $user['role'] === 'admin' && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_ville'] = $user['ville'];
            set_flash('success', 'Connexion réussie !');
            // on redirige l'utilisateur vers la page d'accueil admin
            redirect("admin_panel.php");
        } else {
            $errors[] = 'Identifiants incorrects';
        }
    }
}
// Affichage des messages d'erreur
display_flash();
display_errors($errors);
?>
<!-- formulaire de connexion -->
<form action="" method="post">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
    <div>
        <label for="email">Email :</label>
        <input type="email" name="email" id="email" value="<?= e($email ?? '') ?>">
    </div>
    <div>
        <label for="password">Mot de passe :</label>
        <input type="password" name="password" id="password">
    </div>
    <button type="submit">Se connecter</button>
</form>
