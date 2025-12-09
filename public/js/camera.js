(function() {
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const photoButton = document.getElementById('snap');
    const uploadInput = document.getElementById('upload');
    const preview = document.getElementById('preview');
    let width = 640;
    let height = 480;

    // 1. Accès Webcam
    navigator.mediaDevices.getUserMedia({ video: true, audio: false })
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
        })
        .catch(function(err) {
            console.log("Webcam non disponible : " + err);
            // Fallback : afficher l'upload seulement
        });

    // 2. Gestion du clic "Prendre Photo"
    photoButton.addEventListener('click', function(ev) {
        ev.preventDefault();
        takepicture();
    });

    // 3. Gestion de l'upload fichier (Alternative à la webcam)
    uploadInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const reader = new FileReader();
        reader.onloadend = function() {
            preview.src = reader.result;
            preview.style.display = 'block';
            video.style.display = 'none'; // On cache la vidéo
            sendImage(reader.result); // On envoie directement
        }
        if (file) {
            reader.readAsDataURL(file);
        }
    });

    // Fonction de capture
    function takepicture() {
        const context = canvas.getContext('2d');
        if (width && height) {
            canvas.width = width;
            canvas.height = height;
            context.drawImage(video, 0, 0, width, height);
            
            const data = canvas.toDataURL('image/png');
            sendImage(data);
        }
    }

    // Fonction d'envoi AJAX au serveur
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

        fetch('/save-image', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Ajouter la nouvelle image dans la side-bar dynamiquement
                const img = document.createElement('img');
                img.src = '/uploads/' + data.filename;
                img.style.width = '100%';
                document.getElementById('gallery-side').prepend(img);
            } else {
                alert('Erreur: ' + data.error);
            }
        })
        .catch(console.error);
    }
    
    // Rendre la fonction globale pour l'onchange HTML
    window.enableButton = function() {
        photoButton.disabled = false;
    }
})();
