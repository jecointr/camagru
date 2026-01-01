<?php 
if (!isset($img)) return; 

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$publicLink = $protocol . "://" . $host . "/uploads/" . htmlspecialchars($img['filename']);
$encodedUrl = urlencode($publicLink);
$encodedText = urlencode("Regarde ce montage sur Camagru ! 📸");
?>

<div class="gallery-card" data-id="<?= $img['id'] ?>">
     
    <div style="position: relative;">
        <img src="/uploads/<?= htmlspecialchars($img['filename']) ?>" alt="Montage">
        
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $img['user_id']): ?>
            <form action="/delete-image" method="POST" class="delete-form" style="position: absolute; top: 10px; right: 10px;">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                <button type="submit" class="btn-icon delete" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <div class="gallery-info">
        <p style="color: var(--text-muted); font-size: 0.9em;">Par <strong><?= htmlspecialchars($img['username']) ?></strong></p>
        
        <div class="gallery-actions">
             <span class="like-count">
                <svg class="icon-heart" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="var(--accent)" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                <?= $img['likes'] ?>
             </span>

             <?php if (isset($_SESSION['user_id'])): ?>
                <form action="/like" method="POST" class="like-form" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                    <button type="submit" class="btn-icon like" title="J'aime">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path></svg>
                    </button>
                </form>
             <?php endif; ?>

             <div class="share-buttons">
                <a href="https://twitter.com/intent/tweet?text=<?= $encodedText ?>&url=<?= $encodedUrl ?>" 
                   target="_blank" class="btn-share tw" title="Partager sur Twitter">
                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                </a>
                
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedUrl ?>" 
                   target="_blank" class="btn-share fb" title="Partager sur Facebook">
                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                </a>

                <a href="https://api.whatsapp.com/send?text=<?= $encodedText ?>%20<?= $encodedUrl ?>" 
                   target="_blank" class="btn-share wa" title="Partager sur WhatsApp">
                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                </a>
             </div>
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
                    <input type="text" name="comment" required placeholder="Ajouter un commentaire...">
                    <button type="submit" title="Envoyer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>