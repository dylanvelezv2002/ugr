<?php
// Inicia la sesión
session_start();

// Borra todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la cookie de sesión, también se debe eliminar.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruye completamente la sesión
session_destroy();

// Encabezados para evitar el almacenamiento en caché
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Redirecciona al login
header("Location: login.php");
exit;
?>
