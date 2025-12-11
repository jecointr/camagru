(function() {
    console.log("📷 Chargement du script Camera...");

    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const snapBtn = document.getElementById('snap');
    const uploadInput = document.getElementById('upload-file');
    const previewUpload = document.getElementById('preview-upload');
    const thumbnails = document.getElementById('thumbnails');

    // Vérification que les éléments existent
    if (!video || !canvas || !snapBtn) {
        console.error("❌ Erreur critique : Éléments HTML manquants (video, canvas ou bouton).");
        return;
    }

    let width = 640;
    let height = 480;
    
    // 1. Démarrage Webcam
    navigator.mediaDevices.getUserMedia({ video: true, audio: false })
        .then(function(stream) {
            console.log("✅ Webcam autorisée et active.");
            video.srcObject = stream;
            video.play();
        })
        .catch(function(err) {
            console.error("⚠️ Erreur Webcam (Peut être bloquée ou absente) : " + err);
            // On ne bloque pas tout, l'upload reste possible
        });

    video.addEventListener('canplay', function(ev){
        console.log("🎥 Flux vidéo prêt.");
        // Calcul du ratio seulement si la vidéo a une taille valide
        if (video.videoWidth > 0) {
            height = video.videoHeight / (video.videoWidth / width);
            video.setAttribute('width', width);
            video.setAttribute('height', height);
            canvas.setAttribute('width', width);
            canvas.setAttribute('height', height);
        }
    }, false);

    window.enableSnap = function() {
        console.log("🔘 Filtre sélectionné.");
        snapBtn.disabled = false;
        snapBtn.style.opacity = "1";
        snapBtn.style.cursor = "pointer";

        const filterInput = document.querySelector('input[name="filter"]:checked');
        const overlay = document.getElementById('filter-overlay');
        
        if (filterInput && overlay) {
            // On cherche l'image qui est dans le label du bouton radio coché
            // (L'image est la balise 'sibling' ou enfant du label)
            // Plus simple : on reconstruit le chemin
            overlay.src = '/img/filters/' + filterInput.value;
            overlay.style.display = 'block';
        }
    }

    // 3. Clic sur le bouton Photo
    snapBtn.addEventListener('click', function(ev){
        ev.preventDefault();
        console.log("📸 Clic sur 'Prendre Photo'");
        
        if (snapBtn.disabled) {
            console.log("⛔ Bouton désactivé, clic ignoré.");
            return;
        }
        
        takePicture();
    });

    // 4. Gestion Upload
    if (uploadInput) {
        uploadInput.addEventListener('change', function(e){
            console.log("📂 Fichier sélectionné.");
            const file = e.target.files[0];
            if (file) {
                // Activer le bouton snap car on a une image
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

    function takePicture() {
        const context = canvas.getContext('2d');
        canvas.width = width;
        canvas.height = height;

        // Logique : Si vidéo visible on la prend, sinon on prend l'upload
        if (video && video.style.display !== 'none') {
            console.log("👉 Capture depuis la Webcam");
            context.drawImage(video, 0, 0, width, height);
        } else if (previewUpload && previewUpload.src) {
            console.log("👉 Capture depuis l'Upload");
            context.drawImage(previewUpload, 0, 0, width, height);
        } else {
            console.error("❌ Ni webcam ni upload disponible.");
            return;
        }

        const data = canvas.toDataURL('image/png');
        
        // Vérification de la donnée
        if (data.length < 100) {
            console.error("❌ Erreur: Image vide générée.");
            return;
        }

        sendImage(data);
    }

    function sendImage(base64) {
        const filterInput = document.querySelector('input[name="filter"]:checked');
        
        if (!filterInput) {
            alert("Veuillez sélectionner un filtre !");
            return;
        }
        
        console.log("🚀 Envoi au serveur avec filtre : " + filterInput.value);

        const payload = {
            image: base64,
            filter: filterInput.value,
            csrf_token: (typeof CSRF_TOKEN !== 'undefined') ? CSRF_TOKEN : ''
        };

        fetch('/save-image', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.text()) // On lit en text d'abord pour voir si c'est du HTML d'erreur
        .then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error("❌ Réponse serveur invalide (Pas du JSON) : ", text);
                throw new Error("Réponse serveur invalide");
            }
        })
        .then(data => {
            console.log("✅ Réponse serveur :", data);
            if (data.success) {
                const div = document.createElement('div');
                div.className = 'thumb';
                div.innerHTML = `<img src="/uploads/${data.filename}" style="width:100%; border-radius:4px; margin-bottom:10px;">`;
                if (thumbnails) thumbnails.prepend(div);
            } else {
                alert("Erreur serveur : " + data.error);
            }
        })
        .catch(err => console.error("❌ Erreur Fetch :", err));
    }
})();