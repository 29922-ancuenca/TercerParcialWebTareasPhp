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
		<tr>
		    <td> <br> </td>
		</tr>
		<tr>
		    <td> <br> </td>
		</tr>
		<tr>
		    <th colspan="3">HOME PAGE</th>
		</tr>
	</table>
  </body>
</html>

 

<?php
require_once("Notebook.php");

			$chronos = new Notebook(2,"Samsung",5900);
			$acer = new Notebook(1,"Acer",3500.50);
			$compaq = new Notebook(3,"Compaq",2600.33);
			$lenovo = new Notebook(4,"Lenovo",1555.00);

			$notebooks= array();
			$notebooks['Acer']=$acer;
			$notebooks['Samsung']=$chronos;
			$notebooks['Compaq']=$compaq;
			$notebooks['Lenovo']=$lenovo;
			
	echo"<br><br>";
	
	echo '<form action="verNotebook.php" method="POST">';
		echo '<select id="op" name="op">';
				
			foreach($notebooks as $n){
					echo "<option value=".$n->getMarca().">".$n->getMarca()."</option>";
			}
		echo "</select>";
		echo "<button type='submit' value='consultar'>Session</button>";
		

		session_start();
		$_SESSION['listaNote']=$notebooks;

		echo "</br></br>";
		echo "<a href='FormularioLogin.php'>Login</a>";
	
	echo "</form>";

?>
