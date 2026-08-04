<?php
session_start();
header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');

// ─── Funciones útiles ─────────────────────────────────────────────────────────
function sendResponse($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function getJsonData() {
    $file = 'productos.json';
    if (!file_exists($file)) {
        sendResponse(false, 'El archivo productos.json no existe.');
    }
    $content = file_get_contents($file);
    return json_decode($content, true);
}

function saveJsonData($data) {
    $file = 'productos.json';
    $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($file, $content) !== false;
}

function readJson($file) {
    if (!file_exists($file)) return null;
    return json_decode(file_get_contents($file), true);
}

function writeJson($file, $data) {
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

// ── Función segura para subir archivos ──
function secureUpload($fileInputName, $targetDir) {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Error en la subida del archivo.'];
    }

    $tmpPath = $_FILES[$fileInputName]['tmp_name'];
    
    // Verificar que sea una imagen real
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($mimeType, $allowedMimeTypes)) {
        return ['success' => false, 'error' => 'El archivo no es una imagen válida.'];
    }

    // Verificar extensión
    $extension = strtolower(pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($extension, $allowedExtensions)) {
        return ['success' => false, 'error' => 'Extensión de archivo no permitida.'];
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $safeName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $targetPath = $targetDir . $safeName;

    if (move_uploaded_file($tmpPath, $targetPath)) {
        return ['success' => true, 'path' => $targetPath];
    } else {
        return ['success' => false, 'error' => 'Error guardando archivo en el servidor.'];
    }
}

// Asegurar que venga una acción
if (!isset($_POST['action'])) {
    sendResponse(false, 'No action provided');
}

$action = $_POST['action'];

// ── Manejo de Autenticación ──────────────────────────────────────────────────
if ($action === 'login') {
    // Protección contra fuerza bruta (Rate limiting básico en sesión)
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }
    
    if ($_SESSION['login_attempts'] >= 5) {
        $time_passed = time() - $_SESSION['last_attempt_time'];
        if ($time_passed < 300) { // 5 minutos de bloqueo
            sendResponse(false, 'Demasiados intentos. Intenta de nuevo en ' . (300 - $time_passed) . ' segundos.');
        } else {
            $_SESSION['login_attempts'] = 0; // Resetear después de 5 minutos
        }
    }

    $pwd = $_POST['password'] ?? '';
    // Hash de la contraseña 'Pulpito2' generado con password_hash()
    $hash = '$2y$12$3IZAJ.l7sABSxjsMtbsSH.KEhow9J2r1l8Rib.GRz33gW2F0s8Fsa';

    if (password_verify($pwd, $hash)) {
        // Prevención de fijación de sesión (Session Fixation)
        session_regenerate_id(true);
        
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['login_attempts'] = 0;
        
        // Generar CSRF token seguro si no existe
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        sendResponse(true, 'Autenticado correctamente');
    } else {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        sendResponse(false, 'Contraseña incorrecta');
    }
}

if ($action === 'logout') {
    // Destruir la sesión completamente
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    sendResponse(true, 'Sesión cerrada');
}

// ── Verificación de Seguridad para el resto de acciones ──────────────────────
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    sendResponse(false, 'No autorizado. Por favor inicie sesión.');
}

$csrf = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
    http_response_code(403);
    sendResponse(false, 'Fallo de seguridad CSRF.');
}


// ── Aprobar / Rechazar Reseña ─────────────────────────────────────────────
if ($action === 'approve_resena') {
    $id       = intval($_POST['resena_id'] ?? 0);
    $aprobada = ($_POST['aprobada'] ?? '0') === '1';
    $data = readJson('resenas.json') ?? ['resenas' => []];
    foreach ($data['resenas'] as &$r) {
        if ($r['id'] == $id) { $r['aprobada'] = $aprobada; break; }
    }
    writeJson('resenas.json', $data);
    sendResponse(true, $aprobada ? 'Reseña aprobada.' : 'Reseña ocultada.');
}

// ── Obtener todas las reseñas (admin) ────────────────────────────────────
if ($action === 'get_resenas_admin') {
    $data = readJson('resenas.json') ?? ['resenas' => []];
    sendResponse(true, 'ok', ['resenas' => array_reverse($data['resenas'])]);
}

// ── Obtener sugerencias ──────────────────────────────────────────────────
if ($action === 'get_sugerencias') {
    $data = readJson('sugerencias.json') ?? ['sugerencias' => []];
    // Marcar todas como leídas
    foreach ($data['sugerencias'] as &$s) { $s['leida'] = true; }
    writeJson('sugerencias.json', $data);
    sendResponse(true, 'ok', ['sugerencias' => array_reverse($data['sugerencias'])]);
}

// ── Toggle mostrar en inicio ─────────────────────────────────────────────
if ($action === 'toggle_inicio_season') {
    $id = trim($_POST['season_id'] ?? '');
    $mostrar = ($_POST['mostrar'] ?? '1') === '1';

    $data = getJsonData();
    $found = false;
    foreach ($data['categorias_entemporada'] as &$cat) {
        if ($cat['id'] === $id) {
            $cat['mostrar_en_inicio'] = $mostrar;
            $found = true;
            break;
        }
    }

    if (!$found) {
        sendResponse(false, 'Temporada no encontrada.');
    }

    if (saveJsonData($data)) {
        sendResponse(true, 'Visibilidad actualizada.');
    } else {
        sendResponse(false, 'Error al guardar.');
    }
}

if ($action === 'set_season') {
    $season = $_POST['season'] ?? '';
    if (empty($season)) {
        sendResponse(false, 'Temporada no válida');
    }

    $data = getJsonData();
    $data['temporada_activa'] = $season;
    
    if (saveJsonData($data)) {
        sendResponse(true, 'Temporada actualizada correctamente.');
    } else {
        sendResponse(false, 'Error al guardar el archivo JSON. Revisa los permisos.');
    }
} 
elseif ($action === 'add_product') {
    // Validar campos
    $nombre = $_POST['nombre'] ?? '';
    $desc = $_POST['descripcion'] ?? '';
    $precio = floatval($_POST['precio'] ?? 0);
    $categoria_raw = $_POST['categoria'] ?? ''; // e.g. "productos|escritorio"

    if (empty($nombre) || $precio <= 0 || empty($categoria_raw)) {
        sendResponse(false, 'Faltan datos obligatorios del producto.');
    }

    // Subir Imagen
    $uploadResult = secureUpload('image', 'img/uploads/');
    if (!$uploadResult['success']) {
        sendResponse(false, $uploadResult['error']);
    }
    $targetPath = $uploadResult['path'];

    // Encontrar categoría y agregar el producto
    $data = getJsonData();
    list($seccion, $catId) = explode('|', $categoria_raw);

    $targetArrayKey = ($seccion === 'productos') ? 'categorias_productos' : 'categorias_entemporada';
    
    $productAdded = false;
    foreach ($data[$targetArrayKey] as &$cat) {
        if ($cat['id'] === $catId) {
            $cat['productos'][] = [
                'imagen' => $targetPath,
                'alt' => $nombre,
                'nombre' => $nombre,
                'descripcion' => $desc ? $desc : "...",
                'precio' => $precio
            ];
            $productAdded = true;
            break;
        }
    }

    if (!$productAdded) {
        sendResponse(false, 'No se encontró la categoría seleccionada.');
    }

    // Gestionar adición a temporadas
    if (isset($_POST['seasons_submitted'])) {
        $selected_seasons = $_POST['seasons'] ?? [];
        if (!is_array($selected_seasons)) $selected_seasons = [];
        
        $productoNuevo = [
            'imagen' => $targetPath,
            'alt' => $nombre,
            'nombre' => $nombre,
            'descripcion' => $desc ? $desc : "...",
            'precio' => $precio
        ];

        foreach ($data['categorias_entemporada'] as &$season) {
            if (in_array($season['id'], $selected_seasons)) {
                $season['productos'][] = $productoNuevo;
            }
        }
    }

    if (saveJsonData($data)) {
        sendResponse(true, 'Producto agregado exitosamente.');
    } else {
        sendResponse(false, 'El producto se guardó, pero hubo un error actualizando la base de datos.');
    }
} 
elseif ($action === 'create_season') {
    $titulo = trim($_POST['titulo'] ?? '');
    $id     = trim($_POST['id'] ?? '');

    if (empty($titulo) || empty($id)) {
        sendResponse(false, 'El nombre y el ID de la temporada son obligatorios.');
    }

    // Sanitize id: lowercase, spaces → underscore, remove special chars
    $id = strtolower(preg_replace('/[^a-z0-9_]/i', '_', $id));

    // Handle profile image upload
    $imagenPath = 'img/navidad.jpg'; // default fallback
    if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = secureUpload('imagen_perfil', 'img/temporadas/');
        if (!$uploadResult['success']) {
            sendResponse(false, $uploadResult['error']);
        }
        $imagenPath = $uploadResult['path'];
    }

    $data = getJsonData();

    // Check if ID already exists
    foreach ($data['categorias_entemporada'] as $cat) {
        if ($cat['id'] === $id) {
            sendResponse(false, "Ya existe una temporada con el ID '$id'. Usa otro nombre.");
        }
    }

    $data['categorias_entemporada'][] = [
        'id'       => $id,
        'titulo'   => $titulo,
        'imagen'   => $imagenPath,
        'productos' => []
    ];

    if (saveJsonData($data)) {
        sendResponse(true, "Temporada '$titulo' creada correctamente.");
    } else {
        sendResponse(false, 'Error al guardar. Revisa los permisos del archivo.');
    }
}
elseif ($action === 'delete_season') {
    $id = trim($_POST['season_id'] ?? '');
    if (empty($id)) {
        sendResponse(false, 'ID de temporada no válido.');
    }

    $data = getJsonData();

    $newList = array_filter($data['categorias_entemporada'], fn($c) => $c['id'] !== $id);

    if (count($newList) === count($data['categorias_entemporada'])) {
        sendResponse(false, "No se encontró la temporada '$id'.");
    }

    $data['categorias_entemporada'] = array_values($newList);

    // If the deleted season was the active one, reset to first available
    if ($data['temporada_activa'] === $id) {
        $data['temporada_activa'] = !empty($data['categorias_entemporada'])
            ? $data['categorias_entemporada'][0]['id']
            : '';
    }

    if (saveJsonData($data)) {
        sendResponse(true, "Temporada eliminada correctamente.");
    } else {
        sendResponse(false, 'Error al guardar. Revisa los permisos del archivo.');
    }
}
elseif ($action === 'edit_season') {
    $id = trim($_POST['season_id'] ?? '');
    $titulo = trim($_POST['titulo'] ?? '');

    if (empty($id) || empty($titulo)) {
        sendResponse(false, 'El ID de la temporada y el título son obligatorios.');
    }

    $data = getJsonData();
    $found = false;

    foreach ($data['categorias_entemporada'] as &$cat) {
        if ($cat['id'] === $id) {
            $cat['titulo'] = $titulo;

            // Procesar nueva imagen si se subió
            if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = secureUpload('imagen_perfil', 'img/temporadas/');
                if (!$uploadResult['success']) {
                    sendResponse(false, $uploadResult['error']);
                }
                $cat['imagen'] = $uploadResult['path'];
            }

            $found = true;
            break;
        }
    }

    if (!$found) {
        sendResponse(false, "No se encontró la temporada especificada.");
    }

    if (saveJsonData($data)) {
        sendResponse(true, 'Temporada actualizada correctamente.');
    } else {
        sendResponse(false, 'Error al guardar los cambios.');
    }
}
elseif ($action === 'edit_product') {
    $nombre = $_POST['nombre'] ?? '';
    $desc = $_POST['descripcion'] ?? '';
    $precio = floatval($_POST['precio'] ?? 0);
    $categoria_raw = $_POST['categoria'] ?? ''; // e.g. "productos|escritorio"
    $product_idx = intval($_POST['product_idx'] ?? -1);

    if (empty($nombre) || $precio <= 0 || empty($categoria_raw) || $product_idx < 0) {
        sendResponse(false, 'Faltan datos obligatorios para editar el producto.');
    }

    $data = getJsonData();
    list($seccion, $catId) = explode('|', $categoria_raw);
    $targetArrayKey = ($seccion === 'productos') ? 'categorias_productos' : 'categorias_entemporada';

    $foundCatIdx = -1;
    foreach ($data[$targetArrayKey] as $idx => $cat) {
        if ($cat['id'] === $catId) {
            $foundCatIdx = $idx;
            break;
        }
    }

    if ($foundCatIdx === -1 || !isset($data[$targetArrayKey][$foundCatIdx]['productos'][$product_idx])) {
        sendResponse(false, 'No se encontró el producto especificado.');
    }

    $producto = &$data[$targetArrayKey][$foundCatIdx]['productos'][$product_idx];
    
    // Almacenar valores originales para buscar en temporadas
    $old_nombre = $producto['nombre'];
    $old_imagen = $producto['imagen'];

    // Actualizar campos de texto
    $producto['nombre'] = $nombre;
    $producto['alt'] = $nombre;
    $producto['descripcion'] = $desc ? $desc : "...";
    $producto['precio'] = $precio;

    // Subir nueva Imagen (si se proporcionó)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = secureUpload('image', 'img/uploads/');
        if (!$uploadResult['success']) {
            sendResponse(false, $uploadResult['error']);
        }
        $producto['imagen'] = $uploadResult['path'];
    }

    // Gestionar temporadas (solo si se envió desde el form de edición principal)
    if (isset($_POST['seasons_submitted'])) {
        $selected_seasons = $_POST['seasons'] ?? [];
        if (!is_array($selected_seasons)) $selected_seasons = [];
        
        foreach ($data['categorias_entemporada'] as &$season) {
            // Verificar si el producto ya está en esta temporada
            $prod_idx_in_season = -1;
            foreach ($season['productos'] as $s_idx => $s_prod) {
                if ($s_prod['nombre'] === $old_nombre && $s_prod['imagen'] === $old_imagen) {
                    $prod_idx_in_season = $s_idx;
                    break;
                }
            }
            
            $is_selected = in_array($season['id'], $selected_seasons);
            
            if ($is_selected && $prod_idx_in_season === -1) {
                // Agregar copia a la temporada
                $season['productos'][] = $producto;
            } elseif ($is_selected && $prod_idx_in_season !== -1) {
                // Actualizar la copia que ya existe en la temporada
                $season['productos'][$prod_idx_in_season] = $producto;
            } elseif (!$is_selected && $prod_idx_in_season !== -1) {
                // Quitar de la temporada
                array_splice($season['productos'], $prod_idx_in_season, 1);
            }
        }
    }

    // Mover a otra sección si se especificó
    $nueva_categoria_raw = trim($_POST['nueva_categoria'] ?? '');
    if (!empty($nueva_categoria_raw)) {
        list($newSeccion, $newCatId) = explode('|', $nueva_categoria_raw);
        $newArrayKey = ($newSeccion === 'productos') ? 'categorias_productos' : 'categorias_entemporada';

        $newCatIdx = -1;
        foreach ($data[$newArrayKey] as $idx => $cat) {
            if ($cat['id'] === $newCatId) { $newCatIdx = $idx; break; }
        }

        if ($newCatIdx === -1) {
            sendResponse(false, 'La sección de destino no existe.');
        }

        // Copiar el producto actualizado a la nueva sección
        $productoActualizado = $data[$targetArrayKey][$foundCatIdx]['productos'][$product_idx];
        $data[$newArrayKey][$newCatIdx]['productos'][] = $productoActualizado;

        // Eliminarlo de la sección original
        array_splice($data[$targetArrayKey][$foundCatIdx]['productos'], $product_idx, 1);
    }

    if (saveJsonData($data)) {
        sendResponse(true, 'Producto actualizado correctamente.');
    } else {
        sendResponse(false, 'Error al guardar los cambios en la base de datos.');
    }
}
elseif ($action === 'delete_product') {
    $categoria_raw = $_POST['categoria'] ?? '';
    $product_idx = intval($_POST['product_idx'] ?? -1);

    if (empty($categoria_raw) || $product_idx < 0) {
        sendResponse(false, 'Faltan datos obligatorios para eliminar el producto.');
    }

    $data = getJsonData();
    list($seccion, $catId) = explode('|', $categoria_raw);
    $targetArrayKey = ($seccion === 'productos') ? 'categorias_productos' : 'categorias_entemporada';

    $foundCatIdx = -1;
    foreach ($data[$targetArrayKey] as $idx => $cat) {
        if ($cat['id'] === $catId) {
            $foundCatIdx = $idx;
            break;
        }
    }

    if ($foundCatIdx === -1 || !isset($data[$targetArrayKey][$foundCatIdx]['productos'][$product_idx])) {
        sendResponse(false, 'No se encontró el producto especificado.');
    }

    array_splice($data[$targetArrayKey][$foundCatIdx]['productos'], $product_idx, 1);

    if (saveJsonData($data)) {
        sendResponse(true, 'Producto eliminado correctamente.');
    } else {
        sendResponse(false, 'Error al guardar los cambios en la base de datos.');
    }
}
elseif ($action === 'copy_to_season') {
    $source_cat_id = $_POST['source_cat_id'] ?? '';
    $product_idx = intval($_POST['product_idx'] ?? -1);
    $target_season_id = $_POST['target_season_id'] ?? '';

    if (empty($source_cat_id) || $product_idx < 0 || empty($target_season_id)) {
        sendResponse(false, 'Faltan datos para copiar el producto.');
    }

    $data = getJsonData();

    // Encontrar producto origen
    $producto = null;
    foreach ($data['categorias_productos'] as $cat) {
        if ($cat['id'] === $source_cat_id) {
            if (isset($cat['productos'][$product_idx])) {
                $producto = $cat['productos'][$product_idx];
            }
            break;
        }
    }

    if (!$producto) {
        sendResponse(false, 'El producto original no se encontró.');
    }

    // Insertar en la temporada destino
    $seasonFound = false;
    foreach ($data['categorias_entemporada'] as &$season) {
        if ($season['id'] === $target_season_id) {
            $season['productos'][] = $producto;
            $seasonFound = true;
            break;
        }
    }

    if (!$seasonFound) {
        sendResponse(false, 'La temporada de destino no existe.');
    }

    if (saveJsonData($data)) {
        sendResponse(true, 'Producto copiado a la temporada exitosamente.');
    } else {
        sendResponse(false, 'Error al actualizar la base de datos.');
    }
}
elseif ($action === 'create_category') {
    $titulo = trim($_POST['titulo'] ?? '');
    $subtitulo = trim($_POST['subtitulo'] ?? '');
    $id = trim($_POST['id'] ?? '');

    if (empty($titulo) || empty($id)) {
        sendResponse(false, 'El nombre y el ID de la sección son obligatorios.');
    }

    $id = strtolower(preg_replace('/[^a-z0-9_]/i', '_', $id));

    $data = getJsonData();

    foreach ($data['categorias_productos'] as $cat) {
        if ($cat['id'] === $id) {
            sendResponse(false, "Ya existe una sección con el ID '$id'.");
        }
    }

    $data['categorias_productos'][] = [
        'id'        => $id,
        'titulo'    => $titulo,
        'subtitulo' => $subtitulo ?: '',
        'productos' => []
    ];

    if (saveJsonData($data)) {
        sendResponse(true, "Sección '$titulo' creada correctamente.");
    } else {
        sendResponse(false, 'Error al guardar.');
    }
}
elseif ($action === 'delete_category') {
    $id = trim($_POST['category_id'] ?? '');
    if (empty($id)) {
        sendResponse(false, 'ID de sección no válido.');
    }

    $data = getJsonData();
    $newList = array_filter($data['categorias_productos'], fn($c) => $c['id'] !== $id);

    if (count($newList) === count($data['categorias_productos'])) {
        sendResponse(false, "No se encontró la sección '$id'.");
    }

    $data['categorias_productos'] = array_values($newList);

    if (saveJsonData($data)) {
        sendResponse(true, 'Sección eliminada correctamente.');
    } else {
        sendResponse(false, 'Error al guardar.');
    }
}
elseif ($action === 'edit_category') {
    $id = trim($_POST['category_id'] ?? '');
    $titulo = trim($_POST['titulo'] ?? '');
    $subtitulo = trim($_POST['subtitulo'] ?? '');

    if (empty($id) || empty($titulo)) {
        sendResponse(false, 'El ID de la sección y el nombre son obligatorios.');
    }

    $data = getJsonData();
    $found = false;

    foreach ($data['categorias_productos'] as &$cat) {
        if ($cat['id'] === $id) {
            $cat['titulo'] = $titulo;
            $cat['subtitulo'] = $subtitulo;
            $found = true;
            break;
        }
    }

    if (!$found) {
        sendResponse(false, "No se encontró la sección especificada.");
    }

    if (saveJsonData($data)) {
        sendResponse(true, 'Sección actualizada correctamente.');
    } else {
        sendResponse(false, 'Error al guardar los cambios.');
    }
}
else {
    sendResponse(false, 'Acción desconocida');
}
