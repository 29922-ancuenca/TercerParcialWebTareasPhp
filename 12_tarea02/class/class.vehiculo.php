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
		//echo "EJECUTANDOSE EL CONSTRUCTOR VEHICULO<br><br>";

	}
		
		
//*********************** 3.1 METODO update_vehiculo() **************************************************	
	
	public function update_vehiculo(){
		$this->id = $_POST['id'];
		$this->placa = $_POST['placa'];
		$this->motor = $_POST['motor'];
		$this->chasis = $_POST['chasis'];
			
		$this->marca = $_POST['marcaCMB'];
		$this->anio = $_POST['anio'];
		$this->color = $_POST['colorCMB'];
		$this->combustible = $_POST['combustibleRBT'];
		
		
		
		$sql = "UPDATE vehiculo SET placa='$this->placa',
									marca=$this->marca,
									motor='$this->motor',
									chasis='$this->chasis',
									combustible='$this->combustible',
									anio='$this->anio',
									color=$this->color
				WHERE id=$this->id;";
		//echo $sql;
		//exit;
		if($this->con->query($sql)){
			echo $this->_message_ok("modificó");
		}else{
			echo $this->_message_error("al modificar");
		}								
										
	}
	

//*********************** 3.2 METODO save_vehiculo() **************************************************	

	public function save_vehiculo(){
		
		
		$this->placa = $_POST['placa'];
		$this->motor = $_POST['motor'];
		$this->chasis = $_POST['chasis'];
		$this->avaluo = $_POST['avaluo'];

		
		$this->marca = $_POST['marcaCMB'];
		$this->anio = $_POST['anio'];
		$this->color = $_POST['colorCMB'];
		$this->combustible = $_POST['combustibleRBT'];
		
		 
				/*echo "<br> FILES <br>";
				echo "<pre>";
					print_r($_FILES);
				echo "</pre>";*/
		     
		
		
		//$this->foto = $this->_get_name_file($_FILES['foto']['name'],12);
		$this->foto = $_FILES['foto']['name'];
		
		$path = "../imagenes/autos/" . $this->foto;
		//$path = "../imagenes/autos/TEMP". $this->foto;
		
		//exit;
		if(!move_uploaded_file($_FILES['foto']['tmp_name'],$path)){
			$mensaje = "Cargar la imagen";
			echo $this->_message_error($mensaje);
			exit;
		}
		
		$sql = "INSERT INTO vehiculo VALUES(NULL,
											'$this->placa',
											$this->marca,
											'$this->motor',
											'$this->chasis',
											'$this->combustible',
											'$this->anio',
											$this->color,
											'$this->foto',
											$this->avaluo);";
		//echo $sql;
		//exit;
		if($this->con->query($sql)){
			echo $this->_message_ok("guardó");
		}else{
			echo $this->_message_error("guardar");
		}								
										
	}


//*********************** 3.3 METODO _get_name_File() **************************************************	
	
	private function _get_name_file($nombre_original, $tamanio){
		$tmp = explode(".",$nombre_original); //Divido el nombre por el punto y guardo en un arreglo
		$numElm = count($tmp); //cuento el número de elemetos del arreglo
		$ext = $tmp[$numElm-1]; //Extraer la última posición del arreglo.
		$cadena = "";
			for($i=1;$i<=$tamanio;$i++){
				$c = rand(65,122);
				if(($c >= 91) && ($c <=96)){
					$c = NULL;
					 $i--;
				 }else{
					$cadena .= chr($c);
				}
			}
		return $cadena . "." . $ext;
	}
	
	
//*************************************** PARTE I ************************************************************
	
	    
	 /*Aquí se agregó el parámetro:  $defecto*/
	private function _get_combo_db($tabla,$valor,$etiqueta,$nombre,$defecto){
		$html = '<select name="' . $nombre . '">';
		$sql = "SELECT $valor,$etiqueta FROM $tabla;";
		$res = $this->con->query($sql);
		while($row = $res->fetch_assoc()){
			//ImpResultQuery($row);
			$html .= ($defecto == $row[$valor])?'<option value="' . $row[$valor] . '" selected>' . $row[$etiqueta] . '</option>' . "\n" : '<option value="' . $row[$valor] . '">' . $row[$etiqueta] . '</option>' . "\n";
		}
		$html .= '</select>';
		return $html;
	}
	
	/*Aquí se agregó el parámetro:  $defecto*/
	private function _get_combo_anio($nombre,$anio_inicial,$defecto){
		$html = '<select name="' . $nombre . '">';
		$anio_actual = date('Y');
		for($i=$anio_inicial;$i<=$anio_actual;$i++){
			$html .= ($i == $defecto)? '<option value="' . $i . '" selected>' . $i . '</option>' . "\n":'<option value="' . $i . '">' . $i . '</option>' . "\n";
		}
		$html .= '</select>';
		return $html;
	}
	
	/*Aquí se agregó el parámetro:  $defecto*/
	private function _get_radio($arreglo,$nombre,$defecto){
		
		$html = '
		<table border=0 align="left">';
		
		//CODIGO NECESARIO EN CASO QUE EL USUARIO NO SE ESCOJA UNA OPCION
		
		foreach($arreglo as $etiqueta){
			$html .= '
			<tr>
				<td>' . $etiqueta . '</td>
				<td>';
				
				if($defecto == NULL){
					// OPCION PARA GRABAR UN NUEVO VEHICULO (id=0)
					$html .= '<input type="radio" value="' . $etiqueta . '" name="' . $nombre . '" checked/></td>';
				
				}else{
					// OPCION PARA MODIFICAR UN VEHICULO EXISTENTE
					$html .= ($defecto == $etiqueta)? '<input type="radio" value="' . $etiqueta . '" name="' . $nombre . '" checked/></td>' : '<input type="radio" value="' . $etiqueta . '" name="' . $nombre . '"/></td>';
				}
			
			$html .= '</tr>';
		}
		$html .= '
		</table>';
		return $html;
	}
	
	
//************************************* PARTE II ****************************************************	

public function get_form($id=NULL){
    
    if($id == NULL){
        $this->placa = NULL;
        $this->marca = NULL;
        $this->motor = NULL;
        $this->chasis = NULL;
        $this->combustible = NULL;
        $this->anio = NULL;
        $this->color = NULL;
        $this->foto = NULL;
        $this->avaluo =NULL;
        
        $flag = NULL;
        $op = "new";
        
    }else{

        $sql = "SELECT * FROM vehiculo WHERE id=$id;";
        $res = $this->con->query($sql);
        $row = $res->fetch_assoc();
        
        $num = $res->num_rows;
        if($num==0){
            $mensaje = "tratar de actualizar el vehiculo con id= ".$id;
            echo $this->_message_error($mensaje);
        }else{   
        
          // ***** TUPLA ENCONTRADA *****
            /*echo "<br>TUPLA <br>";
            echo "<pre>";
                print_r($row);
            echo "</pre>";*/
        
            $this->placa = $row['placa'];
            $this->marca = $row['marca'];
            $this->motor = $row['motor'];
            $this->chasis = $row['chasis'];
            $this->combustible = $row['combustible'];
            $this->anio = $row['anio'];
            $this->color = $row['color'];
            $this->foto = $row['foto'];
            $this->avaluo = $row['avaluo'];
            
            $flag = "disabled";
            $op = "update";
        }
    }
    
    
    $combustibles = ["Gasolina",
                     "Diesel",
                     "Eléctrico"
                     ];

    $html = '
    <form name="vehiculo" method="POST" action="index.php" enctype="multipart/form-data">
    
    <input type="hidden" name="id" value="' . $id  . '">
    <input type="hidden" name="op" value="' . $op  . '">
    
    <div class="container mt-4">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle text-center w-auto mx-auto">
                <thead class="table-dark">
                    <tr>
                        <th colspan="2">DATOS VEHÍCULO</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-start fw-semibold">Placa:</td>
                        <td class="text-start"><input type="text" size="6" name="placa" value="' . $this->placa . '" required></td>
                    </tr>
                    <tr>
                        <td class="text-start fw-semibold">Marca:</td>
                        <td class="text-start">' . $this->_get_combo_db("marca","id","descripcion","marcaCMB",$this->marca) . '</td>
                    </tr>
                    <tr>
                        <td class="text-start fw-semibold">Motor:</td>
                        <td class="text-start"><input type="text" size="15" name="motor" value="' . $this->motor . '" required></td>
                    </tr>  
                    <tr>
                        <td class="text-start fw-semibold">Chasis:</td>
                        <td class="text-start"><input type="text" size="15" name="chasis" value="' . $this->chasis . '" required></td>
                    </tr>
                    <tr>
                        <td class="text-start fw-semibold">Combustible:</td>
                        <td class="text-start">' . $this->_get_radio($combustibles, "combustibleRBT",$this->combustible) . '</td>
                    </tr>
                    <tr>
                        <td class="text-start fw-semibold">Año:</td>
                        <td class="text-start">' . $this->_get_combo_anio("anio",1980,$this->anio) . '</td>
                    </tr>
                    <tr>
                        <td class="text-start fw-semibold">Color:</td>
                        <td class="text-start">' . $this->_get_combo_db("color","id","descripcion","colorCMB",$this->color) . '</td>
                    </tr>
                    <tr>
                        <td class="text-start fw-semibold">Foto:</td>
                        <td class="text-start"><input type="file" name="foto" ' . $flag . '></td>
                    </tr>
                    <tr>
                        <td class="text-start fw-semibold">Avalúo:</td>
                        <td class="text-start"><input type="text" size="8" name="avaluo" value="' . $this->avaluo . '" ' . $flag . ' required></td>
                    </tr>
                    <tr>
                        <th colspan="2">
                            <input type="submit" name="Guardar" value="GUARDAR" class="btn btn-success px-4">
                        </th>
                    </tr>
                    <tr>
                        <th colspan="2">
                            <a href="index.php" class="btn btn-secondary px-4">Regresar</a>
                        </th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>';

    return $html;
}
	
public function get_list($placa_buscar = NULL){
    $d_new = "new/0";
    $d_new_final = base64_encode($d_new);

    // Valor actual de búsqueda para mantenerlo en el input
    $valor_busqueda = ($placa_buscar !== NULL) ? $placa_buscar : "";

    // Verificar si el usuario es administrador (ADM)
    $es_admin = (isset($_SESSION['usuario']) && $_SESSION['usuario'] === 'ADM')
             || (isset($_SESSION['rol']) && $_SESSION['rol'] === 'ADM');

    // Usuarios con rol 'R' (roles_id=2) solo pueden leer
    $solo_lectura = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'R');

    $html = '
    <h1 align="center">VEHÍCULOS PARTE III</h1>';

    // La barra de búsqueda solo se muestra si NO es administrador
    if(!$es_admin){
        $html .= '
    <div class="container mb-3">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form class="input-group" method="POST" action="index.php?mod=vehiculo">
                    <input type="text" name="placa_buscar" class="form-control" placeholder="Buscar por placa" value="' . $valor_busqueda . '" required>
                    <button class="btn btn-primary" type="submit" name="btnBuscar">Buscar</button>
                </form>
            </div>
        </div>
    </div>';
    }

    $html .= '

    <table class="table table-bordered" align="center">
        <tr>
            <th colspan="8">Lista de Vehículos</th>
        </tr>';

    // Fila de botón Nuevo solo para usuarios con permisos de escritura
    if(!$solo_lectura){
        $html .= '
        <tr>
            <th colspan="8">
                <a href="index.php?d=' . $d_new_final . '" class="btn btn-primary">
                    Nuevo
                </a>
            </th>
        </tr>';
    }

    // Cabecera de acciones: 1 columna para solo lectura, 3 para CRUD completo
    if($solo_lectura){
        $html .= '
        <tr>
            <th>Placa</th>
            <th>Marca</th>
            <th>Color</th>
            <th>Año</th>
            <th>Avalúo</th>
            <th>Acciones</th>
        </tr>';
    }else{
        $html .= '
        <tr>
            <th>Placa</th>
            <th>Marca</th>
            <th>Color</th>
            <th>Año</th>
            <th>Avalúo</th>
            <th colspan="3">Acciones</th>
        </tr>';
    }

    $sql = "SELECT v.id, v.placa, m.descripcion as marca, c.descripcion as color, v.anio, v.avaluo
            FROM vehiculo v, color c, marca m
            WHERE v.marca=m.id AND v.color=c.id";

    // Si viene una placa para buscar, se agrega a la consulta
    if($placa_buscar !== NULL && $placa_buscar !== ''){
        $placa_buscar = $this->con->real_escape_string($placa_buscar);
        $sql .= " AND v.placa LIKE '%$placa_buscar%'";
    }

    $sql .= ";";

    $res = $this->con->query($sql);

    while($row = $res->fetch_assoc()){
        $d_del_final = base64_encode("del/" . $row['id']);
        $d_act_final = base64_encode("act/" . $row['id']);
        $d_det_final = base64_encode("det/" . $row['id']);

        if($solo_lectura){
            // Solo se muestra la opción de Detalle
            $html .= '
        <tr>
            <td>' . $row['placa'] . '</td>
            <td>' . $row['marca'] . '</td>
            <td>' . $row['color'] . '</td>
            <td>' . $row['anio'] . '</td>
            <td>' . $row['avaluo'] . '</td>
            <td><a class="btn btn-info btn-sm" href="index.php?d=' . $d_det_final . '">Detalle</a></td>
        </tr>';
        }else{
            // CRUD completo
            $html .= '
        <tr>
            <td>' . $row['placa'] . '</td>
            <td>' . $row['marca'] . '</td>
            <td>' . $row['color'] . '</td>
            <td>' . $row['anio'] . '</td>
            <td>' . $row['avaluo'] . '</td>
            <td><a class="btn btn-danger btn-sm" href="index.php?d=' . $d_del_final . '">Borrar</a></td>
            <td><a class="btn btn-warning btn-sm" href="index.php?d=' . $d_act_final . '">Actualizar</a></td>
            <td><a class="btn btn-info btn-sm" href="index.php?d=' . $d_det_final . '">Detalle</a></td>
        </tr>';
        }
    }
    $html .= '
    </table>

    <div class="text-center mt-3">
        <a href="index.html" class="btn btn-secondary">
             Regresar
        </a>
    </div>';

    return $html;
}	
	
public function get_detail_vehiculo($id){
    $sql = "SELECT v.placa, m.descripcion as marca, v.motor, v.chasis, v.combustible, v.anio, c.descripcion as color, v.foto, v.avaluo  
            FROM vehiculo v, color c, marca m 
            WHERE v.id=$id AND v.marca=m.id AND v.color=c.id;";
    $res = $this->con->query($sql);
    $row = $res->fetch_assoc();
    
    $num = $res->num_rows;
    
    if($num==0){
        $mensaje = "tratar de editar el vehiculo con id= ".$id;
        echo $this->_message_error($mensaje);
    }else{ 
        $html = '
        <div class="container mt-4">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle w-auto mx-auto">
                    <thead class="table-dark text-center">
                        <tr>
                            <th colspan="2">DATOS DEL VEHÍCULO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-semibold">Placa:</td>
                            <td>'. $row['placa'] .'</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Marca:</td>
                            <td>'. $row['marca'] .'</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Motor:</td>
                            <td>'. $row['motor'] .'</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Chasis:</td>
                            <td>'. $row['chasis'] .'</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Combustible:</td>
                            <td>'. $row['combustible'] .'</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Año:</td>
                            <td>'. $row['anio'] .'</td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">Color:</td>
                            <td>'. $row['color'] .'</td>
                        </tr>
                        <tr class="table-success">
                            <td class="fw-semibold">Avalúo:</td>
                            <th>$'. $row['avaluo'] .' USD</th>
                        </tr>
                        <tr class="table-info">
                            <td class="fw-semibold">Valor Matrícula:</td>
                            <th>$'. $this->_calculo_matricula($row['avaluo']) .' USD</th>
                        </tr>
                        <tr>
                            <th colspan="2" class="text-center">
                                <img src="imagenes/' . $row['foto'] . '" class="img-fluid rounded" style="max-width:300px"/>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="2" class="text-center">
                                <a href="index.php" class="btn btn-secondary px-4">Regresar</a>
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>';
        
        return $html;
    }
}
	
	
	public function delete_vehiculo($id){
		$sql = "DELETE FROM vehiculo WHERE id=$id;";
			if($this->con->query($sql)){
			echo $this->_message_ok("ELIMINÓ");
		}else{
			echo $this->_message_error("eliminar");
		}	
	}
	
//*************************************************************************

	private function _calculo_matricula($avaluo){
		return number_format(($avaluo * 0.10),2);
	}
	
//*************************************************************************	
	
	private function _message_error($tipo){
		$html = '
		<table border="0" align="center">
			<tr>
				<th>Error al ' . $tipo . '. Favor contactar a .................... </th>
			</tr>
			<tr>
                <th><a href="index.php">Regresar</a></th>
			</tr>
		</table>';
		return $html;
	}
	
	
	private function _message_ok($tipo){
		$html = '
		<table border="0" align="center">
			<tr>
				<th>El registro se  ' . $tipo . ' correctamente</th>
			</tr>
			<tr>
                <th><a href="index.php">Regresar</a></th>
			</tr>
		</table>';
		return $html;
	}
	
//****************************************************************************	
	
} // FIN SCRPIT
?>

