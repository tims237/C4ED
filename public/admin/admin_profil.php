<?php
session_start();
require_once '../../config/config.php';
require_once '../../includes/helpers.php';
require_once '../../includes/middleware.php';

adminOnly();

$admin_id = $_SESSION['user_id'];
$errors = [];
$success = '';

$stmt = $pdo->prepare("SELECT nom, prenom, email, ville FROM users WHERE id = :id AND role = 'admin'");
$stmt->execute(['id' => $admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    exit("Admin introuvable.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($nom) || empty($prenom) || empty($ville) || empty($email)) {
        $errors[] = "Tous les champs sauf le mot de passe sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }

    // Vérifie si l'email est déjà utilisé par un autre utilisateur
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
    $stmt->execute(['email' => $email, 'id' => $admin_id]);
    if ($stmt->fetch()) {
        $errors[] = "Cet email est déjà utilisé par un autre utilisateur.";
    }

    // Si mot de passe fourni, vérifie la confirmation
    $updatePassword = false;
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
        } elseif ($password !== $confirm_password) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        } else {
            $updatePassword = true;
        }
    }

    if (empty($errors)) {
        if ($updatePassword) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET nom = :nom, prenom = :prenom, ville = :ville, email = :email, password = :password WHERE id = :id AND role = 'admin'");
            $stmt->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'ville' => $ville,
                'email' => $email,
                'password' => $hashedPassword,
                'id' => $admin_id
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nom = :nom, prenom = :prenom, ville = :ville, email = :email WHERE id = :id AND role = 'admin'");
            $stmt->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'ville' => $ville,
                'email' => $email,
                'id' => $admin_id
            ]);
        }
        $success = "Profil mis à jour avec succès.";
        // Rafraîchir les infos affichées
        $admin['nom'] = $nom;
        $admin['prenom'] = $prenom;
        $admin['ville'] = $ville;
        $admin['email'] = $email;
    }
}

include '../../templates/header.php';
?>

<h2>Mon profil administrateur</h2>

<?php if ($success): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <ul style="color:red">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post">
    <label>Nom :</label>
    <input type="text" name="nom" value="<?= htmlspecialchars($admin['nom']) ?>" required><br>

    <label>Prénom :</label>
    <input type="text" name="prenom" value="<?= htmlspecialchars($admin['prenom']) ?>" required><br>

    <label>Ville :</label>
    <input type="text" name="ville" value="<?= htmlspecialchars($admin['ville']) ?>" required><br>

    <label>Email :</label>
    <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required><br>

    <label>Nouveau mot de passe :</label>
    <input type="password" name="password" placeholder="Laisser vide pour ne pas changer"><br>

    <label>Confirmer le mot de passe :</label>
    <input type="password" name="confirm_password" placeholder="Laisser vide pour ne pas changer"><br>

    <button type="submit">Mettre à jour</button>
</form>

<p><a href="admin_panel.php">⬅ Retour au panneau d'administration</a></p>

<?php include '../../templates/footer.php'; ?>