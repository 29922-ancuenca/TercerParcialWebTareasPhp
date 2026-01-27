<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
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
	<title>Vehículos y Matrículas - PARTE I</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />

	<!-- Bootstrap 5 (solo para navbar) -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body>

<?php
	require_once("class.vehiculo.php");
	require_once("class.matricula.php");

	$db = conectar();
	$objetoVehiculo  = new vehiculo($db);
	$objetoMatricula = new matricula($db);

	$mod = isset($_GET['mod']) ? $_GET['mod'] : "vehiculo";
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

      <!-- EMPUJA LO SIGUIENTE A LA DERECHA -->
      <ul class="navbar-nav me-auto">
        <!-- vacío a propósito -->
      </ul>

      <!-- DERECHA -->
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
// ===================== VEHICULO =====================
if($mod == "vehiculo"){

	echo "<h1>VEHÍCULOS PARTE I</h1>";

	if(isset($_GET['d'])){

		echo "<pre>";
			print_r($_GET);
		echo "</pre>";

		$tmp = explode("/", $_GET['d']);

		echo "<pre>";
			print_r($tmp);
		echo "</pre>";

		$op = $tmp[0];
		$id = $tmp[1];

		echo "op = " .$op . "<br>" ;
		echo "id = " .$id . "<br>" ;

		switch($op){
			case "C": echo $objetoVehiculo->get_form($id);
					  break;
			case "R":
					  break;
			case "U":
					  break;
			case "D":
					  break;
		}

	}else{

		echo "<pre>";
			print_r($_POST);
		echo "</pre>";

		echo $objetoVehiculo->get_list();
	}
}

// ===================== MATRICULA =====================
if($mod == "matricula"){

	echo "<h1>MATRÍCULAS - PARTE I</h1>";

	if(isset($_GET['d'])){

		echo "<pre>";
			print_r($_GET);
		echo "</pre>";

		$tmp = explode("/", $_GET['d']);

		echo "<pre>";
			print_r($tmp);
		echo "</pre>";

		$op = $tmp[0];
		$id = $tmp[1];

		echo "op = " . $op . "<br>";
		echo "id = " . $id . "<br>";

		switch($op){
			case "C":
				echo $objetoMatricula->get_form($id);
				break;
			case "R":
				break;
			case "U":
				break;
			case "D":
				break;
		}

	}else{

		echo "<pre>";
			print_r($_POST);
		echo "</pre>";

		echo $objetoMatricula->get_list();
	}
}

//*******************************************************
function conectar(){
	$server = "localhost";
	$user = "root";
	$pass = "123";
	$db = "matriculacionfinal";
	$c = new mysqli($server,$user,$pass,$db);
	$c->set_charset("utf8");
	return $c;
}
//**********************************************************
?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>