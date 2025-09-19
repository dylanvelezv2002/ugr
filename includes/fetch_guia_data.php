<?php
include('db.php');
header('Content-Type: application/json');

// Recibir parámetros
$pagina = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$limite = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
$offset = ($pagina - 1) * $limite;

$search = $_POST['query'] ?? '';
$search_safe = '%' . $conn->real_escape_string($search) . '%';

// Total de registros filtrados
$stmtTotal = $conn->prepare("SELECT COUNT(*) AS total FROM guias 
  WHERE nombre_material LIKE ? 
     OR codigo_onu LIKE ? 
     OR guia_emergencia LIKE ? 
     OR aloha_name LIKE ? 
     OR etiqueta_dot LIKE ?");
$stmtTotal->bind_param("sssss", $search_safe, $search_safe, $search_safe, $search_safe, $search_safe);
$stmtTotal->execute();
$total = $stmtTotal->get_result()->fetch_assoc()['total'];

// Consulta paginada
$stmt = $conn->prepare("SELECT * FROM guias 
  WHERE nombre_material LIKE ? 
     OR codigo_onu LIKE ? 
     OR guia_emergencia LIKE ? 
     OR aloha_name LIKE ? 
     OR etiqueta_dot LIKE ?
  ORDER BY id DESC
  LIMIT ?, ?");
$stmt->bind_param("ssssssi", $search_safe, $search_safe, $search_safe, $search_safe, $search_safe, $offset, $limite);
$stmt->execute();
$resultado = $stmt->get_result();

$html = '';
if ($resultado->num_rows > 0) {
  while ($row = $resultado->fetch_assoc()) {
    $html .= "<tr id='row_{$row['id']}'>";
    $html .= "<td>" . htmlspecialchars($row['nombre_material']) . "</td>";
    $html .= "<td>" . htmlspecialchars($row['codigo_onu']) . "</td>";
    $html .= "<td>" . htmlspecialchars($row['guia_emergencia']) . "</td>";
    $html .= "<td>" . htmlspecialchars($row['aloha_name']) . "</td>";
    $html .= "<td>" . htmlspecialchars($row['etiqueta_dot']) . "</td>";

    $nfpa = $row['nfpa704_imagen'];
    if ($nfpa && file_exists("../uploads/nfpa704/$nfpa")) {
      $html .= "<td><img src='uploads/nfpa704/$nfpa' style='height:40px; max-width:60px; border:1px solid #ccc; border-radius:4px;'></td>";
    } else {
      $html .= "<td>No hay imagen</td>";
    }

    $html .= "<td>" . (!empty(trim($row['archivo'])) ? "<button class='btn-view-files btn-primary' data-files='{$row['archivo']}'>Ver</button>" : "No hay") . "</td>";
    $html .= "<td>" . (!empty(trim($row['imagen_guia'])) ? "<button class='btn-view-images btn-primary' data-images='{$row['imagen_guia']}'>Ver</button>" : "No hay") . "</td>";
    $html .= "<td>
                <a href='javascript:void(0);' class='delete-icon' data-id='{$row['id']}'><i class='bx bx-trash text-danger action-btn'></i></a>
                <a href='javascript:void(0);' class='edit-icon' data-id='{$row['id']}'><i class='bx bx-edit-alt text-primary action-btn'></i></a>
              </td>";
    $html .= "</tr>";
  }
} else {
  $html .= "<tr><td colspan='9'>No se encontraron resultados</td></tr>";
}

// Cálculo de rangos mostrados
$desde = $total > 0 ? $offset + 1 : 0;
$hasta = min($offset + $limite, $total);
$total_paginas = ceil($total / $limite);

// Enviar respuesta JSON
echo json_encode([
  'html' => $html,
  'total' => $total,
  'desde' => $desde,
  'hasta' => $hasta,
  'pagina_actual' => $pagina,
  'total_paginas' => $total_paginas
]);
?>
