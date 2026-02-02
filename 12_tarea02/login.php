<?php
session_start();
require_once "constantes.php";

$mensajeError = "";

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $usuario = isset($_POST['username']) ? trim($_POST['username']) : '';
    $clave   = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($usuario === '' || $clave === '') {
        $mensajeError = "Debe ingresar usuario y contraseña.";
    } else {
        // Conexión a la base de datos de usuarios
        $cn = new mysqli(SERVER, USER, PASS, "usuariosdb");
        if ($cn->connect_errno) {
            die("Error de conexión: " . $cn->connect_error);
        }
        $cn->set_charset("utf8");

        // Validar que el username y contraseña existan en la BD y obtener su rol
        $sql = "SELECT u.id, u.username, r.rol
          FROM usuarios u
          LEFT JOIN roles r ON u.roles_id = r.id
          WHERE u.username = ? AND u.password = ?
          LIMIT 1";
        $stmt = $cn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('ss', $usuario, $clave);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado && $resultado->num_rows === 1) {
                // Login correcto: guardar usuario y rol en sesión y redirigir al HOME PAGE
                $fila = $resultado->fetch_assoc();
                $_SESSION['usuario'] = $fila['username'];
                $_SESSION['rol']     = isset($fila['rol']) ? $fila['rol'] : null;
                // Bandera de administrador: usuario ADM o rol ADM
                $_SESSION['es_admin'] = ($_SESSION['usuario'] === 'ADM') || (isset($_SESSION['rol']) && $_SESSION['rol'] === 'ADM');

                header('Location: index.html');
                exit;
            } else {
                $mensajeError = "Usuario o contraseña incorrectos.";
            }

            $stmt->close();
        } else {
            $mensajeError = "Error al preparar la consulta.";
        }

        $cn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Login - Vehículos y Matrículas</title>
    <!-- BOOTSTRAP 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-center mb-4">Aplicación Vehículos y Matrículas</h1>
    <p class="text-center">Por favor, inicie sesión para acceder al HOME PAGE</p>
      <div class="text-center mt-4">
    <a href="login.php" class="btn btn-primary btn-lg">Ingresar</a>
  </div>
</div>

<!-- Modal LOGIN -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loginModalLabel">Inicio de sesión</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if ($mensajeError !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php">
          <div class="mb-3">
            <label for="username" class="form-label">Usuario</label>
            <input type="text" class="form-control" id="username" name="username" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <div class="modal-footer px-0">
            <button type="submit" name="login" class="btn btn-primary">Aceptar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Mostrar el modal automáticamente al cargar la página
  document.addEventListener('DOMContentLoaded', function () {
      var myModal = new bootstrap.Modal(document.getElementById('loginModal'));
      myModal.show();
  });
</script>
</body>
</html>
