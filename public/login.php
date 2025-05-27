<?php
session_start();
// on connecte la base de données
require_once '../config/config.php';
// on vérifie si l'utilisateur est connecté
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // on récupère les données du formulaire
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        // on vérifie  si tous les champs sont remplis
        if (empty($email)||empty($password)){
            die('Veuillez remplir tous les champs');
        }
        // on recherche le mail de l'utilisateur dans la base de données
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $utilisateur = $stmt->fetch();
        // si l'utilisateur existe et que le mot de passe est correct

        if ($utilisateur && password_verify($password, $utilisateur['password'])) {



            $_SESSION['user_id'] = $utilisateur['id'];
            $_SESSION['user_email'] = $utilisateur['email'];
            $_SESSION['user_role'] = $utilisateur['role'];
            // on redirige l'utilisateur vers la page d'accueil
            // on redirige selon le role 
            if ($utilisateur['role'] === 'admin') {
                header("Location: admin_panel.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            // si l'utilisateur n'existe pas ou que le mot de passe est incorrect
            die('Identifiants incorrects');

        }
    }

}

?>