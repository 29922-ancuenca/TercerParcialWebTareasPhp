<?php
session_start();
require_once "constantes.php";

// Solo permite acceso si hay usuario en sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$usuarioActual = $_SESSION['usuario'];

// Solo el SUPERADM puede crear usuarios
if ($usuarioActual !== 'SUPERADM') {
    echo '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Crear Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="alert alert-danger text-center">
      No tiene permisos para acceder a esta opción.
    </div>
    <div class="text-center mt-3">
      <a href="index.html" class="btn btn-secondary">Regresar</a>
    </div>
  </div>
</body>
</html>';
    exit;
}

  // Función listar usuarios (similar a get_list de las otras clases)
  function listar_usuarios()
  {
    $html = "";

    // Conexión a la base de datos de usuarios
    $cn = new mysqli(SERVER, USER, PASS, "usuariosdb");
    if ($cn->connect_errno) {
      return '<div class="alert alert-danger mt-4">Error de conexión al listar usuarios.</div>';
    }
    $cn->set_charset("utf8");

    // Solo mostrar AGENTES (rol R), ocultando ADM y SUPERADM
    $sql = "SELECT u.username, u.password, r.nombreRol
      FROM usuarios u
      LEFT JOIN roles r ON u.roles_id = r.id
      WHERE r.rol = 'R'
      ORDER BY u.id";
    $res = $cn->query($sql);

    $html .= '<h2 class="mt-5 mb-3 text-center">Usuarios existentes</h2>';
    $html .= '<div class="table-responsive">';
    $html .= '<table class="table table-bordered table-striped table-sm align-middle">';
    $html .= '<thead class="table-dark">';
    $html .= '<tr>';
    $html .= '<th>Username</th>';
    $html .= '<th>Password</th>';
    $html .= '<th>Rol (nombre)</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    if ($res && $res->num_rows > 0) {
      while ($row = $res->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['password'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '<td>' . htmlspecialchars($row['nombreRol'], ENT_QUOTES, 'UTF-8') . '</td>';
        $html .= '</tr>';
      }
    } else {
      $html .= '<tr><td colspan="3" class="text-center">No hay agentes registrados.</td></tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';
    $html .= '</div>';

    if ($res) {
      $res->free();
    }
    $cn->close();

    return $html;
  }

$mensajeError = "";
$mensajeOk    = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === '' || $password === '') {
        $mensajeError = "Debe ingresar username (placa) y password.";
    } else {
        // Conexión a la base de datos de usuarios
        $cn = new mysqli(SERVER, USER, PASS, "usuariosdb");
        if ($cn->connect_errno) {
            die("Error de conexión: " . $cn->connect_error);
        }
        $cn->set_charset("utf8");

        // Verificar si ya existe el usuario
        $sqlCheck = "SELECT id FROM usuarios WHERE username = ? LIMIT 1";
        $stmtCheck = $cn->prepare($sqlCheck);
        if ($stmtCheck) {
            $stmtCheck->bind_param('s', $username);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->get_result();
            if ($resCheck && $resCheck->num_rows > 0) {
                $mensajeError = "Ya existe un usuario con esa placa.";
            }
            $stmtCheck->close();
        }

        if ($mensajeError === '') {
            // Asignar siempre rol AGENTE (roles_id = 2)
            $roles_id = 2;
            $sqlIns = "INSERT INTO usuarios (username, password, roles_id) VALUES (?,?,?)";
            $stmtIns = $cn->prepare($sqlIns);
            if ($stmtIns) {
                $stmtIns->bind_param('ssi', $username, $password, $roles_id);
                if ($stmtIns->execute()) {
                    $mensajeOk = "Usuario creado correctamente.";
                } else {
                    $mensajeError = "No se pudo crear el usuario.";
                }
                $stmtIns->close();
            } else {
                $mensajeError = "Error al preparar la inserción.";
            }
        }

        $cn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Crear Usuario - SUPERADM</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
      <?php echo htmlspecialchars($usuarioActual, ENT_QUOTES, 'UTF-8'); ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarUsuarios" aria-controls="navbarUsuarios" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarUsuarios">
      <ul class="navbar-nav ms-auto">
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5" style="max-width: 800px;">
  <h1 class="text-center mb-4">Crear Usuario (AGENTE)</h1>

  <div id="seccion_formulario">
  <?php if ($mensajeError !== ''): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endif; ?>

  <?php if ($mensajeOk !== ''): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensajeOk, ENT_QUOTES, 'UTF-8'); ?></div>
  <?php endif; ?>

  <form method="post" action="create_usuario.php">
    <div class="mb-3">
      <label for="username" class="form-label">Username (placa del vehículo)</label>
      <input type="text" class="form-control" id="username" name="username" required>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <div class="d-flex justify-content-between">
      <a href="index.html" class="btn btn-secondary">Cancelar</a>
      <button type="submit" name="crear" class="btn btn-primary">Crear Usuario</button>
    </div>
  </form>
  </div>

  <!-- Listado de usuarios (anteriores y nuevos) -->
  <div id="seccion_tabla" class="mt-4">
    <?php echo listar_usuarios(); ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
