document.addEventListener("DOMContentLoaded", () => {
  fetch('productos.json?v=' + new Date().getTime())
    .then(response => response.json())
    .then(data => {
      const contenedorProductos = document.getElementById("contenedor-productos");
      if (contenedorProductos) {
        renderProductos(data.categorias_productos, contenedorProductos);
      }

      const contenedorEntemporada = document.getElementById("contenedor-entemporada");
      if (contenedorEntemporada) {
        const urlParams = new URLSearchParams(window.location.search);
        const requestedSeason = urlParams.get('season');
        renderEntemporada(data.categorias_entemporada, contenedorEntemporada, requestedSeason);
      }
    })
    .catch(error => console.error("Error cargando los productos:", error));
});

function renderProductos(categorias, contenedor) {
  let html = "";
  categorias.forEach(categoria => {
    html += `
      <section id="${categoria.id}" class="product-section fade-in">
        <h2>${categoria.titulo}</h2>
        ${categoria.subtitulo ? `<p style="margin-bottom: 25px; color: var(--secondary-color);">${categoria.subtitulo}</p>` : ''}
        <div class="product-grid">
    `;

    categoria.productos.forEach(producto => {
      html += `
        <div class="product-card">
          <img src="${producto.imagen}" alt="${producto.alt}" onclick="openLightbox(this.src)" style="cursor: pointer;">
          <div class="product-info">
            <h4>${producto.nombre}</h4>
            <p>${producto.descripcion}</p>
            <span class="product-price">$${producto.precio.toFixed(2)}</span>
          </div>
        </div>
      `;
    });

    html += `</div></section>`;
  });
  contenedor.innerHTML = html;
}

function renderEntemporada(categorias, contenedor, requestedSeason) {
  let html = `<h2 class="section-title fade-in" style="font-size: 2.2rem;">Échale un vistazo a nuestros productos del momento</h2>`;
  
  let categoriasAMostrar = [];

  if (requestedSeason) {
    const cat = categorias.find(c => c.id === requestedSeason);
    if (cat) categoriasAMostrar.push(cat);
  } else {
    categoriasAMostrar = categorias.filter(c => c.mostrar_en_inicio !== false);
    // Si no hay ninguna seleccionada explícitamente, mostrar la primera por defecto
    if (categoriasAMostrar.length === 0 && categorias.length > 0) {
      categoriasAMostrar.push(categorias[0]);
    }
  }

  if (categoriasAMostrar.length > 0) {
    categoriasAMostrar.forEach(categoria => {
      html += `
        <div class="subcategoria fade-in" id="${categoria.id}">
          <div class="season-header">
            <img src="${categoria.imagen || 'img/navidad.jpg'}" alt="${categoria.titulo}" class="season-header-img">
            <h3 class="season-header-title">${categoria.titulo}</h3>
          </div>
          <div class="product-grid">
      `;

      categoria.productos.forEach(producto => {
        html += `
          <div class="product-card">
            <img src="${producto.imagen}" alt="${producto.alt}" onclick="openLightbox(this.src)" style="cursor: pointer;">
            <div class="product-info">
              <h4>${producto.nombre}</h4>
              <p>${producto.descripcion}</p>
              <span class="product-price">$${producto.precio.toFixed(2)}</span>
            </div>
          </div>
        `;
      });

      html += `</div></div>`;
    });
  } else {
    html += `<p class="section-subtitle">Pronto tendremos nuevos productos para este momento.</p>`;
  }
  
  contenedor.innerHTML = html;
}
