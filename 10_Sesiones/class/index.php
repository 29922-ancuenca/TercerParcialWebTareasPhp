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

$chronos = new Notebook(2,"Samsung",590000);
$acer = new Notebook(1,"Acer",350000);
$compaq = new Notebook(3,"Compaq",260000);
$dell = new Notebook(4,"Dell",40000);

/*
	echo "<h1>". $chronos->getMarca()."</h1>";
	echo "Precio: $". $chronos->getPrecio();
	echo "<h1>". $acer->getMarca()."</h1>";
	echo "Precio: $". $acer->getPrecio();
	echo "<h1>". $compaq->getMarca()."</h1>";
	echo "Precio: $". $compaq->getPrecio();
*/

	/*Arreglo*/

	$notebooks= array();
	$notebooks['Acer']=$acer;
	$notebooks['Samsung']=$chronos;
	$notebooks['Compaq']=$compaq;
    $notebooks['Dell']=$dell;
	
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
