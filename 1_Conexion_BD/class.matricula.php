<?php
class Matricula{
	private $id;
	private $fecha;
	private $vehiculo;
	private $agencia;
	private $anio;
	private $con;
	
	function __construct($cn){
		$this->con = $cn;
	}
		
	//***********************************************************************************************	
	public function get_list(){
		$html = '
		<table border="1" align="center">
			<tr>
				<th colspan="7">Lista de Matrículas</th>
			</tr>
			<tr>
				<th>Fecha</th>
				<th>Vehiculo</th>
				<th>Agencia</th>
				<th>Año</th>
				<th colspan="3">Acciones</th>
			</tr>';
			
		$sql = "SELECT m.id, m.fecha, v.placa, a.descripcion as agencia, m.anio
				FROM matricula m, vehiculo v, agencia a
				WHERE m.vehiculo = v.id AND m.agencia = a.id;";	
		
		$res = $this->con->query($sql);
		while($row = $res->fetch_assoc()){
			
			/*echo "<pre>";
				print_r($row);
			echo "</pre>";*/
		
			$html .= '
				<tr>        
					<td>' . $row['fecha'] . '</td>
					<td>' . $row['placa'] . '</td>
					<td>' . $row['agencia'] . '</td>
					<td>' . $row['anio'] . '</td>
					<td>BORRAR</td>
					<td>ACTUALIZAR</td>
					<td>DETALLE</td>
				</tr>';  
		}
		
		$html .= '  
				</table>';
		
		return $html;
	}
	

//******************************************************************************************
	private function _message_error($tipo){
        $html = '
		<table border="0" align="center">
			<tr>
				<th>Error al ' . $tipo . '. Favor contactar a ..............</th>
			</tr>
			<tr>
				<th><a href="index.php">Regresar</a></th>
			</tr>
		</table>';
		return $html;
	}
	
	
//*******************************************************************************************************************
}
?>
