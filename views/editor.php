<?php include __DIR__ . '/layout/header.php'; ?>

<div class="container">
    <div class="editor-container" style="display: flex; gap: 20px; flex-wrap: wrap;">
        
        <div class="main-section" style="flex: 3; min-width: 300px;">
            <div class="video-wrapper" style="position: relative; background: #000; border-radius: 8px; overflow: hidden;">
                <video id="video" autoplay playsinline style="width: 100%; display: block;"></video>
                
                <img id="filter-overlay" src="" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain; pointer-events: none; display: none;">
                
                <canvas id="canvas" style="display:none;"></canvas>
            </div>

            <div class="controls" style="margin-top: 20px; background: white; padding: 20px; border-radius: 8px;">
                <h3>1. Choisissez un filtre (Obligatoire)</h3>
                <div id="filters" style="display: flex; gap: 10px; margin: 15px 0; overflow-x: auto;">
                    <label style="cursor: pointer; border: 2px solid #ddd; padding: 5px; border-radius: 4px;">
                        <input type="radio" name="filter" value="glasses.png" onchange="updateFilter(this)">
                        <span>🕶️ Lunettes</span>
                    </label>
                    <label style="cursor: pointer; border: 2px solid #ddd; padding: 5px; border-radius: 4px;">
                        <input type="radio" name="filter" value="hat.png" onchange="updateFilter(this)">
                        <span>🎩 Chapeau</span>
                    </label>
                    <label style="cursor: pointer; border: 2px solid #ddd; padding: 5px; border-radius: 4px;">
                        <input type="radio" name="filter" value="frame.png" onchange="updateFilter(this)">
                        <span>🖼️ Cadre</span>
                    </label>
                </div>

                <h3>2. Capture</h3>
                <button id="snap" class="btn btn-blue" disabled>📸 Prendre la photo</button>
                
                <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 10px;">
                    <p>Pas de webcam ?</p>
                    <input type="file" id="upload" accept="image/*">
                </div>
            </div>
        </div>

        <div class="side-section" style="flex: 1; background: white; padding: 15px; border-radius: 8px; height: fit-content;">
            <h3>Vos créations</h3>
            <div id="gallery-side" style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                <p style="color: #777; font-size: 0.9rem;">Aucune image pour l'instant.</p>
            </div>
        </div>
    </div>
</div>

<script src="/js/camera.js"></script>

<?php include __DIR__ . '/layout/footer.php'; ?>