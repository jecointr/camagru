<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="auth-wrapper">
    <h2>Nouveau mot de passe</h2>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="/reset?token=<?= htmlspecialchars($_GET['token'] ?? '') ?>">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        
        <div class="form-group">
            <label for="password">Nouveau mot de passe</label>
            <input type="password" id="password" name="password" required>
            <small>Min. 8 caractères, 1 majuscule, 1 chiffre.</small>
        </div>

        <div class="form-group">
            <label for="password_confirm">Confirmer le mot de passe</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>
        
        <button type="submit" class="btn btn-blue" style="width: 100%;">Réinitialiser</button>
    </form>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
