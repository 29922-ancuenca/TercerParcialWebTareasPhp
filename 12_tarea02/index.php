<?php
session_start();

// SUPERADM solo puede crear usuarios, no acceder a CRUDs ni Matriculación
if (isset($_SESSION['usuario']) && $_SESSION['usuario'] === 'SUPERADM'
    && (isset($_GET['mod']) || isset($_GET['d']) || !empty($_POST))) {
  echo '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Acceso restringido</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5" style="max-width:600px;">
    <div class="alert alert-danger text-center mb-4">
      No tiene permisos para acceder a estas opciones.
    </div>
    <div class="d-flex justify-content-between">
      <a href="index.html" class="btn btn-secondary">Volver al inicio</a>
      <a href="create_usuario.php" class="btn btn-primary">Crear usuarios</a>
    </div>
  </div>
</body>
</html>';
  exit;
}

// Si se entra a index.php SIN parámetros (inicio de la app),
// redirigir siempre al LOGIN con modal.
if (!isset($_GET['mod']) && !isset($_GET['d']) && empty($_POST)) {
  header('Location: login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Vehículos y Matrículas</title>

  <!-- BOOTSTRAP -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<style>
  /* Anula Bootstrap SOLO para tablas */
  table {
    border-collapse: collapse !important;
    border: 1px solid black !important;
  }
  th, td {
    border: 1px solid black !important;
    padding: 6px;
  }
  th {
    font-weight: bold !important;
  }
</style>

<body>

<?php
require_once("constantes.php");
require_once("class/class.vehiculo.php");
require_once("class/class.matricula.php");
require_once("class/class.agencia.php");

/* ===============================
   CONEXIÓN
================================ */
function conectar(){
  //echo "<br> CONEXION A LA BASE DE DATOS<br>";
  $c = new mysqli(SERVER, USER, PASS, BD);
  if($c->connect_errno){
    die("Error de conexión: " . $c->connect_error);
  }else{
		//echo "La conexión tuvo éxito .......<br><br>";
  }
  $c->set_charset("utf8");
  return $c;
}

$cn = conectar();
$v  = new vehiculo($cn);
$m  = new Matricula($cn);
$a  = new Agencia($cn);

/* ===============================
   MÓDULO ACTIVO
================================ */
$mod = isset($_GET['mod']) ? $_GET['mod'] : "vehiculo";
// Banderas de rol según sesión
$es_admin  = (isset($_SESSION['usuario']) && $_SESSION['usuario'] === 'ADM')
       || (isset($_SESSION['rol']) && $_SESSION['rol'] === 'ADM');
$es_agente = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'R');
?>

<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">

    <a class="navbar-brand" href="index.php">
      <?= isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario'], ENT_QUOTES, 'UTF-8') : 'Ejercicios' ?>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto"></ul>

      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link <?= ($mod=="vehiculo") ? "active" : "" ?>" href="index.php?mod=vehiculo">
            Vehículos
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($mod=="matricula") ? "active" : "" ?>" href="index.php?mod=matricula">
            Matrículas
          </a>
        </li>
        <?php if($es_agente): ?>
        <li class="nav-item">
          <a class="nav-link <?= (isset($_GET['accion']) && $_GET['accion']==='agente') ? "active" : "" ?>" href="index.php?mod=matricula&accion=agente">
            Matriculación
          </a>
        </li>
        <?php endif; ?>
        <li class="nav-item">
          <a class="nav-link <?= ($mod=="agencia") ? "active" : "" ?>" href="index.php?mod=agencia">
            Agencias
          </a>
        </li>
      </ul>
    </div>

  </div>
</nav>

<!-- ================= CONTENIDO ================= -->
<div class="container mt-4">

<?php
/* =========================================================
   VEHÍCULOS
========================================================= */
if($mod == "vehiculo"){

  if(isset($_GET['d'])){
    $dato = base64_decode($_GET['d']);
    $tmp  = explode("/", $dato);
    $op   = $tmp[0];
    $id   = $tmp[1];

    if($op=="del"){
      $v->delete_vehiculo($id);
    }elseif($op=="det"){
      echo $v->get_detail_vehiculo($id);
    }elseif($op=="new"){
      echo $v->get_form();
    }elseif($op=="act"){
      echo $v->get_form($id);
    }

  }else{

          echo "<br>PETICION POST <br>";
			echo "<pre>";
					print_r($_POST);
			echo "</pre>";

    if(isset($_POST['Guardar']) && $_POST['op']=="new"){
      $v->save_vehiculo();
    }elseif(isset($_POST['Guardar']) && $_POST['op']=="update"){
      $v->update_vehiculo();
    }elseif(isset($_POST['btnBuscar'])){
      $placa_buscar = isset($_POST['placa_buscar']) ? $_POST['placa_buscar'] : NULL;
      echo $v->get_list($placa_buscar);
    }else{
      echo $v->get_list();
    }
  }
}

/* =========================================================
  MATRÍCULAS
========================================================= */
if($mod == "matricula"){

  // Flujo especial de matriculación para agentes
  if(isset($_GET['accion']) && $_GET['accion']==='agente'){

    if($es_agente){
      // Mostrar formulario especial para agente
      echo $m->get_form_agente();

      // Procesar envío del formulario de agente
      if(isset($_POST['Guardar']) && isset($_POST['op']) && $_POST['op']=="new"){
        $m->save_matricula();
      }

    }else{
      $mensaje = "acceder a la opción de Matriculación (solo agentes)";
      echo "<div class='alert alert-danger text-center'>No tiene permisos para $mensaje</div>";
    }

  }elseif(isset($_GET['d'])){
    $dato = base64_decode($_GET['d']);
    $tmp  = explode("/", $dato);
    $op   = $tmp[0];
    $id   = $tmp[1];

    if($op=="del"){
      $m->delete_matricula($id);
    }elseif($op=="det"){
      echo $m->get_detail_matricula($id);
    }elseif($op=="new"){
      echo $m->get_form();
    }elseif($op=="act"){
      echo $m->get_form($id);
    }

  }else{

    if(isset($_POST['Guardar']) && $_POST['op']=="new"){
      $m->save_matricula();
    }elseif(isset($_POST['Guardar']) && $_POST['op']=="update"){
      $m->update_matricula();
    }else{
      echo $m->get_list();
    }
  }
}

/* =========================================================
   AGENCIAS (CENTROS DE MATRICULACIÓN)
========================================================= */
if($mod == "agencia"){

  if(isset($_GET['d'])){
    $dato = base64_decode($_GET['d']);
    $tmp  = explode("/", $dato);
    $op   = $tmp[0];
    $id   = $tmp[1];

    if($op=="del"){
      $a->delete_agencia($id);
    }elseif($op=="det"){
      echo $a->get_detail_agencia($id);
    }elseif($op=="new"){
      echo $a->get_form();
    }elseif($op=="act"){
      echo $a->get_form($id);
    }

  }else{

    if(isset($_POST['Guardar']) && $_POST['op']=="new"){
      $a->save_agencia();
    }elseif(isset($_POST['Guardar']) && $_POST['op']=="update"){
      $a->update_agencia();
    }else{
      echo $a->get_list();
    }
  }
}
?>

</div>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>