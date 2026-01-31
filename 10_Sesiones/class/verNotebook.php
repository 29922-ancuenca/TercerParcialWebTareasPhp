<?php
require_once("Notebook.php");
session_start();
		
		
			echo "<br>VARIABLE SESSION: <br>";
			echo "<pre>";
				print_r($_SESSION);
			echo "</pre>";

			echo "<br>VARIABLE POST: <br>";
			echo "<pre>";
				print_r($_POST);
			echo "</pre>";
		

// OPCION 1
$aux = $_SESSION['LISTA'];
$indice = $_POST['op'];
$obj = $aux[$indice];

// OPCION 2
//$obj = $_SESSION['LISTA'][$_POST['op']];


echo "<h1>". $obj->getMarca()."</h1>";
echo "Codigo: ". $obj->getCodigo() . "<br>";
echo "Precio: $". $obj->getPrecio(). "<br>";
echo "<br>";
echo "<a href='cerrar.php'>Cerrar Session</a>";
?>
