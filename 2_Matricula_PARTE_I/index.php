<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>Matrículas Vehículos - PARTE I</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
</head>

<body>
<?php
	require_once("class.matricula.php");

	$db = conectar();
	$objetoMatricula = new matricula($db);

	echo "<h1>MATRÍCULAS - PARTE I</h1>";

	// Si viene una operación por GET: index.php?d=C/1  (o R/U/D)
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

		// Sin parámetro d: muestra el listado
		echo "<pre>";
			print_r($_POST);
		echo "</pre>";

		echo $objetoMatricula->get_list();
	}

	// CONEXIÓN
	function conectar(){
		$server = "localhost";
		$user   = "root";
		$pass   = "123";
		$db     = "matriculacionfinal";
		$c = new mysqli($server, $user, $pass, $db);
		$c->set_charset("utf8");
		return $c;
	}
?>
</body>
</html>