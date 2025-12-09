<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Galerie - Camagru</title>
    <style>
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; padding: 20px; }
        .card { border: 1px solid #ccc; padding: 10px; border-radius: 5px; background: #fff; }
        .card img { width: 100%; height: auto; }
        .pagination { text-align: center; margin: 20px; }
        .comment-list { max-height: 100px; overflow-y: auto; font-size: 0.9em; border-top: 1px solid #eee; margin-top:10px;}
    </style>
</head>
<body>
    <?php include 'header.php'; // Supposons un header commun ?>

    <div class="container">
        <h1>Galerie Publique</h1>

        <div class="gallery-grid">
            <?php foreach ($images as $img): ?>
                <div class="card">
                    <img src="/uploads/<?= $img['image_path'] ?>" alt="User Image">
                    <p>Par : <strong><?= htmlspecialchars($img['username']) ?></strong></p>
                    
                    <div class="actions">
                        <span><?= $img['likes'] ?> Likes</span>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form action="/like" method="POST" style="display:inline;">
                                <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                                <button type="submit">❤️/💔</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="comments">
                        <div class="comment-list">
                            <?php foreach ($img['comments'] as $cmt): ?>
                                <p><strong><?= htmlspecialchars($cmt['username']) ?>:</strong> <?= htmlspecialchars($cmt['comment']) ?></p>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form action="/comment" method="POST">
                                <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                                <input type="text" name="comment" placeholder="Un commentaire..." required>
                                <button type="submit">Envoyer</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>">« Précédent</a>
            <?php endif; ?>
            
            <span>Page <?= $page ?> sur <?= $totalPages ?></span>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>">Suivant »</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
