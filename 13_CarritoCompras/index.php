<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>Carrito de compras</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body class="bg-light">
<?php
	session_start();

	require_once("constantes.php");
	include_once("class/class.carrito.php");

	$cn = conectar();
	$t = new tienda($cn);

	$msg = "";
	$edit_id = NULL;

	if(isset($_GET['d'])){
		$dato = base64_decode($_GET['d']);
		$tmp = explode("/", $dato);
		$op = $tmp[0];
		$id = $tmp[1];

		if($op == "del"){
			$msg = $t->delete_item($id);
		}elseif($op == "act"){
			$edit_id = $id;
		}
	}else{
		if(isset($_POST['BuscarCliente']) && $_POST['op']=="client_search"){
			$msg = $t->client_search();
		}elseif(isset($_POST['GuardarCliente']) && $_POST['op']=="client_create"){
			$msg = $t->client_create();
		}elseif(isset($_POST['Agregar']) && $_POST['op']=="add"){
			$msg = $t->add_item();
		}elseif(isset($_POST['Actualizar']) && $_POST['op']=="update"){
			$msg = $t->update_item();
		}elseif(isset($_POST['GuardarPedido']) && $_POST['op']=="save"){
			$msg = $t->save_pedido();
		}elseif(isset($_POST['LimpiarOrden']) && $_POST['op']=="clear_cart"){
			$msg = $t->clear_cart();
		}elseif(isset($_POST['BuscarCompras']) && $_POST['op']=="hist_search"){
			$msg = $t->hist_search();
		}elseif(isset($_POST['VerCompra']) && $_POST['op']=="hist_view"){
			$msg = $t->hist_view();
		}elseif(isset($_POST['LimpiarHistorial']) && $_POST['op']=="hist_clear"){
			$msg = $t->hist_clear();
		}
	}

	echo '<div class="container py-5">';
		echo $t->get_page($msg, $edit_id);
	echo '</div>';

	function conectar(){
		$c = new mysqli(SERVER, USER, PASS, BD, PORT);

		if($c->connect_errno) {
			// Mostramos el código y el mensaje de error reales de mysqli
			die("Error de conexión: " . $c->connect_errno . ", " . $c->connect_error);
		}
		$c->set_charset("utf8");
		return $c;
	}
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
