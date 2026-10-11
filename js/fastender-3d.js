/**
 * Fastender 3D Showcase Engine (js/fastender-3d.js)
 * Mengonversi PNG (Logo/Mockup/Apparel) ke Model 3D dengan WebGL (Three.js)
 * Berotasi pelan secara otomatis dan mendukung interaksi drag/touch 360 derajat.
 */

class FastTender3DViewer {
  constructor(containerId, options = {}) {
    this.container = document.getElementById(containerId);
    if (!this.container) {
      console.warn("Fastender3D: Container #" + containerId + " tidak ditemukan.");
      return;
    }

    this.options = Object.assign({
      imageUrl: 'assets/images/logo.png',
      depth: 6,
      rotationSpeed: 0.006,
      autoRotate: true,
      metalness: 0.35,
      roughness: 0.25,
      rimColor: 0x1e3a8a,      // Biru Navy
      accentColor: 0x619ae6,   // Biru Fastender
      enableParticles: false,  // Bersih tanpa partikel mengambang agar putih bersih
      fov: 45
    }, options);

    this.scene = null;
    this.camera = null;
    this.renderer = null;
    this.modelGroup = null;
    this.mesh = null;
    this.particles = null;
    this.animationId = null;

    // State interaksi mouse / touch drag
    this.isDragging = false;
    this.previousMousePosition = { x: 0, y: 0 };
    this.targetRotationY = 0;
    this.targetRotationX = 0;
    this.isPaused = false;
    this.clock = null;

    this.init();
  }

  init() {
    // Pastikan THREE tersedia
    if (typeof THREE === 'undefined') {
      console.error("Three.js belum dimuat! Pastikan three.min.js sudah ter-include.");
      return;
    }

    this.clock = new THREE.Clock();
    const width = this.container.clientWidth || 360;
    const height = this.container.clientHeight || 360;

    // 1. Scene
    this.scene = new THREE.Scene();

    // 2. Camera
    this.camera = new THREE.PerspectiveCamera(this.options.fov, width / height, 0.1, 1000);
    this.camera.position.set(0, 0, 7.5);

    // 3. Renderer dengan transparansi & antialiasing
    this.renderer = new THREE.WebGLRenderer({
      alpha: true,
      antialias: true,
      powerPreference: "high-performance"
    });
    this.renderer.setSize(width, height);
    this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    this.renderer.shadowMap.enabled = true;
    this.renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    // Kosongkan container lalu masukkan canvas
    this.container.innerHTML = '';
    this.container.appendChild(this.renderer.domElement);

    // 4. Tata Cahaya (Lighting Studio Profesional)
    this.setupLighting();

    // 5. Model Container Group
    this.modelGroup = new THREE.Group();
    this.scene.add(this.modelGroup);

    // 6. Buat Partikel Mengambang (Aura Mewah)
    if (this.options.enableParticles) {
      this.createParticles();
    }

    // 7. Muat Tekstur Gambar PNG & Bangun 3D Mesh
    this.loadPNGTo3D(this.options.imageUrl);

    // 8. Event Listener (Resize & Drag)
    this.setupEvents();

    // 9. Render Loop
    this.animate = this.animate.bind(this);
    this.animate();
  }

  setupLighting() {
    // Cahaya Ambient lembut
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.75);
    this.scene.add(ambientLight);

    // Key Light (Cahaya Utama Putih Hangat dari Kanan Atas)
    const keyLight = new THREE.DirectionalLight(0xffffff, 1.2);
    keyLight.position.set(5, 6, 6);
    this.scene.add(keyLight);

    // Accent Light (Warna Aksen Fastender dari Kiri Bawah)
    const accentLight = new THREE.PointLight(this.options.accentColor, 1.8, 20);
    accentLight.position.set(-5, -3, 4);
    this.scene.add(accentLight);

    // Rim Light Biru Navy / Cyan (Memberi Efek Kilau Pinggiran)
    const rimLight = new THREE.PointLight(0x60a5fa, 1.5, 20);
    rimLight.position.set(4, -4, -4);
    this.scene.add(rimLight);

    // Back Light lembut
    const backLight = new THREE.DirectionalLight(0xffffff, 0.5);
    backLight.position.set(0, 4, -6);
    this.scene.add(backLight);
  }

  createParticles() {
    const particleCount = 28;
    const geometry = new THREE.BufferGeometry();
    const positions = new Float32Array(particleCount * 3);

    for (let i = 0; i < particleCount * 3; i += 3) {
      positions[i] = (Math.random() - 0.5) * 8;
      positions[i + 1] = (Math.random() - 0.5) * 8;
      positions[i + 2] = (Math.random() - 0.5) * 6;
    }

    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

    const material = new THREE.PointsMaterial({
      color: 0xf97316,
      size: 0.08,
      transparent: true,
      opacity: 0.6,
      blending: THREE.AdditiveBlending
    });

    this.particles = new THREE.Points(geometry, material);
    this.scene.add(this.particles);
  }

  loadPNGTo3D(imageUrl) {
    const loader = new THREE.TextureLoader();
    loader.setCrossOrigin('anonymous');

    loader.load(
      imageUrl,
      (texture) => {
        texture.generateMipmaps = true;
        texture.minFilter = THREE.LinearMipmapLinearFilter;
        texture.magFilter = THREE.LinearFilter;

        // Bersihkan mesh lama di modelGroup jika ada
        while (this.modelGroup.children.length > 0) {
          const obj = this.modelGroup.children[0];
          this.modelGroup.remove(obj);
          if (obj.geometry) obj.geometry.dispose();
          if (obj.material) {
            if (Array.isArray(obj.material)) obj.material.forEach(m => m.dispose());
            else obj.material.dispose();
          }
        }

        // Tentukan aspek rasio gambar
        const img = texture.image;
        const aspect = (img && img.width && img.height) ? (img.width / img.height) : 1;
        const radius = 1.8;
        const thickness = Math.max(0.08, (this.options.depth || 6) * 0.035);

        // Geometri 3D Berbentuk Badge Medallion Silinder Presisi
        // Segment 64 menghasilkan lengkungan bulat yang sangat mulus
        const geometry = new THREE.CylinderGeometry(radius, radius, thickness, 64, 1, false);

        // Putar silinder agar sisi muka menghadap kamera (+Z)
        geometry.rotateX(Math.PI / 2);

        // Material untuk Muka Depan (Texture PNG dengan transparansi tajam)
        const frontMaterial = new THREE.MeshStandardMaterial({
          map: texture,
          transparent: true,
          alphaTest: 0.05,
          metalness: this.options.metalness,
          roughness: this.options.roughness,
          side: THREE.FrontSide
        });

        // Material untuk Sisi Pinggiran (Bevel Logam Biru Navy / Metalik Glossy)
        const sideMaterial = new THREE.MeshStandardMaterial({
          color: 0x1e3a8a,
          metalness: 0.75,
          roughness: 0.2,
          emissive: 0x0f172a,
          emissiveIntensity: 0.2
        });

        // Material Belakang (Mirror Logo atau Plat Titanium Glossy)
        const backMaterial = new THREE.MeshStandardMaterial({
          map: texture,
          transparent: true,
          alphaTest: 0.05,
          metalness: this.options.metalness,
          roughness: this.options.roughness,
          side: THREE.BackSide
        });

        // Array Material: [0: Sisi Samping, 1: Muka Depan (+Z), 2: Muka Belakang (-Z)]
        const materials = [sideMaterial, frontMaterial, backMaterial];

        this.mesh = new THREE.Mesh(geometry, materials);
        this.mesh.castShadow = true;
        this.mesh.receiveShadow = true;

        // Tambahkan Cincin Aksen Oranye tipis di sekeliling 3D Model
        const ringGeo = new THREE.TorusGeometry(radius + 0.02, 0.03, 16, 64);
        const ringMat = new THREE.MeshStandardMaterial({
          color: 0xf97316,
          metalness: 0.85,
          roughness: 0.2,
          emissive: 0xf97316,
          emissiveIntensity: 0.25
        });
        const ringMesh = new THREE.Mesh(ringGeo, ringMat);
        this.mesh.add(ringMesh);

        // Sedikit miringkan awal agar kedalaman 3D langsung terlihat dramatis
        this.modelGroup.rotation.x = 0.15;
        this.modelGroup.rotation.y = 0.35;

        this.modelGroup.add(this.mesh);

        // Trigger custom event bahwa 3D sudah selesai dimuat
        if (typeof this.options.onLoaded === 'function') {
          this.options.onLoaded(this);
        }
      },
      undefined,
      (err) => {
        console.error("Gagal memuat tekstur PNG untuk model 3D:", err);
      }
    );
  }

  setupEvents() {
    const dom = this.renderer.domElement;

    // --- Resizing ---
    window.addEventListener('resize', () => {
      if (!this.container || !this.renderer || !this.camera) return;
      const width = this.container.clientWidth;
      const height = this.container.clientHeight;
      if (width && height) {
        this.camera.aspect = width / height;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(width, height);
      }
    });

    // --- Mouse Drag Interaction ---
    dom.addEventListener('mousedown', (e) => {
      this.isDragging = true;
      this.previousMousePosition = { x: e.clientX, y: e.clientY };
      dom.style.cursor = 'grabbing';
    });

    window.addEventListener('mousemove', (e) => {
      if (!this.isDragging || !this.modelGroup) return;
      const deltaX = e.clientX - this.previousMousePosition.x;
      const deltaY = e.clientY - this.previousMousePosition.y;

      this.modelGroup.rotation.y += deltaX * 0.01;
      this.modelGroup.rotation.x = Math.max(-0.6, Math.min(0.6, this.modelGroup.rotation.x + deltaY * 0.01));

      this.previousMousePosition = { x: e.clientX, y: e.clientY };
    });

    window.addEventListener('mouseup', () => {
      this.isDragging = false;
      if (dom) dom.style.cursor = 'grab';
    });

    // --- Touch Drag Interaction (Mobile Device) ---
    dom.addEventListener('touchstart', (e) => {
      if (e.touches.length === 1) {
        this.isDragging = true;
        this.previousMousePosition = { x: e.touches[0].clientX, y: e.touches[0].clientY };
      }
    }, { passive: true });

    dom.addEventListener('touchmove', (e) => {
      if (!this.isDragging || e.touches.length !== 1 || !this.modelGroup) return;
      const deltaX = e.touches[0].clientX - this.previousMousePosition.x;
      const deltaY = e.touches[0].clientY - this.previousMousePosition.y;

      this.modelGroup.rotation.y += deltaX * 0.012;
      this.modelGroup.rotation.x = Math.max(-0.6, Math.min(0.6, this.modelGroup.rotation.x + deltaY * 0.012));

      this.previousMousePosition = { x: e.touches[0].clientX, y: e.touches[0].clientY };
    }, { passive: true });

    dom.addEventListener('touchend', () => {
      this.isDragging = false;
    });

    dom.style.cursor = 'grab';
  }

  animate() {
    this.animationId = requestAnimationFrame(this.animate);

    const delta = this.clock.getDelta();
    const elapsedTime = this.clock.getElapsedTime();

    // Rotasi pelan berkelanjutan jika tidak sedang di-drag oleh user dan tidak di-pause
    if (this.modelGroup && !this.isDragging && !this.isPaused && this.options.autoRotate) {
      this.modelGroup.rotation.y += this.options.rotationSpeed;
      // Efek mengambang lembut (levitasi)
      this.modelGroup.position.y = Math.sin(elapsedTime * 1.6) * 0.12;
    }

    // Rotasi halus pada partikel background
    if (this.particles) {
      this.particles.rotation.y += 0.001;
      this.particles.rotation.x += 0.0005;
    }

    if (this.renderer && this.scene && this.camera) {
      this.renderer.render(this.scene, this.camera);
    }
  }

  // Method Publik untuk Update Gambar secara Instan (Drag & Drop)
  updateImage(newImageUrl, updatedOptions = {}) {
    if (updatedOptions) {
      Object.assign(this.options, updatedOptions);
    }
    this.options.imageUrl = newImageUrl;
    this.loadPNGTo3D(newImageUrl);
  }

  setRotationSpeed(speed) {
    this.options.rotationSpeed = parseFloat(speed) || 0.006;
  }

  setDepth(depth) {
    this.options.depth = parseFloat(depth) || 6;
    if (this.options.imageUrl) {
      this.loadPNGTo3D(this.options.imageUrl);
    }
  }

  togglePause() {
    this.isPaused = !this.isPaused;
    return this.isPaused;
  }

  resetRotation() {
    if (this.modelGroup) {
      this.modelGroup.rotation.set(0.15, 0.35, 0);
    }
  }

  destroy() {
    if (this.animationId) {
      cancelAnimationFrame(this.animationId);
    }
    if (this.renderer && this.renderer.domElement && this.renderer.domElement.parentNode) {
      this.renderer.domElement.parentNode.removeChild(this.renderer.domElement);
    }
  }
}

// Global window exposure
window.FastTender3DViewer = FastTender3DViewer;
window.Fastender3DViewer = FastTender3DViewer;
