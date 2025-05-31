<?php
session_start();
require_once '../../config/config.php';
require_once '../../includes/helpers.php';
require_once '../../includes/middleware.php';

adminOnly(); // Seul un admin peut accéder

include '../../templates/header.php';
?>

<h2>Panneau d'administration</h2>

<nav style="margin: 20px 0;">
    <a href="manage_users.php">👥 Gérer les utilisateurs</a> |
    <a href="add_users.php">➕ Ajouter un utilisateur</a> |
    <a href="admin_transaction.php">💼 Valider les transactions importantes</a> |
    <a href="admin_profil.php">👤 Mon profil</a> |
    <a href="admin_logout.php" style="color: red;">🚪 Déconnexion</a>
</nav>

<ul>
    <li>Ajouter, modifier ou supprimer des utilisateurs</li>
    <li>Définir le seuil de dépense et effectuer un dépôt direct</li>
    <li>Valider les transactions importantes des clients</li>
    <li>Superviser l'activité de la plateforme</li>
    <li>Modifier les informations de votre propre profil</li>
</ul>

<?php include '../../templates/footer.php'; ?>