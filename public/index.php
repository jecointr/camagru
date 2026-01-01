<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

define('ROOT', dirname(__DIR__));
define('CONTROLLERS', ROOT . '/controllers');
define('VIEWS', ROOT . '/views');

require_once ROOT . '/config/database.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$path = str_replace($scriptDir, '', $uri);
$path = '/' . ltrim($path, '/');

switch ($path) {
    case '/setup':
        require ROOT . '/config/setup.php';
        break;

    case '/':
    case '/home':
        require VIEWS . '/home.php';
        break;

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
        (new AuthController())->forgot();
        break;
    case '/reset':
        require CONTROLLERS . '/AuthController.php';
        (new AuthController())->reset();
        break;
        
    case '/profile':
        require CONTROLLERS . '/AuthController.php';
        (new AuthController())->profile();
        break;

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

    case '/editor':
        require CONTROLLERS . '/EditorController.php';
        (new EditorController())->index();
        break;
    case '/save-image':
        require CONTROLLERS . '/EditorController.php';
        (new EditorController())->save();
        break;

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