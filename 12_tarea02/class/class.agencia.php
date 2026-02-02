<?php
class Agencia{
  private $id;
  private $descripcion;
  private $direccion;
  private $telefono;
  private $horainicio;
  private $horafin;
  private $foto;
  private $con;

  function __construct($cn){
    $this->con = $cn;
  }

  // =======================
  // CREATE
  // =======================
  public function save_agencia(){
    $this->descripcion = $_POST['descripcion'];
    $this->direccion   = $_POST['direccion'];
    $this->telefono    = $_POST['telefono'];
    $this->horainicio  = $_POST['horainicio'];
    $this->horafin     = $_POST['horafin'];
    $this->foto        = $_POST['foto'];

    $sql = "INSERT INTO agencia VALUES(
              NULL,
              '$this->descripcion',
              '$this->direccion',
              '$this->telefono',
              '$this->horainicio',
              '$this->horafin',
              '$this->foto'
            );";

    if($this->con->query($sql)){
      echo $this->_message_ok("guardó");
    }else{
      echo $this->_message_error("guardar");
    }
  }

  // =======================
  // UPDATE
  // =======================
  public function update_agencia(){
    $this->id         = $_POST['id'];
    $this->descripcion = $_POST['descripcion'];
    $this->direccion   = $_POST['direccion'];
    $this->telefono    = $_POST['telefono'];
    $this->horainicio  = $_POST['horainicio'];
    $this->horafin     = $_POST['horafin'];
    $this->foto        = $_POST['foto'];

    $sql = "UPDATE agencia SET
              descripcion='$this->descripcion',
              direccion='$this->direccion',
              telefono='$this->telefono',
              horainicio='$this->horainicio',
              horafin='$this->horafin',
              foto='$this->foto'
            WHERE id=$this->id;";

    if($this->con->query($sql)){
      echo $this->_message_ok("modificó");
    }else{
      echo $this->_message_error("al modificar");
    }
  }

  // =======================
  // FORM (NEW / ACT)
  // =======================
  public function get_form($id=NULL){

    $bandera = 1;

    if(($id == NULL) || ($id == 0)){
      $this->descripcion = NULL;
      $this->direccion   = NULL;
      $this->telefono    = NULL;
      $this->horainicio  = NULL;
      $this->horafin     = NULL;
      $this->foto        = NULL;

      $op = "new";

    }else{

      $sql = "SELECT * FROM agencia WHERE id=$id;";
      $res = $this->con->query($sql);
      $row = $res->fetch_assoc();
      $num = $res->num_rows;
      $bandera = ($num==0) ? 0 : 1;

      if(!($bandera)){
        $mensaje = "tratar de actualizar la agencia con id= ".$id;
        echo $this->_message_error($mensaje);
      }else{
        $this->descripcion = $row['descripcion'];
        $this->direccion   = $row['direccion'];
        $this->telefono    = $row['telefono'];
        $this->horainicio  = $row['horainicio'];
        $this->horafin     = $row['horafin'];
        $this->foto        = $row['foto'];

        $op = "update";
      }
    }

    if($bandera){

      $html = '
      <form name="Form_agencia" method="POST" action="index.php?mod=agencia">
        <input type="hidden" name="id" value="' . $id  . '">
        <input type="hidden" name="op" value="' . $op  . '">

        <div class="container mt-4">
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle w-auto mx-auto text-center">
              <thead class="table-dark">
                <tr>
                  <th colspan="2">DATOS AGENCIA</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="text-start fw-semibold">Descripción:</td>
                  <td class="text-start"><input type="text" name="descripcion" value="' . $this->descripcion . '" required></td>
                </tr>
                <tr>
                  <td class="text-start fw-semibold">Dirección:</td>
                  <td class="text-start"><input type="text" name="direccion" value="' . $this->direccion . '" required></td>
                </tr>
                <tr>
                  <td class="text-start fw-semibold">Teléfono:</td>
                  <td class="text-start"><input type="text" name="telefono" value="' . $this->telefono . '" required></td>
                </tr>
                <tr>
                  <td class="text-start fw-semibold">Hora inicio:</td>
                  <td class="text-start"><input type="time" name="horainicio" value="' . $this->horainicio . '" required></td>
                </tr>
                <tr>
                  <td class="text-start fw-semibold">Hora fin:</td>
                  <td class="text-start"><input type="time" name="horafin" value="' . $this->horafin . '" required></td>
                </tr>
                <tr>
                  <td class="text-start fw-semibold">Foto (nombre archivo):</td>
                  <td class="text-start"><input type="text" name="foto" value="' . $this->foto . '" required></td>
                </tr>
                <tr>
                  <th colspan="2">
                    <input type="submit" name="Guardar" value="GUARDAR" class="btn btn-success px-4">
                  </th>
                </tr>
                <tr>
                  <th colspan="2">
                    <a href="index.php?mod=agencia" class="btn btn-secondary px-4">Regresar</a>
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
    // Usuarios con rol 'R' (roles_id=2) solo pueden leer
    $solo_lectura = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'R');

    $html = '
    <h1 align="center">CENTROS DE MATRICULACIÓN</h1>

    <table class="table table-bordered" align="center">
      <tr>
        <th colspan="7">Lista de Agencias</th>
      </tr>';

    // Fila de botón Nuevo solo para usuarios con permisos de escritura
    if(!$solo_lectura){
      $html .= '
      <tr>
        <th colspan="7">
          <a href="index.php?mod=agencia&d=' . $d_new_final . '" class="btn btn-primary">
            Nuevo
          </a>
        </th>
      </tr>';
    }

    // Cabecera de acciones: 1 columna para solo lectura, 3 para CRUD completo
    if($solo_lectura){
      $html .= '
      <tr>
        <th>Descripción</th>
        <th>Dirección</th>
        <th>Teléfono</th>
        <th>Horario</th>
        <th>Acciones</th>
      </tr>';
    }else{
      $html .= '
      <tr>
        <th>Descripción</th>
        <th>Dirección</th>
        <th>Teléfono</th>
        <th>Horario</th>
        <th colspan="3">Acciones</th>
      </tr>';
    }

    $sql = "SELECT * FROM agencia";
    $res = $this->con->query($sql);
    $num = $res->num_rows;

    if($num != 0){

      while($row = $res->fetch_assoc()){

        $d_del_final = base64_encode('del/' . $row['id']);
        $d_act_final = base64_encode('act/' . $row['id']);
        $d_det_final = base64_encode('det/' . $row['id']);

        if($solo_lectura){
          // Solo se muestra la opción de Detalle
          $html .= '
        <tr>
            <td>' . $row['descripcion'] . '</td>
            <td>' . $row['direccion'] . '</td>
            <td>' . $row['telefono'] . '</td>
            <td>' . $row['horainicio'] . ' - ' . $row['horafin'] . '</td>
            <td><a class="btn btn-info btn-sm" href="index.php?mod=agencia&d=' . $d_det_final . '">Detalle</a></td>
        </tr>';
        }else{
          // CRUD completo
          $html .= '
        <tr>
            <td>' . $row['descripcion'] . '</td>
            <td>' . $row['direccion'] . '</td>
            <td>' . $row['telefono'] . '</td>
            <td>' . $row['horainicio'] . ' - ' . $row['horafin'] . '</td>
            <td><a class="btn btn-danger btn-sm" href="index.php?mod=agencia&d=' . $d_del_final . '">Borrar</a></td>
            <td><a class="btn btn-warning btn-sm" href="index.php?mod=agencia&d=' . $d_act_final . '">Actualizar</a></td>
            <td><a class="btn btn-info btn-sm" href="index.php?mod=agencia&d=' . $d_det_final . '">Detalle</a></td>
        </tr>';
        }
      }
    }else{
      $mensaje = "Tabla Agencia";
      echo $this->_message_BD_Vacia($mensaje);
      echo "<br><br><br>";
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

  // =======================
  // READ (DETAIL)
  // =======================
  public function get_detail_agencia($id){

    $sql = "SELECT * FROM agencia WHERE id=$id";
    $res = $this->con->query($sql);
    $row = $res->fetch_assoc();
    $num = $res->num_rows;

    if($num == 0){
      $mensaje = "desplegar el detalle de la agencia con id= ".$id;
      echo $this->_message_error($mensaje);
    }else{

      $html = '
      <h1 align="center">CENTROS DE MATRICULACIÓN</h1>

      <table class="table table-bordered" align="center">
        <tr>
          <th colspan="2">DETALLE AGENCIA</th>
        </tr>
        <tr>
          <td>Descripción:</td>
          <td>' . $row['descripcion'] . '</td>
        </tr>
        <tr>
          <td>Dirección:</td>
          <td>' . $row['direccion'] . '</td>
        </tr>
        <tr>
          <td>Teléfono:</td>
          <td>' . $row['telefono'] . '</td>
        </tr>
        <tr>
          <td>Horario:</td>
          <td>' . $row['horainicio'] . ' - ' . $row['horafin'] . '</td>
        </tr>
        <tr>
          <td>Foto:</td>
          <td><img src="imagenes/' . $row['foto'] . '" class="img-fluid rounded" style="max-width:300px"/></td>
        </tr>
        <tr>
          <th colspan="2">
            <a href="index.php?mod=agencia" class="btn btn-secondary">Regresar</a>
          </th>
        </tr>
      </table>';

      return $html;
    }
  }

  // =======================
  // DELETE
  // =======================
  public function delete_agencia($id){

    $sql = "DELETE FROM agencia WHERE id=$id;";
    if($this->con->query($sql)){
      echo $this->_message_ok("eliminó");
    }else{
      echo $this->_message_error("eliminar");
    }
  }

  // =======================
  // MENSAJES
  // =======================
  private function _message_error($tipo){
    $html = '
    <table border="0" align="center">
      <tr>
        <th>Error al ' . $tipo . '. Favor contactar a .................... </th>
      </tr>
      <tr>
        <th><a href="index.php?mod=agencia">Regresar</a></th>
      </tr>
    </table>';
    return $html;
  }

  private function _message_BD_Vacia($tipo){
    $html = '
    <table border="0" align="center">
      <tr>
        <th> NO existen registros en la ' . $tipo . '. Favor contactar a .................... </th>
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
        <th><a href="index.php?mod=agencia">Regresar</a></th>
      </tr>
    </table>';
    return $html;
  }
}
?>
