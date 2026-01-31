<html>
<head>
	<meta charset="utf-8">
	<title>Bienvenida</title>
</head>
<body>
	 <table border=0 alingn="center" style="width:100%">
		<!-- <tr>
			<th colspan="3"> <a href="cerrar.php">Cerrar Sesión</a> </th>
		</tr> -->
		<tr>
		    <td> <br> </td>
		</tr>
		<tr>
		    <th colspan="3">BIENVENIDOS !!!!</th>
		</tr>
	</table>
</body>
</html>

<?php
require_once("Notebook.php");

session_start();
$notes = $_SESSION['listaNote'];

if (isset($_POST['op']))
    $op = $_POST['op'];
else
    if (isset($_GET['op']))
        $op = $_GET['op'];

$obj = $notes[$op];

echo "<h1>". $obj->getMarca()."</h1>";
echo "Precio: $". $obj->getPrecio();
echo "</br>";

session_destroy();
echo "<a href='index.php'>Coninuar</a>";

//header("location: index.php");


?>
