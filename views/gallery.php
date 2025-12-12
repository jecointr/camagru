<?php include __DIR__ . '/layout/header.php'; ?>

<div class="container">
    <h1 style="text-align: center; margin-bottom: 30px;">Galerie Publique</h1>

    <div class="gallery-grid" id="gallery-container">
        <?php foreach ($images as $img): ?>
            <?php include __DIR__ . '/partials/image_card.php'; ?>
        <?php endforeach; ?>
    </div>

    <div id="next-page-data">
        <?php if (isset($totalPages) && $page < $totalPages): ?>
            <span id="next-page" data-page="<?= $page + 1 ?>" style="display:none;"></span>
        <?php endif; ?>
    </div>

    <div id="loading" style="text-align: center; padding: 20px; display: none;">
        <p>Chargement des photos...</p>
    </div>

    <?php if (isset($totalImages) && $totalImages > 0 && $page >= $totalPages): ?>
        <div id="end-message" style="text-align: center; margin-top: 30px; color: #7f8c8d;">Fin de la galerie.</div>
    <?php endif; ?>

    <?php if (isset($totalImages) && $totalImages === 0): ?>
        <p style="text-align: center; color: #7f8c8d; font-size: 1.2em; padding: 50px 0;">
            Aucune photo n'a encore été publiée. Soyez le premier !
        </p>
    <?php endif; ?>

</div>

<script src="/js/gallery.js"></script>
<?php include __DIR__ . '/layout/footer.php'; ?>