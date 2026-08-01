<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// ─── CONFIGURA TU CORREO AQUÍ ────────────────────────────────────────────────
$EMAIL_DESTINO = 'plushiecrochetpty@gmail.com';
// ─────────────────────────────────────────────────────────────────────────────

function sendResponse($success, $message, $data = null) {
    $res = ['success' => $success, 'message' => $message];
    if ($data) $res['data'] = $data;
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
    exit;
}

function readJson($file) {
    if (!file_exists($file)) return null;
    return json_decode(file_get_contents($file), true);
}

function writeJson($file, $data) {
    return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

$action = $_REQUEST['action'] ?? '';

// ── Obtener reseñas aprobadas ──────────────────────────────────────────────
if ($action === 'get_resenas') {
    $data = readJson('resenas.json') ?? ['resenas' => []];
    $aprobadas = array_values(array_filter($data['resenas'], fn($r) => $r['aprobada'] === true));
    // Devolver máximo las últimas 6
    $aprobadas = array_slice(array_reverse($aprobadas), 0, 6);
    sendResponse(true, 'ok', $aprobadas);
}

// ── Guardar reseña de cliente ──────────────────────────────────────────────
if ($action === 'add_resena') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $estrellas = intval($_POST['estrellas'] ?? 0);
    $comentario = trim($_POST['comentario'] ?? '');

    if (empty($nombre) || $estrellas < 1 || $estrellas > 5 || empty($comentario)) {
        sendResponse(false, 'Por favor completa todos los campos.');
    }
    if (mb_strlen($comentario) > 300) {
        sendResponse(false, 'El comentario no puede superar los 300 caracteres.');
    }

    $data = readJson('resenas.json') ?? ['resenas' => []];

    $data['resenas'][] = [
        'id'         => time(),
        'nombre'     => htmlspecialchars($nombre),
        'estrellas'  => $estrellas,
        'comentario' => htmlspecialchars($comentario),
        'fecha'      => date('Y-m-d'),
        'aprobada'   => false  // pendiente de aprobación en el panel admin
    ];

    if (writeJson('resenas.json', $data)) {
        sendResponse(true, '¡Gracias por tu reseña! 💕 La revisaremos pronto.');
    } else {
        sendResponse(false, 'Error al guardar. Intenta de nuevo.');
    }
}

// ── Enviar sugerencia por correo ───────────────────────────────────────────
if ($action === 'send_sugerencia') {
    $tipo    = trim($_POST['tipo'] ?? 'Sugerencia');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $nombre  = trim($_POST['nombre_sug'] ?? 'Anónimo');

    if (empty($mensaje)) {
        sendResponse(false, 'El mensaje no puede estar vacío.');
    }

    // Guardar en sugerencias.json
    $data = readJson('sugerencias.json') ?? ['sugerencias' => []];
    $data['sugerencias'][] = [
        'id'      => time(),
        'tipo'    => htmlspecialchars($tipo),
        'nombre'  => htmlspecialchars($nombre),
        'mensaje' => htmlspecialchars($mensaje),
        'fecha'   => date('Y-m-d H:i'),
        'leida'   => false
    ];
    writeJson('sugerencias.json', $data);

    // Intentar enviar correo
    global $EMAIL_DESTINO;
    $asunto  = "[Plushie Crochet] Nueva $tipo de: $nombre";
    $cuerpo  = "Has recibido una nueva $tipo desde tu página web.\n\n";
    $cuerpo .= "De: $nombre\n";
    $cuerpo .= "Tipo: $tipo\n";
    $cuerpo .= "Fecha: " . date('d/m/Y H:i') . "\n\n";
    $cuerpo .= "Mensaje:\n$mensaje";
    $headers = "From: noreply@plushiecrochet.com\r\nContent-Type: text/plain; charset=UTF-8";

    @mail($EMAIL_DESTINO, $asunto, $cuerpo, $headers);
    // (mail() puede no funcionar en localhost, pero sí en hosting real)

    sendResponse(true, '¡Tu sugerencia fue enviada! Gracias por ayudarnos a mejorar 🙌');
}

sendResponse(false, 'Acción no reconocida.');
