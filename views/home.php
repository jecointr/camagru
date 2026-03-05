<?php include __DIR__ . '/layout/header.php'; ?>

<section class="hero">
    <h1>
        <?php if (isset($_SESSION['username'])): ?>
            Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!
        <?php else: ?>
            Welcome to Camagru 📷
        <?php endif; ?>
    </h1>
    <p>
        <?php if (isset($_SESSION['username'])): ?>
            Ready to create something new? Head to the studio or browse the community gallery.
        <?php else: ?>
            The ultimate app to edit your photos with fun filters and share them with the community!
        <?php endif; ?>
    </p>

    <div class="hero-buttons">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/editor" class="btn">Open Studio 📸</a>
            <a href="/gallery" class="btn btn-outline">Browse Gallery</a>
        <?php else: ?>
            <a href="/register" class="btn">Create an account</a>
            <a href="/login" class="btn btn-outline">Log in</a>
        <?php endif; ?>
    </div>
</section>

<section class="features">
    <div class="feature-card">
        <h3>📷 Webcam</h3>
        <p>Take photos directly from your browser using your webcam.</p>
    </div>
    <div class="feature-card">
        <h3>✨ Compositing</h3>
        <p>Add fun sticker overlays on top of your images.</p>
    </div>
    <div class="feature-card">
        <h3>❤️ Sharing</h3>
        <p>Publish your creations and interact with the community.</p>
    </div>
</section>

<?php include __DIR__ . '/layout/footer.php'; ?>