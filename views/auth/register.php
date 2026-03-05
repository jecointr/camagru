<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="auth-wrapper">
    <h2>Sign up</h2>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="/register">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required placeholder="e.g. JohnDoe">
        </div>

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" required placeholder="e.g. john@mail.com">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <small>Min. 8 characters, 1 uppercase letter, 1 number.</small>
        </div>

        <button type="submit" class="btn btn-blue" style="width: 100%;">Sign up</button>
    </form>

    <p style="margin-top: 20px; text-align: center;">
        Already have an account? <a href="/login" style="color: #2980b9; font-weight: bold;">Log in</a>
    </p>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>