<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="auth-wrapper">
    <h2>Log in</h2>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'registered'): ?>
        <div class="alert alert-success">Registration successful! Check your email to activate your account.</div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'verified'): ?>
        <div class="alert alert-success">Account verified! You can now log in.</div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'password_reset'): ?>
        <div class="alert alert-success">Password updated successfully! Please log in.</div>
    <?php endif; ?>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="/login">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-blue" style="width: 100%;">Log in</button>
    </form>

    <div style="margin-top: 20px; text-align: center; font-size: 0.9rem;">
        <p><a href="/forgot-password">Forgot your password?</a></p>
        <p style="margin-top: 10px;">No account yet? <a href="/register" style="color: #2980b9; font-weight: bold;">Sign up</a></p>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>