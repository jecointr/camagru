<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Camagru</title>
    <link rel="stylesheet" href="/css/style.css"> 
</head>
<body>
    <div class="container">
        <h2>Inscription</h2>
        <?php if (isset($error) && $error): ?>
            <p style="color: red;"><?= $error ?></p>
        <?php endif; ?>
        
        <form method="POST" action="/register">
            <label>Nom d'utilisateur :</label>
            <input type="text" name="username" required>
            
            <label>Email :</label>
            <input type="email" name="email" required>
            
            <label>Mot de passe :</label>
            <input type="password" name="password" required>
            <small>Min 8 chars, 1 Majuscule, 1 Chiffre</small>
            
            <button type="submit">S'inscrire</button>
        </form>
        <p>Déjà un compte ? <a href="/login">Se connecter</a></p>
    </div>
</body>
</html>
