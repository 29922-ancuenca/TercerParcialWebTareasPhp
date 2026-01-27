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

/* ===============================
   MÓDULO ACTIVO
================================ */
$mod = isset($_GET['mod']) ? $_GET['mod'] : "vehiculo";
?>

<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">

    <a class="navbar-brand" href="index.php">Ejercicios</a>

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
    }else{
      echo $v->get_list();
    }
  }
}

/* =========================================================
   MATRÍCULAS
========================================================= */
if($mod == "matricula"){

  if(isset($_GET['d'])){
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
?>

</div>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>