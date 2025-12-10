<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="auth-wrapper">
    <h2>Mon Profil</h2>

    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="/profile">
        <div class="form-group">
            <label>Nom d'utilisateur</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Nouveau mot de passe (Laisser vide pour ne pas changer)</label>
            <input type="password" name="password" placeholder="••••••••">
            <small>Min 8 chars, 1 Maj, 1 Chiffre</small>
        </div>
        
        <button type="submit" class="btn btn-blue" style="width: 100%;">Mettre à jour</button>
    </form>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>