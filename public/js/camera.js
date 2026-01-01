(function() {
    console.log("📷 Camagru Studio v3.1 (Pro) Loaded");

    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const snapBtn = document.getElementById('snap');
    const uploadInput = document.getElementById('upload-file');
    const previewUpload = document.getElementById('preview-upload');
    const thumbnails = document.getElementById('thumbnails');
    const overlay = document.getElementById('filter-overlay');

    if (!video || !canvas || !snapBtn) {
        console.error("❌ Éléments critiques manquants dans le HTML.");
        return;
    }
    
    let isDragging = false;
    let startX, startY, initialLeft, initialTop;
    
    if (overlay) {
        overlay.style.cursor = "move";
        
        let containerWidth, containerHeight;
        let stickerWidth, stickerHeight;

        const stickerConfigs = {
            'glasses.png': { scaleX: 0.70, scaleY: 0.10 }, 

            'hat.png':     { scaleX: 0.60, scaleY: 0.10 }, 

            'frame.png':   { scaleX: 1.00, scaleY: 1.00 },

            'default':     { scaleX: 0.80, scaleY: 0.50 }
        };

        const dragStart = (clientX, clientY) => {
            isDragging = true;
            
            const container = overlay.parentElement;
            containerWidth = container.offsetWidth;
            containerHeight = container.offsetHeight;
            stickerWidth = overlay.offsetWidth;
            stickerHeight = overlay.offsetHeight;

            startX = clientX;
            startY = clientY;
            initialLeft = overlay.offsetLeft;
            initialTop = overlay.offsetTop;
            overlay.style.cursor = "grabbing";
        };

        const dragMove = (clientX, clientY) => {
            if (!isDragging) return;

            const dx = clientX - startX;
            const dy = clientY - startY;

            let newLeft = initialLeft + dx;
            let newTop = initialTop + dy;

            const currentFilterInput = document.querySelector('input[name="filter"]:checked');
            const filename = currentFilterInput ? currentFilterInput.value : 'default';
            const conf = stickerConfigs[filename] || stickerConfigs['default'];

            const realHalfWidth = stickerWidth / 2;
            const realHalfHeight = stickerHeight / 2;

            const effectiveHalfW = realHalfWidth * conf.scaleX;
            const effectiveHalfH = realHalfHeight * conf.scaleY;

            const minX = effectiveHalfW;
            const maxX = containerWidth - effectiveHalfW;
            
            const minY = effectiveHalfH;
            const maxY = containerHeight - effectiveHalfH;

            newLeft = Math.max(minX, Math.min(newLeft, maxX));
            newTop = Math.max(minY, Math.min(newTop, maxY));

            overlay.style.left = `${newLeft}px`;
            overlay.style.top = `${newTop}px`;
        };

        const dragEnd = () => {
            isDragging = false;
            overlay.style.cursor = "move";
        };
        
        overlay.addEventListener('mousedown', (e) => {
            e.preventDefault();
            dragStart(e.clientX, e.clientY);
        });

        document.addEventListener('mousemove', (e) => {
            dragMove(e.clientX, e.clientY);
        });

        document.addEventListener('mouseup', dragEnd);

        overlay.addEventListener('touchstart', (e) => {
            if(e.cancelable) e.preventDefault(); 
            
            const touch = e.touches[0];
            dragStart(touch.clientX, touch.clientY);
        }, { passive: false });

        document.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            if(e.cancelable) e.preventDefault();
            
            const touch = e.touches[0];
            dragMove(touch.clientX, touch.clientY);
        }, { passive: false });

        document.addEventListener('touchend', dragEnd);
    }

    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            video.srcObject = stream;
            video.play();
        })
        .catch(err => {
            console.error("⚠️ Webcam non disponible :", err);
            if (typeof showToast === 'function') {
                showToast("Webcam inaccessible. Utilisez l'upload.", "error");
            }
        });
    }

    window.enableSnap = function() {
        snapBtn.disabled = false;
        snapBtn.style.opacity = "1";
        snapBtn.style.cursor = "pointer";

        const filterInput = document.querySelector('input[name="filter"]:checked');
        if (filterInput && overlay) {
            overlay.src = '/img/filters/' + filterInput.value;
            overlay.style.display = 'block';            
            overlay.style.top = '50%';
            overlay.style.left = '50%';
        }
    }

    snapBtn.addEventListener('click', function(ev){
        ev.preventDefault();
        
        if (snapBtn.disabled) return;

        const context = canvas.getContext('2d');
        
        canvas.width = 640;
        canvas.height = 480;

        if (video && video.style.display !== 'none') {
            
            context.save();
            context.translate(canvas.width, 0);
            context.scale(-1, 1);
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            context.restore();

        } else if (previewUpload && previewUpload.src) {
            context.drawImage(previewUpload, 0, 0, canvas.width, canvas.height);
        } else {
            showToast("Erreur : Aucune image source !", "error");
            return;
        }

        const data = canvas.toDataURL('image/png');
        const container = document.querySelector('.video-wrapper');
        const contW = container.offsetWidth; 
        const contH = container.offsetHeight;        
        const filterLeft = overlay.offsetLeft - (overlay.offsetWidth / 2);
        const filterTop = overlay.offsetTop - (overlay.offsetHeight / 2);
        
        const scaleX = 640 / contW;
        const scaleY = 480 / contH;

        const payload = {
            image: data,
            filter: document.querySelector('input[name="filter"]:checked').value,
            csrf_token: (typeof CSRF_TOKEN !== 'undefined') ? CSRF_TOKEN : '',
            
            meta: {
                x: Math.round(filterLeft * scaleX),
                y: Math.round(filterTop * scaleY),
                w: Math.round(overlay.offsetWidth * scaleX),
                h: Math.round(overlay.offsetHeight * scaleY)
            }
        };

        sendImage(payload);
    });

    function sendImage(payload) {
        fetch('/save-image', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.text())
        .then(text => {
            try { return JSON.parse(text); } 
            catch (e) { throw new Error("Réponse serveur invalide: " + text); }
        })
        .then(data => {
            if (data.success) {
                if (typeof showToast === "function") showToast("Montage sauvegardé ! 🎨");
                else alert("Sauvegardé !");
                
                const div = document.createElement('div');
                div.className = 'thumb';
                div.innerHTML = `<img src="/uploads/${data.filename}" style="width:100%;">`;
                if (thumbnails) thumbnails.prepend(div);
            } else {
                if (typeof showToast === "function") showToast("Erreur: " + data.error, "error");
                else alert("Erreur: " + data.error);
            }
        })
        .catch(err => console.error("Erreur Fetch:", err));
    }
    
    if (uploadInput) {
         uploadInput.addEventListener('change', function(e){
            const file = e.target.files[0];
            if (file) {
                enableSnap();
                const reader = new FileReader();
                
                reader.onload = function(ev) {
                    previewUpload.src = ev.target.result;
                    previewUpload.style.display = 'block';
                    if (video) video.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        });
    }

})();