// Fonction utilitaire pour éviter les failles XSS lors de l'affichage JS
function escapeHtml(text) {
    if (!text) return text;
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Fonction pour générer le HTML d'une carte via JS (Scroll Infini)
function createGalleryCard(img) {
    // On utilise la variable définie dans le PHP
    const currentUserId = (typeof CURRENT_USER_ID !== 'undefined') ? CURRENT_USER_ID : 0;
    const isOwner = (currentUserId > 0 && img.user_id == currentUserId);
    
    // Génération des commentaires
    const commentsHtml = img.comments.map(c => 
        `<p style="margin-bottom: 5px;"><strong>${escapeHtml(c.username)}:</strong> ${escapeHtml(c.comment)}</p>`
    ).join('');

    // URLs pour le partage social
    const host = window.location.protocol + "//" + window.location.host;
    const sharePageUrl = encodeURIComponent(host + "/gallery");
    const shareImageUrl = encodeURIComponent(host + "/uploads/" + img.filename);
    const shareText = encodeURIComponent("Regardez mon montage sur Camagru !");

    return `
    <div class="gallery-card">
        <div style="position: relative;">
            <img src="/uploads/${escapeHtml(img.filename)}" alt="Montage">
            ${isOwner ? `
                <form action="/delete-image" method="POST" class="delete-form" style="position: absolute; top: 10px; right: 10px; background: none; padding: 0; box-shadow: none;">
                    <input type="hidden" name="image_id" value="${img.id}">
                    <button type="submit" style="background: red; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">🗑️</button>
                </form>
            ` : ''}
        </div>

        <div class="gallery-info">
            <p style="color: #777; font-size: 0.9em;">Par <strong>${escapeHtml(img.username)}</strong></p>
            
            <div class="social-share" style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px; text-align: center; font-size: 0.9em;">
                <span style="color: #555; margin-right: 5px;">Partager :</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u=${sharePageUrl}" target="_blank" style="margin-right: 8px; text-decoration: none;">📘 FB</a>
                <a href="https://twitter.com/intent/tweet?url=${sharePageUrl}&text=${shareText}" target="_blank" style="margin-right: 8px; text-decoration: none;">🐦 X</a>
                <a href="https://pinterest.com/pin/create/button/?url=${sharePageUrl}&media=${shareImageUrl}&description=${shareText}" target="_blank" style="text-decoration: none;">📌 Pin</a>
            </div>

            <div class="gallery-actions" style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px;">
                <span class="like-count" style="margin-right: 15px;">❤️ ${img.likes} J'aime</span>
                ${currentUserId > 0 ? `
                    <form action="/like" method="POST" class="like-form" style="display:inline; padding: 0; background: none; box-shadow: none;">
                        <input type="hidden" name="image_id" value="${img.id}">
                        <button type="submit" class="btn-like" style="font-size: 1.2rem;">👍</button>
                    </form>
                ` : ''}
            </div>

            <div class="comments-section" style="margin-top: 15px;">
                <div class="comment-list" style="max-height: 100px; overflow-y: auto; background: #f9f9f9; padding: 5px; border-radius: 4px; font-size: 0.85em;">
                    ${commentsHtml}
                </div>
                ${currentUserId > 0 ? `
                    <form action="/comment" method="POST" class="comment-form" style="margin-top: 10px; display: flex; gap: 5px; padding: 0; background: none; box-shadow: none;">
                        <input type="hidden" name="image_id" value="${img.id}">
                        <input type="text" name="comment" placeholder="Un commentaire..." required style="flex: 1; padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
                        <button type="submit" style="background: #2980b9; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">OK</button>
                    </form>
                ` : ''}
            </div>
        </div>
    </div>`;
}

// Fonction principale qui attache les événements
function attachGalleryListeners() {
    // 1. Likes
    document.querySelectorAll('.like-form').forEach(form => {
        // Pour éviter d'attacher 2 fois l'événement, on vérifie un attribut
        if (form.getAttribute('data-listening')) return;
        form.setAttribute('data-listening', 'true');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const imageId = this.querySelector('input[name="image_id"]').value;
            const likeCountElement = this.closest('.gallery-actions').querySelector('.like-count');
            const likeButton = this.querySelector('button');

            // Dans la section fetch('/like', ...)
            const params = new URLSearchParams({ image_id: imageId });
            if (typeof CSRF_TOKEN !== 'undefined') params.append('csrf_token', CSRF_TOKEN);

            fetch('/like', {
                method: 'POST',
                body: params,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    likeCountElement.textContent = `❤️ ${data.new_count} J'aime`;
                    likeButton.textContent = (data.status === 'liked') ? '👍' : '👎';
                }
            })
            .catch(console.error);
        });
    });

    // 2. Commentaires
    document.querySelectorAll('.comment-form').forEach(form => {
        if (form.getAttribute('data-listening')) return;
        form.setAttribute('data-listening', 'true');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const imageId = this.querySelector('input[name="image_id"]').value;
            const commentInput = this.querySelector('input[name="comment"]');
            const commentsContainer = this.closest('.gallery-info').querySelector('.comment-list');
            
            if (!commentInput.value) return;

            // Dans la section fetch('/comment', ...)
            const params = new URLSearchParams({ image_id: imageId, comment: commentInput.value });
            if (typeof CSRF_TOKEN !== 'undefined') params.append('csrf_token', CSRF_TOKEN); // 👈 AJOUT

            fetch('/comment', {
                method: 'POST',
                body: params,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const newComment = document.createElement('p');
                    newComment.style.marginBottom = "5px";
                    newComment.innerHTML = `<strong>${escapeHtml(data.username)}:</strong> ${escapeHtml(data.comment)}`;
                    commentsContainer.appendChild(newComment);
                    commentInput.value = '';
                    commentsContainer.scrollTop = commentsContainer.scrollHeight;
                }
            })
            .catch(console.error);
        });
    });

    // 3. Suppression
    document.querySelectorAll('.delete-form').forEach(form => {
        if (form.getAttribute('data-listening')) return;
        form.setAttribute('data-listening', 'true');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) return;

            const imageId = this.querySelector('input[name="image_id"]').value;
            const card = this.closest('.gallery-card');

            // Dans la section fetch('/delete-image', ...)
            const params = new URLSearchParams({ image_id: imageId });
            if (typeof CSRF_TOKEN !== 'undefined') params.append('csrf_token', CSRF_TOKEN); // 👈 AJOUT

            fetch('/delete-image', {
                method: 'POST',
                body: params,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) card.remove();
                else alert('Erreur suppression');
            })
            .catch(console.error);
        });
    });
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    
    // Attacher les listeners sur les images chargées par PHP (page 1)
    attachGalleryListeners();

    // --- 4. Gestion du Scroll Infini ---
    const galleryGrid = document.querySelector('.gallery-grid');
    const loadingIndicator = document.getElementById('loading');
    let isLoading = false;

    function loadNextPage() {
        const nextPageData = document.getElementById('next-page');
        if (!nextPageData || isLoading) return; 

        isLoading = true;
        loadingIndicator.style.display = 'block';

        const nextPage = nextPageData.dataset.page;
        console.log(`Chargement de la page ${nextPage}...`);

        // On ajoute ajax=1 dans l'URL
        fetch(`/gallery?page=${nextPage}&ajax=1`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.images && data.images.length > 0) {
                data.images.forEach(img => {
                    galleryGrid.insertAdjacentHTML('beforeend', createGalleryCard(img));
                });

                // Réattacher les listeners sur les NOUVELLES images
                attachGalleryListeners();

                // Préparer la page suivante
                if (data.next_page) {
                    nextPageData.dataset.page = data.next_page;
                } else {
                    nextPageData.remove();
                    // On crée l'élément de fin s'il n'existe pas
                    if (!document.getElementById('end-message')) {
                        const endMsg = document.createElement('div');
                        endMsg.id = 'end-message';
                        endMsg.style.textAlign = 'center';
                        endMsg.style.marginTop = '30px';
                        endMsg.style.color = '#7f8c8d';
                        endMsg.innerText = 'Fin de la galerie.';
                        document.querySelector('.container').appendChild(endMsg);
                    }
                }
            } else {
                nextPageData.remove();
            }
        })
        .catch(err => console.error("Erreur Scroll:", err))
        .finally(() => {
            isLoading = false;
            loadingIndicator.style.display = 'none';
        });
    }

    // Scroll event
    window.addEventListener('scroll', () => {
        const scrollPosition = window.scrollY + window.innerHeight;
        const totalHeight = document.documentElement.scrollHeight;
        if (scrollPosition >= totalHeight - 300) {
            loadNextPage();
        }
    });
});