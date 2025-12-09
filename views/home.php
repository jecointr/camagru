<?php 
// Inclusion de l'entête global (Navigation, CSS, Ouverture du Body)
include __DIR__ . '/layout/header.php'; 
?>

<div class="hero-section">
    <div class="hero-content">
        <h1>Bienvenue sur Camagru 📷</h1>
        <p>L'application ultime pour éditer vos photos avec des filtres funs et les partager avec la communauté !</p>
        
        <div class="cta-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/editor" class="btn btn-primary">📸 Aller au Studio</a>
                <a href="/gallery" class="btn btn-secondary">Voir la Galerie</a>
            <?php else: ?>
                <a href="/register" class="btn btn-primary">Créer un compte</a>
                <a href="/login" class="btn btn-secondary">Se connecter</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="features-grid">
    <div class="feature-card">
        <h3>📸 Webcam & Upload</h3>
        <p>Utilisez votre webcam ou importez vos images pour commencer.</p>
    </div>
    <div class="feature-card">
        <h3>✨ Filtres Superposés</h3>
        <p>Ajoutez des stickers et des cadres funs sur vos photos.</p>
    </div>
    <div class="feature-card">
        <h3>❤️ Galerie Sociale</h3>
        <p>Partagez vos créations, likez et commentez celles des autres.</p>
    </div>
</div>

<?php 
// Inclusion du pied de page global (Fermeture du Body/HTML)
include __DIR__ . '/layout/footer.php'; 
?>