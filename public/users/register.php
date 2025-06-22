<?php
session_start();
// on inclut la base de données
require_once '../../config/config.php';
// on valide les donnees du formulaire
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = 'client'; // Par défaut, le rôle est client
    $csrf_token = $_POST['csrf_token'] ?? '';

    // on verifie le token csrf
    if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $errors[] = "Token CSRF invalide";
    }
    // on doit valider les champs
    if (empty($nom) || empty($prenom) || empty($ville)) {
        $errors[] = "Nom, prénom et ville sont obligatoires.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide";
    }
    if (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }
    // on verifie l'unicite de l'email
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = "L'email existe déjà";
        }
    }
    // on hash le mot de passe et on insère l'utilisateur
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (nom, prenom, ville, email, password, role) VALUES (:nom, :prenom, :ville, :email, :password, :role)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':ville' => $ville,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':role' => $role
        ]);
        unset($_SESSION['csrf_token']);
        header("Location: login.php?success=1");
        exit();
    }
}
// on genere le token csrf si besoin
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ----------- Début du buffer de contenu -----------
ob_start();
?>


<?php require_once '../../includes/tete.php'; 
require_once '../../includes/navbar.php'; ?>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="/C4ED/assets/style.css">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="form-bg shadow-lg border border-3 border-success">
                <h2 class="text-center text-primary mb-4 display-4 fw-bold">Inscription</h2>
                <p class="text-center mb-4">Veuillez remplir le formulaire ci-dessous pour vous inscrire.</p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                    <div class="mb-3">
                        <label for="nom" class="form-label fw-bold">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    <div class="mb-3">
                        <label for="prenom" class="form-label fw-bold">Prénom</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" required>
                    </div>
                    <div class="mb-3">
                        <label for="ville" class="form-label fw-bold">Ville</label>
                        <input type="text" class="form-control" id="ville" name="ville" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Adresse Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label fw-bold">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        <button type="submit" class="btn btn-success w-75">S'inscrire</button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <a href="login.php">Déjà inscrit ? Connexion</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include '../../templates/footer.php';
// ----------- Fin du buffer de contenu -----------
ob_end_flush();



