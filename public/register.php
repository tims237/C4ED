<?php
//on inclut la base de données
requered_once '../config/config.php';
//on valide les donnees  du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'user';
    // on doit valider les champs
    // on verifie l'email existe
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Email invalide");
    }
    if (strlen($password) < 8) {
        die("Le mot de passe doit contenir au moins 8 caractères");
    }
    // on verifie que la valeur du role est valide
    if (!in_array($role, ['user', 'admin'])) {
        die("Role invalide");
    }
    // on verifie que l'email n'existe pas deja
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    // on lie le parametre email
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        die("L'email existe deja");
    }
    // on hash le mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
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

}