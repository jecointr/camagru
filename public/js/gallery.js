// Fonction utilitaire pour éviter les failles XSS lors de l'ajout dynamique de commentaire
function escapeHtml(text) {
    if (!text) return text;
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Fonction principale qui attache les événements
function attachGalleryListeners() {
    
    // 1. Likes
    document.querySelectorAll('.like-form').forEach(form => {
        if (form.getAttribute('data-listening')) return;
        form.setAttribute('data-listening', 'true');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const imageId = this.querySelector('input[name="image_id"]').value;
            // RECUPERATION DU TOKEN DIRECTEMENT DEPUIS LE FORMULAIRE PHP
            const csrfToken = this.querySelector('input[name="csrf_token"]').value;
            
            const likeCountElement = this.closest('.gallery-actions').querySelector('.like-count');
            const likeButton = this.querySelector('button');

            const params = new URLSearchParams({ 
                image_id: imageId,
                csrf_token: csrfToken // On envoie le token trouvé
            });

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
            const csrfToken = this.querySelector('input[name="csrf_token"]').value;
            const commentInput = this.querySelector('input[name="comment"]');
            const commentsContainer = this.closest('.gallery-info').querySelector('.comment-list');
            
            if (!commentInput.value) return;

            const params = new URLSearchParams({ 
                image_id: imageId, 
                comment: commentInput.value,
                csrf_token: csrfToken 
            });

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
                    // Ici on utilise escapeHtml car c'est du JS qui insère du texte utilisateur
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
            const csrfToken = this.querySelector('input[name="csrf_token"]').value;
            const card = this.closest('.gallery-card');

            const params = new URLSearchParams({ 
                image_id: imageId,
                csrf_token: csrfToken
            });

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

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    
    // Attacher les listeners sur les images chargées par PHP (page 1)
    attachGalleryListeners();

    // --- Gestion du Scroll Infini ---
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

        fetch(`/gallery?page=${nextPage}&ajax=1`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.html) {
                // MAGIE : On insère le HTML reçu directement !
                galleryGrid.insertAdjacentHTML('beforeend', data.html);

                // Réattacher les listeners sur les NOUVELLES images
                attachGalleryListeners();

                // Préparer la page suivante
                if (data.next_page) {
                    nextPageData.dataset.page = data.next_page;
                } else {
                    nextPageData.remove();
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