<?php
session_start();
if (!isset($_SESSION['correo'])) {
  header("Location: login.php");
  exit;
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
include('includes/db.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Guías de Emergencia - GRED</title>
  <link rel="stylesheet" href="assets/css/guides.css?v=3.0">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />

</head>
<body>
<div class="dashboard">
  <div class="sidebar">
    <a href="dashboard.php" class="sidebar-icon active" data-tooltip="Dashboard"><i class="bx bx-home"></i></a>
    <a href="guides.php" class="sidebar-icon" data-tooltip="Guías"><i class="bx bx-notepad"></i></a>
    <a href="usuarios.php" class="sidebar-icon" data-tooltip="Usuarios"><i class="bx bx-user"></i></a>
    <a href="logout.php" class="sidebar-icon" data-tooltip="Cerrar Sesión"><i class="bx bx-log-out"></i></a>
  </div>

  <div class="main-content">
    <div class="header">
      <h1>Guías de Emergencia</h1>
      <div class="actions">
        <button id="nuevaGuia" class="btn-primary"><i class="bx bx-plus"></i> Nueva Guía</button>
        <form action="includes/importar_guias.php" method="POST" enctype="multipart/form-data" style="margin-left: 10px;">
          <label class="btn-primary" style="cursor: pointer;">
            <input type="file" name="csv_file" accept=".csv" style="display: none;" onchange="this.form.submit();">
            Importar CSV
          </label>
        </form>
        <input type="text" id="searchInput" placeholder="🔍 Buscar guía..." style="margin-left: auto; padding: 6px 10px; width: 250px; border: 1px solid #ccc; border-radius: 4px;" />
      </div>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Material</th>
            <th>Código ONU</th>
            <th>Guía</th>
            <th>Aloha</th>
            <th>Etiqueta DOT</th>
            <th>NFPA</th>
            <th>Archivos</th>
            <th>Imágenes</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody id="tableBody"></tbody>
      </table>
    </div>

    <!-- Pie de tabla (info + selector abajo) -->
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; margin: 20px 10px 5px 10px;">
      <div id="infoRegistros" style="font-size: 0.85em; color:#555;"></div>
      <div style="font-size: 14px;">
        Mostrar:
        <select id="registrosPorPagina" style="padding: 4px 8px; border-radius: 6px; border: 1px solid #ccc;">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
        </select>
      </div>
    </div>

    <div id="pagination" style="display: flex; justify-content: center; gap: 6px; flex-wrap: wrap; margin-bottom: 25px;"></div>
  </div>
</div>

<!-- MODALES -->
<div class="modal" id="modalGuia" style="display:none;">
  <div class="modal-content" style="max-width:700px;">
    <span class="close-modal">&times;</span>
    <h2 id="modalTitle">Registrar Nueva Guía</h2>
    <form id="formGuia" enctype="multipart/form-data" onsubmit="return false;">
      <div class="form-container">
        <div>
          <label>Material:</label><input type="text" name="nombre_material" required>
          <label>Código ONU:</label><input type="text" name="codigo_onu" required>
          <label>Guía de Emergencia:</label><input type="text" name="guia_emergencia" required>
          <label>Aloha Name:</label><input type="text" name="aloha_name">
          <label>Etiqueta DOT:</label><input type="text" name="etiqueta_dot">
        </div>
        <div>
          <label>Archivo (PDF/DOC):</label><input type="file" name="archivo[]" accept=".pdf,.doc,.docx" multiple>
          <label>Imágenes:</label><input type="file" name="imagen_guia[]" accept="image/*" multiple>
          <label>Imagen NFPA 704:</label><input type="file" name="nfpa704_imagen" accept="image/*">
        </div>
      </div>
      <button type="submit" class="btn-primary" id="submitBtn">Registrar</button>
    </form>
  </div>
</div>

<div class="modal" id="modalFiles" style="display:none;">
  <div class="modal-content" style="max-width:700px;">
    <span class="close-modal" id="closeFilesModal">&times;</span>
    <h2 id="modalFilesTitle">Archivos Asociados</h2>
    <div id="modalFilesContent" style="max-height:400px; overflow:auto;"></div>
  </div>
</div>

<!-- JS principal -->
<script src="assets/js/script.js?v=5.1"></script>

<!-- Cierre automático por inactividad -->
<script>
  let tiempoLimite = 10 * 60 * 1000; // 10 minutos en milisegundos
  let temporizador = setTimeout(cerrarSesionPorInactividad, tiempoLimite);

  function resetearTemporizador() {
    clearTimeout(temporizador);
    temporizador = setTimeout(cerrarSesionPorInactividad, tiempoLimite);
  }

  function cerrarSesionPorInactividad() {
    Swal.fire({
      icon: 'info',
      title: 'Sesión cerrada',
      text: 'Tu sesión ha expirado por inactividad.',
      timer: 3000,
      showConfirmButton: false
    }).then(() => {
      window.location.href = 'logout.php';
    });
  }

  document.addEventListener('mousemove', resetearTemporizador);
  document.addEventListener('keypress', resetearTemporizador);
  document.addEventListener('click', resetearTemporizador);
</script>
</body>
</html>
