<?php
    session_start();
    // on connecte la base de données
    require_once '../config/config.php';
    // on inclut le fichier d'authentification
    require_once '../includes/authentifation.php';
    require_once '../includes/helpers.php';

    if (is_logged_in()) {
        redirect($_SESSION['user_role'] === 'admin' ? 'admin_panel.php' : 'dashboard.php');
    }

    // on vérifie si l'utilisateur est connecté
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    // on inclut le fichier de configuration
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
        // on vérifie  si tous les champs sont remplis
        if (empty($email)||empty($password)){
            $errors[] = 'Veuillez remplir tous les champs' ;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide";
        }
        // on recherche le mail de l'utilisateur dans la base de données
        if (empty($errors)) 
        {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
            // si l'utilisateur existe et que le mot de passe est correct

            if ($utilisateur && password_verify($password, $utilisateur['password'])) 
            {
                $_SESSION['user_id'] = $utilisateur['id'];
                $_SESSION['user_email'] = $utilisateur['email'];
                $_SESSION['user_role'] = $utilisateur['role'];
                // on redirige l'utilisateur vers la page d'accueil
                // on redirige selon le role 
                if ($utilisateur['role'] === 'admin') {
                    redirect("admin_panel.php");
                } else {
                    redirect("dashboard.php");
                }
            } else {
                $errors[] = 'Identifiants incorrects';
            }
        }
    }

    // Affichage des erreurs si besoin
    display_errors($errors);
?>

<!-- Formulaire de connexion -->
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
    <label>Email :</label>
    <input type="email" name="email" required><br>
    <label>Mot de passe :</label>
    <input type="password" name="password" required><br>
    <button type="submit">Connexion</button>
</form>