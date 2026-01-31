<html>
  <head>
    <meta charset="utf-8">
    <title>Sesiones en PHP</title>
  </head>
  <body>
     <table border=0 alingn="center" style="width:100%">
		<tr>
			<th colspan="3"> <a href="../index.html">Página principal</a> </th>
		</tr>
	</table>
  </body>
</html>

<?php

require_once("Notebook.php");

// Conexión a la base de datos 
$conexion = new mysqli("localhost", "root", "123", "SesionesBD");

if ($conexion->connect_errno) {
	die("Error de conexión a la base de datos: " . $conexion->connect_error);
}

/*
	Obtener los datos de la tabla `notebook` y
	llenar el arreglo de objetos Notebook a partir de la BD
*/

$sql = "SELECT Codigo, Marca, Precio FROM notebook";
$resultado = $conexion->query($sql);

$notebooks = array();

if ($resultado) {
	while ($fila = $resultado->fetch_assoc()) {
		$nota = new Notebook($fila['Codigo'], $fila['Marca'], $fila['Precio']);
		// Usamos la marca como índice
		$notebooks[$fila['Marca']] = $nota;
	}
	$resultado->free();
}

$conexion->close();

	
/*
	//Mostrar el precio
	echo "<h1>". $notebooks['Dell']->getMarca()."</h1>";
	echo "Precio $".$notebooks['Dell']->getPrecio() . "<br><br>";
*/	
	
	
	/*Recorrer*/
	echo "<h1>Recorrer un vector con foreach</h1>";
	echo "<table border='1'>";
		echo "<tr>";
			echo "<th>Codigo</td>";
			echo "<th>Marca</td>";
			echo "<th>Precio</td>";
		echo"</tr>";
	

    foreach($notebooks as $obj){
        echo "<tr>";
			echo "<td>".$obj->getCodigo()."</td>";
			echo "<td>".$obj->getMarca()."</td>";
			echo "<td>".$obj->getPrecio()."</td>";
		
		echo"</tr>";
	}
    
	echo"</table>";
    echo"<br>";
    echo"<br>";
    echo"<br>";

/*  ESCOGER USUARIO PARA SESION */
	echo '<form action="verNotebook.php" method="POST">';
	echo '<select id="op" name="op">';
		
    foreach($notebooks as $obj){
        	echo "<option value=".$obj->getMarca().">".$obj->getMarca()."</option>";
	}
	echo "</select>";
	echo "<button type='submit'value='consultar'>consultar</button>";
	echo "</form>";


	session_start();
	$_SESSION['LISTA']=$notebooks;
	//echo "<a href='verNotebook.php'>Ver Notebook</a>";
?>
