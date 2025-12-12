<?php
// On s'assure que $img est disponible
if (!isset($img)) return;

// Logique pour les URLs de partage (isolée ici pour être réutilisable)
$host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$sharePageUrl = urlencode($host . "/gallery");
$shareImageUrl = urlencode($host . "/uploads/" . $img['filename']);
$shareText = urlencode("Regardez mon montage sur Camagru !");
?>

<div class="gallery-card">
    <div style="position: relative;">
        <img src="/uploads/<?= htmlspecialchars($img['filename']) ?>" alt="Montage">
        
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $img['user_id']): ?>
            <form action="/delete-image" method="POST" class="delete-form" style="position: absolute; top: 10px; right: 10px; background: none; padding: 0; box-shadow: none;">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                <button type="submit" style="background: red; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">🗑️</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="gallery-info">
        <p style="color: #777; font-size: 0.9em;">Par <strong><?= htmlspecialchars($img['username']) ?></strong></p>
        
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
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
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
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                    <input type="text" name="comment" placeholder="Un commentaire..." required style="flex: 1; padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
                    <button type="submit" style="background: #2980b9; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">OK</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
