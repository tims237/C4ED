<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>C4ED - Mon espace</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Ajoutez Font Awesome si besoin -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/C4ED/assets/style.css">
    <style>
        .navbar-logo {
            height: 40px; /* Ajustez la taille selon vos besoins */
            width: auto;
            object-fit: contain;
        }
    </style>
</head>
<body class="bg-light text-dark">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <img src="/C4ED/assets/C4ED_LOGO.jpg" alt="Logo C4ED" class="navbar-logo me-2">
            <a class="navbar-brand" href="">C4ED</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                     <li>
                        <a href="/C4ED/public/users/dashboard.php" class="btn btn-light ms-2">
                            <i class="fas fa-home me-2"></i> Dashboard
                        </a>
                    </li>
                     <li>
                        <a href="/C4ED/public/users/historique.php" class="btn btn-success ms-2">
                            <i class="fas fa-exchange-alt me-2"></i> Transactions
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                   
                    <li>
                        <a href="/C4ED/public/users/profil.php" class="btn btn-primary ms-2">
                            <i class="fas fa-user me-2"></i> Profil
                        </a>
                    </li>
                   
                    <li>
                        <a href="/C4ED/public/users/logout.php" class="btn btn-danger ms-2">
                            <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<?php
// Assurez-vous que la session est démarrée si elle ne l'est pas déjà
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
