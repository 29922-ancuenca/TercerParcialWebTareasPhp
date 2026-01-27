<?php
include_once("constantes.php");
require_once("class/class.matricula.php");

$cn = conectar();
$m = new Matricula($cn);


// GET: d=base64(op/id)
// op: det / del / new / act
if(isset($_GET['d'])){

	echo "<br>PETICION GET <br>";
	echo "<pre>";
		print_r($_GET);
	echo "</pre>";

	$dato = base64_decode($_GET['d']);
	$tmp = explode("/", $dato);

	echo "<br>VARIABLE TEMP <br>";
	echo "<pre>";
		print_r($tmp);
	echo "</pre>";

	$op = $tmp[0];
	$id = $tmp[1];

	if($op == "det"){
		echo $m->get_detail_matricula($id);

	}elseif($op == "del"){
		echo $m->delete_matricula($id);

	}elseif($op == "act"){
		echo $m->get_form($id);   

	}elseif($op == "new"){
		echo $m->get_form();     

	}else{
		echo $m->get_list();
	}


// POST: Guardar

}else{

	if(isset($_POST['Guardar'])){
		echo "<br>PETICION POST ...... <br>";
		echo "<pre>";
			print_r($_POST);
		echo "</pre>";

		echo '<br><br><th colspan="2"><a href="index_matricula.php">Regresar</a></th>';
	}else{
		echo $m->get_list();
	}
}


// CONEXION
function conectar(){
	echo "<br> CONEXION A LA BASE DE DATOS<br>";
	$c = new mysqli(SERVER,USER,PASS,BD);

	if($c->connect_errno) {
		die("Error de conexión: " . $c->connect_errno . ", " . $c->connect_error);
	}else{
		echo "La conexión tuvo éxito .......<br><br>";
	}

	$c->set_charset("utf8");
	return $c;
}
?>
