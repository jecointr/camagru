<?php if (!isset($img)) return; ?>

<div class="gallery-card" data-id="<?= $img['id'] ?>">
     
    <div style="position: relative;">
        <img src="/uploads/<?= htmlspecialchars($img['filename']) ?>" alt="Montage">
        
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $img['user_id']): ?>
            <form action="/delete-image" method="POST" class="delete-form" style="position: absolute; top: 10px; right: 10px;">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                <button type="submit" style="background: red; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">🗑️</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="gallery-info">
        <p style="color: #777; font-size: 0.9em;">Par <strong><?= htmlspecialchars($img['username']) ?></strong></p>
        
        <div class="gallery-actions">
             <span class="like-count">❤️ <?= $img['likes'] ?></span>
             <?php if (isset($_SESSION['user_id'])): ?>
                <form action="/like" method="POST" class="like-form" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                    <button type="submit">👍</button>
                </form>
             <?php endif; ?>
        </div>
        
        <div class="comments-section">
            <div class="comment-list" style="max-height: 100px; overflow-y: auto;">
                <?php foreach ($img['comments'] as $cmt): ?>
                    <p><strong><?= htmlspecialchars($cmt['username']) ?>:</strong> <?= htmlspecialchars($cmt['comment']) ?></p>
                <?php endforeach; ?>
            </div>
             <?php if (isset($_SESSION['user_id'])): ?>
                <form action="/comment" method="POST" class="comment-form">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                    <input type="text" name="comment" required>
                    <button type="submit">OK</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>