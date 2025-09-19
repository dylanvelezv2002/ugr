<?php
/**
 * ============================================================================
 * SISTEMA GRED 2024 – Buscador de Guías de Respuesta ante Emergencias
 * Unidad de Gestión de Riesgos (UGR)
 * Universidad Estatal de Bolívar – www.ueb.edu.ec
 * ============================================================================
 *
 * Responsable de la UGR: Ing. Paul Sánchez Franco
 * Desarrollado por: Dylan Vélez
 * Contacto del desarrollador: 0963180830
 *
 * Este sistema permite realizar búsquedas inteligentes de materiales
 * peligrosos registrados en la base GRED 2024, utilizando como criterios:
 *   • Nombre del material
 *   • Código ONU
 *   • Número de Guía de Emergencia
 *
 * Los resultados se presentan en un modal responsive adaptado para escritorio
 * y dispositivos móviles. Cada resultado incluye símbolos DOT, etiquetas de
 * riesgo, imagen NFPA 704 y archivos de referencia en formato PDF o imagen.
 *
 * Este sistema ha sido desarrollado con fines estrictamente académicos y
 * educativos, como parte de una actividad técnica universitaria. Queda
 * prohibido su uso con fines comerciales o su redistribución sin autorización.
 *
 * Última actualización: Mayo 2025
 */

include('includes/db.php'); // Conexión a la base de datos

// Verificar si se han recibido datos
if (isset($_POST['nombre_material']) && isset($_POST['codigo_onu']) && isset($_POST['guia_emergencia'])) {
    $nombre_material = $_POST['nombre_material'];
    $codigo_onu = $_POST['codigo_onu'];
    $guia_emergencia = $_POST['guia_emergencia'];

    // Aquí va el código para manejar los archivos subidos (documentos e imágenes)
    // Asegúrate de manejar la carga de archivos correctamente

    $insert_query = "INSERT INTO guias (nombre_material, codigo_onu, guia_emergencia) VALUES ('$nombre_material', '$codigo_onu', '$guia_emergencia')";

    if (mysqli_query($conn, $insert_query)) {
        echo json_encode(['success' => true, 'message' => 'Guía agregada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al agregar la guía.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
}
?>
