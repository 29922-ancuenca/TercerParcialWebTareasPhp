<?php
class vehiculo{
	private $id;
	private $placa;
	private $marca;
	private $motor;
	private $chasis;
	private $combustible;
	private $anio;
	private $color;
	private $foto;
	private $avaluo;
	private $con;
	
	function __construct($cn){
		$this->con = $cn;
	}
	
	
	//***********************************************************************************************	
	public function get_list(){
		$html = '
		<table border="1" align="center">
			<tr>
				<th colspan="8">Lista de Vehículos</th>
			</tr>
			<tr>
				<th>Placa</th>
				<th>Marca</th>
				<th>Color</th>
				<th>Año</th>
				<th>Avalúo</th>
				<th colspan="3">Acciones</th>
			</tr>';
		$sql = "SELECT v.id, v.placa, m.descripcion as marca, c.descripcion as color, v.anio, v.avaluo  FROM vehiculo v, color c, marca m WHERE v.marca=m.id AND v.color=c.id;";	
		$res = $this->con->query($sql);
		while($row = $res->fetch_assoc()){
			
		
			/*echo "<pre>";
				print_r($row);
			echo "</pre>";*/
		
			
			$html .= '
				<tr>        
					<td>' . $row['placa'] . '</td>
					<td>' . $row['marca'] . '</td>
					<td>' . $row['color'] . '</td>
					<td>' . $row['anio'] . '</td>
					<td>' . $row['avaluo'] . '</td>
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

