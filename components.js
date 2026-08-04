const headerHTML = `
  <header class="header">
    <a href="/">
      <img src="img/logonew.png" alt="nuestro logo" class="header-logo">
    </a>
    
    <div class="header-text">
      <h1 class="header-title">Plushie Crochet</h1>
      <span class="header-subtitle">PTY</span>
      <p class="header-desc">Tejidos personalizados | Encuentra el regalo ideal</p>
    </div>

    <!-- Botón hamburguesa -->
    <button onclick="toggleMenu()" class="menu-button" id="menuButton" aria-label="Abrir menú">
      ☰
    </button>

    <!-- Menú -->
    <div id="navLinks" class="nav-links">
      <nav id="menu" class="nav-menu">
        <a href="/">Inicio</a>
        <a href="/entemporada">Del momento</a>
        <a href="/productos">Productos</a>
        <a href="/historia">Historia</a>
        <a href="/contacto">Contacto</a>
      </nav>
    </div>
  </header>
`;

const footerHTML = `
  <footer class="footer">
    <div class="footer-overlay">
      <div id="contacto">
          <h4>Contáctanos y síguenos en nuestras redes:</h4>
          
          <div class="social-links">
              <a href="https://www.instagram.com/plushiecrochetpty/" class="social-link" target="_blank" rel="noopener noreferrer">
                  <img src="img/instagramlogo.webp" alt="Instagram"> plushiecrochetpty
              </a>
              
              <a href="https://wa.me/50768691897" class="social-link" target="_blank" rel="noopener noreferrer">
                  <img src="img/whatsapplogo.png" alt="WhatsApp"> +507 6869-1897
              </a>

              <a href="https://www.facebook.com/plushiecrochetpty" class="social-link" target="_blank" rel="noopener noreferrer">
                  <img src="img/facebook-logo.png" alt="Facebook"> Facebook
              </a>
          </div>
          
          <p style="font-size: 0.95rem; color: var(--secondary-color);">
              © 2026 Plushie Crochet. Todos los derechos reservados.
          </p>
      </div>
    </div>
  </footer>
`;

function renderHeader() {
  document.write(headerHTML);
}

function renderFooter() {
  document.write(footerHTML);
  // Botón flotante de WhatsApp
  document.write(`
    <a href="https://wa.me/50768691897" class="whatsapp-float" target="_blank" rel="noopener noreferrer" aria-label="Chatear por WhatsApp">
      <img src="img/whatsapplogo.png" alt="WhatsApp">
    </a>
  `);
}

function toggleMenu() {
  var nav = document.getElementById("navLinks");
  if (nav) {
    nav.classList.toggle("show");
  }
}

// ── Lightbox para imágenes ──
document.addEventListener('DOMContentLoaded', () => {
  const lightboxHTML = `
    <div id="image-lightbox" class="lightbox-overlay" onclick="closeLightbox()">
      <span class="lightbox-close">&times;</span>
      <img id="lightbox-img" src="" alt="Vista ampliada">
    </div>
  `;
  document.body.insertAdjacentHTML('beforeend', lightboxHTML);

  // Cerrar al presionar la tecla Esc
  document.addEventListener('keydown', (e) => {
    if (e.key === "Escape") {
      closeLightbox();
    }
  });
});

function openLightbox(src) {
  const lb = document.getElementById('image-lightbox');
  const img = document.getElementById('lightbox-img');
  if (lb && img) {
    img.src = src;
    lb.classList.add('active');
  }
}

function closeLightbox() {
  const lb = document.getElementById('image-lightbox');
  if (lb) {
    lb.classList.remove('active');
  }
}

