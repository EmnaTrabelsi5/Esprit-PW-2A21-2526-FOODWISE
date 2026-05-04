<?php
/** AI Digital Twin — Simulation métabolique prédictive */
$pageTitle = 'AI Digital Twin — FoodWise';
$sidebarActive = 'digital_twin';

require __DIR__ . '/layouts/header.php';
require __DIR__ . '/layouts/sidebar.php';
?>

<div class="layout__main">
    <div class="bi-dashboard">
    <header class="bi-header">
        <div class="header-main">
            <h1><i class="fas fa-project-diagram"></i> Digital Twin Command Center</h1>
            <p>Simulation métabolique prédictive basée sur vos flux nutritionnels.</p>
        </div>
        <div class="header-status">
            <!-- Objective Selector -->
            <form action="index.php" method="get" class="bi-selector-form">
                <input type="hidden" name="resource" value="digital_twin">
                <input type="hidden" name="action" value="index">
                <select name="id_obj" onchange="this.form.submit()" class="bi-select">
                    <?php foreach ($objectifs as $obj): ?>
                        <option value="<?= (int)$obj['id_obj'] ?>" <?= $selectedId === (int)$obj['id_obj'] ? 'selected' : '' ?>>
                            Objectif #<?= (int)$obj['id_obj'] ?> : <?= htmlspecialchars((string)$obj['type']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <span class="status-indicator <?php echo $simulation['status']; ?>">
                <i class="fas fa-circle"></i> Simulation Active
            </span>
        </div>
    </header>

    <?php if (isset($error)): ?>
        <div class="bi-alert danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php else: ?>

    <!-- KPI Grid -->
    <div class="bi-kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-fire"></i></div>
            <div class="kpi-content">
                <span class="kpi-label">Burn Rate Quotidien</span>
                <span class="kpi-value"><?php echo $simulation['burn_rate']; ?> <small>kcal/j</small></span>
                <span class="kpi-trend <?php echo $simulation['burn_rate'] < 0 ? 'down' : 'up'; ?>">
                    <?php echo $simulation['burn_rate'] < 0 ? 'Déficit' : 'Surplus'; ?>
                </span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-weight"></i></div>
            <div class="kpi-content">
                <span class="kpi-label">Projection 90j</span>
                <span class="kpi-value"><?php echo $simulation['final_weight']; ?> <small>kg</small></span>
                <span class="kpi-trend <?php echo $simulation['total_change'] < 0 ? 'down' : 'up'; ?>">
                    <?php echo ($simulation['total_change'] > 0 ? '+' : '') . $simulation['total_change']; ?> kg
                </span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon"><i class="fas fa-utensils"></i></div>
            <div class="kpi-content">
                <span class="kpi-label">Apport Moyen Réel</span>
                <span class="kpi-value"><?php echo $simulation['avg_intake']; ?> <small>kcal</small></span>
                <span class="kpi-sub">Cible: <?php echo $simulation['objectif']['calories_cible']; ?></span>
            </div>
        </div>
    </div>

    <div class="bi-main-content">
        <!-- AI Comparative Morphological Simulator -->
        <div class="bi-chart-card bi-3d-section">
            <div class="card-header">
                <h2><i class="fas fa-user-friends"></i> Comparateur de Silhouette Prédictif</h2>
                <div class="bi-3d-controls">
                    <button type="button" class="btn-3d-toggle active" data-gender="male">Homme</button>
                    <button type="button" class="btn-3d-toggle" data-gender="female">Femme</button>
                </div>
            </div>
            
            <div class="bi-3d-container">
                <div class="bi-3d-viewport" id="viewport-current">
                    <div class="viewport-overlay">
                        <span><i class="fas fa-history"></i> État Actuel</span>
                        <span id="weight-current"><?php echo $simulation['start_weight']; ?> kg</span>
                    </div>
                    <div id="canvas-current"></div>
                </div>

                <div class="bi-3d-viewport" id="viewport-future">
                    <div class="viewport-overlay">
                        <span><i class="fas fa-magic"></i> Projection J+90</span>
                        <span id="weight-future"><?php echo $simulation['final_weight']; ?> kg</span>
                    </div>
                    <div id="canvas-future"></div>
                </div>
            </div>

            <div class="bi-3d-legend">
                <p><i class="fas fa-info-circle"></i> À gauche : votre silhouette actuelle. À droite : votre évolution projetée selon l'objectif choisi.</p>
            </div>
        </div>

        <!-- Metabolic Timeline Chart -->

        <!-- Metabolic Timeline Chart -->
        <div class="bi-chart-card">
            <div class="card-header">
                <h2><i class="fas fa-chart-line"></i> Timeline de Transformation (90 Jours)</h2>
            </div>
            <div class="timeline-container">
                <div class="timeline-y-axis">
                    <span><?php echo $simulation['start_weight'] + 5; ?>kg</span>
                    <span><?php echo $simulation['start_weight']; ?>kg</span>
                    <span><?php echo $simulation['start_weight'] - 5; ?>kg</span>
                </div>
                <div class="timeline-grid">
                    <?php foreach ($simulation['projection'] as $point): ?>
                        <div class="timeline-point" style="--day: <?php echo $point['day']; ?>; --weight: <?php echo $point['weight']; ?>; --start: <?php echo $simulation['start_weight']; ?>">
                            <div class="point-dot"></div>
                            <span class="point-label">J+<?php echo $point['day']; ?></span>
                            <span class="point-value"><?php echo $point['weight']; ?>kg</span>
                        </div>
                    <?php endforeach; ?>
                    <div class="timeline-line"></div>
                </div>
            </div>
        </div>

        <!-- Gap Closing / Strategy -->
        <div class="bi-strategy-card <?php echo $simulation['status']; ?>">
            <div class="card-header">
                <h2><i class="fas fa-sync-alt"></i> Ajusteur de Stratégie Dynamique</h2>
            </div>
            <div class="strategy-body">
                <p class="strategy-message"><?php echo $simulation['correction_message']; ?></p>
                <?php if ($simulation['correction_value'] !== 0): ?>
                    <div class="correction-box">
                        <span class="correction-label">Correctif Métabolique Requis :</span>
                        <span class="correction-value <?php echo $simulation['correction_value'] < 0 ? 'minus' : 'plus'; ?>">
                            <?php echo ($simulation['correction_value'] > 0 ? '+' : '') . $simulation['correction_value']; ?> kcal/jour
                        </span>
                    </div>
                <?php else: ?>
                    <div class="success-box">
                        <i class="fas fa-check-double"></i> Stratégie optimale détectée.
                    </div>
                <?php endif; ?>
                
                <div class="metabolic-info">
                    <div class="m-item">
                        <span>Maintenance Estimée (TDEE)</span>
                        <strong><?php echo $simulation['maintenance']; ?> kcal</strong>
                    </div>
                    <div class="m-item">
                        <span>Objectif Fixé</span>
                        <strong><?php echo $simulation['objectif']['type']; ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>
    </div>
</div>

<style>
.bi-dashboard {
    background: #f0f2f5;
    padding: 25px;
    min-height: 100vh;
    font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.bi-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    background: white;
    padding: 20px 30px;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.bi-header h1 {
    margin: 0;
    font-size: 1.8rem;
    color: #1a202c;
    display: flex;
    align-items: center;
    gap: 12px;
}

.bi-header h1 i { color: #4a5568; }

.header-status {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.bi-select {
    padding: 0.6rem 1rem;
    border-radius: 8px;
    border: 1px solid #edf2f7;
    background: #f8fafc;
    color: #4a5568;
    font-weight: 600;
    cursor: pointer;
    outline: none;
    transition: border-color 0.2s;
}

.bi-select:hover {
    border-color: #cbd5e0;
}

.status-indicator {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-indicator.success { background: #c6f6d5; color: #22543d; }
.status-indicator.warning { background: #feebc8; color: #744210; }

.bi-kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.kpi-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    border: 1px solid #edf2f7;
}

.kpi-icon {
    width: 50px;
    height: 50px;
    background: #edf2f7;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #4a5568;
}

.kpi-content { display: flex; flex-direction: column; }
.kpi-label { font-size: 0.85rem; color: #718096; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { font-size: 1.6rem; font-weight: 800; color: #2d3748; }
.kpi-trend { font-size: 0.8rem; font-weight: 600; margin-top: 4px; }
.kpi-trend.down { color: #e53e3e; }
.kpi-trend.up { color: #38a169; }

.bi-main-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
}

.bi-chart-card, .bi-strategy-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.02);
}

.bi-3d-section {
    grid-column: 1 / -1;
    margin-bottom: 25px;
}

.bi-3d-container {
    display: flex;
    gap: 20px;
    height: 450px;
    margin: 20px 0;
}

.bi-3d-viewport {
    flex: 1;
    background: radial-gradient(circle, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 12px;
    position: relative;
    overflow: hidden;
    border: 1px solid #cbd5e0;
    min-height: 450px;
}

.viewport-overlay {
    position: absolute;
    top: 15px;
    left: 15px;
    right: 15px;
    display: flex;
    justify-content: space-between;
    z-index: 10;
}

.viewport-overlay span {
    background: rgba(255, 255, 255, 0.9);
    color: #1a202c;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

#canvas-current, #canvas-future {
    width: 100%;
    height: 100%;
}

.viewport-label {
    position: absolute;
    top: 15px;
    left: 15px;
    background: rgba(0,0,0,0.6);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    z-index: 10;
}

.bi-3d-controls {
    display: flex;
    gap: 10px;
}

.btn-3d-toggle {
    background: #edf2f7;
    border: 1px solid #cbd5e0;
    padding: 5px 15px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-3d-toggle.active {
    background: #3182ce;
    color: white;
    border-color: #3182ce;
}

.bi-3d-legend {
    color: #718096;
    font-size: 0.85rem;
    text-align: center;
}

.card-header h2 {
    font-size: 1.1rem;
    margin: 0 0 25px 0;
    color: #4a5568;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Metabolic Timeline Visualization */
.timeline-container {
    height: 300px;
    position: relative;
    margin-top: 40px;
    padding-left: 50px;
    padding-bottom: 40px;
}

.timeline-y-axis {
    position: absolute;
    left: 0;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    color: #a0aec0;
    font-size: 0.8rem;
}

.timeline-grid {
    height: 100%;
    border-left: 2px solid #edf2f7;
    border-bottom: 2px solid #edf2f7;
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}

.timeline-point {
    position: relative;
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    /* Calc position based on weight delta */
    bottom: calc(50% + (var(--weight) - var(--start)) * 20px);
}

.point-dot {
    width: 10px;
    height: 10px;
    background: #3182ce;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 4px rgba(49, 130, 206, 0.2);
    z-index: 2;
}

.point-label {
    position: absolute;
    bottom: -60px;
    font-size: 0.75rem;
    color: #718096;
}

.point-value {
    position: absolute;
    top: -25px;
    font-size: 0.8rem;
    font-weight: bold;
    color: #2d3748;
}

/* Strategy Card */
.bi-strategy-card.warning { border-top: 4px solid #f6ad55; }
.bi-strategy-card.success { border-top: 4px solid #68d391; }

.strategy-message {
    font-size: 1rem;
    color: #4a5568;
    line-height: 1.5;
    margin-bottom: 20px;
}

.correction-box {
    background: #fffaf0;
    border: 1px solid #feebc8;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 25px;
}

.correction-label { display: block; font-size: 0.85rem; color: #744210; margin-bottom: 5px; }
.correction-value { font-size: 1.4rem; font-weight: 800; }
.correction-value.minus { color: #c53030; }
.correction-value.plus { color: #2f855a; }

.success-box {
    background: #f0fff4;
    color: #2f855a;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    font-weight: 600;
}

.metabolic-info {
    border-top: 1px solid #edf2f7;
    padding-top: 20px;
}

.m-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 0.9rem;
}

.m-item span { color: #718096; }
.m-item strong { color: #2d3748; }

.bi-alert.danger {
    background: #fff5f5;
    color: #c53030;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #fc8181;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof THREE === 'undefined') {
        console.error('Three.js is not loaded.');
        document.querySelectorAll('.bi-3d-viewport').forEach(v => {
            v.innerHTML += '<p style="color:white; text-align:center; padding-top:150px;">Erreur: Moteur 3D non chargé.</p>';
        });
        return;
    }

    const simulationData = <?php echo json_encode($simulation); ?>;
    let currentGender = 'male';

    function init3D(containerId, scaleFactor) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const width = container.offsetWidth || container.parentElement.clientWidth;
        const height = container.offsetHeight || container.parentElement.clientHeight || 450;

        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0xf8fafc); // White/Gray background like the image

        const camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 1000);
        camera.position.set(0, 1.2, 5);

        const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setSize(width, height);
        renderer.setPixelRatio(window.devicePixelRatio);
        container.appendChild(renderer.domElement);

        // Better Lighting for Black Silhouette
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.4);
        scene.add(ambientLight);
        
        const mainLight = new THREE.DirectionalLight(0xffffff, 0.8);
        mainLight.position.set(5, 10, 7.5);
        scene.add(mainLight);

        // Body Group
        const bodyGroup = new THREE.Group();
        scene.add(bodyGroup);

        function createModel(gender, scale) {
            bodyGroup.clear();
            
            const material = new THREE.MeshPhongMaterial({ 
                color: 0x000000,
                shininess: 10,
                specular: 0x111111
            });
            const fatFactor = (scale - 1) * 1.5;
            const widthScale = 1 + fatFactor;
            
            // --- TRUNK (Advanced Anatomical Lathe) ---
            const bodySegments = 60;
            const bodyPoints = [];
            
            for (let i = 0; i <= bodySegments; i++) {
                const t = i / bodySegments;
                let r = 0;
                let y = (2.0 - t * 2.2); 
                
                if (t < 0.05) { // Neck
                    r = 0.12;
                } else if (t < 0.15) { // Shoulders/Trapezius
                    const st = (t - 0.05) / 0.1;
                    const shoulderR = gender === 'male' ? 0.65 : 0.52;
                    r = 0.12 + (shoulderR - 0.12) * Math.pow(Math.sin(st * Math.PI / 2), 0.5);
                } else if (t < 0.4) { // Ribcage (Thorax)
                    const ct = (t - 0.15) / 0.25;
                    const shoulderR = gender === 'male' ? 0.65 : 0.52;
                    const waistR = (gender === 'female' ? 0.35 : 0.48) * (1 + fatFactor * 0.4);
                    r = shoulderR - (shoulderR - waistR) * Math.sin(ct * Math.PI / 2);
                    // Add pectoral/chest volume for male
                    if (gender === 'male' && ct < 0.5) r += 0.05 * Math.sin(ct * Math.PI);
                } else if (t < 0.6) { // Waist/Abdomen (The Morphing Zone)
                    const wt = (t - 0.4) / 0.2;
                    const waistR = (gender === 'female' ? 0.35 : 0.48) * (1 + fatFactor * 0.4);
                    const hipR = (gender === 'female' ? 0.65 : 0.55) * widthScale;
                    r = waistR + (hipR - waistR) * Math.sin(wt * Math.PI / 2);
                    // Belly fat volume
                    if (scale > 1) {
                        r += fatFactor * 0.5 * Math.sin(wt * Math.PI);
                    }
                } else { // Pelvis to Crotch
                    const pt = (t - 0.6) / 0.4;
                    const hipR = (gender === 'female' ? 0.65 : 0.55) * widthScale;
                    r = hipR * (1 - pt * 0.3);
                }
                
                bodyPoints.push(new THREE.Vector2(r, y));
            }
            
            const bodyG = new THREE.LatheGeometry(bodyPoints, 40);
            const body = new THREE.Mesh(bodyG, material);
            bodyGroup.add(body);

            // --- HEAD ---
            const headG = new THREE.SphereGeometry(0.3, 32, 32);
            const head = new THREE.Mesh(headG, material);
            head.position.y = 2.25;
            head.scale.set(1, 1.3, 0.95);
            bodyGroup.add(head);

            // --- ARMS (Muscular taper) ---
            function createArm(isLeft) {
                const armPoints = [];
                const armSegments = 30;
                for (let i = 0; i <= armSegments; i++) {
                    const t = i / armSegments;
                    let r = 0;
                    if (t < 0.4) r = 0.15 - t * 0.05; // Bicep area
                    else if (t < 0.9) r = 0.1 - (t - 0.4) * 0.04; // Forearm
                    else r = 0.06; // Wrist
                    
                    r *= (1 + fatFactor * 0.15);
                    armPoints.push(new THREE.Vector2(r, -t * 1.4));
                }
                const armG = new THREE.LatheGeometry(armPoints, 20);
                const arm = new THREE.Mesh(armG, material);
                
                const shoulderWidth = (gender === 'male' ? 0.65 : 0.52) * (1 + fatFactor * 0.2);
                arm.position.set(isLeft ? -shoulderWidth : shoulderWidth, 1.85, 0);
                arm.rotation.z = isLeft ? 0.1 : -0.1;
                
                // Hand
                const hand = new THREE.Mesh(new THREE.SphereGeometry(0.09, 16, 16), material);
                hand.position.y = -1.45;
                hand.scale.set(0.7, 1.3, 0.5);
                arm.add(hand);
                
                return arm;
            }
            bodyGroup.add(createArm(true));
            bodyGroup.add(createArm(false));

            // --- LEGS (Anatomical taper) ---
            function createLeg(isLeft) {
                const legPoints = [];
                const legSegments = 40;
                for (let i = 0; i <= legSegments; i++) {
                    const t = i / legSegments;
                    let r = 0;
                    if (t < 0.4) r = 0.3 - t * 0.12; // Thigh
                    else if (t < 0.8) r = 0.22 - (t - 0.4) * 0.1; // Calf
                    else r = 0.14 - (t - 0.8) * 0.4; // Ankle
                    
                    r *= widthScale;
                    legPoints.push(new THREE.Vector2(r, -t * 2.0));
                }
                const legG = new THREE.LatheGeometry(legPoints, 20);
                const leg = new THREE.Mesh(legG, material);
                
                const hipPos = (gender === 'female' ? 0.38 : 0.32) * widthScale;
                leg.position.set(isLeft ? -hipPos : hipPos, 0.2, 0);
                
                // Foot
                const foot = new THREE.Mesh(new THREE.BoxGeometry(0.24, 0.12, 0.5), material);
                foot.position.set(0, -2.05, 0.15);
                leg.add(foot);
                
                return leg;
            }
            bodyGroup.add(createLeg(true));
            bodyGroup.add(createLeg(false));
        }

        createModel(currentGender, scaleFactor);

        function animate() {
            requestAnimationFrame(animate);
            // Rotate slightly for 3D effect but keep mostly front-facing like the image
            bodyGroup.rotation.y = Math.sin(Date.now() * 0.001) * 0.2;
            renderer.render(scene, camera);
        }
        animate();

        return {
            updateGender: (gender, scale) => {
                currentGender = gender;
                createModel(gender, scale);
            }
        };
    }

    // Initialize the two views
    const burnRate = simulationData.burn_rate;
    const kcalPerKg = 7700;
    const projectedDelta = (burnRate * 90) / kcalPerKg;
    const futureScale = 1.0 + (projectedDelta / 20);

    const viewCurrent = init3D('canvas-current', 1.0);
    const viewFuture = init3D('canvas-future', Math.max(0.6, Math.min(1.6, futureScale)));

    // Controls
    document.querySelectorAll('.btn-3d-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.btn-3d-toggle').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentGender = btn.dataset.gender;
            
            viewCurrent.updateGender(currentGender, 1.0);
            viewFuture.updateGender(currentGender, Math.max(0.6, Math.min(1.6, futureScale)));
        });
    });
});
</script>

<?php require __DIR__ . '/layouts/footer.php'; ?>
