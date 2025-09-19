<?php
session_start();

if (!isset($_SESSION['correo'])) {
  header("Location: login.php");
  exit;
}

// Mostrar modal si no es admin
if ($_SESSION['rol'] !== 'admin') {
  echo <<<HTML
  <!DOCTYPE html>
  <html lang="es">
  <head>
    <meta charset="UTF-8">
    <title>Acceso Restringido</title>
    <link rel="stylesheet" href="../ugr/assets/css/guides.css?v=1.2" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  </head>
  <body>
    <script>
      Swal.fire({
        icon: 'error',
        title: 'Acceso restringido',
        text: 'No tienes permisos para acceder a esta sección. Comunícate con un administrador.',
        confirmButtonText: 'Volver',
        allowOutsideClick: false,
        allowEscapeKey: false
      }).then(() => {
        window.location.href = 'dashboard.php';
      });
    </script>
  </body>
  </html>
  HTML;
  exit;
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include('includes/db.php');

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

$search_safe = "%" . $conn->real_escape_string($search) . "%";

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo LIKE ? OR rol LIKE ? ORDER BY id DESC LIMIT ?, ?");
$stmt->bind_param("ssii", $search_safe, $search_safe, $offset, $items_per_page);
$stmt->execute();
$resultado = $stmt->get_result();

$stmt2 = $conn->prepare("SELECT COUNT(*) AS total FROM usuarios WHERE correo LIKE ? OR rol LIKE ?");
$stmt2->bind_param("ss", $search_safe, $search_safe);
$stmt2->execute();
$result2 = $stmt2->get_result();
$total_row = $result2->fetch_assoc();
$total_pages = ceil($total_row['total'] / $items_per_page);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>VillaStream - Usuarios</title>
  <link rel="stylesheet" href="../ugr/assets/css/guides.css?v=1.2" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>
<body>
<div class="dashboard">
  <div class="sidebar">
    <a href="dashboard.php" class="sidebar-icon" data-tooltip="Dashboard"><i class="bx bx-home"></i></a>
    <a href="guides.php" class="sidebar-icon" data-tooltip="Guías"><i class="bx bx-notepad"></i></a>
    <a href="usuarios.php" class="sidebar-icon active" data-tooltip="Usuarios"><i class="bx bx-user"></i></a>
    <a href="logout.php" class="sidebar-icon" data-tooltip="Cerrar Sesión"><i class="bx bx-log-out"></i></a>
  </div>

  <div class="main-content">
    <div class="header">
      <h1>Gestión de Usuarios</h1>
      <div class="actions">
        <button id="nuevoUsuario" class="btn-primary"><i class="bx bx-user-plus"></i> Nuevo Usuario</button>
        <div class="search-bar">
          <input type="text" id="searchInput" placeholder="Buscar usuario..." value="<?= htmlspecialchars($search) ?>" />
        </div>
      </div>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Activo</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($resultado->num_rows > 0): ?>
            <?php while ($row = $resultado->fetch_assoc()): ?>
              <tr id="row_<?= $row['id'] ?>">
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['correo']) ?></td>
                <td><?= htmlspecialchars($row['rol']) ?></td>
                <td><?= $row['activo'] ? 'Sí' : 'No' ?></td>
                <td>
                  <a href="javascript:void(0);" class="delete-user" data-id="<?= $row['id'] ?>" data-correo="<?= $row['correo'] ?>"><i class="bx bx-trash text-danger action-btn"></i></a>
                  <a href="edit_user.php?id=<?= $row['id'] ?>"><i class="bx bx-edit-alt text-primary action-btn"></i></a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5">No se encontraron usuarios</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="pagination">
      <?php for ($p = 1; $p <= $total_pages; $p++): ?>
        <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>" class="<?= ($p == $page) ? 'active' : '' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
  </div>
</div>

<!-- Modal de creación -->
<div class="modal" id="modalUsuario" style="display:none;">
  <div class="modal-content">
    <span class="close-modal" onclick="document.getElementById('modalUsuario').style.display='none';">&times;</span>
    <h2>Registrar Nuevo Usuario</h2>
    <form id="formUsuario" method="POST" onsubmit="return false;">
      <div class="form-container">
        <div>
          <label for="correo">Correo:</label>
          <input type="email" name="correo" id="correo" required />
          <label for="contrasena">Contraseña:</label>
          <input type="password" name="contrasena" id="contrasena" required />
        </div>
        <div>
          <label for="rol">Rol:</label>
          <select name="rol" id="rol" required>
            <option value="admin">Administrador</option>
            <option value="usuario">Usuario</option>
          </select>
          <label for="activo">Activo:</label>
          <select name="activo" id="activo">
            <option value="1">Sí</option>
            <option value="0">No</option>
          </select>
        </div>
      </div>
      <button type="submit" class="btn-primary" id="crearUsuario">Registrar</button>
    </form>
  </div>
</div>

<script>
  document.getElementById("nuevoUsuario").addEventListener("click", () => {
    document.getElementById("modalUsuario").style.display = "flex";
  });

  document.getElementById("crearUsuario").addEventListener("click", () => {
    const form = document.getElementById("formUsuario");
    const datos = new FormData(form);

    fetch("includes/crear_usuario.php", {
      method: "POST",
      body: datos
    })
    .then(res => res.json())
    .then(res => {
      if (res.success) {
        Swal.fire("¡Usuario creado!", res.message, "success").then(() => location.reload());
      } else {
        Swal.fire("Error", res.message, "error");
      }
    });
  });

  document.querySelectorAll('.delete-user').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;
      const correo = this.dataset.correo;

      Swal.fire({
        title: 'Confirmar eliminación',
        input: 'text',
        inputLabel: `Escribe el correo "${correo}" para confirmar`,
        inputPlaceholder: 'Ejemplo: usuario@correo.com',
        showCancelButton: true,
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        preConfirm: (inputValue) => {
          if (inputValue !== correo) {
            Swal.showValidationMessage('El correo ingresado no coincide.');
          }
        }
      }).then(result => {
        if (result.isConfirmed) {
          fetch(`includes/eliminar_usuario.php?id=${id}&confirm=${encodeURIComponent(correo)}`)
            .then(res => res.json())
            .then(res => {
              if (res.success) {
                document.getElementById(`row_${id}`).remove();
                Swal.fire('¡Eliminado!', 'Usuario eliminado correctamente.', 'success');
              } else {
                Swal.fire('Error', res.message, 'error');
              }
            });
        }
      });
    });
  });
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
