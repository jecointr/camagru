<?php
session_start();

// 1. Configuration (Base de données)
require_once '../config/database.php';

// 2. Inclusion des définitions de classes (Tes Contrôleurs)
require_once '../controllers/AuthController.php';
require_once '../controllers/GalleryController.php'; // <--- NOUVEAU

// 3. Instanciation des objets
$auth = new AuthController();
$gallery = new GalleryController(); // <--- NOUVEAU

// 4. Analyse de l'URL
$url = $_SERVER['REQUEST_URI'];
$path = parse_url($url, PHP_URL_PATH);

// 5. Routeur (Le Switch)
switch ($path) {
    
    // --- ACCUEIL ---
    case '/':
        require '../views/home.php';
        break;

    // --- DOMAINE : AUTHENTIFICATION ---
    case '/login':
        $auth->login();
        break;
    case '/register':
        $auth->register();
        break;
    case '/logout':
        $auth->logout();
        break;
    case '/verify':
        $auth->verify();
        break;

    // --- DOMAINE : GALERIE (Mise à jour ici) ---
    
    case '/gallery':
        // AVANT : require '../controllers/gallery.php';
        // MAINTENANT : On appelle la méthode qui affiche la liste
        $gallery->index(); 
        break;

    case '/like':
        // Nouvelle route pour liker une image
        $gallery->like(); 
        break;

    case '/comment':
        // Nouvelle route pour commenter
        $gallery->comment(); 
        break;

    // --- DOMAINE : ÉDITEUR (Pas encore converti en objet) ---
    case '/editor':
        require '../controllers/editor.php';
        break;

    // --- ERREUR ---
    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
?>