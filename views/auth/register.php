<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="auth-wrapper">
    <h2>Inscription</h2>
    
    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="/register">
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" required placeholder="Ex: JeanDupont">
        </div>
        
        <div class="form-group">
            <label for="email">Adresse Email</label>
            <input type="email" id="email" name="email" required placeholder="Ex: jean@mail.com">
        </div>
        
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
            <small>Min. 8 caractères, 1 majuscule, 1 chiffre.</small>
        </div>
        
        <button type="submit" class="btn btn-blue" style="width: 100%;">S'inscrire</button>
    </form>
    
    <p style="margin-top: 20px; text-align: center;">
        Déjà un compte ? <a href="/login" style="color: #2980b9; font-weight: bold;">Se connecter</a>
    </p>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>