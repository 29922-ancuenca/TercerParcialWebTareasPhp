<?php
class tienda{
	private $con;

	function __construct($cn){
		$this->con = $cn;

		if(!isset($_SESSION['cart'])){
			$_SESSION['cart'] = array();
		}

		if(!isset($_SESSION['cliente_id'])) $_SESSION['cliente_id'] = 0;
		if(!isset($_SESSION['cliente_rif'])) $_SESSION['cliente_rif'] = "";
		if(!isset($_SESSION['cliente_nombre'])) $_SESSION['cliente_nombre'] = "";

		if(!isset($_SESSION['ui_show_client_modal'])) $_SESSION['ui_show_client_modal'] = 0;
		if(!isset($_SESSION['ui_new_rif'])) $_SESSION['ui_new_rif'] = "";

		if(!isset($_SESSION['hist'])){
			$_SESSION['hist'] = array(
				'rif' => '',
				'cliente_id' => 0,
				'razon' => '',
				'pedidos' => array(),
				'pedido_sel' => 0,
				'items' => array()
			);
		}
	}

	//*************************************** HELPERS ************************************************************

	private function _message_error_inline($tipo){
		return '<div class="alert alert-danger" role="alert">Error: ' . $tipo . '</div>';
	}

	private function _message_ok_inline($tipo){
		return '<div class="alert alert-success" role="alert">' . $tipo . '</div>';
	}

	private function _cart_total(){
		$total = 0;
		foreach($_SESSION['cart'] as $pid => $it){
			$total += ((float)$it['precio']) * ((int)$it['cantidad']);
		}
		return $total;
	}

	private function _cliente_locked(){
		return (count($_SESSION['cart']) > 0);
	}

	private function _get_combo_productos($nombre,$defecto,$disabled=NULL){
		$html = '<select class="form-select" name="' . $nombre . '" ' . $disabled . '>';
		$sql = "SELECT ProductoID, Descripcion FROM Productos;";
		$res = $this->con->query($sql);
		while($row = $res->fetch_assoc()){
			$val = (int)$row['ProductoID'];
			$lab = $row['Descripcion'];
			$html .= ($defecto == $val)
				? '<option value="' . $val . '" selected>' . $lab . '</option>' . "\n"
				: '<option value="' . $val . '">' . $lab . '</option>' . "\n";
		}
		$html .= '</select>';
		return $html;
	}

	//*************************************** CLIENTE *************************************************************

	public function client_search(){

		$rif = (isset($_POST['rif_cliente']))? trim($_POST['rif_cliente']) : "";
		if($rif == ""){
			return $this->_message_error_inline("Ingrese la cédula del cliente.");
		}

		// NO mostrar mensaje (solo bloquear)
		if($this->_cliente_locked()){
			return "";
		}

		$rif_sql = $this->con->real_escape_string($rif);
		$sql = "SELECT ClienteID, RazonSocial, Rif FROM Clientes WHERE Rif='$rif_sql' LIMIT 1;";
		$res = $this->con->query($sql);
		$row = $res->fetch_assoc();
		$num = $res->num_rows;

		if($num==0){
			$_SESSION['cliente_id'] = 0;
			$_SESSION['cliente_rif'] = $rif;
			$_SESSION['cliente_nombre'] = "";

			$_SESSION['ui_new_rif'] = $rif;
			$_SESSION['ui_show_client_modal'] = 1;

			return ""; // sin mensaje
		}

		$_SESSION['cliente_id'] = (int)$row['ClienteID'];
		$_SESSION['cliente_rif'] = $row['Rif'];
		$_SESSION['cliente_nombre'] = ($row['RazonSocial']==NULL)? "" : $row['RazonSocial'];
		// Ocultar formulario de nuevo cliente si la cédula/Rif es válida
		$_SESSION['ui_show_client_modal'] = 0;
		$_SESSION['ui_new_rif'] = "";

		return ""; // sin mensaje
	}

	public function client_create(){

		// NO mostrar mensaje (solo bloquear)
		if($this->_cliente_locked()){
			return "";
		}

		$rif = (isset($_POST['Rif']))? trim($_POST['Rif']) : "";
		$raz = (isset($_POST['RazonSocial']))? trim($_POST['RazonSocial']) : "";
		$dir = (isset($_POST['Direccion']))? trim($_POST['Direccion']) : "";
		$ciu = (isset($_POST['Ciudad']))? trim($_POST['Ciudad']) : "";
		$est = (isset($_POST['Estado']))? trim($_POST['Estado']) : "";
		$cp  = (isset($_POST['CodigoPostal']))? trim($_POST['CodigoPostal']) : "";
		$pai = (isset($_POST['Pais']))? trim($_POST['Pais']) : "";
		$tel = (isset($_POST['Telefonos']))? trim($_POST['Telefonos']) : "";

		if($rif == ""){
			$_SESSION['ui_new_rif'] = $rif;
			$_SESSION['ui_show_client_modal'] = 1;
			return $this->_message_error_inline("Cédula inválida.");
		}

		if($raz == ""){
			$_SESSION['ui_new_rif'] = $rif;
			$_SESSION['ui_show_client_modal'] = 1;
			return $this->_message_error_inline("Debe ingresar el Nombre / Razón Social.");
		}

		$rif_sql = $this->con->real_escape_string($rif);

		$sql0 = "SELECT ClienteID, RazonSocial FROM Clientes WHERE Rif='$rif_sql' LIMIT 1;";
		$res0 = $this->con->query($sql0);
		if($res0->num_rows > 0){
			$row0 = $res0->fetch_assoc();

			$_SESSION['cliente_id'] = (int)$row0['ClienteID'];
			$_SESSION['cliente_rif'] = $rif;
			$_SESSION['cliente_nombre'] = ($row0['RazonSocial']==NULL)? $raz : $row0['RazonSocial'];

			$_SESSION['ui_show_client_modal'] = 0;
			$_SESSION['ui_new_rif'] = "";
			return "";
		}

		$raz_sql = $this->con->real_escape_string($raz);
		$dir_sql = $this->con->real_escape_string($dir);
		$ciu_sql = $this->con->real_escape_string($ciu);
		$est_sql = $this->con->real_escape_string($est);
		$cp_sql  = $this->con->real_escape_string($cp);
		$pai_sql = $this->con->real_escape_string($pai);
		$tel_sql = $this->con->real_escape_string($tel);

		$sql = "INSERT INTO Clientes (RazonSocial, Direccion, Ciudad, Estado, CodigoPostal, Rif, Pais, Telefonos)
				VALUES ('$raz_sql','$dir_sql','$ciu_sql','$est_sql','$cp_sql','$rif_sql','$pai_sql','$tel_sql');";

		if(!$this->con->query($sql)){
			$_SESSION['ui_new_rif'] = $rif;
			$_SESSION['ui_show_client_modal'] = 1;
			return $this->_message_error_inline("No se pudo guardar el cliente.");
		}

		$new_id = (int)$this->con->insert_id;

		$_SESSION['cliente_id'] = $new_id;
		$_SESSION['cliente_rif'] = $rif;
		$_SESSION['cliente_nombre'] = $raz;

		$_SESSION['ui_show_client_modal'] = 0;
		$_SESSION['ui_new_rif'] = "";

		return "";
	}

	//*************************************** PAGE ***************************************************************

	public function get_page($msg="", $edit_id=NULL){

		$op = "add";
		$btn_name = "Agregar";
		$qty_def = 1;
		$prod_def = NULL;
		$disabled_prod = NULL;

		if($edit_id != NULL){
			$eid = (int)$edit_id;
			if(isset($_SESSION['cart'][$eid])){
				$op = "update";
				$btn_name = "Actualizar";
				$qty_def = (int)$_SESSION['cart'][$eid]['cantidad'];
				$prod_def = $eid;
				$disabled_prod = "disabled";
			}else{
				$edit_id = NULL;
			}
		}

		$cliente_locked = $this->_cliente_locked();
		$cliente_id = (int)$_SESSION['cliente_id'];
		$cliente_rif = $_SESSION['cliente_rif'];
		$cliente_nombre = $_SESSION['cliente_nombre'];

		$rif_hist = $_SESSION['hist']['rif'];
		$razon_hist = $_SESSION['hist']['razon'];
		$pedidos = $_SESSION['hist']['pedidos'];
		$pedido_sel = (int)$_SESSION['hist']['pedido_sel'];
		$items = $_SESSION['hist']['items'];

		$html = '';

		$html .= '<div class="card shadow-sm border-0">';
		$html .= '<div class="card-body">';
		$html .= '<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
			<h2 class="h3 mb-2 mb-md-0 text-primary fw-bold">Carrito de compras</h2>
			<p class="text-muted mb-0 small">Administra el pedido actual y revisa el historial de compras.</p>
		</div>';

		if($msg != "") $html .= $msg;

		$html .= '<div class="row g-4">';

		// ------------------ IZQUIERDA ------------------
		$html .= '<div class="col-12 col-lg-7">';

		// Buscar cliente por cédula
		$html .= '
		<div class="card mb-3">
			<div class="card-body">
				<h5 class="mb-3">Cliente</h5>
				<form class="row g-2" method="POST" action="index.php">
					<input type="hidden" name="op" value="client_search">

					<div class="col-12 col-md-7">
						<label class="form-label">Cédula (Rif)</label>
						<input class="form-control" type="text" name="rif_cliente" value="' . htmlspecialchars($cliente_rif) . '" ' . ($cliente_locked ? 'disabled' : '') . ' required>
					</div>

					<div class="col-12 col-md-5 d-flex align-items-end">
						<button type="submit" class="btn btn-primary w-100" name="BuscarCliente" value="BuscarCliente" ' . ($cliente_locked ? 'disabled' : '') . '>Buscar</button>
					</div>

					<div class="col-12">
						<label class="form-label">Nombre</label>
						<input class="form-control" type="text" value="' . htmlspecialchars($cliente_nombre) . '" readonly>
					</div>
				</form>
			</div>
		</div>';

		// Si no se encontró cliente, mostrar formulario inline para nuevo registro
		if((int)$_SESSION['ui_show_client_modal'] == 1 && $_SESSION['ui_new_rif'] != ""){
			$newrif = htmlspecialchars($_SESSION['ui_new_rif']);
			$html .= '
		<div class="alert alert-warning mb-2">Cliente no encontrado. Registrar nuevo cliente.</div>
		<div class="card mb-3">
		  <div class="card-body">
			<h5 class="mb-3">Registrar nuevo cliente</h5>
			<form method="POST" action="index.php" class="row g-3">
			  <input type="hidden" name="op" value="client_create">

			  <div class="col-12 col-md-4">
				<label class="form-label">Cédula (Rif)</label>
				<input class="form-control" type="text" name="Rif" value="' . $newrif . '" readonly>
			  </div>

			  <div class="col-12 col-md-8">
				<label class="form-label">Nombre</label>
				<input class="form-control" type="text" name="RazonSocial" required>
			  </div>

			  <div class="col-12">
				<label class="form-label">Dirección</label>
				<input class="form-control" type="text" name="Direccion">
			  </div>

			  <div class="col-12 col-md-6">
				<label class="form-label">Ciudad</label>
				<input class="form-control" type="text" name="Ciudad">
			  </div>

			  <div class="col-12 col-md-6">
				<label class="form-label">Provincia</label>
				<input class="form-control" type="text" name="Estado">
			  </div>

			  <div class="col-12 col-md-4">
				<label class="form-label">Código Postal</label>
				<input class="form-control" type="text" name="CodigoPostal">
			  </div>

			  <div class="col-12 col-md-4">
				<label class="form-label">País</label>
				<input class="form-control" type="text" name="Pais" value="Ecuador">
			  </div>

			  <div class="col-12 col-md-4">
				<label class="form-label">Teléfonos</label>
				<input class="form-control" type="text" name="Telefono">
			  </div>

			  <div class="col-12 d-flex gap-2">
				<button type="submit" class="btn btn-success" name="GuardarCliente" value="GuardarCliente">Guardar Cliente</button>
			  </div>
			</form>
		  </div>
		</div>';
		}

		// Form productos (solo si ya hay cliente seleccionado)
		$disable_add = ($cliente_id<=0) ? 'disabled' : '';
		$html .= '
		<form class="row g-3 mb-3" method="POST" action="index.php">
			<input type="hidden" name="op" value="' . $op . '">
			<input type="hidden" name="edit_id" value="' . (($edit_id==NULL)? "" : (int)$edit_id) . '">

			<div class="col-12 col-md-8">
				<label class="form-label">Producto</label>
				' . $this->_get_combo_productos("productoCMB",$prod_def,$disabled_prod . ' ' . $disable_add) . '
			</div>

			<div class="col-12 col-md-4">
				<label class="form-label">Cantidad</label>
				<input class="form-control" type="number" min="1" name="cantidad" value="' . $qty_def . '" required ' . $disable_add . '>
			</div>

			<div class="col-12 d-flex gap-2">
				<button type="submit" class="btn btn-success" name="' . $btn_name . '" value="' . $btn_name . '" ' . $disable_add . '>' . $btn_name . '</button>';

		if($op == "update"){
			$html .= '<a class="btn btn-secondary" href="index.php">Cancelar</a>';
		}

		$html .= '
			</div>
		</form>';

		// Tabla carrito
		$html .= '
		<div class="table-responsive">
		<table class="table table-bordered table-striped align-middle">
			<thead class="table-dark">
				<tr>
					<th>Producto</th>
					<th class="text-end">Precio</th>
					<th class="text-center">Cantidad</th>
					<th class="text-end">Subtotal</th>
					<th class="text-center" style="width:200px;">Acciones</th>
				</tr>
			</thead>
			<tbody>';

		if(count($_SESSION['cart']) == 0){
			$html .= '<tr><td colspan="5" class="text-center">No has agregado productos todavía.</td></tr>';
		}else{
			foreach($_SESSION['cart'] as $pid => $it){
				$pid_i = (int)$pid;
				$desc = $it['descripcion'];
				$precio = (float)$it['precio'];
				$cant = (int)$it['cantidad'];
				$sub = $precio * $cant;

				$d_del = base64_encode("del/" . $pid_i);
				$d_act = base64_encode("act/" . $pid_i);

				$html .= '
				<tr>
					<td>' . $desc . '</td>
					<td class="text-end">$' . number_format($precio,2) . '</td>
					<td class="text-center">' . $cant . '</td>
					<td class="text-end">$' . number_format($sub,2) . '</td>
					<td class="text-center">
						<a class="btn btn-sm btn-primary" href="index.php?d=' . $d_act . '">Editar</a>
						<a class="btn btn-sm btn-danger" href="index.php?d=' . $d_del . '">Borrar</a>
					</td>
				</tr>';
			}
		}

		$total = $this->_cart_total();

		$html .= '
			</tbody>
			<tfoot>
				<tr>
					<th class="text-end" colspan="3">TOTAL</th>
					<th class="text-end">$' . number_format($total,2) . '</th>
					<th></th>
				</tr>
			</tfoot>
		</table>
		</div>';

		// BOTONES: Guardar Pedido + Limpiar Orden
		$html .= '
		<div class="d-flex gap-2 mt-2">
			<form method="POST" action="index.php">
				<input type="hidden" name="op" value="save">
				<button type="submit" class="btn btn-dark" name="GuardarPedido" value="GuardarPedido" ' . ((count($_SESSION['cart'])==0)? "disabled" : "") . '>Guardar Pedido</button>
			</form>

			<form method="POST" action="index.php">
				<input type="hidden" name="op" value="clear_cart">
				<button type="submit" class="btn btn-outline-danger" name="LimpiarOrden" value="LimpiarOrden" ' . ((count($_SESSION['cart'])==0)? "disabled" : "") . '>Limpiar orden</button>
			</form>
		</div>';

		$html .= '</div>'; // FIN IZQUIERDA

		// ------------------ DERECHA: HISTORIAL ------------------
		$html .= '<div class="col-12 col-lg-5">';

		$html .= '
		<div class="card border-0 shadow-sm bg-white">
			<div class="card-body">
				<h5 class="mb-3">Historial de compras</h5>

				<form class="row g-2 mb-2" method="POST" action="index.php">
					<input type="hidden" name="op" value="hist_search">
					<div class="col-12">
						<label class="form-label">Buscar por Cédula (Rif)</label>
						<input class="form-control" type="text" name="rif" value="' . htmlspecialchars($rif_hist) . '" required>
					</div>
					<div class="col-12">
						<button type="submit" class="btn btn-primary w-100" name="BuscarCompras" value="BuscarCompras">Buscar</button>
					</div>
				</form>

				<form class="row g-2 mb-3" method="POST" action="index.php">
					<input type="hidden" name="op" value="hist_clear">
					<div class="col-12">
						<button type="submit" class="btn btn-outline-secondary w-100" name="LimpiarHistorial" value="LimpiarHistorial">Limpiar</button>
					</div>
				</form>';

		if($razon_hist != ""){
			$html .= '<div class="mb-2"><strong>Cliente:</strong> ' . htmlspecialchars($razon_hist) . '</div>';
		}

		// Tabla de historial: todas las compras del cliente con fecha
		if(count($items) > 0){
			$tot_hist = 0;

			$html .= '
				<div class="table-responsive">
				<table class="table table-sm table-bordered align-middle">
					<thead class="table-dark">
						<tr>
							<th>Fecha</th>
							<th>Producto</th>
							<th class="text-end">Precio</th>
							<th class="text-center">Cant.</th>
							<th class="text-end">Subt.</th>
						</tr>
					</thead>
					<tbody>';

			foreach($items as $it){
				$desc = $it['Descripcion'];
				$precio = (float)$it['Precio'];
				$cant = (int)$it['Cantidad'];
				$fecha = $it['FechaPedido'];
				$sub = $precio * $cant;
				$tot_hist += $sub;

				$html .= '
						<tr>
							<td>' . $fecha . '</td>
							<td>' . $desc . '</td>
							<td class="text-end">$' . number_format($precio,2) . '</td>
							<td class="text-center">' . $cant . '</td>
							<td class="text-end">$' . number_format($sub,2) . '</td>
						</tr>';
			}

			$html .= '
					</tbody>
					<tfoot>
						<tr>
							<th class="text-end" colspan="4">TOTAL</th>
							<th class="text-end">$' . number_format($tot_hist,2) . '</th>
						</tr>
					</tfoot>
				</table>
				</div>';
		}

		$html .= '
			</div>
		</div>';

		$html .= '</div>'; // FIN DERECHA
		$html .= '</div>'; // row
		$html .= '</div>'; // card-body
		$html .= '</div>'; // card

		return $html;
	}

	//*************************************** COMPRA ACTUAL ********************************************************

	public function add_item(){

		$cliente_id = (int)$_SESSION['cliente_id'];
		if($cliente_id <= 0){
			return $this->_message_error_inline("Primero busque un cliente por cédula.");
		}

		$pid = (isset($_POST['productoCMB']))? (int)$_POST['productoCMB'] : 0;
		$cant = (isset($_POST['cantidad']))? (int)$_POST['cantidad'] : 0;

		if($pid <= 0 || $cant <= 0){
			return $this->_message_error_inline("Debe seleccionar un producto y una cantidad válida.");
		}

		$sql = "SELECT ProductoID, Descripcion, Precio, Imagen, Detalles FROM Productos WHERE ProductoID=$pid;";
		$res = $this->con->query($sql);
		$row = $res->fetch_assoc();
		$num = $res->num_rows;

		if($num==0){
			return $this->_message_error_inline("Producto no encontrado.");
		}

		if(isset($_SESSION['cart'][$pid])){
			$_SESSION['cart'][$pid]['cantidad'] += $cant;
		}else{
			$_SESSION['cart'][$pid] = array(
				'producto_id' => (int)$row['ProductoID'],
				'descripcion' => $row['Descripcion'],
				'precio' => (float)$row['Precio'],
				'imagen' => $row['Imagen'],
				'detalles' => $row['Detalles'],
				'cantidad' => $cant
			);
		}

		return $this->_message_ok_inline("Producto agregado correctamente.");
	}

	public function update_item(){

		$cliente_id = (int)$_SESSION['cliente_id'];
		if($cliente_id <= 0){
			return $this->_message_error_inline("Primero busque un cliente por cédula.");
		}

		$edit_id = (isset($_POST['edit_id']))? (int)$_POST['edit_id'] : 0;
		$cant = (isset($_POST['cantidad']))? (int)$_POST['cantidad'] : 0;

		if($edit_id <= 0 || !isset($_SESSION['cart'][$edit_id])){
			return "";
		}
		if($cant <= 0){
			return $this->_message_error_inline("Cantidad inválida.");
		}

		$_SESSION['cart'][$edit_id]['cantidad'] = $cant;

		return $this->_message_ok_inline("Cantidad actualizada correctamente.");
	}

	public function delete_item($id){

		$pid = (int)$id;

		if(isset($_SESSION['cart'][$pid])){
			unset($_SESSION['cart'][$pid]);
			return "";
		}else{
			return ""; // <- quitado el mensaje "Error: El producto no existe..."
		}
	}

	public function clear_cart(){

		$_SESSION['cart'] = array();

		// también dejo libre el cliente para que pueda buscar otro inmediatamente
		$_SESSION['cliente_id'] = 0;
		$_SESSION['cliente_rif'] = "";
		$_SESSION['cliente_nombre'] = "";

		return ""; // sin mensaje
	}

	public function save_pedido(){

		$cliente_id = (int)$_SESSION['cliente_id'];

		if($cliente_id <= 0){
			return $this->_message_error_inline("Primero busque un cliente por cédula.");
		}
		if(count($_SESSION['cart']) == 0){
			return $this->_message_error_inline("No hay productos para guardar.");
		}

		$this->con->begin_transaction();

		$sql = "INSERT INTO Pedidos VALUES(NULL, $cliente_id, NOW());";
		if(!$this->con->query($sql)){
			$this->con->rollback();
			return $this->_message_error_inline("No se pudo crear el pedido.");
		}

		$pedido_id = (int)$this->con->insert_id;

		foreach($_SESSION['cart'] as $pid => $it){
			$pid_i = (int)$pid;
			$cant = (int)$it['cantidad'];
			$sql2 = "INSERT INTO PedidosItems VALUES(NULL, $pedido_id, $pid_i, $cant);";
			if(!$this->con->query($sql2)){
				$this->con->rollback();
				return $this->_message_error_inline("No se pudo guardar un ítem del pedido.");
			}
		}

		$this->con->commit();

		$_SESSION['cart'] = array();

		return $this->_message_ok_inline("Pedido guardado correctamente. PedidoID: " . $pedido_id);
	}

	//*************************************** HISTORIAL ***********************************************************

	public function hist_search(){

		$rif = (isset($_POST['rif']))? trim($_POST['rif']) : "";
		if($rif == ""){
			return $this->_message_error_inline("Ingrese la cédula (Rif).");
		}

		$rif_sql = $this->con->real_escape_string($rif);

		$sql = "SELECT ClienteID, RazonSocial, Rif FROM Clientes WHERE Rif='$rif_sql' LIMIT 1;";
		$res = $this->con->query($sql);
		$row = $res->fetch_assoc();
		$num = $res->num_rows;

		$_SESSION['hist']['rif'] = $rif;
		$_SESSION['hist']['cliente_id'] = 0;
		$_SESSION['hist']['razon'] = "";
		$_SESSION['hist']['pedidos'] = array();
		$_SESSION['hist']['pedido_sel'] = 0;
		$_SESSION['hist']['items'] = array();

		if($num == 0){
			return $this->_message_error_inline("No existe cliente con esa cédula (Rif).");
		}

		$cid = (int)$row['ClienteID'];
		$raz = $row['RazonSocial'];

		// Obtener todos los ítems de todos los pedidos de este cliente, con la fecha
		$sql2 = "SELECT DATE_FORMAT(pe.FechaPedido,'%Y-%m-%d %H:%i:%s') AS FechaPedido,
					pr.Descripcion,
					pr.Precio,
					pi.Cantidad
				FROM Pedidos pe
				INNER JOIN PedidosItems pi ON pi.PedidoID = pe.PedidoID
				INNER JOIN Productos pr ON pr.ProductoID = pi.ProductoID
				WHERE pe.ClienteID = $cid
				ORDER BY pe.FechaPedido DESC, pe.PedidoID DESC;";
		$res2 = $this->con->query($sql2);

		$items = array();
		while($r = $res2->fetch_assoc()){
			$items[] = $r;
		}

		$_SESSION['hist']['cliente_id'] = $cid;
		$_SESSION['hist']['razon'] = ($raz == NULL || $raz=="")? ("Cliente (Rif: ".$rif.")") : $raz;
		$_SESSION['hist']['items'] = $items;

		if(count($items)==0){
			return $this->_message_ok_inline("Cliente encontrado, pero no tiene compras registradas.");
		}
		return $this->_message_ok_inline("Compras encontradas.");
	}

	public function hist_view(){

		$pid = (isset($_POST['pedidoID']))? (int)$_POST['pedidoID'] : 0;
		if($pid <= 0){
			return $this->_message_error_inline("Seleccione una fecha de compra.");
		}

		$_SESSION['hist']['pedido_sel'] = $pid;

		$sql = "SELECT p.Descripcion, p.Precio, pi.Cantidad
				FROM PedidosItems pi
				INNER JOIN Productos p ON p.ProductoID = pi.ProductoID
				WHERE pi.PedidoID = $pid;";
		$res = $this->con->query($sql);

		$items = array();
		while($r = $res->fetch_assoc()){
			$items[] = $r;
		}
		$_SESSION['hist']['items'] = $items;

		if(count($items)==0){
			return $this->_message_error_inline("No hay detalle para ese pedido.");
		}
		return $this->_message_ok_inline("Detalle cargado.");
	}

	public function hist_clear(){

		$_SESSION['hist'] = array(
			'rif' => '',
			'cliente_id' => 0,
			'razon' => '',
			'pedidos' => array(),
			'pedido_sel' => 0,
			'items' => array()
		);

		return $this->_message_ok_inline("Historial limpiado.");
	}
}
?>
