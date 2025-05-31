<?php

// Utilisation : 
// $title = "Titre de la page";
// ob_start();
// ... contenu de la page ...
// $content = ob_get_clean();
// include __DIR__ . '/layout.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= isset($title) ? htmlspecialchars($title) : 'C4ED' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN -->
    <script src="https://kit.fontawesome.com/yourkitid.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans">
    <header class="bg-white shadow-md p-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-green-600">C4ED</h1>
        <nav class="space-x-4">
            <a href="/C4ED/public/users/dashboard.php" class="text-blue-600 hover:underline"><i class="fas fa-home"></i> Dashboard</a>
            <a href="/C4ED/public/users/profil.php" class="text-blue-600 hover:underline"><i class="fas fa-user"></i> Profil</a>
            <a href="/C4ED/public/users/historique.php" class="text-blue-600 hover:underline"><i class="fas fa-exchange-alt"></i> Transactions</a>
            <a href="/C4ED/public/users/logout.php" class="text-red-600 hover:underline"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="/C4ED/public/admin/admin_panel.php" class="text-purple-600 hover:underline"><i class="fas fa-tools"></i> Admin</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="p-6">
        <?= $content ?? '' ?>
    </main>
    <footer class="bg-white shadow-inner p-4 mt-8 text-center text-sm text-gray-500">
        &copy; <?= date('Y') ?> C4ED. Tous droits réservés.
        <br>
        <a href="mailto:support@c4ed.local" style="color:#888;text-decoration:underline;">Contact support</a>
    </footer>
    <script>
        // JS simple ici si besoin
        console.log("C4ED interface utilisateur chargée !");
    </script>
</body>
</html>