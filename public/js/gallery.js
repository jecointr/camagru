document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. Gestion des Likes ---
    document.querySelectorAll('.like-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // EMPÊCHE LE RECHARGEMENT DE PAGE

            const imageId = this.querySelector('input[name="image_id"]').value;
            const likeCountElement = this.closest('.gallery-actions').querySelector('.like-count');
            const likeButton = this.querySelector('button');

            fetch('/like', {
                method: 'POST',
                // Important : on utilise URLSearchParams pour simuler l'envoi d'un formulaire
                body: new URLSearchParams({ image_id: imageId }), 
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour le compteur
                    likeCountElement.textContent = `❤️ ${data.new_count} J'aime`;
                    
                    // Mettre à jour le bouton visuellement (si vous avez un CSS/icône pour ça)
                    if (data.status === 'liked') {
                        likeButton.textContent = '👍';
                    } else {
                        likeButton.textContent = '👎';
                    }
                } else {
                    alert('Erreur: ' + (data.error || 'Connexion impossible.'));
                }
            })
            .catch(err => console.error('Erreur Like AJAX:', err));
        });
    });

    // --- 2. Gestion des Commentaires ---
    document.querySelectorAll('.comment-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const imageId = this.querySelector('input[name="image_id"]').value;
            const commentInput = this.querySelector('input[name="comment"]');
            const commentText = commentInput.value;
            const commentsContainer = this.closest('.gallery-info').querySelector('.comment-list');
            
            if (!commentText) return;

            fetch('/comment', {
                method: 'POST',
                body: new URLSearchParams({ 
                    image_id: imageId, 
                    comment: commentText 
                }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Créer et ajouter le nouveau commentaire dans la liste
                    const newComment = document.createElement('p');
                    newComment.innerHTML = `<strong>${data.username}:</strong> ${data.comment}`;
                    commentsContainer.appendChild(newComment);
                    
                    // Vider le champ de saisie
                    commentInput.value = '';
                    
                    // Faire défiler vers le bas (si la liste est pleine)
                    commentsContainer.scrollTop = commentsContainer.scrollHeight;

                } else {
                    alert('Erreur : ' + (data.error || 'Commentaire non enregistré.'));
                }
            })
            .catch(err => console.error('Erreur Commentaire AJAX:', err));
        });
    });

    // --- 3. Gestion de la Suppression ---
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Empêche le rechargement
            
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) {
                return;
            }

            const imageId = this.querySelector('input[name="image_id"]').value;
            const cardToRemove = this.closest('.gallery-card'); // La carte entière à supprimer

            fetch('/delete-image', {
                method: 'POST',
                body: new URLSearchParams({ image_id: imageId }),
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Supprimer l'élément du DOM sans recharger
                    cardToRemove.remove();
                } else {
                    alert('Erreur suppression : ' + (data.error || 'Erreur inconnue.'));
                }
            })
            .catch(err => console.error('Erreur Suppression AJAX:', err));
        });
    });
});
