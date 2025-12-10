<?php include __DIR__ . '/layout/header.php'; ?>

<section class="hero">
    <h1>
        <?php if (isset($_SESSION['username'])): ?>
            Bienvenue, <?= htmlspecialchars($_SESSION['username']) ?> ! 🎉
        <?php else: ?>
            Bienvenue sur Camagru 📷
        <?php endif; ?>
    </h1>
    <p>
        <?php if (isset($_SESSION['username'])): ?>
            Prêt à réaliser de nouveaux montages ? Allez au studio ou regardez les créations de la communauté.
        <?php else: ?>
            L'application ultime pour éditer vos photos avec des filtres funs et les partager avec la communauté !
        <?php endif; ?>
    </p>
    
    <div class="hero-buttons">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/editor" class="btn">Aller au Studio 📸</a>
            <a href="/gallery" class="btn btn-outline">Voir la Galerie</a>
        <?php else: ?>
            <a href="/register" class="btn">Créer un compte</a>
            <a href="/login" class="btn btn-outline">Se connecter</a>
        <?php endif; ?>
    </div>
</section>

<section class="features">
    <div class="feature-card">
        <h3>📷 Webcam</h3>
        <p>Prenez des photos directement depuis votre navigateur.</p>
    </div>
    <div class="feature-card">
        <h3>✨ Montages</h3>
        <p>Ajoutez des superpositions funs sur vos images.</p>
    </div>
    <div class="feature-card">
        <h3>❤️ Partage</h3>
        <p>Publiez vos créations et échangez avec la communauté.</p>
    </div>
</section>

<?php include __DIR__ . '/layout/footer.php'; ?>