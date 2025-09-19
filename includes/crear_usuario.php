<?php
// includes/crear_usuario.php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $rol = $_POST['rol'] ?? 'usuario';
    $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Correo inválido.']);
        exit;
    }

    if (strlen($contrasena) < 5) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 5 caracteres.']);
        exit;
    }

    // Verificar si ya existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El correo ya está registrado.']);
        exit;
    }

    $hash = password_hash($contrasena, PASSWORD_BCRYPT);

    $insert = $conn->prepare("INSERT INTO usuarios (correo, contrasena, rol, activo) VALUES (?, ?, ?, ?)");
    $insert->bind_param("sssi", $correo, $hash, $rol, $activo);

    if ($insert->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario.']);
    }
}
?>
