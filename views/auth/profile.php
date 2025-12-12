<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="auth-wrapper">
    <h2>Mon Profil</h2>

    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="/profile" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 25px;">
            <?php 
                // Logique Image : Si user a une image, on prend dans /uploads/, sinon on prend l'image par défaut dans /img/
                $avatarSrc = !empty($user['profile_pic']) 
                    ? '/uploads/' . $user['profile_pic'] 
                    : '/img/default_avatar.png'; 
            ?>
            
            <img id="avatar-preview" src="<?= htmlspecialchars($avatarSrc) ?>" 
                 alt="Avatar" 
                 style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #f0f0f0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 15px;">
            
            <input type="file" id="avatar-input" name="avatar" accept="image/*" style="display: none;">
            
            <label for="avatar-input" style="cursor: pointer; color: #3498db; font-weight: bold; font-size: 0.95rem; text-decoration: underline;">
                Changer photo
            </label>
        </div>
        <div class="form-group">
            <label>Nom d'utilisateur</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Nouveau mot de passe <span style="color:#999; font-weight:normal;">(laisser vide si inchangé)</span></label>
            <input type="password" name="password" placeholder="••••••••">
        </div>
        
        <button type="submit" class="btn btn-blue" style="width: 100%; margin-top: 10px;">Enregistrer les modifications</button>
    </form>
</div>

<script>
document.getElementById('avatar-input').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>