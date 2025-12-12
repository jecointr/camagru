<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="auth-wrapper">
    <h2>Mot de passe oublié</h2>
    <p style="text-align: center; color: #666; font-size: 0.9em; margin-bottom: 20px;">
        Entrez votre email pour recevoir un lien de réinitialisation.
    </p>

    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="/forgot-password">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        
        <div class="form-group">
            <label for="email">Votre adresse Email</label>
            <input type="email" id="email" name="email" required placeholder="Ex: jean@mail.com">
        </div>
        
        <button type="submit" class="btn btn-blue" style="width: 100%;">Envoyer le lien</button>
    </form>
    
    <div style="margin-top: 20px; text-align: center;">
        <a href="/login" style="font-size: 0.9rem;">Retour à la connexion</a>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
