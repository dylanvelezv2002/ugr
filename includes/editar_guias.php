<?php
include('db.php');
header('Content-Type: application/json'); // ✅ importante para que fetch espere JSON

$uploadDocsDir = '../uploads/documents/';
$uploadImagesDir = '../uploads/images/';
$uploadNfpaDir = '../uploads/nfpa704/';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id               = (int)$_POST['id'];
    $nombre_material  = $_POST['nombre_material'] ?? '';
    $codigo_onu       = $_POST['codigo_onu'] ?? '';
    $guia_emergencia  = $_POST['guia_emergencia'] ?? '';
    $aloha_name       = $_POST['aloha_name'] ?? '';
    $etiqueta_dot     = $_POST['etiqueta_dot'] ?? '';
    $nfpa704_imagen   = '';

    // === Cargar imagen NFPA ===
    if (isset($_FILES['nfpa704_imagen']) && $_FILES['nfpa704_imagen']['error'] === UPLOAD_ERR_OK) {
        $nfpa_name = time() . '_' . basename($_FILES['nfpa704_imagen']['name']);
        move_uploaded_file($_FILES['nfpa704_imagen']['tmp_name'], $uploadNfpaDir . $nfpa_name);
        $nfpa704_imagen = $nfpa_name;
    } else {
        $stmt = $conn->prepare("SELECT nfpa704_imagen FROM guias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $nfpa704_imagen = $row['nfpa704_imagen'] ?? '';
    }

    // === Cargar archivos (PDF/DOC) ===
    $archivos = [];
    if (!empty($_FILES['archivo']['name'][0])) {
        foreach ($_FILES['archivo']['tmp_name'] as $index => $tmpFile) {
            $filename = time() . '_' . basename($_FILES['archivo']['name'][$index]);
            move_uploaded_file($tmpFile, $uploadDocsDir . $filename);
            $archivos[] = $filename;
        }
    } else {
        $stmt = $conn->prepare("SELECT archivo FROM guias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $archivos = explode(',', $row['archivo']);
    }

    // === Cargar imágenes ===
    $imagenes = [];
    if (!empty($_FILES['imagen_guia']['name'][0])) {
        foreach ($_FILES['imagen_guia']['tmp_name'] as $index => $tmpFile) {
            $filename = time() . '_' . basename($_FILES['imagen_guia']['name'][$index]);
            move_uploaded_file($tmpFile, $uploadImagesDir . $filename);
            $imagenes[] = $filename;
        }
    } else {
        $stmt = $conn->prepare("SELECT imagen_guia FROM guias WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $imagenes = explode(',', $row['imagen_guia']);
    }

    $archivosStr = implode(',', $archivos);
    $imagenesStr = implode(',', $imagenes);

    $stmt = $conn->prepare("UPDATE guias SET nombre_material = ?, codigo_onu = ?, guia_emergencia = ?, aloha_name = ?, etiqueta_dot = ?, nfpa704_imagen = ?, archivo = ?, imagen_guia = ? WHERE id = ?");
    $stmt->bind_param("ssssssssi", $nombre_material, $codigo_onu, $guia_emergencia, $aloha_name, $etiqueta_dot, $nfpa704_imagen, $archivosStr, $imagenesStr, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Guía actualizada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
}
?>
