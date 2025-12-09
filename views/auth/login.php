<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="auth-wrapper">
    <h2>Connexion</h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'registered'): ?>
        <div class="alert alert-success">Inscription réussie ! Vérifiez vos emails.</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'verified'): ?>
        <div class="alert alert-success">Compte vérifié ! Vous pouvez vous connecter.</div>
    <?php endif; ?>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="/login">
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-blue" style="width: 100%;">Se connecter</button>
    </form>
    
    <div style="margin-top: 20px; text-align: center; font-size: 0.9rem;">
        <p><a href="/forgot-password">Mot de passe oublié ?</a></p>
        <p style="margin-top: 10px;">Pas encore de compte ? <a href="/register" style="color: #2980b9; font-weight: bold;">S'inscrire</a></p>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>