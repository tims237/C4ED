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

<h2>Inscription</h2>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <label for="nom">Nom :</label>
    <input type="text" name="nom" id="nom" required><br>
    <label for="prenom">Prénom :</label>
    <input type="text" name="prenom" id="prenom" required><br>
    <label for="ville">Ville :</label>
    <input type="text" name="ville" id="ville" required><br>
    <label for="email">Email :</label>
    <input type="email" name="email" id="email" required><br>
    <label for="password">Mot de passe :</label>
    <input type="password" name="password" id="password" required><br>
    <label for="confirm_password">Confirmer le mot de passe :</label>
    <input type="password" name="confirm_password" id="confirm_password" required><br>
    <button type="submit">S'inscrire</button>
</form>
<?php if (!empty($errors)): ?>
    <ul style="color:red">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<p><a href="login.php">Déjà inscrit ? Se connecter</a></p>

<?php
$content = ob_get_clean();
$title = "Inscription";
include '../../templates/layout.php';
?>