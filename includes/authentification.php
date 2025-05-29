<?php
//verifie si l'utilisateur est connecté
function is_logged_in(): bool{
    return isset($_SESSION['user_id']);
}
// verifie si l'utilisateur est un admin
function is_admin(): bool {
    return is_logged_in() && $_SESSION['user']['role'] === 'admin';
}
function require_login(): void {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}
function require_admin(): void {
    if (!is_admin()) {
        header("Location: dashboard.php");
        exit();
    }
}