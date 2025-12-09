<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="auth-wrapper">
    <h2 style="text-align: center; margin-bottom: 20px;">Connexion</h2>
    
    <?php if (isset($error) && $error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/login">
        <label>Nom d'utilisateur</label>
        <input type="text" name="username" required>
        
        <label>Mot de passe</label>
        <input type="password" name="password" required>
        
        <button type="submit">Se connecter</button>
        
        <p style="margin-top: 15px; text-align: center;">
            <a href="/forgot-password">Mot de passe oublié ?</a>
        </p>
    </form>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
