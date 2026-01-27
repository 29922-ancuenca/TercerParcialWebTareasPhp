<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<title>Ejercicios</title>

	<!-- BOOTSTRAP -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
include_once("constantes.php");
require_once("class/class.vehiculo.php");
require_once("class/class.matricula.php");

// -------------------------------
// MODULO ACTIVO
// -------------------------------
$mod = isset($_GET['mod']) ? $_GET['mod'] : 'vehiculo';

// -------------------------------
// NAVBAR (EL QUE PEDISTE)
// -------------------------------
?>
<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">

    <!-- IZQUIERDA -->
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

<div class="container mt-4">

<?php
// -------------------------------
// CONEXION
// -------------------------------
function conectar(){
	echo "<br> CONEXION A LA BASE DE DATOS<br>";
	$c = new mysqli(SERVER,USER,PASS,BD);
	if($c->connect_errno){
		die("Error de conexión: " . $c->connect_errno . ", " . $c->connect_error);
	}else{
		echo "La conexión tuvo éxito .......<br><br>";
	}
	$c->set_charset("utf8");
	return $c;
}

$cn = conectar();
$v  = new vehiculo($cn);
$m  = new Matricula($cn);

// -------------------------------
// GET: d=base64(op/id)
// -------------------------------
if(isset($_GET['d'])){

	echo "<br>PETICION GET <br>";
	echo "<pre>";
		print_r($_GET);
	echo "</pre>";

	$dato = base64_decode($_GET['d']);
	$tmp = explode("/", $dato);

	echo "<br>VARIABLE TEMP <br>";
	echo "<pre>";
		print_r($tmp);
	echo "</pre>";

	$op = $tmp[0];
	$id = $tmp[1];

	if($mod == "vehiculo"){

		if($op=="det")      echo $v->get_detail_vehiculo($id);
		elseif($op=="del")  echo $v->delete_vehiculo($id);
		elseif($op=="act")  echo $v->get_form($id);
		elseif($op=="new")  echo $v->get_form();
		else                echo $v->get_list();

	}else{ // matricula

		if($op=="det")      echo $m->get_detail_matricula($id);
		elseif($op=="del")  echo $m->delete_matricula($id);
		elseif($op=="act")  echo $m->get_form($id);
		elseif($op=="new")  echo $m->get_form();
		else                echo $m->get_list();
	}

// -------------------------------
// POST
// -------------------------------
}else{

	if(isset($_POST['Guardar'])){
		echo "<br>PETICION POST ...... <br>";
		echo "<pre>";
			print_r($_POST);
		echo "</pre>";

		echo '<br><a href="index.php?mod='.$mod.'" class="btn btn-secondary">Regresar</a>';

	}else{
		echo ($mod=="vehiculo") ? $v->get_list() : $m->get_list();
	}
}
?>

</div>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>