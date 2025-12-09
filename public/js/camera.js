(function() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const photoButton = document.getElementById('snap');
    const uploadInput = document.getElementById('upload');
    const filterOverlay = document.getElementById('filter-overlay');
    const gallerySide = document.getElementById('gallery-side');
    
    let width = 640;
    let height = 480;
    let streaming = false;

    // 1. Démarrer la Webcam
    navigator.mediaDevices.getUserMedia({ video: true, audio: false })
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
        })
        .catch(function(err) {
            console.log("Erreur Webcam : " + err);
            // Si pas de webcam, on peut quand même utiliser l'upload
        });

    video.addEventListener('canplay', function(ev){
        if (!streaming) {
            // Ajustement ratio
            height = video.videoHeight / (video.videoWidth/width);
            video.setAttribute('width', width);
            video.setAttribute('height', height);
            canvas.setAttribute('width', width);
            canvas.setAttribute('height', height);
            streaming = true;
        }
    }, false);

    // 2. Gestion du filtre (Aperçu)
    window.updateFilter = function(radio) {
        photoButton.disabled = false;
        photoButton.innerText = "📸 Prendre la photo";
        // En vrai projet, met ici le chemin réel vers l'image du filtre
        // filterOverlay.src = '/img/filters/' + radio.value;
        // filterOverlay.style.display = 'block';
    };

    // 3. Prise de photo
    photoButton.addEventListener('click', function(ev) {
        ev.preventDefault();
        takePicture();
    });

    // 4. Upload fichier alternatif
    uploadInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    // Dessiner l'image uploadée sur le canvas
                    const context = canvas.getContext('2d');
                    canvas.width = width;
                    canvas.height = height;
                    context.drawImage(img, 0, 0, width, height);
                    sendImage(canvas.toDataURL('image/png'));
                }
                img.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    function takePicture() {
        const context = canvas.getContext('2d');
        if (width && height) {
            canvas.width = width;
            canvas.height = height;
            // Dessiner la vidéo sur le canvas
            context.drawImage(video, 0, 0, width, height);
            
            const data = canvas.toDataURL('image/png');
            sendImage(data);
        }
    }

    function sendImage(base64Data) {
        const filter = document.querySelector('input[name="filter"]:checked');
        if (!filter) {
            alert("Veuillez sélectionner un filtre !");
            return;
        }

        const data = {
            image: base64Data,
            filter: filter.value
        };

        // Envoi AJAX
        fetch('/save-image', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                addImageToSide(data.filename);
            } else {
                alert('Erreur serveur : ' + data.error);
            }
        })
        .catch(err => console.error("Erreur Fetch:", err));
    }

    function addImageToSide(filename) {
        const img = document.createElement('img');
        img.src = '/uploads/' + filename;
        img.style.width = '100%';
        img.style.borderRadius = '4px';
        img.style.marginBottom = '10px';
        img.style.border = '1px solid #ddd';
        
        // On retire le texte "Aucune image" si présent
        if (gallerySide.querySelector('p')) gallerySide.innerHTML = '';
        
        gallerySide.prepend(img);
    }
})();