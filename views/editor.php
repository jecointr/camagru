<?php include __DIR__ . '/layout/header.php'; ?>

<div class="container">
    <div class="editor-container" style="display: flex; gap: 20px; flex-wrap: wrap;">
        
        <div class="main-section" style="flex: 3; min-width: 320px;">
            
            <div class="video-wrapper" style="position: relative; background: #000; border-radius: 8px; overflow: hidden; width: 100%;">
                
                <video id="video" autoplay playsinline muted style="width: 100%; height: auto; display: block; transform: scaleX(-1); pointer-events: none;"></video>
                
                <canvas id="canvas" style="display:none;"></canvas>
                <img id="preview-upload" style="display:none; width: 100%; height: auto; object-fit: contain;">
                
                <img id="filter-overlay" src="" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: auto; max-width: 100%; max-height: 100%; display: none; cursor: move;">
            </div>

            <div class="controls" style="margin-top: 20px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                
                <h3>1. Choisissez un filtre (Obligatoire)</h3>
                <div id="filters" style="display: flex; gap: 15px; margin: 15px 0; overflow-x: auto; padding-bottom: 5px;">
                    
                    <label style="cursor: pointer; border: 2px solid #ddd; padding: 10px; border-radius: 4px; display: flex; flex-direction: column; align-items: center; min-width: 80px;">
                        <input type="radio" name="filter" value="glasses.png" onchange="enableSnap()"> 
                        <img src="/img/filters/glasses.png" alt="Lunettes" style="width: 50px; margin-top: 5px;">
                        <span style="font-size: 0.8em; margin-top: 5px;">🕶️ Lunettes</span>
                    </label>

                    <label style="cursor: pointer; border: 2px solid #ddd; padding: 10px; border-radius: 4px; display: flex; flex-direction: column; align-items: center; min-width: 80px;">
                        <input type="radio" name="filter" value="hat.png" onchange="enableSnap()">
                        <img src="/img/filters/hat.png" alt="Chapeau" style="width: 50px; margin-top: 5px;">
                        <span style="font-size: 0.8em; margin-top: 5px;">🎩 Chapeau</span>
                    </label>

                    <label style="cursor: pointer; border: 2px solid #ddd; padding: 10px; border-radius: 4px; display: flex; flex-direction: column; align-items: center; min-width: 80px;">
                        <input type="radio" name="filter" value="frame.png" onchange="enableSnap()">
                        <img src="/img/filters/frame.png" alt="Cadre" style="width: 50px; margin-top: 5px;">
                        <span style="font-size: 0.8em; margin-top: 5px;">🖼️ Cadre</span>
                    </label>
                </div>

                <h3>2. Capture</h3>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button id="snap" class="btn btn-blue" disabled style="opacity: 0.5; cursor: not-allowed;">📸 Prendre la photo</button>
                    
                    <div style="border-left: 1px solid #ddd; padding-left: 15px;">
                        <p style="margin-bottom: 5px; font-size: 0.9em;">Pas de webcam ?</p>
                        <input type="file" id="upload-file" accept="image/*">
                    </div>
                </div>
            </div>
        </div>

        <div class="side-section" style="flex: 1; background: white; padding: 15px; border-radius: 8px; height: fit-content; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3>Vos créations</h3>
            <div id="thumbnails" style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                
                <?php if (isset($images) && !empty($images)): ?>
                    <?php foreach($images as $img): ?>
                        <div class="thumb">
                            <img src="/uploads/<?= htmlspecialchars($img['image_path']) ?>" style="width: 100%; border-radius: 4px; border: 1px solid #eee;">
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #777; font-size: 0.9rem;" id="no-img-msg">Aucune image pour l'instant.</p>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script src="/js/camera.js"></script>

<?php include __DIR__ . '/layout/footer.php'; ?>