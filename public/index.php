<?php
session_start();

// Définition des chemins absolus
define('ROOT', dirname(__DIR__));
define('CONTROLLERS', ROOT . '/controllers');
define('VIEWS', ROOT . '/views');

// Config BDD
require_once ROOT . '/config/database.php';

// Nettoyage de l'URL pour le routing
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Si l'app est dans un sous-dossier, on le retire (optionnel mais prudent)
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$path = str_replace($scriptDir, '', $uri);
$path = '/' . ltrim($path, '/'); // Assure qu'on commence par /

// ROUTEUR
switch ($path) {
    // --- INFRA ---
    case '/setup':
        require ROOT . '/config/setup.php';
        break;

    // --- ACCUEIL ---
    case '/':
    case '/home':
        require VIEWS . '/home.php';
        break;

    // --- AUTHENTIFICATION ---
    case '/login':
        require CONTROLLERS . '/AuthController.php';
        (new AuthController())->login();
        break;
    case '/register':
        require CONTROLLERS . '/AuthController.php';
        (new AuthController())->register();
        break;
    case '/logout':
        require CONTROLLERS . '/AuthController.php';
        (new AuthController())->logout();
        break;
    case '/verify':
        require CONTROLLERS . '/AuthController.php';
        (new AuthController())->verify();
        break;
    case '/forgot-password':
        require CONTROLLERS . '/AuthController.php';
        (new AuthController())->forgotPassword();
        break;
    case '/reset-password':
        require CONTROLLERS . '/AuthController.php';
        (new AuthController())->resetPassword();
        break;

    // --- GALERIE ---
    case '/gallery':
        require CONTROLLERS . '/GalleryController.php';
        (new GalleryController())->index();
        break;
    case '/like':
        require CONTROLLERS . '/GalleryController.php';
        (new GalleryController())->like();
        break;
    case '/comment':
        require CONTROLLERS . '/GalleryController.php';
        (new GalleryController())->comment();
        break;
    case '/delete-image':
        require CONTROLLERS . '/GalleryController.php';
        (new GalleryController())->delete();
        break;

    // --- EDITOR ---
    case '/editor':
        require CONTROLLERS . '/EditorController.php';
        (new EditorController())->index();
        break;
    case '/save-image': // Pour l'AJAX
        require CONTROLLERS . '/EditorController.php';
        (new EditorController())->save();
        break;

    // --- 404 ---
    default:
        http_response_code(404);
        // On essaie d'afficher le header si possible, sinon juste du texte
        if (file_exists(VIEWS . '/layout/header.php')) include VIEWS . '/layout/header.php';
        echo "<div class='container'><h1>404 - Page introuvable</h1></div>";
        if (file_exists(VIEWS . '/layout/footer.php')) include VIEWS . '/layout/footer.php';
        break;
}
?>