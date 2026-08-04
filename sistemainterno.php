<?php
session_start();
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /login');
    exit;
}
$csrf_token = $_SESSION['csrf_token'] ?? '';
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token) ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema Interno | Plushie Crochet</title>
  <link rel="icon" href="img/logonew.png" type="image/png">
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- jQuery + DataTables -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link  rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
  <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

  <style>
    :root {
      --primary: #A0522D;
      --primary-light: #d48a66;
      --primary-gradient: linear-gradient(135deg, #A0522D 0%, #d35400 100%);
      --bg: #fdf5eb;
      --card-bg: #ffffff;
      --text: #333333;
      --text-light: #777777;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Quicksand', sans-serif;
      background-color: var(--bg);
      color: var(--text);
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar */
    aside {
      width: 220px;
      min-width: 220px;
      background: var(--card-bg);
      padding: 30px 20px;
      box-shadow: 2px 0 15px rgba(0, 0, 0, 0.05);
      display: flex;
      flex-direction: column;
      gap: 30px;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow-y: auto;
    }

    .brand {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
    }

    .brand img {
      width: 100px;
      margin-bottom: 10px;
    }

    .brand h2 {
      color: var(--primary);
      font-size: 22px;
    }

    .nav-items {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .nav-item {
      padding: 12px 15px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      color: var(--text-light);
      transition: all 0.3s ease;
    }

    .nav-item.active,
    .nav-item:hover {
      background: rgba(160, 82, 45, 0.1);
      color: var(--primary);
    }

    /* Main Content */
    main {
      flex: 1;
      padding: 30px 40px;
      overflow-y: auto;
      min-width: 0;
    }

    h1 {
      font-size: 2rem;
      margin-bottom: 24px;
      color: var(--primary);
    }

    /* Panel de herramientas colapsable */
    details.tools-panel {
      margin-bottom: 30px;
    }

    details.tools-panel > summary {
      list-style: none;
      cursor: pointer;
      user-select: none;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 14px 20px;
      background: var(--card-bg);
      border-radius: 16px;
      border: 1px solid rgba(160,82,45,0.08);
      box-shadow: 0 4px 15px rgba(160,82,45,0.04);
      color: var(--primary);
      font-weight: 700;
      font-size: 1.05rem;
      transition: background 0.2s;
    }

    details.tools-panel > summary:hover {
      background: #fdf0e6;
    }

    details.tools-panel > summary::after {
      content: '▼';
      margin-left: auto;
      font-size: 0.8rem;
      transition: transform 0.3s;
    }

    details.tools-panel[open] > summary::after {
      transform: rotate(180deg);
    }

    details.tools-panel > summary::-webkit-details-marker { display: none; }

    .tools-content {
      padding-top: 20px;
    }

    .grid-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
    }

    .card {
      background: var(--card-bg);
      border-radius: 24px;
      padding: 35px;
      box-shadow: 0 10px 30px rgba(160, 82, 45, 0.05);
      border: 1px solid rgba(160, 82, 45, 0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(160, 82, 45, 0.1);
    }

    .card h3 {
      font-size: 1.5rem;
      margin-bottom: 20px;
      color: #222;
      border-bottom: 2px solid rgba(160, 82, 45, 0.1);
      padding-bottom: 10px;
    }

    /* Forms */
    .form-group {
      margin-bottom: 20px;
    }

    label {
      display: block;
      font-weight: 600;
      margin-bottom: 8px;
      color: #555;
      font-size: 0.95rem;
    }

    input,
    select,
    textarea {
      width: 100%;
      padding: 14px 18px;
      border-radius: 12px;
      border: 2px solid #eee;
      font-family: 'Quicksand', sans-serif;
      font-size: 1rem;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
      background: #fafafa;
    }

    input:focus,
    select:focus,
    textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(160, 82, 45, 0.1);
      background: #fff;
    }

    textarea {
      resize: vertical;
      min-height: 100px;
    }

    .btn {
      background: var(--primary-gradient);
      color: #fff;
      border: none;
      padding: 14px 24px;
      border-radius: 50px;
      font-size: 1.05rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
      width: 100%;
      font-family: 'Quicksand', sans-serif;
      box-shadow: 0 4px 15px rgba(160, 82, 45, 0.2);
    }

    .btn:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 25px rgba(160, 82, 45, 0.35);
    }
    
    .btn:active {
      transform: translateY(1px);
      box-shadow: 0 2px 10px rgba(160, 82, 45, 0.2);
    }

    /* Custom File Upload */
    .file-upload-wrapper {
      position: relative;
      width: 100%;
      height: 150px;
      border: 2px dashed #ddd;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      cursor: pointer;
      background: #fafafa;
      transition: all 0.3s ease;
    }

    .file-upload-wrapper:hover {
      border-color: var(--primary);
      background: rgba(160, 82, 45, 0.05);
    }

    .file-upload-wrapper input[type="file"] {
      position: absolute;
      width: 100%;
      height: 100%;
      opacity: 0;
      cursor: pointer;
    }

    .file-upload-icon {
      font-size: 2rem;
      color: var(--primary);
      margin-bottom: 10px;
    }

    #preview-image {
      max-height: 130px;
      border-radius: 8px;
      display: none;
      margin-top: 10px;
      object-fit: cover;
    }

    /* === DataTables override theming === */
    div.dataTables_wrapper {
      font-family: 'Quicksand', sans-serif;
      font-size: 0.95rem;
    }

    /* Search box del DT — lo ocultamos, usamos el nuestro */
    div.dataTables_filter { display: none !important; }

    div.dataTables_length select {
      padding: 6px 12px;
      border-radius: 50px;
      border: 2px solid #eee;
      font-family: 'Quicksand', sans-serif;
      font-size: 0.9rem;
      background: #fafafa;
    }

    div.dataTables_info {
      color: var(--text-light);
      font-size: 0.88rem;
      padding-top: 10px;
    }

    div.dataTables_paginate .paginate_button {
      border-radius: 50px !important;
      font-family: 'Quicksand', sans-serif !important;
      font-weight: 600 !important;
      border: none !important;
      margin: 0 3px;
    }

    div.dataTables_paginate .paginate_button.current,
    div.dataTables_paginate .paginate_button.current:hover {
      background: var(--primary-gradient) !important;
      color: #fff !important;
      border: none !important;
    }

    div.dataTables_paginate .paginate_button:hover {
      background: #fdf0e6 !important;
      color: var(--primary) !important;
      border: none !important;
    }

    table.dataTable thead th {
      background: var(--bg);
      color: var(--primary);
      font-weight: 700;
      font-family: 'Quicksand', sans-serif;
      font-size: 0.95rem;
      padding: 14px 18px;
      border-bottom: 2px solid rgba(160,82,45,0.15) !important;
    }

    table.dataTable thead th.dt-orderable-asc span.dt-column-order::before,
    table.dataTable thead th.dt-orderable-desc span.dt-column-order::after {
      color: var(--primary-light);
    }

    table.dataTable tbody tr {
      border-bottom: 1px solid #f0ece8;
      transition: background 0.2s;
    }

    table.dataTable tbody tr:hover { background: #fdf9f6 !important; }

    table.dataTable tbody td {
      padding: 12px 18px;
      vertical-align: middle;
    }

    .prod-thumb {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 10px;
      border: 2px solid #eee;
    }

    .price-badge {
      background: var(--bg);
      color: var(--primary);
      font-weight: 700;
      padding: 4px 12px;
      border-radius: 20px;
      display: inline-block;
      font-size: 1rem;
    }

    .cat-badge {
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      white-space: nowrap;
    }
    .cat-badge.badge-productos { background: #e8f4fd; color: #1a73e8; }
    .cat-badge.badge-temporada { background: #fce4ec; color: #c2185b; }

    .action-btns { display: flex; gap: 8px; }

    .btn-sm {
      padding: 6px 14px;
      border-radius: 50px;
      border: none;
      cursor: pointer;
      font-family: 'Quicksand', sans-serif;
      font-weight: 700;
      font-size: 0.82rem;
      transition: all 0.25s;
      white-space: nowrap;
    }
    .btn-sm.btn-edit   { background: var(--primary-gradient); color:#fff; box-shadow:0 3px 10px rgba(160,82,45,.2); }
    .btn-sm.btn-edit:hover  { transform:translateY(-2px); box-shadow:0 6px 15px rgba(160,82,45,.3); }
    .btn-sm.btn-season { background: linear-gradient(135deg,#2ecc71,#27ae60); color:#fff; box-shadow:0 3px 10px rgba(46,204,113,.2); }
    .btn-sm.btn-season:hover{ transform:translateY(-2px); box-shadow:0 6px 15px rgba(46,204,113,.3); }

    #catalog-empty {
      text-align: center;
      padding: 40px;
      color: var(--text-light);
      font-size: 1rem;
    }

    /* Alerts (Toast) */
    .alert {
      position: fixed;
      bottom: 30px;
      right: 30px;
      padding: 18px 25px;
      border-radius: 16px;
      display: none;
      font-weight: 700;
      z-index: 99999;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
      animation: slideInUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
      opacity: 0;
    }

    @keyframes slideInUp {
      from { transform: translateY(40px) scale(0.9); opacity: 0; }
      to { transform: translateY(0) scale(1); opacity: 1; }
    }

    .alert.success {
      background: #d4edda;
      color: #155724;
      display: block;
      border-left: 5px solid #28a745;
    }

    .alert.error {
      background: #f8d7da;
      color: #721c24;
      display: block;
      border-left: 5px solid #dc3545;
    }

    @media (max-width: 768px) {
      body {
        flex-direction: column;
      }

      aside {
        width: 100%;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      }

      .grid-container {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <div id="status-message" class="alert"></div>

  <!-- Sidebar -->
  <aside>
    <div class="brand">
      <img src="img/logonew.png" alt="Logo">
      <h2>Panel Admin</h2>
    </div>
    <div class="nav-items">
      <div class="nav-item active">Dashboard</div>
      <div class="nav-item" onclick="window.open('/', '_blank')">Ver Sitio Web ↗</div>
      <div class="nav-item" onclick="logout()" style="color: #c0392b; margin-top: 20px;">Cerrar Sesión</div>
    </div>
  </aside>

  <!-- Main Content -->
  <main>
    <h1>🧸 Catálogo de Productos</h1>

    <!-- ── TABLA: primer bloque ── -->
    <div class="card">
      <div style="display:flex; justify-content:flex-end; gap:12px; margin-bottom:16px; flex-wrap:wrap;">
        <input type="search" id="catalog-search" placeholder="🔍 Buscar..."
          style="width:220px; padding:8px 16px; border-radius:50px; border:2px solid #eee; font-family:'Quicksand',sans-serif; font-size:0.9rem; background:#fafafa; transition:border-color .3s;"
          onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#eee'">
        <select id="catalog-filter"
          style="width:200px; padding:8px 16px; border-radius:50px; border:2px solid #eee; font-family:'Quicksand',sans-serif; font-size:0.9rem; background:#fafafa; transition:border-color .3s;"
          onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#eee'">
          <option value="">Todas las categorías</option>
        </select>
      </div>
      <div style="overflow-x: auto;">
        <table id="catalog-dt" style="width:100%">
          <thead>
            <tr>
              <th>Foto</th>
              <th>Nombre</th>
              <th>Precio</th>
              <th>Categoría</th>
              <th>Tipo</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="catalog-tbody"></tbody>
        </table>
      </div>
    </div>

    <!-- ── PANEL DE HERRAMIENTAS (colapsable) ── -->
    <details class="tools-panel" style="margin-top: 30px;">
      <summary>⚙️ Herramientas de Administración</summary>
      <div class="tools-content">

        <div class="grid-container">


      <!-- Card 2: Agregar Producto -->
      <div class="card">
        <h3>🛍️ Agregar Nuevo Producto</h3>
        <form id="product-form" enctype="multipart/form-data">

          <div class="form-group">
            <label>Imagen del Producto</label>
            <div class="file-upload-wrapper" id="drop-area">
              <div class="file-upload-icon">📸</div>
              <span id="file-name">Haz clic o arrastra una imagen</span>
              <input type="file" id="product-image" name="image" accept="image/*" required>
            </div>
            <img id="preview-image" src="" alt="Vista previa">
          </div>

          <div class="form-group">
            <label for="product-name">Nombre del Producto</label>
            <input type="text" id="product-name" name="nombre" placeholder="Ej: Osito Navideño" required>
          </div>

          <div class="form-group">
            <label for="product-desc">Descripción corta</label>
            <textarea id="product-desc" name="descripcion"
              placeholder="Altura, materiales, u observaciones..."></textarea>
          </div>

          <div class="form-group">
            <label for="product-price">Precio ($)</label>
            <input type="number" id="product-price" name="precio" step="0.01" placeholder="Ej: 15.00" required>
          </div>

          <div class="form-group">
            <label for="product-category">¿A qué categoría pertenece?</label>
            <select id="product-category" name="categoria" required>
              <option value="">Cargando categorías...</option>
            </select>
          </div>

          <div class="form-group">
            <label>Agregar también a categorías Del Momento (Opcional):</label>
            <input type="hidden" name="seasons_submitted" value="1">
            <div id="add-product-seasons-container" style="background:#fafafa; padding:10px; border-radius:8px; border:1px solid #eee; display:flex; flex-direction:column; gap:5px;">
              <!-- checkboxes will be populated here -->
            </div>
          </div>

          <button type="submit" class="btn">Publicar Producto</button>
        </form>
      </div>

      <!-- Card 3: Crear Temporada -->
      <div class="card">
        <h3>🌸 Crear Nueva Categoría del Momento</h3>
        <p style="margin-bottom: 20px; color: var(--text-light); font-size: 0.95rem;">
          Crea una nueva categoría "del momento" con su foto de perfil. Luego podrás agregarle productos desde el catálogo.
        </p>
        <form id="create-season-form" enctype="multipart/form-data">

          <div class="form-group">
            <label>Foto de Perfil de la Categoría</label>
            <div class="file-upload-wrapper" id="season-drop-area">
              <div class="file-upload-icon" id="season-upload-icon">🖼️</div>
              <span id="season-file-name">Haz clic o arrastra una imagen</span>
              <input type="file" id="season-image" name="imagen_perfil" accept="image/*" required>
            </div>
            <img id="season-preview" src="" alt="Vista previa" style="max-height:130px; border-radius:8px; display:none; margin-top:10px; object-fit:cover;">
          </div>

          <div class="form-group">
            <label for="season-name">Nombre de la Temporada</label>
            <input type="text" id="season-name" name="titulo" placeholder="Ej: Halloween" required>
          </div>

          <div class="form-group">
            <label for="season-id">ID único (sin espacios ni acentos)</label>
            <input type="text" id="season-id" name="id" placeholder="Ej: halloween" required>
            <small style="color: var(--text-light); font-size:0.8rem; display:block; margin-top:5px;">Se generará automáticamente al escribir el nombre.</small>
          </div>

          <button type="submit" class="btn">✅ Crear Temporada</button>
        </form>
      </div>

      <!-- Card 4: Eliminar Temporada -->
      <div class="card">
        <h3>🗑️ Eliminar Temporada</h3>
        <p style="margin-bottom: 20px; color: var(--text-light); font-size: 0.95rem;">
          Elimina permanentemente una temporada y todos sus productos del catálogo. Esta acción no se puede deshacer.
        </p>
        <form id="delete-season-form">
          <div class="form-group">
            <label for="delete-season-select">Seleccionar Temporada a eliminar</label>
            <select id="delete-season-select" required>
              <option value="">Cargando...</option>
            </select>
          </div>

          <div id="season-preview-card" style="display:none; background:var(--bg); border-radius:12px; padding:15px; margin-bottom:20px; display:flex; align-items:center; gap:15px;">
            <img id="season-preview-img" src="" alt="Foto temporada" style="width:60px; height:60px; border-radius:50%; object-fit:cover; border:3px solid var(--primary);">
            <div>
              <strong id="season-preview-name" style="color:var(--primary); font-size:1.1rem;"></strong>
              <p id="season-preview-count" style="color:var(--text-light); font-size:0.85rem; margin:0;"></p>
            </div>
          </div>

          <button type="submit" class="btn" style="background:#c0392b;" id="delete-btn">🗑️ Eliminar Permanentemente</button>
        </form>
      </div>

      <!-- Card 5: Editar Temporada -->
      <div class="card">
        <h3>✏️ Editar Temporada</h3>
        <p style="margin-bottom: 20px; color: var(--text-light); font-size: 0.95rem;">
          Modifica el título o la imagen de una temporada existente.
        </p>
        <form id="edit-season-form" enctype="multipart/form-data">
          <div class="form-group">
            <label for="edit-season-select">Seleccionar Temporada a editar</label>
            <select id="edit-season-select" name="season_id" required>
              <option value="">Cargando...</option>
            </select>
          </div>

          <div class="form-group">
            <label>Cambiar Imagen (Opcional)</label>
            <input type="file" id="edit-season-image" name="imagen_perfil" accept="image/*">
            <img id="edit-season-preview-img" src="" alt="Actual" style="max-height:100px; border-radius:8px; display:none; margin-top:10px; object-fit:cover;">
          </div>

          <div class="form-group">
            <label for="edit-season-name">Nuevo Título</label>
            <input type="text" id="edit-season-name" name="titulo" required>
          </div>

          <button type="submit" class="btn" style="background:var(--primary-gradient);">💾 Guardar Cambios</button>
        </form>
      </div>

        <!-- Card: Crear Sección de Productos -->
        <div class="card">
          <h3>🗂️ Crear Sección de Productos</h3>
          <p style="margin-bottom: 20px; color: var(--text-light); font-size: 0.95rem;">
            Crea una nueva sección dentro del catálogo general (ej: "Llaveros", "Peluches grandes").
          </p>
          <form id="create-category-form">
            <div class="form-group">
              <label for="cat-name">Nombre de la Sección</label>
              <input type="text" id="cat-name" name="titulo" placeholder="Ej: 🐾 Peluches grandes" required>
            </div>
            <div class="form-group">
              <label for="cat-subtitle">Subtítulo (opcional)</label>
              <input type="text" id="cat-subtitle" name="subtitulo" placeholder="Ej: (Ideales para regalar)">
            </div>
            <div class="form-group">
              <label for="cat-id">ID único (sin espacios ni acentos)</label>
              <input type="text" id="cat-id" name="id" placeholder="Ej: peluches_grandes" required>
              <small style="color:var(--text-light);font-size:0.8rem;display:block;margin-top:5px;">Se genera automáticamente al escribir el nombre.</small>
            </div>
            <button type="submit" class="btn">✅ Crear Sección</button>
          </form>
        </div>

        <!-- Card: Eliminar Sección de Productos -->
        <div class="card">
          <h3>🗑️ Eliminar Sección de Productos</h3>
          <p style="margin-bottom: 20px; color: var(--text-light); font-size: 0.95rem;">
            Elimina permanentemente una sección y todos sus productos. Esta acción no se puede deshacer.
          </p>
          <form id="delete-category-form">
            <div class="form-group">
              <label for="delete-category-select">Seleccionar Sección a eliminar</label>
              <select id="delete-category-select" required>
                <option value="">Cargando...</option>
              </select>
            </div>
            <button type="submit" class="btn" style="background:#c0392b;">🗑️ Eliminar Sección</button>
          </form>
        </div>

        </div>

        <!-- Card: Editar Sección de Productos -->
        <div class="card">
          <h3>✏️ Editar Sección de Productos</h3>
          <p style="margin-bottom: 20px; color: var(--text-light); font-size: 0.95rem;">
            Modifica el nombre o subtítulo de una sección existente.
          </p>
          <form id="edit-category-form">
            <div class="form-group">
              <label for="edit-category-select">Seleccionar Sección</label>
              <select id="edit-category-select" name="category_id" required>
                <option value="">Cargando...</option>
              </select>
            </div>
            <div class="form-group">
              <label for="edit-cat-name">Nombre de la Sección</label>
              <input type="text" id="edit-cat-name" name="titulo" required>
            </div>
            <div class="form-group">
              <label for="edit-cat-subtitle">Subtítulo (opcional)</label>
              <input type="text" id="edit-cat-subtitle" name="subtitulo">
            </div>
            <button type="submit" class="btn" style="background:var(--primary-gradient);">💾 Guardar Cambios</button>
          </form>
        </div>

        </div> <!-- /grid-container -->

        <!-- Temporadas en Inicio -->
        <div class="card" style="margin-top: 30px;">
          <h3>🏡 Temporadas en la Página de Inicio</h3>
          <p style="margin-bottom: 20px; color: var(--text-light); font-size: 0.95rem;">
            Selecciona qué temporadas quieres que aparezcan como destacadas en la sección "En Temporada" de la página principal.
          </p>
          <div id="inicio-seasons-list" style="display:flex; flex-direction:column; gap:10px;">
            <p style="color:var(--text-light);">Cargando temporadas...</p>
          </div>
        </div>

        <!-- Reseñas -->
        <div class="card" style="margin-top: 30px;">
          <h3>⭐ Reseñas de Clientes</h3>
          <p style="margin-bottom: 20px; color: var(--text-light); font-size: 0.95rem;">
            Aprueba o rechaza las reseñas enviadas desde la página de Contacto. Solo las aprobadas aparecen en el inicio.
          </p>
          <div id="resenas-list">
            <p style="color:var(--text-light);">Cargando reseñas...</p>
          </div>
        </div>

        <!-- Buzón -->
        <div class="card" style="margin-top: 30px;">
          <h3>📬 Buzón de Sugerencias</h3>
          <p style="margin-bottom: 20px; color: var(--text-light); font-size: 0.95rem;">
            Mensajes enviados desde el buzón de sugerencias de la página de Contacto.
          </p>
          <div id="sugerencias-list">
            <p style="color:var(--text-light);">Cargando sugerencias...</p>
          </div>
        </div>

      </div> <!-- /tools-content -->
    </details> <!-- /tools-panel -->

    <!-- ── Paneles de acción inline (debajo de la tabla) ── -->
    <div id="inline-panels" style="display:none; margin-top: 24px;">
      <div style="display: flex; gap: 24px; flex-wrap: wrap; align-items: flex-start;">

        <!-- Panel Editar -->
        <div id="edit-panel" class="card" style="display:none; flex:1; min-width:320px;">
          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
            <h3 style="margin:0;">✏️ Editar Producto</h3>
            <button onclick="closeEditPanel()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#999;line-height:1;">&times;</button>
          </div>
          <form id="edit-product-form" enctype="multipart/form-data">
            <input type="hidden" id="edit-product-idx" name="product_idx">
            <input type="hidden" id="edit-product-categoria" name="categoria">
            <div class="form-group">
              <label>Cambiar Imagen (Opcional)</label>
              <input type="file" id="edit-product-image" name="image" accept="image/*">
              <img id="edit-preview-image" src="" alt="Actual" style="max-height:100px; border-radius:8px; display:block; margin-top:10px; object-fit:cover;">
            </div>
            <div class="form-group">
              <label>Nombre del Producto</label>
              <input type="text" id="edit-product-name" name="nombre" required>
            </div>
            <div class="form-group">
              <label>Descripción corta</label>
              <textarea id="edit-product-desc" name="descripcion" style="min-height:80px;"></textarea>
            </div>
            <div class="form-group">
              <label>Precio ($)</label>
              <input type="number" id="edit-product-price" name="precio" step="0.01" required>
            </div>
            <div class="form-group">
              <label>Mover a otra Sección (opcional)</label>
              <select id="edit-product-new-cat" name="nueva_categoria">
                <option value="">-- Mantener en la sección actual --</option>
              </select>
            </div>
            <div class="form-group" id="edit-season-checkboxes">
              <label>Presente en categorías Del Momento:</label>
              <input type="hidden" name="seasons_submitted" value="1">
              <div id="edit-product-seasons-container" style="background:#fafafa; padding:10px; border-radius:8px; border:1px solid #eee; display:flex; flex-direction:column; gap:5px;">
                <!-- checkboxes here -->
              </div>
            </div>
            <button type="submit" class="btn">Guardar Cambios</button>
          </form>
        </div>

        <!-- Panel Añadir a Temporada -->
        <div id="copy-season-panel" class="card" style="display:none; flex:1; min-width:280px;">
          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
            <h3 style="margin:0;">➕ Añadir a Temporada</h3>
            <button onclick="closeCopyPanel()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#999;line-height:1;">&times;</button>
          </div>
          <p style="font-size:0.9rem; color:var(--text-light); margin-bottom:20px;">
            Selecciona a qué temporada quieres enviar una copia de este producto.
          </p>
          <form id="copy-season-form">
            <input type="hidden" id="copy-source-cat" name="source_cat_id">
            <input type="hidden" id="copy-product-idx" name="product_idx">
            <div class="form-group">
              <label>Temporada de Destino</label>
              <select id="copy-target-season" name="target_season_id" required>
                <option value="">Cargando temporadas...</option>
              </select>
            </div>
            <button type="submit" class="btn" style="background:linear-gradient(135deg,#2ecc71,#27ae60);">Añadir a la Temporada</button>
          </form>
        </div>

      </div>
    </div>

  </main>



  <script>
    // Configuración CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function fetchApi(formData) {
      if (!formData.has('csrf_token')) {
        formData.append('csrf_token', csrfToken);
      }
      return fetch('api_admin.php', { method: 'POST', body: formData }).then(res => {
        if (res.status === 401) {
          window.location.href = '/login';
          throw new Error('No autorizado');
        }
        return res;
      });
    }

    function logout() {
      const fd = new FormData();
      fd.append('action', 'logout');
      fetchApi(fd).then(() => {
        window.location.href = '/login';
      });
    }

    // Preview Image
    const fileInput = document.getElementById('product-image');
    const previewImage = document.getElementById('preview-image');
    const fileNameSpan = document.getElementById('file-name');
    const dropArea = document.getElementById('drop-area');

    fileInput.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        fileNameSpan.style.display = 'none';
        document.querySelector('.file-upload-icon').style.display = 'none';
        const reader = new FileReader();
        reader.onload = function (e) {
          previewImage.src = e.target.result;
          previewImage.style.display = 'block';
        }
        reader.readAsDataURL(file);
      }
    });

    // Load Data
    document.addEventListener("DOMContentLoaded", () => {
      fetch('productos.json?v=' + new Date().getTime())
        .then(res => res.json())
        .then(data => {
          window._seasonData = data;

          // Populate delete season select
          populateDeleteSelect(data);

          // Populate category select
          const catSelect = document.getElementById('product-category');
          catSelect.innerHTML = '<optgroup label="Sección Productos Base">';
          data.categorias_productos.forEach(cat => {
            catSelect.innerHTML += `<option value="productos|${cat.id}">[General] ${cat.titulo}</option>`;
          });
          catSelect.innerHTML += '</optgroup><optgroup label="Sección Del Momento">';
          data.categorias_entemporada.forEach(cat => {
            catSelect.innerHTML += `<option value="entemporada|${cat.id}">[Del Momento] ${cat.titulo}</option>`;
          });
          catSelect.innerHTML += '</optgroup>';

          populateAddSeasons(data);

          // Populate seasons visibility toggles
          populateInicioSeasons(data);

          // Populate category select (edit & delete)
          // (función definida más abajo, se llama tras DOMContentLoaded)
          setTimeout(() => { if (typeof populateCategorySelects === 'function') populateCategorySelects(data); }, 0);

          // Populate catalog
          renderCatalog(data);
        })
        .catch(err => console.error('Error cargando productos.json:', err));
    });

    // Cargar reseñas y sugerencias al iniciar
    loadResenas();
    loadSugerencias();

    let globalProductsData = null;

    function populateInicioSeasons(data) {
      const list = document.getElementById('inicio-seasons-list');
      list.innerHTML = '';
      if (!data.categorias_entemporada || data.categorias_entemporada.length === 0) {
        list.innerHTML = '<p style="color:var(--text-light);">No hay temporadas creadas.</p>';
        return;
      }
      data.categorias_entemporada.forEach(cat => {
        const isChecked = cat.mostrar_en_inicio !== false;
        
        list.innerHTML += `
          <label style="display:flex; align-items:center; gap:10px; background:var(--bg); padding:10px 15px; border-radius:8px; cursor:pointer; border:1px solid #eee;">
            <input type="checkbox" onchange="toggleInicioSeason('${cat.id}', this.checked)" ${isChecked ? 'checked' : ''} style="width:18px; height:18px; cursor:pointer;">
            <img src="${cat.imagen || 'img/navidad.jpg'}" alt="${cat.titulo}" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
            <strong>${cat.titulo}</strong>
          </label>
        `;
      });
    }

    function toggleInicioSeason(id, isChecked) {
      const fd = new FormData();
      fd.append('action', 'toggle_inicio_season');
      fd.append('season_id', id);
      fd.append('mostrar', isChecked ? '1' : '0');

      fetchApi(fd)
        .then(r => r.json())
        .then(res => showMessage(res.success, res.message))
        .catch(() => showMessage(false, 'Error al guardar la visibilidad.'));
    }

    // ── DataTable Catálogo ─────────────────────────────────────────────────────
    let allRows  = [];
    let catalogDT = null;

    function renderCatalog(data) {
      globalProductsData = data;
      allRows = [];

      data.categorias_productos.forEach(cat => {
        cat.productos.forEach((prod, idx) => {
          allRows.push({ prefix: 'productos', catId: cat.id, catTitulo: cat.titulo, idx, prod });
        });
      });
      data.categorias_entemporada.forEach(cat => {
        cat.productos.forEach((prod, idx) => {
          allRows.push({ prefix: 'entemporada', catId: cat.id, catTitulo: cat.titulo, idx, prod });
        });
      });

      // Llenar filtro de categorías
      const filterSel = document.getElementById('catalog-filter');
      filterSel.innerHTML = '<option value="">Todas las categorías</option>';
      [...data.categorias_productos.map(c => ({...c, tipo:'productos'})),
       ...data.categorias_entemporada.map(c => ({...c, tipo:'entemporada'}))]
      .forEach(c => {
        filterSel.innerHTML += `<option value="${c.tipo}|${c.id}">${c.titulo}</option>`;
      });

      // Construir filas para DataTables
      const rows = allRows.map(row => {
        const { prefix, catId, catTitulo, idx, prod } = row;
        const badgeClass = prefix === 'productos' ? 'badge-productos' : 'badge-temporada';
        const badgeLabel = prefix === 'productos' ? 'General' : 'Del Momento';
        const deleteBtn = `<button class="btn-sm" style="background:#e74c3c; color:white; margin-left:5px;" onclick="deleteProduct('${prefix}|${catId}',${idx})">🗑️ Eliminar</button>`;
        return [
          `<img class="prod-thumb" src="${prod.imagen}" alt="${prod.nombre}">`,
          prod.nombre,
          prod.precio,
          catTitulo,
          `<span class="cat-badge ${badgeClass}">${badgeLabel}</span>`,
          `<div class="action-btns">
            <button class="btn-sm btn-edit" onclick="openEditModal('${prefix}|${catId}',${idx})">✏️ Editar</button>
            ${deleteBtn}
          </div>`
        ];
      });

      if (catalogDT) {
        catalogDT.clear().rows.add(rows).draw();
      } else {
        catalogDT = $('#catalog-dt').DataTable({
          data: rows,
          pageLength: 9,
          lengthMenu: [[9, 18, 36, -1], [9, 18, 36, 'Todos']],
          language: {
            search:       'Buscar:',
            lengthMenu:   'Mostrar _MENU_ por página',
            info:         'Mostrando _START_–_END_ de _TOTAL_ productos',
            infoEmpty:    'Sin productos',
            paginate: { previous: '←', next: '→' },
            emptyTable:   'No hay productos registrados.'
          },
          columns: [
            { orderable: false, searchable: false },
            {},
            { render: d => `<span class="price-badge">$${Number(d).toFixed(2)}</span>` },
            {},
            { orderable: false, searchable: false },
            { orderable: false, searchable: false }
          ],
          order: [[1, 'asc']]
        });

        // Buscador propio → DataTables
        document.getElementById('catalog-search').addEventListener('input', function() {
          catalogDT.search(this.value).draw();
        });

        // Filtro categoría → busca en columna "Categoría"
        document.getElementById('catalog-filter').addEventListener('change', function() {
          const txt = this.options[this.selectedIndex].text;
          catalogDT.column(3).search(txt === 'Todas las categorías' ? '' : txt).draw();
        });
      }
    }


    function openEditModal(categoriaRaw, productIdx) {
      const [prefix, catId] = categoriaRaw.split('|');
      const targetArray = prefix === 'productos' ? globalProductsData.categorias_productos : globalProductsData.categorias_entemporada;
      const cat = targetArray.find(c => c.id === catId);
      if (!cat) return;
      const prod = cat.productos[productIdx];
      if (!prod) return;

      document.getElementById('edit-product-idx').value = productIdx;
      document.getElementById('edit-product-categoria').value = categoriaRaw;
      document.getElementById('edit-product-name').value = prod.nombre;
      document.getElementById('edit-product-desc').value = prod.descripcion !== '...' ? prod.descripcion : '';
      document.getElementById('edit-product-price').value = prod.precio;
      document.getElementById('edit-preview-image').src = prod.imagen;
      document.getElementById('edit-product-image').value = '';

      // Rellenar selector de sección destino (Solo Generales)
      const newCatSel = document.getElementById('edit-product-new-cat');
      newCatSel.innerHTML = '<option value="">-- Mantener en la sección actual --</option>';
      if (globalProductsData) {
        globalProductsData.categorias_productos.forEach(c => {
          if (!(prefix === 'productos' && c.id === catId)) {
            newCatSel.innerHTML += `<option value="productos|${c.id}">[General] ${c.titulo}</option>`;
          }
        });
      }

      // Rellenar checkboxes de temporadas
      const seasonsDiv = document.getElementById('edit-product-seasons-container');
      seasonsDiv.innerHTML = '';
      if (globalProductsData && globalProductsData.categorias_entemporada) {
        globalProductsData.categorias_entemporada.forEach(season => {
          const exists = season.productos.some(p => p.nombre === prod.nombre && p.imagen === prod.imagen);
          seasonsDiv.innerHTML += `
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
              <input type="checkbox" name="seasons[]" value="${season.id}" ${exists ? 'checked' : ''} style="width:auto; margin:0;">
              <span style="font-size:0.95rem;">${season.titulo}</span>
            </label>
          `;
        });
      }
      
      // Ocultar opciones si editamos desde una temporada
      document.getElementById('edit-season-checkboxes').style.display = prefix === 'entemporada' ? 'none' : 'block';

      // Mostrar panel inline
      document.getElementById('edit-panel').style.display = 'block';
      document.getElementById('copy-season-panel').style.display = 'none';
      document.getElementById('inline-panels').style.display = 'block';
      document.getElementById('inline-panels').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function closeEditPanel() {
      document.getElementById('edit-panel').style.display = 'none';
      checkInlinePanels();
    }

    // Mantener compatibilidad por si algo llama closeEditModal
    function closeEditModal() { closeEditPanel(); }

    function openCopyToSeasonModal(sourceCatId, productIdx) {
      document.getElementById('copy-source-cat').value = sourceCatId;
      document.getElementById('copy-product-idx').value = productIdx;

      const select = document.getElementById('copy-target-season');
      select.innerHTML = '<option value="">-- Elige una temporada --</option>';
      if (globalProductsData && globalProductsData.categorias_entemporada) {
        globalProductsData.categorias_entemporada.forEach(season => {
          const opt = document.createElement('option');
          opt.value = season.id;
          opt.textContent = season.titulo;
          select.appendChild(opt);
        });
      }

      // Mostrar panel inline
      document.getElementById('copy-season-panel').style.display = 'block';
      document.getElementById('edit-panel').style.display = 'none';
      document.getElementById('inline-panels').style.display = 'block';
      document.getElementById('inline-panels').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function closeCopyPanel() {
      document.getElementById('copy-season-panel').style.display = 'none';
      checkInlinePanels();
    }

    function checkInlinePanels() {
      const ep = document.getElementById('edit-panel').style.display !== 'none';
      const cp = document.getElementById('copy-season-panel').style.display !== 'none';
      document.getElementById('inline-panels').style.display = (ep || cp) ? 'block' : 'none';
    }

    // compatibilidad
    function closeCopyToSeasonModal() { closeCopyPanel(); }

    document.getElementById('copy-season-form').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      formData.append('action', 'copy_to_season');

      fetchApi(formData)
      .then(response => response.json())
      .then(data => {
        showMessage(data.success, data.message);
        if (data.success) {
          closeCopyPanel();
          setTimeout(() => window.location.reload(), 1500);
        }
      })
      .catch(error => showMessage(false, 'Error de red.'));
    });

    document.getElementById('edit-product-form').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      formData.append('action', 'edit_product');

      fetchApi(formData)
      .then(response => response.json())
      .then(data => {
        showMessage(data.success, data.message);
        if (data.success) {
          closeEditPanel();
          setTimeout(() => window.location.reload(), 1500);
        }
      })
      .catch(error => {
        showMessage(false, 'Error de red al actualizar el producto.');
      });
    });

    function deleteProduct(categoriaRaw, productIdx) {
      if (!confirm('¿Estás seguro de que deseas eliminar este producto?')) return;
      const fd = new FormData();
      fd.append('action', 'delete_product');
      fd.append('categoria', categoriaRaw);
      fd.append('product_idx', productIdx);

      fetchApi(fd)
        .then(res => res.json())
        .then(res => {
          showMessage(res.success, res.message);
          if (res.success) {
            setTimeout(() => window.location.reload(), 1500);
          }
        })
        .catch(err => showMessage(false, 'Error al eliminar producto.'));
    }

    function populateAddSeasons(data) {
      const addSeasonsDiv = document.getElementById('add-product-seasons-container');
      if (addSeasonsDiv) {
        addSeasonsDiv.innerHTML = '';
        if (data.categorias_entemporada) {
          data.categorias_entemporada.forEach(season => {
            addSeasonsDiv.innerHTML += `
              <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="seasons[]" value="${season.id}" style="width:auto; margin:0;">
                <span style="font-size:0.95rem;">${season.titulo}</span>
              </label>
            `;
          });
        }
      }
    }

    function populateDeleteSelect(data) {
      const deleteSelect = document.getElementById('delete-season-select');
      const editSelect = document.getElementById('edit-season-select');
      const optionsHTML = '<option value="">-- Elige una temporada --</option>' + 
        (data.categorias_entemporada || []).map(cat => `<option value="${cat.id}">${cat.titulo}</option>`).join('');

      deleteSelect.innerHTML = optionsHTML;
      editSelect.innerHTML = optionsHTML;
      deleteSelect.onchange = () => {
        const sel = data.categorias_entemporada.find(c => c.id === deleteSelect.value);
        const card = document.getElementById('season-preview-card');
        if (sel) {
          document.getElementById('season-preview-img').src = sel.imagen || '';
          document.getElementById('season-preview-name').textContent = sel.titulo;
          document.getElementById('season-preview-count').textContent =
            `${sel.productos.length} producto(s) en esta temporada`;
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      };
    }

    // Handle Season Form
    // Handle Product Form
    document.getElementById('product-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      formData.append('action', 'add_product');

      fetchApi(formData)
        .then(res => res.json())
        .then(res => {
          showMessage(res.success, res.message);
          if (res.success) {
            this.reset();
            previewImage.style.display = 'none';
            fileNameSpan.style.display = 'block';
            document.querySelector('.file-upload-icon').style.display = 'block';
            setTimeout(() => window.location.reload(), 1500);
          }
        })
        .catch(err => showMessage(false, "Error de red: Asegúrate de que PHP está corriendo."));
    });

    // Preview imagen de nueva temporada
    document.getElementById('season-image').addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        document.getElementById('season-file-name').style.display = 'none';
        document.getElementById('season-upload-icon').style.display = 'none';
        const reader = new FileReader();
        reader.onload = e => {
          const prev = document.getElementById('season-preview');
          prev.src = e.target.result;
          prev.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });

    // Preview imagen de editar temporada
    document.getElementById('edit-season-image').addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = e => {
          const prev = document.getElementById('edit-season-preview-img');
          prev.src = e.target.result;
          prev.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });

    // Llenar campos al seleccionar temporada para editar
    document.getElementById('edit-season-select').addEventListener('change', function() {
      const catId = this.value;
      if (!catId) {
        document.getElementById('edit-season-form').reset();
        document.getElementById('edit-season-preview-img').style.display = 'none';
        return;
      }
      const cat = (window._seasonData?.categorias_entemporada || []).find(c => c.id === catId);
      if (cat) {
        document.getElementById('edit-season-name').value = cat.titulo;
        if (cat.imagen) {
          const prev = document.getElementById('edit-season-preview-img');
          prev.src = cat.imagen;
          prev.style.display = 'block';
        }
      }
    });

    // Auto-generar ID desde el nombre
    document.getElementById('season-name').addEventListener('input', function () {
      const idField = document.getElementById('season-id');
      idField.value = this.value
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // quitar acentos
        .replace(/\s+/g, '_')
        .replace(/[^a-z0-9_]/g, '');
    });

    // Handle Create Season Form
    document.getElementById('create-season-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      formData.append('action', 'create_season');

      fetchApi(formData)
        .then(res => res.json())
        .then(res => {
          showMessage(res.success, res.message);
          if (res.success) {
            this.reset();
            document.getElementById('season-preview').style.display = 'none';
            document.getElementById('season-file-name').style.display = 'inline';
            document.getElementById('season-upload-icon').style.display = 'block';
            fetch('productos.json?v=' + new Date().getTime()).then(r => r.json()).then(data => {
              window._seasonData = data;
              populateDeleteSelect(data);
              populateInicioSeasons(data);
            });
          }
        })
        .catch(() => showMessage(false, 'Error de red.'));
    });

    // Handle Delete Season Form
    document.getElementById('delete-season-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const seasonId = document.getElementById('delete-season-select').value;
      if (!seasonId) { showMessage(false, 'Selecciona una temporada primero.'); return; }
      if (!confirm('¿Seguro que quieres eliminar esta temporada? Se borrarán todos sus productos. Esta acción no se puede deshacer.')) return;

      const formData = new FormData();
      formData.append('action', 'delete_season');
      formData.append('season_id', seasonId);

      fetchApi(formData)
        .then(res => res.json())
        .then(res => {
          showMessage(res.success, res.message);
          if (res.success) {
            document.getElementById('season-preview-card').style.display = 'none';
            fetch('productos.json?v=' + new Date().getTime()).then(r => r.json()).then(data => {
              window._seasonData = data;
              populateDeleteSelect(data);
              populateInicioSeasons(data);
            });
          }
        })
        .catch(() => showMessage(false, 'Error de red.'));
    });

    // Handle Edit Season Form
    document.getElementById('edit-season-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const seasonId = document.getElementById('edit-season-select').value;
      if (!seasonId) { showMessage(false, 'Selecciona una temporada primero.'); return; }

      const formData = new FormData(this);
      formData.append('action', 'edit_season');

      fetchApi(formData)
        .then(res => res.json())
        .then(res => {
          showMessage(res.success, res.message);
          if (res.success) {
            fetch('productos.json?v=' + new Date().getTime()).then(r => r.json()).then(data => {
              window._seasonData = data;
              populateDeleteSelect(data);
              populateInicioSeasons(data);
            });
          }
        })
        .catch(() => showMessage(false, 'Error de red.'));
    });

    // ── Gestión de Secciones de Productos ────────────────────────────────────

    // Auto-generar ID desde nombre de sección
    document.getElementById('cat-name').addEventListener('input', function () {
      document.getElementById('cat-id').value = this.value
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, '_')
        .replace(/[^a-z0-9_]/g, '');
    });

    function populateCategorySelects(data) {
      const delSel = document.getElementById('delete-category-select');
      const editSel = document.getElementById('edit-category-select');
      
      const options = '<option value="">-- Elige una sección --</option>' + 
        (data.categorias_productos || []).map(cat => `<option value="${cat.id}">${cat.titulo}</option>`).join('');
        
      delSel.innerHTML = options;
      editSel.innerHTML = options;
    }

    // Llenar campos al seleccionar sección para editar
    document.getElementById('edit-category-select').addEventListener('change', function() {
      const catId = this.value;
      if (!catId) {
        document.getElementById('edit-category-form').reset();
        return;
      }
      const cat = (window._seasonData?.categorias_productos || []).find(c => c.id === catId);
      if (cat) {
        document.getElementById('edit-cat-name').value = cat.titulo;
        document.getElementById('edit-cat-subtitle').value = cat.subtitulo || '';
      }
    });

    // Crear Sección
    document.getElementById('create-category-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      formData.append('action', 'create_category');
      fetchApi(formData)
        .then(res => res.json())
        .then(res => {
          showMessage(res.success, res.message);
          if (res.success) {
            this.reset();
            fetch('productos.json?v=' + new Date().getTime()).then(r => r.json()).then(data => {
              window._seasonData = data;
              populateCategorySelects(data);
              // Refrescar el select de categoría en agregar producto
              const catSelect = document.getElementById('product-category');
              catSelect.innerHTML = '<optgroup label="Sección Productos Base">';
              data.categorias_productos.forEach(cat => {
                catSelect.innerHTML += `<option value="productos|${cat.id}">[General] ${cat.titulo}</option>`;
              });
              catSelect.innerHTML += '</optgroup>';
              renderCatalog(data);
            });
          }
        })
        .catch(() => showMessage(false, 'Error de red.'));
    });

    // Eliminar Sección
    document.getElementById('delete-category-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const catId = document.getElementById('delete-category-select').value;
      if (!catId) { showMessage(false, 'Selecciona una sección primero.'); return; }
      if (!confirm('¿Seguro? Se eliminarán la sección y todos sus productos. Esta acción no se puede deshacer.')) return;

      const formData = new FormData();
      formData.append('action', 'delete_category');
      formData.append('category_id', catId);
      fetchApi(formData)
        .then(res => res.json())
        .then(res => {
          showMessage(res.success, res.message);
          if (res.success) {
            fetch('productos.json?v=' + new Date().getTime()).then(r => r.json()).then(data => {
              window._seasonData = data;
              populateCategorySelects(data);
              renderCatalog(data);
            });
          }
        })
        .catch(() => showMessage(false, 'Error de red.'));
    });

    // Editar Sección
    document.getElementById('edit-category-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const catId = document.getElementById('edit-category-select').value;
      if (!catId) { showMessage(false, 'Selecciona una sección primero.'); return; }
      
      const formData = new FormData(this);
      formData.append('action', 'edit_category');
      
      fetchApi(formData)
        .then(res => res.json())
        .then(res => {
          showMessage(res.success, res.message);
          if (res.success) {
            fetch('productos.json?v=' + new Date().getTime()).then(r => r.json()).then(data => {
              window._seasonData = data;
              populateCategorySelects(data);
              // Refrescar el select de categoría en agregar producto
              const catSelect = document.getElementById('product-category');
              catSelect.innerHTML = '<optgroup label="Sección Productos Base">';
              data.categorias_productos.forEach(cat => {
                catSelect.innerHTML += `<option value="productos|${cat.id}">[General] ${cat.titulo}</option>`;
              });
              catSelect.innerHTML += '</optgroup>';
              renderCatalog(data);
            });
          }
        })
        .catch(() => showMessage(false, 'Error de red.'));
    });

    // ── Cargar Reseñas ────────────────────────────────────────────────────────
    function loadResenas() {
      const fd = new FormData();
      fd.append('action', 'get_resenas_admin');
      fetchApi(fd)
        .then(r => r.json())
        .then(res => {
          const list = document.getElementById('resenas-list');
          if (!res.resenas || res.resenas.length === 0) {
            list.innerHTML = '<p style="color:var(--text-light); padding:20px; text-align:center;">No hay reseñas todavía.</p>';
            return;
          }
          list.innerHTML = res.resenas.map(r => {
            const stars = '★'.repeat(r.estrellas) + '☆'.repeat(5 - r.estrellas);
            const badge = r.aprobada
              ? '<span style="background:#d4edda;color:#155724;padding:3px 10px;border-radius:12px;font-size:0.8rem;">✅ Aprobada</span>'
              : '<span style="background:#fff3cd;color:#856404;padding:3px 10px;border-radius:12px;font-size:0.8rem;">⏳ Pendiente</span>';
            return `
              <div style="display:flex;align-items:flex-start;gap:15px;padding:15px;border-radius:12px;border:1px solid #eee;margin-bottom:12px;background:#fafafa;">
                <div style="flex:1;">
                  <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                    <strong style="color:var(--primary);">${r.nombre}</strong>
                    <span style="color:#f5a623;font-size:1.1rem;">${stars}</span>
                    ${badge}
                    <span style="color:var(--text-light);font-size:0.8rem;margin-left:auto;">${r.fecha}</span>
                  </div>
                  <p style="color:#444;font-size:0.95rem;margin:0;">${r.comentario}</p>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;flex-shrink:0;">
                  ${!r.aprobada
                    ? `<button onclick="aprobarResena(${r.id}, true)" class="btn" style="padding:8px 16px;font-size:0.85rem;">✅ Aprobar</button>`
                    : `<button onclick="aprobarResena(${r.id}, false)" class="btn" style="padding:8px 16px;font-size:0.85rem;background:#777;">🙈 Ocultar</button>`
                  }
                </div>
              </div>`;
          }).join('');
        });
    }

    function aprobarResena(id, aprobada) {
      const fd = new FormData();
      fd.append('action', 'approve_resena');
      fd.append('resena_id', id);
      fd.append('aprobada', aprobada ? '1' : '0');
      fetchApi(fd)
        .then(r => r.json())
        .then(res => { showMessage(res.success, res.message); loadResenas(); });
    }

    // ── Cargar Sugerencias ────────────────────────────────────────────────────
    function loadSugerencias() {
      const fd = new FormData();
      fd.append('action', 'get_sugerencias');
      fetchApi(fd)
        .then(r => r.json())
        .then(res => {
          const list = document.getElementById('sugerencias-list');
          if (!res.sugerencias || res.sugerencias.length === 0) {
            list.innerHTML = '<p style="color:var(--text-light); padding:20px; text-align:center;">No hay sugerencias todavía.</p>';
            return;
          }
          const tipoColores = {
            'Sugerencia': '#e3f2fd', 'Felicitación': '#f3e5f5',
            'Queja': '#fce4ec', 'Pregunta': '#fff8e1'
          };
          list.innerHTML = res.sugerencias.map(s => {
            const bg = tipoColores[s.tipo] || '#f5f5f5';
            return `
              <div style="padding:18px;border-radius:12px;margin-bottom:12px;background:${bg};border-left:4px solid var(--primary);">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                  <strong style="color:var(--primary);">${s.tipo}</strong>
                  <span style="color:var(--text-light);font-size:0.85rem;">De: ${s.nombre || 'Anónimo'}</span>
                  <span style="color:var(--text-light);font-size:0.8rem;margin-left:auto;">📅 ${s.fecha}</span>
                </div>
                <p style="color:#333;margin:0;line-height:1.6;">${s.mensaje}</p>
              </div>`;
          }).join('');
        });
    }

    function showMessage(success, text) {
      const msgDiv = document.getElementById('status-message');
      msgDiv.className = 'alert ' + (success ? 'success' : 'error');
      msgDiv.textContent = text;
      setTimeout(() => { msgDiv.className = 'alert'; msgDiv.style.display = 'none'; }, 5000);
    }
  </script>
</body>

</html>