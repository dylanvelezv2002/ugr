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
if (isset($_SESSION['correo'])) {
  header("Location: dashboard.php");
  exit;
}
include('includes/db.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ? AND activo = 1 LIMIT 1");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($contrasena, $usuario['contrasena'])) {
            $_SESSION['correo'] = $usuario['correo'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['bienvenida'] = "Bienvenido, " . explode("@", $usuario['correo'])[0] . " 👋";
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "El correo no existe o está inactivo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../ugr/assets/css/login.css?v=1.2.1">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .slider-container img {
      display: none;
    }
    .slider-container img.active {
      display: block;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="section_login">
      <h1>Bienvenido de nuevo</h1>
      <p class="des">¡Estamos tan emocionados de verte de nuevo!</p>
      <form class="form" method="POST" autocomplete="off">
        <div class="input_group">
          <label for="correo">CORREO</label>
          <input type="text" id="correo" name="correo" required />
        </div>
        <div class="input_group">
          <label for="contrasena">CONTRASEÑA</label>
          <input type="password" id="contrasena" name="contrasena" required />
          <a class="link_form" href="#">¿Olvidaste tu contraseña?</a>
        </div>
        <button type="submit" class="btn_form">Iniciar sesión</button>
       <p class="des_form"><- <a class="link_form" href="index.php">Ir al buscador</a></p>
      </form>
    </div>

    <!-- Slider bonito a la derecha -->
    <div class="section_img">
      <div class="slider-container">
        <img src="../ugr/assets/img/slider01.jpeg" class="active" alt="slide 1">
        <img src="../ugr/assets/img/slider02.jpeg" alt="slide 2">
        <img src="../ugr/assets/img/slider03.jpeg" alt="slide 3">
        <img src="../ugr/assets/img/slider04.jpeg" alt="slide 4">
        <img src="../ugr/assets/img/slider05.jpeg" alt="slide 5">
        <img src="../ugr/assets/img/slider06.jpeg" alt="slide 6">
      </div>
    </div>
  </div>

  <?php if (!empty($error)) : ?>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Error de inicio de sesión',
        text: '<?= $error ?>',
        confirmButtonText: 'Cerrar'
      });
    </script>
  <?php endif; ?>

  <script>
    // Slider automático
    const slides = document.querySelectorAll(".slider-container img");
    let current = 0;

    setInterval(() => {
      slides[current].classList.remove("active");
      current = (current + 1) % slides.length;
      slides[current].classList.add("active");
    }, 3000);
  </script>
</body>
</html>
