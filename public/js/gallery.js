console.log("Gallery Script Loaded v6.0 (ID ONLY Mode)");

function attachGalleryListeners() {
    document.querySelectorAll('.like-form:not([data-listening])').forEach(form => {
        form.setAttribute('data-listening', 'true');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const params = new URLSearchParams(new FormData(this));
            fetch('/like', { method: 'POST', body: params })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.closest('.gallery-actions').querySelector('.like-count').textContent = `❤️ ${data.new_count}`;
                    }
                });
        });
    });

    document.querySelectorAll('.comment-form:not([data-listening])').forEach(form => {
        form.setAttribute('data-listening', 'true');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const input = this.querySelector('input[name="comment"]');
            if(!input.value) return;
            const params = new URLSearchParams(new FormData(this));
            fetch('/comment', { method: 'POST', body: params })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const list = this.closest('.gallery-info').querySelector('.comment-list');
                        const p = document.createElement('p');
                        p.innerHTML = `<strong>${data.username}:</strong> ${data.comment}`;
                        list.appendChild(p);
                        input.value = '';
                    }
                });
        });
    });

    document.querySelectorAll('.delete-form:not([data-listening])').forEach(form => {
        form.setAttribute('data-listening', 'true');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!confirm('Supprimer ?')) return;

            const card = this.closest('.gallery-card');
            const params = new URLSearchParams(new FormData(this));

            fetch('/delete-image', { method: 'POST', body: params })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        card.remove();
                        setTimeout(() => {
                            if (window.loadNextPage) window.loadNextPage(true);
                        }, 50);
                    }
                });
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    attachGalleryListeners();

    const galleryGrid = document.querySelector('.gallery-grid');
    const loadingDiv = document.getElementById('loading');
    
    let isLoading = false;
    let hasMore = true;

    window.loadNextPage = function(force = false) {
        if ((isLoading && !force) || !hasMore) return;

        const cards = document.querySelectorAll('.gallery-card');
        if (cards.length === 0) return;
        
        const lastCard = cards[cards.length - 1];
        const lastId = lastCard.getAttribute('data-id');

        if (!lastId) return;

        isLoading = true;
        if(loadingDiv) loadingDiv.style.display = 'block';

        const url = `/gallery?ajax=1&last_id=${lastId}`;
        
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("Erreur JSON:", text);
                    return { success: false };
                }
            })
            .then(data => {
                if (data.success && data.html) {
                    if (data.html.trim() === "") {
                        hasMore = false;
                    } else {
                        galleryGrid.insertAdjacentHTML('beforeend', data.html);
                        attachGalleryListeners();
                        hasMore = data.has_more;
                    }
                } else {
                    hasMore = false;
                }
            })
            .finally(() => {
                isLoading = false;
                if(loadingDiv) loadingDiv.style.display = 'none';
                
                if (hasMore && (force || document.body.offsetHeight < window.innerHeight)) {
                    loadNextPage();
                }
            });
    };

    window.addEventListener('scroll', () => {
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 100) {
            loadNextPage();
        }
    });
});