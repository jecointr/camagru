<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Camagru</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="logo">
            <a href="/">📷 Camagru</a>
        </div>
        <nav>
            <a href="/gallery">Galerie Publique</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/editor" class="btn-highlight">Montage</a>
                <a href="/profile">Mon Profil</a>
                <a href="/logout">Déconnexion</a>
            <?php else: ?>
                <a href="/login">Connexion</a>
                <a href="/register" class="btn-highlight">Inscription</a>
            <?php endif; ?>
        </nav>
    </header>
</body>
</html>
