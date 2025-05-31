<?php
session_start();
require_once '../../config/config.php';
require_once '../../includes/helpers.php';
// require_once '../../includes/middleware.php';

// adminOnly(); // Vérifie que seul un admin peut accéder à cette page

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = 'client'; // Forcé à client

    if (empty($nom) || empty($prenom) || empty($ville) || empty($email) || empty($password)) {
        $errors[] = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    } else {
        // Vérifie si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = "Cet email est déjà utilisé.";
        }
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, ville, email, password, role) VALUES (:nom, :prenom, :ville, :email, :mdp, :role)");
        $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'ville' => $ville,
            'email' => $email,
            'mdp' => $hashedPassword,
            'role' => $role
        ]);
        $success = "Utilisateur créé avec succès.";
    }
}
?>

<?php include '../../templates/header.php'; ?>

<h2>Ajouter un utilisateur</h2>

<?php if (!empty($errors)): ?>
    <ul style="color: red;">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color: green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post">
    <label>Nom :</label>
    <input type="text" name="nom" required><br>

    <label>Prénom :</label>
    <input type="text" name="prenom" required><br>

    <label>Ville :</label>
    <input type="text" name="ville" required><br>

    <label>Email :</label>
    <input type="email" name="email" required><br>

    <label>Mot de passe :</label>
    <input type="password" name="password" required><br>

    <!-- Le rôle n'est plus sélectionnable -->
    <input type="hidden" name="role" value="client">

    <button type="submit">Créer l'utilisateur</button>
</form>

<p><a href="manage_users.php">← Retour à la gestion des utilisateurs</a></p>

<?php include '../../templates/footer.php'; ?>
