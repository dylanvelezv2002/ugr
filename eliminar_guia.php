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

include('db.php');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Opcional: borrar archivos asociados si quieres
    // Primero obtener archivos (archivos, imágenes)
    $stmt = $conn->prepare("SELECT archivo, imagen_guia FROM guias WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $row = $res->fetch_assoc()) {
        $archivos = explode(',', $row['archivo']);
        foreach ($archivos as $archivo) {
            $path = 'uploads/documents/' . $archivo;
            if (file_exists($path)) unlink($path);
        }
        $imagenes = explode(',', $row['imagen_guia']);
        foreach ($imagenes as $img) {
            $path_img = 'uploads/images/' . $img;
            if (file_exists($path_img)) unlink($path_img);
        }
    }

    $stmtDel = $conn->prepare("DELETE FROM guias WHERE id = ?");
    $stmtDel->bind_param("i", $id);
    if ($stmtDel->execute()) {
        echo json_encode(['success' => true, 'message' => 'Guía eliminada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la guía.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID no válido.']);
}
?>
