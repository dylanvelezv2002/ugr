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

session_start();
if (!isset($_SESSION['correo'])) {
  header("Location: login.php");
  exit;
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include('includes/db.php');

// Consulta de estadísticas
$total_guias = $conn->query("SELECT COUNT(*) as total FROM guias")->fetch_assoc()['total'];
$guias_con_archivos = $conn->query("SELECT COUNT(*) as total FROM guias WHERE archivo != ''")->fetch_assoc()['total'];
$guias_con_imagenes = $conn->query("SELECT COUNT(*) as total FROM guias WHERE imagen_guia != ''")->fetch_assoc()['total'];

// Mensaje según la hora
date_default_timezone_set("America/Guayaquil");
$hora = date("H");
$saludo = ($hora < 12) ? "¡Buenos días!" : (($hora < 18) ? "¡Buenas tardes!" : "¡Buenas noches!");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>VillaStream - Dashboard</title>
  <link rel="stylesheet" href="../ugr/assets/css/guides.css?v=1.3" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <style>
    .header-flex {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
    }
    .greeting {
      font-size: 14px;
      color: var(--text-secondary);
      margin: 0;
    }
    .cards {
      display: flex;
      gap: 20px;
      margin-top: 20px;
      flex-wrap: wrap;
    }
    .card {
      flex: 1 1 220px;
      background: var(--bg-card);
      border-radius: 16px;
      padding: 24px;
      text-align: center;
      box-shadow: var(--shadow-md);
    }
    .card h3 {
      margin-bottom: 8px;
      font-size: 16px;
      color: var(--text-secondary);
    }
    .card p {
      font-size: 32px;
      font-weight: bold;
      color: var(--text-primary);
    }
    .chart-container {
      margin-top: 30px;
      background: var(--bg-card);
      padding: 20px;
      border-radius: 16px;
      box-shadow: var(--shadow-md);
    }
    canvas {
      max-width: 100%;
      height: 300px !important;
    }
  </style>
</head>
<body>
  <div class="dashboard">
    <!-- Sidebar -->
    <div class="sidebar">
      <a href="dashboard.php" class="sidebar-icon active" data-tooltip="Dashboard">
        <i class="bx bx-home"></i>
      </a>
      <a href="guides.php" class="sidebar-icon" data-tooltip="Guías">
        <i class="bx bx-notepad"></i>
      </a>
      <a href="usuarios.php" class="sidebar-icon" data-tooltip="Guías"><i class="bx bx-user"></i></a>
      <a href="logout.php" class="sidebar-icon" data-tooltip="Cerrar Sesión">
        <i class="bx bx-log-out"></i>
      </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <div class="header header-flex">
        <h1>Dashboard</h1>
        <p class="greeting"><?= $saludo ?> Bienvenido al sistema de emergencias de la UGR.</p>
      </div>

      <div class="cards">
        <div class="card">
          <h3>Total de Guías</h3>
          <p><?= $total_guias ?></p>
        </div>
        <div class="card">
          <h3>Guías con Archivos</h3>
          <p><?= $guias_con_archivos ?></p>
        </div>
        <div class="card">
          <h3>Guías con Imágenes</h3>
          <p><?= $guias_con_imagenes ?></p>
        </div>
      </div>

      <div class="chart-container">
        <h3 style="margin-bottom: 16px;">Resumen Visual</h3>
        <canvas id="chartResumen"></canvas>
      </div>
    </div>
  </div>

  <script>
    const ctx = document.getElementById('chartResumen').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Total de Guías', 'Con Archivos', 'Con Imágenes'],
        datasets: [{
          label: 'Estadísticas',
          data: [<?= $total_guias ?>, <?= $guias_con_archivos ?>, <?= $guias_con_imagenes ?>],
          backgroundColor: ['#0b2b3f', '#6366f1', '#10b981'],
          borderRadius: 10,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        }
      }
    });

    <?php if (isset($_SESSION['bienvenida'])): ?>
      Swal.fire({
        title: "🎉 <?= $_SESSION['bienvenida'] ?>",
        text: "Acceso correcto al sistema de emergencias",
        icon: "success",
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
      });
      <?php unset($_SESSION['bienvenida']); ?>
    <?php endif; ?>

      if (window.performance && window.performance.navigation.type === 2) {
    // Si el usuario regresó con el botón de "atrás"
    window.location.href = "login.php";
  }
  </script>
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
