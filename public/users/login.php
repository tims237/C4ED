<?php
    session_start();
    // on connecte la base de données
    require_once '../../config/config.php';
    // on inclut le fichier d'authentification
    require_once '../../includes/authentification.php';
    require_once '../../includes/helpers.php';
    require_once '../../includes/middleware.php';

    // Redirige si déjà connecté
    if (is_logged_in()) {
        redirect($_SESSION['user_role'] === 'admin' ? 'admin_panel.php' : 'dashboard.php');
    }

    // Génère le token CSRF si besoin
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $errors = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // on récupère les données du formulaire
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $csrf_token = $_POST['csrf_token'] ?? '';

        // on vérifie le token CSRF
        if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
            $errors[] = "Token CSRF invalide";
        }
        // on vérifie si tous les champs sont remplis
        if (empty($email) || empty($password)) {
            $errors[] = 'Veuillez remplir tous les champs';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide";
        }
        // on recherche le mail de l'utilisateur dans la base de données
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // si l'utilisateur existe et que le mot de passe est correct
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_ville'] = $user['ville'];
                // on redirige selon le role 
                if ($user['role'] === 'admin') {
                    redirect("admin_panel.php");
                } else {
                    redirect("dashboard.php");
                }
            } else {
                $errors[] = "Identifiants incorrects";
            }
        }
    }

    // Affichage des erreurs si besoin
    display_errors($errors);
?>
<?php
include '../../includes/tete.php';
include '../../includes/navbar.php';
?>
<!-- Formulaire de connexion -->
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <!-- Logo avec arrière-plan -->
            <div class="d-flex justify-content-center mb-3">
                <div style="background: #e9f7ef; border-radius: 50%; padding: 20px;">
                    <img src="/C4ED/assets/C4ED_LOGO.jpg" alt="Logo C4ED" style="height: 70px; width: auto;">
                </div>
            </div>
            <div class="card form-bg border border-3 border-success shadow-lg">
                <div class="card-body">
                    <h2 class="card-title text-center fw-bold">
                        <i class="fas fa-user me-2"></i>Connexion
                    </h2>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email :</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe :</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Connexion
                        </button>
                    </form>
                </div>
                <div class="text-center mt-3">
                    <button class="btn btn-link" onclick="window.location.href='forget_password.php'">Mot de passe oublié ?</button>
                </div>
                <div class="text-center mt-3">
                    <a class="icon-link icon-link-hover" style="--bs-icon-link-transform: translate3d(0, -.125rem, 0);" href="register.php">
                        <i class="fa-solid fa-list-check"></i>
                        S'inscrire
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Affichage des erreurs stylé -->
<?php if (!empty($errors)): ?>
    <div class="d-flex justify-content-center">
        <div class="alert alert-danger alert-animate w-100 text-center shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
<?php
include '../../templates/footer.php';
?>
<style>
.alert-animate {
    animation: fadeInDown 0.7s;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-30px);}
    to { opacity: 1; transform: translateY(0);}
}
.alert .fa-exclamation-triangle {
    margin-right: 8px;
    color: #dc3545;
    font-size: 1.2em;
    vertical-align: middle;
}
</style>