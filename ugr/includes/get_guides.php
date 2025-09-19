<?php
include('db.php');

header('Content-Type: application/json');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM guias WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            // Asegurar que los campos multimedia no devuelvan null sino string vacío
            $row['archivo'] = $row['archivo'] ?? '';
            $row['imagen_guia'] = $row['imagen_guia'] ?? '';
            $row['nfpa704_imagen'] = $row['nfpa704_imagen'] ?? '';
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Guía no encontrada.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID no válido o no enviado.']);
}
?>
