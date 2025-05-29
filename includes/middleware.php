<?php
session_start();

// Rediriger les utilisateurs non connectés qui essaient d'accéder à des pages protégées
$protectedPages = [
    '/dashboard.php',
    '/admin_panel.php',
    '/transactions.php',
    '/profile.php'
];

$currentPage = $_SERVER['PHP_SELF'];

// Si la page est protégée et que l'utilisateur n'est pas connecté
if (in_array($currentPage, $protectedPages) && empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Rediriger les utilisateurs connectés qui essaient d'accéder à login ou register
$guestOnlyPages = [
    '/login.php',
    '/register.php'
];

if (in_array($currentPage, $guestOnlyPages) && isset($_SESSION['user_id'])) {
    // Redirige vers le bon espace en fonction du rôle
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: /admin_panel.php');
    } else {
        header('Location: /dashboard.php');
    }
    exit();
}

// Rediriger les clients qui essaient d’accéder à des pages admin
$adminOnlyPages = [
    '/admin_panel.php',
    '/admin_transactions.php'
];

if (in_array($currentPage, $adminOnlyPages) && ($_SESSION['user_role'] ?? '') !== 'admin') {
    // Accès refusé : redirige vers tableau de bord utilisateur
    header('Location: /dashboard.php');
    exit();
}
