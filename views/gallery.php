<?php include __DIR__ . '/layout/header.php'; ?>

<script>
    const CURRENT_USER_ID = <?= isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0 ?>;
</script>

<div class="container">
    <h1 style="text-align: center; margin-bottom: 30px;">Galerie Publique</h1>

    <div class="gallery-grid" id="gallery-container">
        <?php foreach ($images as $img): ?>
            <div class="gallery-card">
                <div style="position: relative;">
                    <img src="/uploads/<?= htmlspecialchars($img['image_path']) ?>" alt="Montage">
                    
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $img['user_id']): ?>
                        <form action="/delete-image" method="POST" class="delete-form" style="position: absolute; top: 10px; right: 10px; background: none; padding: 0; box-shadow: none;">
                            <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                            <button type="submit" style="background: red; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">🗑️</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="gallery-info">
                    <p style="color: #777; font-size: 0.9em;">Par <strong><?= htmlspecialchars($img['username']) ?></strong></p>
                    
                    <?php
                        // On prépare les URLs pour le partage
                        $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                        $sharePageUrl = urlencode($host . "/gallery");
                        $shareImageUrl = urlencode($host . "/uploads/" . $img['image_path']);
                        $shareText = urlencode("Regardez mon montage sur Camagru !");
                    ?>
                    <div class="social-share" style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px; text-align: center; font-size: 0.9em;">
                        <span style="color: #555; margin-right: 5px;">Partager :</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $sharePageUrl ?>" target="_blank" style="margin-right: 8px; text-decoration: none;">📘 FB</a>
                        <a href="https://twitter.com/intent/tweet?url=<?= $sharePageUrl ?>&text=<?= $shareText ?>" target="_blank" style="margin-right: 8px; text-decoration: none;">🐦 X</a>
                        <a href="https://pinterest.com/pin/create/button/?url=<?= $sharePageUrl ?>&media=<?= $shareImageUrl ?>&description=<?= $shareText ?>" target="_blank" style="text-decoration: none;">📌 Pin</a>
                    </div>

                    <div class="gallery-actions" style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px;">
                        <span class="like-count" style="margin-right: 15px;">❤️ <?= $img['likes'] ?> J'aime</span>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form action="/like" method="POST" class="like-form" style="display:inline; padding: 0; background: none; box-shadow: none;">
                                <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                                <button type="submit" class="btn-like" style="font-size: 1.2rem;">👍</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    
                    <div class="comments-section" style="margin-top: 15px;">
                        <div class="comment-list" style="max-height: 100px; overflow-y: auto; background: #f9f9f9; padding: 5px; border-radius: 4px; font-size: 0.85em;">
                            <?php foreach ($img['comments'] as $cmt): ?>
                                <p style="margin-bottom: 5px;">
                                    <strong><?= htmlspecialchars($cmt['username']) ?>:</strong> 
                                    <?= htmlspecialchars($cmt['comment']) ?>
                                </p>
                            <?php endforeach; ?>
                        </div>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form action="/comment" method="POST" class="comment-form" style="margin-top: 10px; display: flex; gap: 5px; padding: 0; background: none; box-shadow: none;">
                                <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                                <input type="text" name="comment" placeholder="Un commentaire..." required style="flex: 1; padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
                                <button type="submit" style="background: #2980b9; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">OK</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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