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
		
		$db = conectar();
		$objetoVehiculo = new vehiculo($db);
		

//PARTE 1.1	
	echo "<h1>VEHÍCULOS PARTE I</h1>";
	//echo $objetoVehiculo->get_list();	

//PARTE 1.2	
	//URL para la petición GET
	//$URL = "http://localhost:8080/DW/Taller_03/index.php?d=C/1";
		
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
			
		//	$dato = base64_decode($id);
		//	echo $dato;exit;
			
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
