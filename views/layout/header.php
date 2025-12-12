<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camagru</title>
    <link rel="stylesheet" href="/css/style.css">
    
    <script>
        const CSRF_TOKEN = "<?= $_SESSION['csrf_token'] ?? '' ?>";
    </script>
    
    <script src="/js/utils.js" defer></script>
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
                    <li style="margin-left: 15px; display: flex; align-items: center; gap: 10px;">
                        <a href="/profile" style="display: flex; align-items: center; text-decoration: none;">
                             <img src="<?= isset($_SESSION['profile_pic']) && $_SESSION['profile_pic'] ? '/uploads/'.$_SESSION['profile_pic'] : '/img/default_avatar.png' ?>" 
                                 alt="Avatar" 
                                 style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; border: 2px solid white;">
                            
                            <span style="font-weight: bold; margin-left: 5px; color: #3498db;">
                                <?= htmlspecialchars($_SESSION['username']) ?>
                            </span>
                        </a>
                    </li>
                    <li><a href="/editor" class="btn">Créer</a></li>
                    <li><a href="/logout">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="/login">Connexion</a></li>
                    <li><a href="/register" class="btn">Inscription</a></li>
                <?php endif; ?>

                <li>
                    <a href="#" id="theme-toggle" style="font-size: 1.2rem; padding: 5px;">🌙</a>
                </li>
            </ul>
        </nav>
    </header>

    <main class="container">