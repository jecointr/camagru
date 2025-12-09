<?php
session_start();

// 1. Config BDD
require_once '../config/database.php';

// 2. IMPORTANT : On inclut la définition de la classe AuthController
require_once '../controllers/AuthController.php';

// 3. On instancie l'objet (on prépare le contrôleur)
$auth = new AuthController();

// 4. Récupération de l'URL
$url = $_SERVER['REQUEST_URI'];
$path = parse_url($url, PHP_URL_PATH);

// 5. Routeur (Switch mis à jour)
switch ($path) {
    case '/':
        // Exemple : Redirection si pas connecté (optionnel selon ton besoin)
        /* if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        */
        require '../views/home.php'; 
        break;

    // --- ROUTES D'AUTHENTIFICATION (Via l'objet $auth) ---
    
    case '/login':
        // On n'appelle plus 'require login.php', mais la méthode login() de l'objet
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

    // --- AUTRES ROUTES (Restent classiques pour l'instant) ---
    
    case '/gallery':
        // Si tu n'as pas encore de GalleryController, tu gardes l'ancien système ici
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