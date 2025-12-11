(function() {
    console.log("📷 Camagru Studio v3.0 (Pro) Loaded");

    // Récupération des éléments du DOM
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const snapBtn = document.getElementById('snap');
    const uploadInput = document.getElementById('upload-file');
    const previewUpload = document.getElementById('preview-upload');
    const thumbnails = document.getElementById('thumbnails');
    const overlay = document.getElementById('filter-overlay');

    // Vérification basique
    if (!video || !canvas || !snapBtn) {
        console.error("❌ Éléments critiques manquants dans le HTML.");
        return;
    }
    
    // Variables pour le Drag & Drop
    let isDragging = false;
    let startX, startY, initialLeft, initialTop;
    
    // --- 1. GESTION DU DRAG & DROP (SOURIS) ---
    if (overlay) {
        overlay.style.cursor = "move"; // Curseur de déplacement
        
        // Quand on clique sur le filtre
        overlay.addEventListener('mousedown', (e) => {
            e.preventDefault(); 
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            initialLeft = overlay.offsetLeft;
            initialTop = overlay.offsetTop;
            overlay.style.cursor = "grabbing"; // Curseur "main fermée"
        });

        // Quand on bouge la souris
        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            overlay.style.left = `${initialLeft + dx}px`;
            overlay.style.top = `${initialTop + dy}px`;
        });

        // Quand on relâche la souris
        document.addEventListener('mouseup', () => {
            isDragging = false;
            overlay.style.cursor = "move";
        });
    }

    // --- 2. INITIALISATION WEBCAM ---
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

    // --- 3. ACTIVATION DU FILTRE (Au clic sur un sticker) ---
    window.enableSnap = function() {
        snapBtn.disabled = false;
        snapBtn.style.opacity = "1";
        snapBtn.style.cursor = "pointer";

        const filterInput = document.querySelector('input[name="filter"]:checked');
        if (filterInput && overlay) {
            overlay.src = '/img/filters/' + filterInput.value;
            overlay.style.display = 'block';
            
            // On le remet au centre par défaut quand on change de filtre
            overlay.style.top = '50%';
            overlay.style.left = '50%';
        }
    }

    // --- 4. PRISE DE PHOTO (Capture + Calculs) ---
    snapBtn.addEventListener('click', function(ev){
        ev.preventDefault();
        
        if (snapBtn.disabled) return;

        const context = canvas.getContext('2d');
        
        // On fixe la taille interne du canvas (taille de traitement)
        // C'est cette taille qui sera envoyée au serveur (640x480)
        canvas.width = 640;
        canvas.height = 480;

        // A. DESSINER L'IMAGE DE FOND (Webcam ou Upload)
        if (video && video.style.display !== 'none') {
            // Capture Webcam
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
        } else if (previewUpload && previewUpload.src) {
            // Capture Upload
            context.drawImage(previewUpload, 0, 0, canvas.width, canvas.height);
        } else {
            showToast("Erreur : Aucune image source !", "error");
            return;
        }

        const data = canvas.toDataURL('image/png');
        
        // B. CALCULER LA POSITION RELATIVE DU FILTRE
        // Le filtre est positionné en CSS (pixels écran). 
        // Il faut convertir ça en pixels image (640x480).
        
        const container = document.querySelector('.video-wrapper');
        // Taille actuelle affichée à l'écran
        const contW = container.offsetWidth; 
        const contH = container.offsetHeight;
        
        // Position du filtre par rapport au conteneur (centré sur son propre milieu)
        const filterLeft = overlay.offsetLeft - (overlay.offsetWidth / 2);
        const filterTop = overlay.offsetTop - (overlay.offsetHeight / 2);
        
        // Facteur d'échelle (Ratio Image Réelle / Image Écran)
        const scaleX = 640 / contW;
        const scaleY = 480 / contH;

        // Construction du paquet de données pour le serveur
        const payload = {
            image: data,
            filter: document.querySelector('input[name="filter"]:checked').value,
            // 👇 Protection CSRF (Importante !)
            csrf_token: (typeof CSRF_TOKEN !== 'undefined') ? CSRF_TOKEN : '',
            
            // 👇 Données de position pour le PHP
            meta: {
                x: Math.round(filterLeft * scaleX),
                y: Math.round(filterTop * scaleY),
                w: Math.round(overlay.offsetWidth * scaleX),
                h: Math.round(overlay.offsetHeight * scaleY)
            }
        };

        sendImage(payload);
    });

    // --- 5. ENVOI AJAX AU SERVEUR ---
    function sendImage(payload) {
        fetch('/save-image', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.text()) // On lit en text pour debug si erreur HTML
        .then(text => {
            try { return JSON.parse(text); } 
            catch (e) { throw new Error("Réponse serveur invalide: " + text); }
        })
        .then(data => {
            if (data.success) {
                // Succès : Toast vert + Ajout miniature
                if (typeof showToast === "function") showToast("Montage sauvegardé ! 🎨");
                else alert("Sauvegardé !");
                
                const div = document.createElement('div');
                div.className = 'thumb';
                div.innerHTML = `<img src="/uploads/${data.filename}" style="width:100%;">`;
                if (thumbnails) thumbnails.prepend(div);
            } else {
                // Erreur : Toast rouge
                if (typeof showToast === "function") showToast("Erreur: " + data.error, "error");
                else alert("Erreur: " + data.error);
            }
        })
        .catch(err => console.error("Erreur Fetch:", err));
    }
    
    // --- 6. GESTION DE L'UPLOAD DE FICHIER ---
    if (uploadInput) {
         uploadInput.addEventListener('change', function(e){
            const file = e.target.files[0];
            if (file) {
                enableSnap(); // Active le bouton photo
                const reader = new FileReader();
                
                reader.onload = function(ev) {
                    // Affiche l'image uploadée à la place de la vidéo
                    previewUpload.src = ev.target.result;
                    previewUpload.style.display = 'block';
                    if (video) video.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        });
    }

})();