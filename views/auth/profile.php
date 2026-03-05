<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="auth-wrapper">
    <h2>My Profile</h2>

    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="/profile" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <?php
            $avatarSrc = (!empty($user['profile_pic']) && $user['profile_pic'] !== 'default_avatar.png')
                ? '/uploads/' . $user['profile_pic']
                : '/img/default_avatar.png';
        ?>
        <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 25px;">
            <img id="avatar-preview" src="<?= htmlspecialchars($avatarSrc) ?>"
                 alt="Avatar"
                 style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--border); box-shadow: var(--shadow-sm); margin-bottom: 15px;">
            <input type="file" id="avatar-input" name="avatar" accept="image/*" style="display: none;">
            <label for="avatar-input" style="cursor: pointer; color: #3498db; font-weight: bold; font-size: 0.95rem; text-decoration: underline;">
                Change photo
            </label>
        </div>

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label>New password <span style="color: var(--text-muted); font-weight: normal;">(leave blank to keep current)</span></label>
            <input type="password" name="password" placeholder="••••••••">
        </div>

        <div class="notif-row">
            <div class="notif-row-text">
                <span class="notif-row-label">Email notifications</span>
                <small>Receive an email when someone comments on your images.</small>
            </div>
            <label class="toggle-switch">
                <input type="checkbox" name="notification_active" value="1"
                    <?= !empty($user['notification_active']) ? 'checked' : '' ?>>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <button type="submit" class="btn btn-blue" style="width: 100%; margin-top: 24px;">Save changes</button>
    </form>
</div>

<script>
document.getElementById('avatar-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('avatar-preview').src = ev.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>