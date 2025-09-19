<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Obtener correo del usuario para confirmar
    $query = $conn->prepare("SELECT correo FROM usuarios WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
        exit;
    }

    $usuario = $result->fetch_assoc();
    $correoUsuario = $usuario['correo'];

    // Validación adicional (esto es solo para confirmación previa con JS)
    if (!isset($_GET['confirm']) || $_GET['confirm'] !== $correoUsuario) {
        echo json_encode(['success' => false, 'message' => 'Confirmación de correo requerida para eliminar.']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el usuario.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
}
?>
