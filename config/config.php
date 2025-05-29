<?php

// Informations de connexion à la base de données
$host = 'localhost';
$dbname = 'c4ed';
$username = 'root';
$password = '';

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Affiche les erreurs SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Récupère les résultats sous forme de tableau associatif par défaut
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Désactive l'émulation des requêtes préparées pour éviter certaines injections SQL
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Ne jamais afficher le détail de l'erreur en production
    die("Erreur de connexion à la base de données.");
}
?>