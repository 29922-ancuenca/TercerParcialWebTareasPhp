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
    //echo "EJECUTANDOSE EL CONSTRUCTOR MATRICULA<br><br>";
  }

  // =======================
  // CREATE
  // =======================
  public function save_matricula(){
    $this->fecha = $_POST['fecha'];
    $this->vehiculo = $_POST['vehiculo'];
    $this->agencia = $_POST['agencia'];
    $this->anio = $_POST['anio'];

    $sql = "INSERT INTO matricula VALUES(
              NULL,
              '$this->fecha',
              $this->vehiculo,
              $this->agencia,
              '$this->anio'
            );";

    // echo $sql; exit;
    if($this->con->query($sql)){
      echo $this->_message_ok("guardó");
    }else{
      echo $this->_message_error("guardar<br>");
    }
  }

  // =======================
  // UPDATE
  // =======================
  public function update_matricula(){
    $this->id = $_POST['id'];
    $this->fecha = $_POST['fecha'];
    $this->vehiculo = $_POST['vehiculo'];
    $this->agencia = $_POST['agencia'];
    $this->anio = $_POST['anio'];

    $sql = "UPDATE matricula SET
              fecha='$this->fecha',
              vehiculo=$this->vehiculo,
              agencia=$this->agencia,
              anio='$this->anio'
            WHERE id=$this->id;";

    // echo $sql; exit;
    if($this->con->query($sql)){
      echo $this->_message_ok("modificó");
    }else{
      echo $this->_message_error("al modificar<br>");
    }
  }

  // =======================
  // FORM (NEW / ACT)
  // =======================
public function get_form($id=NULL){

  if(($id == NULL) || ($id == 0)){
    $this->fecha = NULL;
    $this->vehiculo = NULL;
    $this->agencia = NULL;
    $this->anio = NULL;

    $op = "new";
    $bandera = 1;

  }else{

    $sql = "SELECT * FROM matricula WHERE id=$id;";
    $res = $this->con->query($sql);
    $row = $res->fetch_assoc();
    $num = $res->num_rows;
    $bandera = ($num==0) ? 0 : 1;

    if(!($bandera)){
      $mensaje = "tratar de actualizar la matricula con id= ".$id . "<br>";
      echo $this->_message_error($mensaje);

    }else{

      echo "<br>REGISTRO A MODIFICAR: <br>";
      echo "<pre>";
        print_r($row);
      echo "</pre>";

      $this->fecha = $row['fecha'];
      $this->vehiculo = $row['vehiculo'];
      $this->agencia = $row['agencia'];
      $this->anio = $row['anio'];

      $op = "update";
    }
  }

  if($bandera){

    $html = '
    <form name="Form_matricula" method="POST" action="index.php?mod=matricula">
      <input type="hidden" name="id" value="' . $id  . '">
      <input type="hidden" name="op" value="' . $op  . '">

      <div class="container mt-4">
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-hover align-middle w-auto mx-auto text-center">
            <thead class="table-dark">
              <tr>
                <th colspan="2">DATOS MATRÍCULA</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="text-start fw-semibold">Fecha:</td>
                <td class="text-start"><input type="date" name="fecha" value="' . $this->fecha . '" required></td>
              </tr>
              <tr>
                <td class="text-start fw-semibold">Vehículo:</td>
                <td class="text-start">' . $this->_get_combo_db("vehiculo","id","placa","vehiculo",$this->vehiculo) . '</td>
              </tr>
              <tr>
                <td class="text-start fw-semibold">Agencia:</td>
                <td class="text-start">' . $this->_get_combo_db("agencia","id","descripcion","agencia",$this->agencia) . '</td>
              </tr>
              <tr>
                <td class="text-start fw-semibold">Año:</td>
                <td class="text-start">' . $this->_get_combo_anio("anio",1950,$this->anio) . '</td>
              </tr>
              <tr>
                <th colspan="2">
                  <input type="submit" name="Guardar" value="GUARDAR" class="btn btn-success px-4">
                </th>
              </tr>
              <tr>
                <th colspan="2">
                  <a href="index.php?mod=matricula" class="btn btn-secondary px-4">Regresar</a>
                </th>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </form>';

    return $html;
  }
}

  // =======================
  // READ (LIST)
  // =======================
public function get_list(){

    $d_new = "new/0";
    $d_new_final = base64_encode($d_new);

    $html = '
    <h1 align="center">MATRÍCULAS PARTE III</h1>

    <table class="table table-bordered" align="center">
        <tr>
            <th colspan="7">Lista de Matrículas</th>
        </tr>
        <tr>
            <th colspan="7">
                <a href="index.php?mod=matricula&d=' . $d_new_final . '" class="btn btn-primary">
                    Nuevo
                </a>
            </th>
        </tr>
        <tr>
            <th>Fecha</th>
            <th>Vehículo</th>
            <th>Agencia</th>
            <th>Año</th>
            <th colspan="3">Acciones</th>
        </tr>';

    $sql = "SELECT m.id, m.fecha, v.placa, a.descripcion as agencia, m.anio
            FROM matricula m, vehiculo v, agencia a
            WHERE m.vehiculo = v.id AND m.agencia = a.id;";

    $res = $this->con->query($sql);
    $num = $res->num_rows;

    if($num != 0){

        while($row = $res->fetch_assoc()){

            $d_del_final = base64_encode('del/' . $row['id']);
            $d_act_final = base64_encode('act/' . $row['id']);
            $d_det_final = base64_encode('det/' . $row['id']);

            $html .= '
            <tr>
                <td>' . $row['fecha'] . '</td>
                <td>' . $row['placa'] . '</td>
                <td>' . $row['agencia'] . '</td>
                <td>' . $row['anio'] . '</td>
                <td><a class="btn btn-danger btn-sm" href="index.php?mod=matricula&d=' . $d_del_final . '">Borrar</a></td>
                <td><a class="btn btn-warning btn-sm" href="index.php?mod=matricula&d=' . $d_act_final . '">Actualizar</a></td>
                <td><a class="btn btn-info btn-sm" href="index.php?mod=matricula&d=' . $d_det_final . '">Detalle</a></td>
            </tr>';
        }

    }else{
        $mensaje = "Tabla Matricula<br>";
        echo $this->_message_BD_Vacia($mensaje);
        echo "<br><br><br>";
    }

    $html .= '
    </table>

    <div class="text-center mt-3">
  	  <a href="index.php" class="btn btn-secondary">
        Regresar
      </a>
    </div>';

    return $html;
}

  // =======================
  // READ (DETAIL)
  // =======================
public function get_detail_matricula($id){

  $sql = "SELECT m.fecha, v.placa, a.descripcion as agencia, m.anio
          FROM matricula m, vehiculo v, agencia a
          WHERE m.id=$id AND m.vehiculo=v.id AND m.agencia=a.id;";

  $res = $this->con->query($sql);
  $row = $res->fetch_assoc();
  $num = $res->num_rows;

  if($num == 0){
    $mensaje = "desplegar el detalle de la matricula con id= ".$id . "<br>";
    echo $this->_message_error($mensaje);
  }else{

    echo "<br>TUPLA<br>";
    echo "<pre>";
      print_r($row);
    echo "</pre>";

    $html = '
    <h1 align="center">MATRÍCULAS PARTE III</h1>

    <table class="table table-bordered" align="center">
      <tr>
        <th colspan="2">DETALLE MATRÍCULA</th>
      </tr>
      <tr>
        <td>Fecha:</td>
        <td>'. $row['fecha'] .'</td>
      </tr>
      <tr>
        <td>Vehículo (placa):</td>
        <td>'. $row['placa'] .'</td>
      </tr>
      <tr>
        <td>Agencia:</td>
        <td>'. $row['agencia'] .'</td>
      </tr>
      <tr>
        <td>Año:</td>
        <td>'. $row['anio'] .'</td>
      </tr>
      <tr>
        <th colspan="2">
          <a href="index.php?mod=matricula" class="btn btn-secondary">
            Regresar
          </a>
        </th>
      </tr>
    </table>';

    return $html;
  }
}

  // =======================
  // DELETE
  // =======================
  public function delete_matricula($id){

    $sql = "DELETE FROM matricula WHERE id=$id;";
    if($this->con->query($sql)){
      echo $this->_message_ok("eliminó");
    }else{
      echo $this->_message_error("eliminar<br>");
    }
  }

  // =======================
  // HELPERS
  // =======================
  private function _get_combo_db($tabla,$valor,$etiqueta,$nombre,$defecto=NULL){
    $html = '<select name="' . $nombre . '">';
    $sql = "SELECT $valor,$etiqueta FROM $tabla;";
    $res = $this->con->query($sql);

    while($row = $res->fetch_assoc()){
      $html .= ($defecto == $row[$valor])
        ? '<option value="' . $row[$valor] . '" selected>' . $row[$etiqueta] . '</option>' . "\n"
        : '<option value="' . $row[$valor] . '">' . $row[$etiqueta] . '</option>' . "\n";
    }

    $html .= '</select>';
    return $html;
  }

  private function _get_combo_anio($nombre,$anio_inicial,$defecto=NULL){
    $html = '<select name="' . $nombre . '">';
    $anio_actual = date('Y');
    for($i=$anio_inicial;$i<=$anio_actual;$i++){
      $html .= ($defecto == $i)
        ? '<option value="' . $i . '" selected>' . $i . '</option>' . "\n"
        : '<option value="' . $i . '">' . $i . '</option>' . "\n";
    }
    $html .= '</select>';
    return $html;
  }

  // =======================
  // MENSAJES
  // =======================
  private function _message_error($tipo){
    $html = '
    <table border="0" align="center">
      <tr>
        <th>Error al ' . $tipo . 'Favor contactar a .................... </th>
      </tr>
      <tr>
        <th><a href="index.php?mod=matricula ">Regresar</a></th>
      </tr>
    </table>';
    return $html;
  }

  private function _message_BD_Vacia($tipo){
    $html = '
    <table border="0" align="center">
      <tr>
        <th> NO existen registros en la ' . $tipo . 'Favor contactar a .................... </th>
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
  		<th><a href="index.php?mod=matricula">Regresar</a></th>
      </tr>
    </table>';
    return $html;
  }
}
?>
