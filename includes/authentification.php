<?php
// Vérifie si l'utilisateur est connecté
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

// Vérifie si l'utilisateur est un admin
function is_admin(): bool {
    return is_logged_in() && (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
}

// Redirige vers la page de connexion si l'utilisateur n'est pas connecté
function require_login(): void {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

// Redirige vers le dashboard si l'utilisateur n'est pas admin
function require_admin(): void {
    if (!is_admin()) {
        header("Location: dashboard.php");
        exit();
    }
}