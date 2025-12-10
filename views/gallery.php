<?php include __DIR__ . '/layout/header.php'; ?>

<div class="container">
    <h1 style="text-align: center; margin-bottom: 30px;">Galerie Publique</h1>

    <div class="gallery-grid">
        <?php foreach ($images as $img): ?>
            <div class="gallery-card">
                <div style="position: relative;">
                    <img src="/uploads/<?= htmlspecialchars($img['image_path']) ?>" alt="Montage">
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $img['user_id']): ?>
                        <form action="/delete-image" method="POST" onsubmit="return confirm('Supprimer cette image ?');" style="position: absolute; top: 10px; right: 10px; background: none; padding: 0; box-shadow: none;">
                            <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                            <button type="submit" style="background: red; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">🗑️</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="gallery-info">
                    <p style="color: #777; font-size: 0.9em;">Par <strong><?= htmlspecialchars($img['username']) ?></strong> le <?= date('d/m/Y', strtotime($img['created_at'])) ?></p>
                    
                    <div class="gallery-actions" style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px;">
                        <span>❤️ <?= $img['likes'] ?> J'aime</span>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form action="/like" method="POST" style="display:inline; padding: 0; background: none; box-shadow: none;">
                                <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                                <button type="submit" class="btn-like">👍</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="comments-section" style="margin-top: 15px;">
                        <div style="max-height: 100px; overflow-y: auto; background: #f9f9f9; padding: 5px; border-radius: 4px; font-size: 0.85em;">
                            <?php if (empty($img['comments'])): ?>
                                <p style="color:#aaa;">Aucun commentaire.</p>
                            <?php else: ?>
                                <?php foreach ($img['comments'] as $cmt): ?>
                                    <p style="margin-bottom: 5px;">
                                        <strong><?= htmlspecialchars($cmt['username']) ?>:</strong> 
                                        <?= htmlspecialchars($cmt['comment']) ?>
                                    </p>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form action="/comment" method="POST" style="margin-top: 10px; display: flex; gap: 5px; padding: 0; background: none; box-shadow: none;">
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

    <div class="pagination" style="text-align: center; margin-top: 40px;">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="btn btn-outline">« Précédent</a>
        <?php endif; ?>
        
        <span style="margin: 0 15px; font-weight: bold;">Page <?= $page ?> sur <?= $totalPages ?></span>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>" class="btn btn-outline">Suivant »</a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>