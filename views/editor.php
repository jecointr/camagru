<div class="editor-container">
    <div class="main-section">
        <div class="video-wrapper">
            <video id="video" autoplay playsinline></video>
            <canvas id="canvas" style="display:none;"></canvas>
            <img id="preview" alt="Aperçu" style="display:none;">
        </div>

        <div class="controls">
            <h3>1. Choisissez un filtre (Obligatoire)</h3>
            <div id="filters">
                <label>
                    <input type="radio" name="filter" value="glasses.png" onchange="enableButton()">
                    <img src="/img/filters/glasses.png" style="width:50px;">
                </label>
                <label>
                    <input type="radio" name="filter" value="hat.png" onchange="enableButton()">
                    <img src="/img/filters/hat.png" style="width:50px;">
                </label>
            </div>

            <h3>2. Prenez la photo ou Uploadez</h3>
            <button id="snap" disabled>Prendre Photo</button>
            
            <p>Ou</p>
            <input type="file" id="upload" accept="image/*">
        </div>
    </div>

    <div class="side-section">
        <h3>Vos créations</h3>
        <div id="gallery-side">
            </div>
    </div>
</div>

<script src="/js/camera.js"></script>
