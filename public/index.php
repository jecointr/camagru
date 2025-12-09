<?php
session_start();

// Autoloader basique ou require manuels
require_once '../config/database.php';

// Routeur très simpliste
$url = $_SERVER['REQUEST_URI'];

// Nettoyage de l'URL pour éviter les failles
$path = parse_url($url, PHP_URL_PATH);

// Logique de routing (Switch Case simple pour commencer)
switch ($path) {
    case '/':
        require '../views/home.php';
        break;
    case '/login':
        require '../controllers/login.php';
        break;
    case '/register':
        require '../controllers/register.php';
        break;
    case '/gallery':
        require '../controllers/gallery.php';
        break;
    case '/editor':
        // Vérification de sécurité (Auth) à ajouter ici
        require '../controllers/editor.php';
        break;
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
?>