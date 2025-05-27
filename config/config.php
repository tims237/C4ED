<?php

//  info de connexion de la base de donnees 
$host = 'localhost';
$dbname = 'c4ed';
$username = 'root';
$password = '';


// connexion a la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//permet de voir les erreurs de requetes
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>