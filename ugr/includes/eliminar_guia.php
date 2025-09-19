<?php
include('db.php');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $query = $conn->query("SELECT archivo, imagen_guia, nfpa704_imagen FROM guias WHERE id = $id");
    $datos = $query->fetch_assoc();

    $docs = explode(",", $datos['archivo']);
    foreach ($docs as $doc) {
        $path = "../uploads/documents/" . $doc;
        if (file_exists($path)) unlink($path);
    }

    $imgs = explode(",", $datos['imagen_guia']);
    foreach ($imgs as $img) {
        $path = "../uploads/images/" . $img;
        if (file_exists($path)) unlink($path);
    }

    if (!empty($datos['nfpa704_imagen'])) {
        $path_nfpa = "../uploads/nfpa/" . $datos['nfpa704_imagen'];
        if (file_exists($path_nfpa)) unlink($path_nfpa);
    }

    $stmt = $conn->prepare("DELETE FROM guias WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Guía eliminada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar.']);
    }
}
?>
