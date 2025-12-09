<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camagru</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="logo">
            <a href="/">📷 Camagru</a>
        </div>
        <nav>
            <ul>
                <li><a href="/gallery">Galerie</a></li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="/editor" class="btn">Créer</a></li>
                    <li><a href="/logout">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="/login">Connexion</a></li>
                    <li><a href="/register" class="btn">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main class="container">