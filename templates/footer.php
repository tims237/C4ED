<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C4ED - Microfinance digitale moderne et sécurisée</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        footer {
            margin-top: auto;
        }
    </style>
</head>
<body>


    <main class="flex-fill">
        <div class="container py-5">
            <h2 class="h4 mb-4">Bienvenue chez C4ED</h2>
            <p class="lead">Nous offrons des solutions de microfinance digitales, modernes et sécurisées.</p>
            <!-- Contenu principal de la page -->
        </div>
    </main>

    <footer class="bg-success text-white py-4 mt-5">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="text-center text-md-start mb-3 mb-md-0">
                <h2 class="h5 mb-1">C4ED S.A.</h2>
                <p class="mb-0 small">Microfinance digitale moderne et sécurisée</p>
            </div>
            <ul class="nav justify-content-center">
                <li class="nav-item"><a href="/C4ED/public/about.php" class="nav-link px-2 text-white">À propos</a></li>
                <li class="nav-item"><a href="/C4ED/public/contact.php" class="nav-link px-2 text-white">Contact</a></li>
                <li class="nav-item"><a href="/C4ED/public/legal.php" class="nav-link px-2 text-white">Mentions légales</a></li>
            </ul>
            <div class="text-center text-md-end small mt-3 mt-md-0">
                &copy; <?= date('Y') ?> C4ED. Tous droits réservés.
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

