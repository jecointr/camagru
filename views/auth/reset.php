<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="auth-wrapper">
    <h2>New password</h2>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="/reset?token=<?= htmlspecialchars($_GET['token'] ?? '') ?>">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

        <div class="form-group">
            <label for="password">New password</label>
            <input type="password" id="password" name="password" required>
            <small>Min. 8 characters, 1 uppercase letter, 1 number.</small>
        </div>

        <div class="form-group">
            <label for="password_confirm">Confirm password</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
        </div>

        <button type="submit" class="btn btn-blue" style="width: 100%;">Reset password</button>
    </form>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
