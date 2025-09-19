<?php
$servername = "localhost"; // Cambia estos datos según tu configuración
$username = "joelvelez_ugr"; // Usuario de la base de datos
$password = "Ugr2025%"; // Contraseña de la base de datos
$dbname = "joelvelez_ugr"; // Nombre de la base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Asegurarse de que se use UTF-8 para evitar problemas con caracteres especiales
$conn->set_charset("utf8mb4");
?>
