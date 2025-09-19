<?php
include('db.php');
header('Content-Type: application/json'); // ✅ Respuesta en formato JSON

function limpiarNombre($nombre) {
    return preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $nombre);
}

$nombre_material   = $_POST['nombre_material'] ?? '';
$codigo_onu        = $_POST['codigo_onu'] ?? '';
$guia_emergencia   = $_POST['guia_emergencia'] ?? '';
$aloha_name        = $_POST['aloha_name'] ?? '';
$etiqueta_dot      = $_POST['etiqueta_dot'] ?? '';

$archivos_subidos = [];
$imagenes_subidas = [];
$nfpa704_imagen = '';

// === Subir archivos (PDF/DOC) ===
if (!empty($_FILES['archivo']['name'][0])) {
    $upload_dir = '../uploads/documents/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    foreach ($_FILES['archivo']['name'] as $i => $nombre) {
        $tmp = $_FILES['archivo']['tmp_name'][$i];
        if ($tmp && is_uploaded_file($tmp)) {
            $nombre_limpio = uniqid() . '_' . limpiarNombre($nombre);
            $destino = $upload_dir . $nombre_limpio;
            if (move_uploaded_file($tmp, $destino)) {
                $archivos_subidos[] = $nombre_limpio;
            }
        }
    }
}

// === Subir imágenes ===
if (!empty($_FILES['imagen_guia']['name'][0])) {
    $upload_dir = '../uploads/images/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    foreach ($_FILES['imagen_guia']['name'] as $i => $nombre) {
        $tmp = $_FILES['imagen_guia']['tmp_name'][$i];
        if ($tmp && is_uploaded_file($tmp)) {
            $nombre_limpio = uniqid() . '_' . limpiarNombre($nombre);
            $destino = $upload_dir . $nombre_limpio;
            if (move_uploaded_file($tmp, $destino)) {
                $imagenes_subidas[] = $nombre_limpio;
            }
        }
    }
}

// === Subir imagen NFPA704 ===
if (!empty($_FILES['nfpa704_imagen']['name'])) {
    $upload_dir = '../uploads/nfpa704/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    $tmp = $_FILES['nfpa704_imagen']['tmp_name'];
    $nombre = $_FILES['nfpa704_imagen']['name'];
    if ($tmp && is_uploaded_file($tmp)) {
        $nombre_limpio = uniqid() . '_' . limpiarNombre($nombre);
        $destino = $upload_dir . $nombre_limpio;
        if (move_uploaded_file($tmp, $destino)) {
            $nfpa704_imagen = $nombre_limpio;
        }
    }
}

$stmt = $conn->prepare("INSERT INTO guias 
    (nombre_material, codigo_onu, guia_emergencia, aloha_name, etiqueta_dot, archivo, imagen_guia, nfpa704_imagen) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

$archivos_str = implode(',', $archivos_subidos);
$imagenes_str = implode(',', $imagenes_subidas);

$stmt->bind_param("ssssssss",
    $nombre_material,
    $codigo_onu,
    $guia_emergencia,
    $aloha_name,
    $etiqueta_dot,
    $archivos_str,
    $imagenes_str,
    $nfpa704_imagen
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Guía registrada correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
}
?>
