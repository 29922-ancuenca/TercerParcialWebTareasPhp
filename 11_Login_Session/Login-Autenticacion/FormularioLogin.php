
<html lang="es">
<head>
	<title>Formulario de login</title>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="recursos/estilos.css">
</head>
<body>
	
<?php
require("Notebook.php");
session_start();
$obj = $_SESSION['listaNote'];
			
	
	echo '<form action="validar.php" method="POST">';
		echo '<h2>Formulario de login</h2>';
		//echo '<input type="text" placeholder="&#128272; Usuario" name="usuario">';
		echo '<select name="usuario">';
			echo "<option disabled selected>" . "Escoje un usuario...." . "</option>";	
			foreach($obj as $n){
				echo "<option value=".$n->getMarca().">".$n->getMarca()."</option>";
			}
		echo "</select>";
		echo '<input type="password" placeholder="&#128272; Contraseña" name="clave">';	
		echo '<input type="submit" value="LOGIN">';
	echo "</form>";



?>

	
</body>
</html>
