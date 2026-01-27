<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>Matriculas Vehículos PARTE I</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
</head>
<body>
	<?php
		require_once("class.vehiculo.php");
		require_once("class.matricula.php");

		$cn = conectar();
		$objetoVehiculo = new vehiculo($cn);

		$objetoMatricula = new Matricula($cn);
		

//PARTE 1.1		
	echo "<h2>CONEXIÓN A LA BASE DE DATOS</h2>";
	echo $objetoVehiculo->get_list();
	echo "<br>";	
	echo "<br>";
	echo $objetoMatricula->get_list();


	

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
</body>
</html>