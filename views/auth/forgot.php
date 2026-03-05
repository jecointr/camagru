<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="auth-wrapper">
    <h2>Forgot password</h2>
    <p style="text-align: center; color: #666; font-size: 0.9em; margin-bottom: 20px;">
        Enter your email address to receive a password reset link.
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
            <label for="email">Your email address</label>
            <input type="email" id="email" name="email" required placeholder="e.g. john@mail.com">
        </div>

        <button type="submit" class="btn btn-blue" style="width: 100%;">Send reset link</button>
    </form>

    <div style="margin-top: 20px; text-align: center;">
        <a href="/login" style="font-size: 0.9rem;">Back to login</a>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
