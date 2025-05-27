<?php
//on inclut la base de données
require_once '../config/config.php';
//on valide les donnees  du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_Password = trim($_POST['confirm_password'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = 'user'; 
    $csrf_token = $_POST['csrf_token'] ?? '';
    // on verifie le token csrf
    if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $errors[] = "Token CSRF invalide";
    }
    // on doit valider les champs
    if (empty($nom) || empty($prenom)) {
        $errors[] = "Nom et prénom sont obligatoires.";
    }
    // on verifie l'email existe
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Email invalide");
    }
    if (strlen($password) < 8) {
        die("Le mot de passe doit contenir au moins 8 caractères");
    }
    if ($password !== $confirm_Password) {
        die("Les mots de passe ne correspondent pas");
    }
    // on verifie l'unicite de l'email
    if (empty($errors)) {
        // on verifie que l'email n'existe pas deja
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        // on lie le parametre email
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            die("L'email existe deja");
        }
    }
    // on hash le mot de passe
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
 
    }

    // on confirme le mot de passe en verifiant que mot de passe et la confirmation du mot de passe sont identiques
    // on recupere le mot de passe et la confirmation du mot de passe
    $Password = $_POST['password'] ?? '';
    $confirm_Password = $_POST['confirm_password'] ?? '';
    if ($Password !== $confirm_Password) {
        die("Les mots de passe ne correspondent pas");
    }
    // on insere l'utilisateur dans la base de donnees
    $sql = "INSERT INTO users (nom, prenom, email, password, role) VALUES (:nom, :prenom, :email, :password, :role)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':email' => $email,
        ':password' => $hashedPassword,
        ':role' => $role
    ]);
    // detruire le token crsf pour eviter la reutilisation
    unset($_SESSION['csrf_token']);
    // on redirige l'utilisateur vers la page de connexion
    header("Location: login.php?success=1");
    exit();
    }