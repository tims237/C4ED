<?php
// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirige vers une URL et arrête le script
 * @param string $url
 */
function redirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    }
}

/**
 * Ajoute un message flash à la session
 * @param string $type success|error|info|warning
 * @param string $message
 */
function set_flash($type, $message) {
    $_SESSION['flash'][$type][] = $message;
}

/**
 * Affiche et supprime les messages flash de la session
 */
function display_flash() {
    if (!empty($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $messages) {
            $color = match ($type) {
                'success' => 'green',
                'error' => 'red',
                'info' => 'blue',
                'warning' => 'orange',
                default => 'black',
            };
            foreach ($messages as $msg) {
                echo "<p style='color:$color'>" . e($msg) . "</p>";
            }
        }
        unset($_SESSION['flash']);
    }
}

/**
 * Affiche un tableau d'erreurs sous forme de liste HTML
 * @param array $errors
 */
function display_errors(array $errors) {
    if (!empty($errors)) {
        echo '<ul style="color:red">';
        foreach ($errors as $error) {
            echo '<li>' . e($error) . '</li>';
        }
        echo '</ul>';
    }
}

/**
 * Sécurise une chaîne pour l'affichage HTML (protection XSS)
 * @param string $string
 * @return string
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */