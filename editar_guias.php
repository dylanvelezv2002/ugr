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

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id               = (int)$_POST['id'];
    $nombre_material  = trim($_POST['nombre_material'] ?? '');
    $codigo_onu       = trim($_POST['codigo_onu'] ?? '');
    $guia_emergencia  = trim($_POST['guia_emergencia'] ?? '');
    $aloha_name       = trim($_POST['aloha_name'] ?? '');
    $etiqueta_dot     = trim($_POST['etiqueta_dot'] ?? '');

    // Opción avanzada: permitir que nfpa704 sea opcional
    $nfpa704_imagen = $_POST['nfpa704_imagen'] ?? null;
    if ($nfpa704_imagen === '' || $nfpa704_imagen === null) {
        $stmt = $conn->prepare("UPDATE guias SET nombre_material = ?, codigo_onu = ?, guia_emergencia = ?, aloha_name = ?, etiqueta_dot = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $nombre_material, $codigo_onu, $guia_emergencia, $aloha_name, $etiqueta_dot, $id);
    } else {
        $stmt = $conn->prepare("UPDATE guias SET nombre_material = ?, codigo_onu = ?, guia_emergencia = ?, aloha_name = ?, etiqueta_dot = ?, nfpa704_imagen = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $nombre_material, $codigo_onu, $guia_emergencia, $aloha_name, $etiqueta_dot, $nfpa704_imagen, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Guía actualizada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la guía.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos insuficientes para actualizar.']);
}
