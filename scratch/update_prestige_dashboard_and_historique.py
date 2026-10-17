import re

# Update dashboard.blade.php
with open('/home/ayoub/school-docs-saas/resources/views/portal/dashboard.blade.php', 'r') as f:
    dash = f.read()

# Replace neon colors in :root
dash = re.sub(r'--accent:\s*#06b6d4;', '--accent: #c5a059;\n            --gold: #c5a059;\n            --gold-light: #dfba73;\n            --gold-border: rgba(197, 160, 89, 0.3);', dash)
dash = re.sub(r'--secondary:\s*#8b5cf6;', '--secondary: #937338;', dash)
dash = re.sub(r'--accent-rgb:\s*6,\s*182,\s*212;', '--accent-rgb: 197, 160, 89;', dash)
dash = re.sub(r'--bg-deep:\s*#04050b;', '--bg-deep: #070a14;', dash)
dash = re.sub(r'--bg-card:\s*rgba\(12,\s*16,\s*38,\s*0\.78\);', '--bg-card: rgba(13, 19, 35, 0.88);', dash)

# Remove glowing ambient divs
dash = dash.replace('<div class="ambient-glow-1"></div>', '<div class="ambient-security-mask"></div>')
dash = dash.replace('<div class="ambient-glow-2"></div>', '')
dash = dash.replace('<div class="cursor-trail" id="cursor-trail"></div>', '')

# Remove laser anim in card and modal
dash = re.sub(r'<div class="card-laser-anim"></div>\s*', '', dash)
dash = re.sub(r'<div class="holo-glare"></div>\s*', '', dash)
dash = re.sub(r'<div class="holo-foil"></div>\s*', '', dash)

# Add ambient security mask CSS if not present
security_mask_css = """
        .ambient-security-mask {
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 50% 30%, transparent 40%, rgba(7, 10, 20, 0.75) 100%);
            pointer-events: none;
            z-index: 1;
        }
"""
dash = dash.replace('/* ── Header ── */', security_mask_css + '\n        /* ── Header ── */')

# Replace the Three.js script in dashboard.blade.php with Guilloche script
guilloche_script = """
        /* ── Fullscreen Three.js WebGL 3D Guilloché Security Matrix ── */
        (function initGuillocheSecurityMatrix() {
            if (typeof THREE === 'undefined') return;
            const canvas = document.getElementById('webgl-bg');
            if (!canvas) return;

            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(50, window.innerWidth / window.innerHeight, 1, 2000);
            camera.position.set(0, 0, 360);

            const renderer = new THREE.WebGLRenderer({
                canvas: canvas,
                alpha: true,
                antialias: true,
                powerPreference: 'high-performance'
            });
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));

            const goldColor = new THREE.Color(0xc5a059);
            const slateBlue = new THREE.Color(0x1e293b);
            const schoolColor = new THREE.Color('{{ $ecole->couleur_primaire ?? "#1e3a8a" }}');

            const ribbonCount = 16;
            const pointsPerRibbon = 130;
            const ribbons = [];

            for (let r = 0; r < ribbonCount; r++) {
                const geometry = new THREE.BufferGeometry();
                const positions = new Float32Array(pointsPerRibbon * 3);
                const colors = new Float32Array(pointsPerRibbon * 3);
                
                const isGold = (r % 3 === 0);
                const baseCol = isGold ? goldColor : (r % 2 === 0 ? schoolColor : slateBlue);
                const opacity = isGold ? 0.38 : 0.22;

                geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
                geometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));

                const material = new THREE.LineBasicMaterial({
                    vertexColors: true,
                    transparent: true,
                    opacity: opacity,
                    blending: THREE.NormalBlending
                });

                const line = new THREE.Line(geometry, material);
                scene.add(line);

                ribbons.push({
                    line: line,
                    geo: geometry,
                    offset: (r / ribbonCount) * Math.PI * 2,
                    baseY: (r - ribbonCount / 2) * 22,
                    z: -160 + (r * 18),
                    amp1: 40 + (r % 4) * 10,
                    amp2: 20 + (r % 3) * 8,
                    color: baseCol
                });
            }

            const watermarkGroup = new THREE.Group();
            watermarkGroup.position.set(0, 0, -200);
            scene.add(watermarkGroup);

            const ringGeo1 = new THREE.TorusGeometry(120, 0.7, 16, 120);
            const ringMat1 = new THREE.MeshBasicMaterial({ color: 0xc5a059, transparent: true, opacity: 0.12 });
            watermarkGroup.add(new THREE.Mesh(ringGeo1, ringMat1));

            const ringGeo2 = new THREE.TorusGeometry(85, 0.5, 16, 90);
            const ringMat2 = new THREE.MeshBasicMaterial({ color: 0xc5a059, transparent: true, opacity: 0.09 });
            watermarkGroup.add(new THREE.Mesh(ringGeo2, ringMat2));

            const rosettePetalCount = 36;
            const petalPoints = [];
            for (let p = 0; p <= 360; p += 3) {
                const rad = (p * Math.PI) / 180;
                const radius = 100 + Math.sin(rad * rosettePetalCount / 2) * 10;
                petalPoints.push(new THREE.Vector3(Math.cos(rad) * radius, Math.sin(rad) * radius, 0));
            }
            const rosetteGeo = new THREE.BufferGeometry().setFromPoints(petalPoints);
            const rosetteMat = new THREE.LineBasicMaterial({ color: 0xc5a059, transparent: true, opacity: 0.14 });
            watermarkGroup.add(new THREE.Line(rosetteGeo, rosetteMat));

            const fleckCount = 35;
            const fleckGeo = new THREE.BufferGeometry();
            const fleckPos = new Float32Array(fleckCount * 3);
            for (let i = 0; i < fleckCount; i++) {
                fleckPos[i * 3]     = (Math.random() - 0.5) * 800;
                fleckPos[i * 3 + 1] = (Math.random() - 0.5) * 600;
                fleckPos[i * 3 + 2] = (Math.random() - 0.5) * 350;
            }
            fleckGeo.setAttribute('position', new THREE.BufferAttribute(fleckPos, 3));
            const fleckMat = new THREE.PointsMaterial({
                color: 0xdfba73,
                size: 2.0,
                transparent: true,
                opacity: 0.4
            });
            const flecks = new THREE.Points(fleckGeo, fleckMat);
            scene.add(flecks);

            let targetX = 0, targetY = 0;
            let curX = 0, curY = 0;

            window.addEventListener('mousemove', (e) => {
                targetX = (e.clientX - window.innerWidth / 2) * 0.12;
                targetY = (e.clientY - window.innerHeight / 2) * 0.12;
            });

            window.addEventListener('resize', () => {
                camera.aspect = window.innerWidth / window.innerHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(window.innerWidth, window.innerHeight);
            });

            let time = 0;
            function animate() {
                requestAnimationFrame(animate);
                time += 0.01;

                curX += (targetX - curX) * 0.03;
                curY += (targetY - curY) * 0.03;

                camera.position.x = curX;
                camera.position.y = -curY;
                camera.lookAt(0, 0, 0);

                watermarkGroup.rotation.z += 0.0004;
                flecks.rotation.y += 0.0002;

                for (let r = 0; r < ribbons.length; r++) {
                    const rib = ribbons[r];
                    const pos = rib.geo.attributes.position.array;
                    const col = rib.geo.attributes.color.array;
                    const t = time + rib.offset;

                    for (let i = 0; i < pointsPerRibbon; i++) {
                        const u = (i / pointsPerRibbon) * 2 - 1;
                        const x = u * 420;
                        const y = rib.baseY + 
                                  Math.sin(u * 4.0 + t * 0.7) * rib.amp1 + 
                                  Math.cos(u * 8.0 - t * 0.4) * rib.amp2;
                        const z = rib.z + Math.sin(u * 3.0 + t) * 30;

                        pos[i * 3]     = x;
                        pos[i * 3 + 1] = y;
                        pos[i * 3 + 2] = z;

                        const edgeFade = 1.0 - Math.pow(Math.abs(u), 2.2);
                        col[i * 3]     = rib.color.r * edgeFade;
                        col[i * 3 + 1] = rib.color.g * edgeFade;
                        col[i * 3 + 2] = rib.color.b * edgeFade;
                    }
                    rib.geo.attributes.position.needsUpdate = true;
                    rib.geo.attributes.color.needsUpdate = true;
                }

                renderer.render(scene, camera);
            }
            animate();
        })();
"""

# Replace old initThreeBackground in dashboard
old_bg_pattern = r'/\* ── Fullscreen Three\.js WebGL Interactive 3D Background ── \*/[\s\S]*?animate\(\);\s*\}\)\(\);'
dash = re.sub(old_bg_pattern, guilloche_script, dash)

with open('/home/ayoub/school-docs-saas/resources/views/portal/dashboard.blade.php', 'w') as f:
    f.write(dash)

print("Updated dashboard.blade.php successfully!")

# Update historique.blade.php
with open('/home/ayoub/school-docs-saas/resources/views/portal/historique.blade.php', 'r') as f:
    histo = f.read()

histo = re.sub(r'--accent:\s*#06b6d4;', '--accent: #c5a059;\n            --gold: #c5a059;\n            --gold-light: #dfba73;\n            --gold-border: rgba(197, 160, 89, 0.3);', histo)
histo = re.sub(r'--bg-deep:\s*#04050b;', '--bg-deep: #070a14;', histo)
histo = re.sub(r'--bg-card:\s*rgba\(12,\s*16,\s*38,\s*0\.78\);', '--bg-card: rgba(13, 19, 35, 0.88);', histo)
histo = histo.replace('<div class="ambient-glow-1"></div>', '<div class="ambient-security-mask"></div>')
histo = histo.replace('<div class="ambient-glow-2"></div>', '')
histo = histo.replace('<div class="cursor-trail" id="cursor-trail"></div>', '')

# Replace old Three.js in historique
histo = re.sub(old_bg_pattern, guilloche_script, histo)

with open('/home/ayoub/school-docs-saas/resources/views/portal/historique.blade.php', 'w') as f:
    f.write(histo)

print("Updated historique.blade.php successfully!")
