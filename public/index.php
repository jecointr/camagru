<?php
session_start();

// On génère un token unique par session s'il n'existe pas encore
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Définition des chemins absolus
define('ROOT', dirname(__DIR__));
define('CONTROLLERS', ROOT . '/controllers');
define('VIEWS', ROOT . '/views');

// Config BDD
require_once ROOT . '/config/database.php';

// Nettoyage de l'URL pour le routing
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Si l'app est dans un sous-dossier, on le retire
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
        
    // CORRECTION ICI : Noms des méthodes et route
    case '/forgot-password':
        require CONTROLLERS . '/AuthController.php';
        (new AuthController())->forgot(); // Méthode renamed
        break;
    case '/reset': // Route renamed pour correspondre au lien du mail
        require CONTROLLERS . '/AuthController.php';
        (new AuthController())->reset(); // Méthode renamed
        break;
        
    case '/profile':
        require CONTROLLERS . '/AuthController.php';
        (new AuthController())->profile();
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
        if (file_exists(VIEWS . '/layout/header.php')) include VIEWS . '/layout/header.php';
        echo "<div class='container' style='text-align:center; padding:50px;'>";
        echo "<h1>404</h1><p>Oups ! Cette page n'existe pas.</p>";
        echo "<a href='/' class='btn'>Retour à l'accueil</a>";
        echo "</div>";
        if (file_exists(VIEWS . '/layout/footer.php')) include VIEWS . '/layout/footer.php';
        break;
}
?>