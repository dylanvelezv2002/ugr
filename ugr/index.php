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
 * Este sistema ha sido desarrollado con fines estrictamente académicos y
 * educativos. Queda prohibido su uso con fines comerciales sin autorización.
 */

session_start();
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $material = trim($_POST['material']);
  $onu = trim($_POST['onu']);
  $guia = trim($_POST['guia']);

  if ($material === '' && $onu === '' && $guia === '') {
    $_SESSION['mensaje'] = "Debes llenar al menos un campo para realizar la búsqueda.";
  } else {
    $condiciones = [];
    $parametros = [];
    $tipos = '';

    if ($material !== '') {
      $condiciones[] = "nombre_material LIKE ?";
      $parametros[] = "%" . $material . "%";
      $tipos .= 's';
    }
    if ($onu !== '') {
      $condiciones[] = "codigo_onu LIKE ?";
      $parametros[] = "%" . $onu . "%";
      $tipos .= 's';
    }
    if ($guia !== '') {
      $condiciones[] = "guia_emergencia LIKE ?";
      $parametros[] = "%" . $guia . "%";
      $tipos .= 's';
    }

    $query = "SELECT * FROM guias WHERE " . implode(" OR ", $condiciones);
    $stmt = $conn->prepare($query);
    $stmt->bind_param($tipos, ...$parametros);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 1) {
      $_SESSION['lista_resultados'] = [];
      while ($row = $result->fetch_assoc()) {
        $_SESSION['lista_resultados'][] = $row;
      }
    } elseif ($result->num_rows === 1) {
      $_SESSION['resultado'] = $result->fetch_assoc();
    } else {
      $_SESSION['sin_resultado'] = true;
    }
  }
  header("Location: index.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Buscar Material Peligroso</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/css/index.css?v=3.4">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container">
  <div class="content">
    <div class="left-section">
      <img src="assets/img/ugr.png" alt="Logo UGR" class="logo-img">
      <h1>Buscar Guías de Emergencia</h1>
      <p>Utiliza al menos uno de los campos para encontrar materiales peligrosos registrados.</p>
      <a href="login.php" class="btn-primary">Iniciar Sesión</a>
    </div>
    <div class="right-section">
      <form method="POST">
        <input type="text" name="material" placeholder="Nombre del material">
        <input type="text" name="onu" placeholder="Código ONU">
        <input type="text" name="guia" placeholder="Guía de emergencia">
        <button type="submit">BUSCAR</button>
      </form>
    </div>
  </div>
</div>

<footer>
  <p>Universidad Estatal de Bolívar — <a href="https://www.ueb.edu.ec" target="_blank">www.ueb.edu.ec</a></p>
  <p>Creado por: Dylan Vélez Villasagua</p>
</footer>

<!-- Modal único resultado -->
<div id="modalGuia" class="modal">
  <div class="modal-content">
    <div class="modal-header" id="modalTitle">NOMBRE DEL MATERIAL</div>
    <span class="modal-close" onclick="cerrarModal()">&times;</span>
    <div class="modal-body" id="modalBodyContent"></div>
  </div>
</div>

<!-- Modal múltiples resultados -->
<div id="modalLista" class="modal">
  <div class="modal-content">
    <div class="modal-header">Resultados encontrados</div>
    <span class="modal-close" onclick="cerrarLista()">&times;</span>
    <div id="listaResultados"></div>
  </div>
</div>

<script>
  function mostrarGuia(data) {
    const modal = document.getElementById('modalGuia');
    const body = document.getElementById('modalBodyContent');
    const title = document.getElementById('modalTitle');

    const nombreMaterial = data.nombre_material?.toUpperCase() || 'SIN NOMBRE';
    const guia = data.guia_emergencia ?? '---';
    const onu = data.codigo_onu ?? '---';
    const aloha = data.aloha_name ?? '---';
    const etiquetas = data.etiqueta_dot ? data.etiqueta_dot.split(',').join('<br>') : '---';
    const nfpa = data.nfpa704_imagen ? `<img src="uploads/nfpa704/${data.nfpa704_imagen}" style="width:80px; margin-top:20px;">` : '';

    const simbolos = (data.imagen_guia ?? '').split(',').filter(i => i.trim()).map(img =>
      `<img src="uploads/images/${img}" style="width:80px; margin:10px;">`
    ).join('');

    const archivoPDF = (data.archivo ?? '').split(',').filter(i => i.trim()).map(file => {
      const ext = file.split('.').pop().toLowerCase();
      const isMobile = window.innerWidth <= 1024;
      if (ext === 'pdf') {
        if (isMobile) {
          return `
            <iframe src="uploads/documents/${file}#toolbar=0" style="width:100%; height:400px; border:none; border-radius:6px; margin-bottom:15px;"></iframe>
            <a href="uploads/documents/${file}" target="_blank" class="btn-primary" style="display:inline-block;">Ver guía completa en PDF</a>`;
        } else {
          return `<iframe src="uploads/documents/${file}#toolbar=0" style="width:100%; max-height:550px; border:none; border-radius:6px;"></iframe>`;
        }
      } else {
        return `<img src="uploads/documents/${file}" style="width:100%; border-radius:6px;">`;
      }
    }).join('');

    title.textContent = nombreMaterial;

    body.innerHTML = `
      <div style="flex:1; min-width:300px; font-family: Montserrat, sans-serif; color:#0b2b3f; text-align:center;">
        <p style="font-size: 20px; font-weight: bold;">Guía de Respuesta ${guia}<br>Código ONU ${onu}</p>
        <div>${simbolos}</div>
        <p style="font-size: 18px;"><strong>Aloha Name:</strong> ${aloha}</p>
        <p><strong>Etiqueta de peligro del DOT:</strong><br>${etiquetas}</p>
        <div>${nfpa}</div>
      </div>
      <div style="flex:1; min-width:300px;">${archivoPDF || '<p>No se ha subido la guía.</p>'}</div>
    `;
    modal.style.display = 'flex';
  }

  function cerrarModal() {
    document.getElementById('modalGuia').style.display = 'none';
  }

  function cerrarLista() {
    document.getElementById('modalLista').style.display = 'none';
  }

  function abrirLista(resultados) {
    const contenedor = document.getElementById('listaResultados');
    contenedor.innerHTML = '';

    resultados.forEach(data => {
      const div = document.createElement('div');
      div.innerHTML = `<strong>${data.nombre_material}</strong> — ONU: ${data.codigo_onu} — Guía: ${data.guia_emergencia}`;
      div.onclick = () => {
        cerrarLista();
        setTimeout(() => mostrarGuia(data), 200);
      };
      contenedor.appendChild(div);
    });

    document.getElementById('modalLista').style.display = 'flex';
  }
</script>

<?php
if (isset($_SESSION['mensaje'])) {
  echo "<script>Swal.fire('Atención', '{$_SESSION['mensaje']}', 'warning');</script>";
  unset($_SESSION['mensaje']);
}
if (isset($_SESSION['sin_resultado'])) {
  echo "<script>Swal.fire('Sin resultados', 'No se encontraron coincidencias.', 'info');</script>";
  unset($_SESSION['sin_resultado']);
}
if (isset($_SESSION['resultado'])) {
  $data = json_encode($_SESSION['resultado']);
  echo "<script>window.onload = () => mostrarGuia($data);</script>";
  unset($_SESSION['resultado']);
}
if (isset($_SESSION['lista_resultados'])) {
  $lista = json_encode($_SESSION['lista_resultados']);
  echo "<script>window.onload = () => abrirLista($lista);</script>";
  unset($_SESSION['lista_resultados']);
}
?>
</body>
</html>
