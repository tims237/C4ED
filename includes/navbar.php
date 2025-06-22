<?php
// Assurez-vous que la session est démarrée si elle ne l'est pas déjà
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? null;
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= $user ? 'home.php' : 'index.php' ?>"> C4ED</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($user): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Accueil</a>
                    </li>
                    <?php if ($role === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">Admin Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="user_management.php">Gestion Utilisateurs</a>
                        </li>
                    <?php elseif ($role === 'user'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profil.php">Mon Profil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="transactions.php">Mes Transactions</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if ($user): ?>
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user me-2"></i>
                            <?= htmlspecialchars($user['prenom']) ?>
                            <span class="badge bg-secondary ms-2"><?= htmlspecialchars($role) ?></span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-danger ms-2" href="logout.php" tabindex="0">
                            <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-light" href="index.php" tabindex="0">
                            <i class="fas fa-sign-in-alt me-2"></i>Connexion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-success ms-2" href="register.php" tabindex="0">
                            <i class="fas fa-user-plus me-2"></i>Inscription
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<style>
.btn:focus, .btn:active {
    box-shadow: 0 0 0 0.2rem #00800033 !important;
    border-color: #008000 !important;
    outline: none !important;
    transition: box-shadow 0.2s, border-color 0.2s;
}
</style>