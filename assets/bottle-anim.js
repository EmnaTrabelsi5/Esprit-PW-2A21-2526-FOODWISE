// Script pour animer le niveau d'eau de la bouteille SVG selon la consommation réelle
// Animation fluide et professionnelle

document.addEventListener('DOMContentLoaded', function () {
  const svg = document.getElementById('bottle-svg');
  const liquid = document.getElementById('bottle-liquid');
  
  if (!svg || !liquid) return;

  // Récupérer les données
  const totalWater = parseFloat(svg.dataset.totalWater) || 0;
  const objectif = parseFloat(svg.dataset.objectif) || 2000;

  // Fade-in animation
  svg.style.opacity = '0';
  svg.style.transition = 'opacity 1.5s cubic-bezier(0.16, 1, 0.3, 1)';
  
  requestAnimationFrame(() => {
    svg.style.opacity = '1';
  });

  // Calculs
  const percent = Math.min(1, Math.max(0, totalWater / objectif));
  const yBase = 365;
  const yTop = 102;
  const yLevel = yBase - (yBase - yTop) * percent;
  
  // Paramètres de vague sophistiqués
  const baseAmplitude = 6 + (1 - percent) * 5;
  let startTime = Date.now();

  // Animation fluide avec RequestAnimationFrame
  function animateWave() {
    const elapsed = Date.now() - startTime;
    const progress = (elapsed % 3500) / 3500;
    
    // Deux ondes pour plus de complexité
    const phase1 = progress * Math.PI * 2;
    const phase2 = progress * Math.PI * 2 * 0.73;
    
    // Amplitude modulée
    const modulatedAmplitude = baseAmplitude * (0.75 + 0.25 * Math.sin(phase1 * 0.5));
    
    // Calcul des points de la vague avec Bézier
    const wave1 = Math.sin(phase1) * modulatedAmplitude;
    const wave2 = Math.cos(phase2 * 1.4) * modulatedAmplitude * 0.8;
    const wave3 = Math.sin(phase1 * 0.6) * modulatedAmplitude * 0.6;
    
    // Construction du chemin SVG avec courbes fluides
    const path = `M 62 ${yLevel + wave1}
      C 72 ${yLevel - modulatedAmplitude * 0.9} 82 ${yLevel + wave2} 90 ${yLevel - wave3}
      C 100 ${yLevel + modulatedAmplitude * 0.7} 110 ${yLevel - wave1 * 0.8} 118 ${yLevel + wave1}
      L 118 ${yBase}
      Q 118 ${yBase + 15} 106 ${yBase + 18}
      Q 90 ${yBase + 20} 74 ${yBase + 18}
      Q 62 ${yBase + 15} 62 ${yBase}
      Z`;

    liquid.setAttribute('d', path);
    requestAnimationFrame(animateWave);
  }

  // Démarrer l'animation
  animateWave();
});
