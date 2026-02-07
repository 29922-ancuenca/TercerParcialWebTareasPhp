<?php
class producto{
    private $id;
    private $descripcion;
    private $precio;
    private $imagen;
    private $detalles;
    private $con;

    function __construct($cn){
        $this->con = $cn;
    }

    //*********************** METODO update_producto() ****************************
    public function update_producto(){
        $this->id = $_POST['id'];
        $this->descripcion = $_POST['descripcion'];
        $this->precio = $_POST['precio'];
        $this->detalles = $_POST['detalles'];

        // Si NO sube imagen nueva, se mantiene la anterior
        $imagen_actual = $_POST['imagen_actual'];

        if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0){
            $this->imagen = $_FILES['imagen']['name'];
            $path = "imagenes/productos/" . $this->imagen;

            if(!move_uploaded_file($_FILES['imagen']['tmp_name'], $path)){
                echo $this->_message_error("cargar la imagen");
                exit;
            }
        }else{
            $this->imagen = $imagen_actual;
        }

        $sql = "UPDATE Productos SET 
                    Descripcion='$this->descripcion',
                    Precio=$this->precio,
                    Imagen='$this->imagen',
                    Detalles='$this->detalles'
                WHERE ProductoID=$this->id;";

        if($this->con->query($sql)){
            echo $this->_message_ok("modificó");
        }else{
            echo $this->_message_error("al modificar");
        }
    }

    //*********************** METODO save_producto() ******************************
    public function save_producto(){
        $this->descripcion = $_POST['descripcion'];
        $this->precio = $_POST['precio'];
        $this->detalles = $_POST['detalles'];

        $this->imagen = $_FILES['imagen']['name'];
        $path = "imagenes/productos/" . $this->imagen;

        if(!move_uploaded_file($_FILES['imagen']['tmp_name'], $path)){
            echo $this->_message_error("cargar la imagen");
            exit;
        }

        $sql = "INSERT INTO Productos VALUES(NULL,
                    '$this->descripcion',
                    $this->precio,
                    '$this->imagen',
                    '$this->detalles'
                );";

        if($this->con->query($sql)){
            echo $this->_message_ok("guardó");
        }else{
            echo $this->_message_error("guardar");
        }
    }

    //*********************** METODO delete_producto() ****************************
    public function delete_producto($id){
        $sql = "DELETE FROM Productos WHERE ProductoID=$id;";

        if($this->con->query($sql)){
            echo $this->_message_ok("ELIMINÓ");
        }else{
            // Si está referenciado en PedidosItems, puede fallar por FK (RESTRICT)
            echo $this->_message_error("eliminar (puede estar usado en pedidos)");
        }
    }

    //***************************** FORMULARIO ***********************************
    public function get_form($id=NULL){

        if($id == NULL){
            $this->descripcion = NULL;
            $this->precio = NULL;
            $this->imagen = NULL;
            $this->detalles = NULL;

            $flag = NULL;
            $op = "new";
        }else{
            $sql = "SELECT * FROM Productos WHERE ProductoID=$id;";
            $res = $this->con->query($sql);
            $row = $res->fetch_assoc();
            $num = $res->num_rows;

            if($num==0){
                $mensaje = "tratar de actualizar el producto con id= ".$id;
                echo $this->_message_error($mensaje);
            }else{
                $this->descripcion = $row['Descripcion'];
                $this->precio = $row['Precio'];
                $this->imagen = $row['Imagen'];
                $this->detalles = $row['Detalles'];

                $flag = NULL; // puedes poner "disabled" si quieres bloquear algo
                $op = "update";
            }
        }

        $html = '
        <form name="producto" method="POST" action="index.php" enctype="multipart/form-data">
            <input type="hidden" name="id" value="'.$id.'">
            <input type="hidden" name="op" value="'.$op.'">
            <input type="hidden" name="imagen_actual" value="'.$this->imagen.'">

            <table border="1" align="center">
                <tr>
                    <th colspan="2">DATOS PRODUCTO</th>
                </tr>
                <tr>
                    <td>Descripción:</td>
                    <td><input type="text" size="30" name="descripcion" value="'.$this->descripcion.'" required></td>
                </tr>
                <tr>
                    <td>Precio:</td>
                    <td><input type="number" step="0.01" name="precio" value="'.$this->precio.'" required></td>
                </tr>
                <tr>
                    <td>Detalles:</td>
                    <td><input type="text" size="20" name="detalles" value="'.$this->detalles.'" required></td>
                </tr>
                <tr>
                    <td>Imagen:</td>
                    <td><input type="file" name="imagen" '.$flag.'></td>
                </tr>';

        if($id != NULL && $this->imagen != NULL){
            $html .= '
                <tr>
                    <th colspan="2">
                        <img src="imagenes/productos/'.$this->imagen.'" width="250px">
                    </th>
                </tr>';
        }

        $html .= '
                <tr>
                    <th colspan="2"><input type="submit" name="Guardar" value="GUARDAR"></th>
                </tr>
                <tr>
                    <th colspan="2"><a href="index.php">Regresar</a></th>
                </tr>
            </table>
        </form>';

        return $html;
    }

    //***************************** LISTADO **************************************
    public function get_list(){
        $d_new = "new/0";
        $d_new_final = base64_encode($d_new);

        $html = '
        <table border="1" align="center">
            <tr>
                <th colspan="7">Lista de Productos</th>
            </tr>
            <tr>
                <th colspan="7"><a href="index.php?d='.$d_new_final.'">Nuevo</a></th>
            </tr>
            <tr>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Detalles</th>
                <th>Imagen</th>
                <th colspan="3">Acciones</th>
            </tr>';

        $sql = "SELECT ProductoID, Descripcion, Precio, Imagen, Detalles
                FROM Productos
                ORDER BY ProductoID ASC;";

        $res = $this->con->query($sql);

        while($row = $res->fetch_assoc()){
            $d_del = "del/" . $row['ProductoID'];
            $d_del_final = base64_encode($d_del);

            $d_act = "act/" . $row['ProductoID'];
            $d_act_final = base64_encode($d_act);

            $d_det = "det/" . $row['ProductoID'];
            $d_det_final = base64_encode($d_det);

            $html .= '
            <tr>
                <td>'.$row['Descripcion'].'</td>
                <td>$'.number_format($row['Precio'],2).'</td>
                <td>'.$row['Detalles'].'</td>
                <td>'.$row['Imagen'].'</td>
                <td><a href="index.php?d='.$d_del_final.'">Borrar</a></td>
                <td><a href="index.php?d='.$d_act_final.'">Actualizar</a></td>
                <td><a href="index.php?d='.$d_det_final.'">Detalle</a></td>
            </tr>';
        }

        $html .= '</table>';
        return $html;
    }

    //********** LISTA SIMPLE PARA COMBOS (SELECT) ******************************
    // Devuelve las opciones <option> para un <select> de productos
    public function get_select_options(){
        $html = '<option value="">Seleccione un producto...</option>';

        $sql = "SELECT ProductoID, Descripcion, Precio FROM Productos ORDER BY Descripcion";
        $res = $this->con->query($sql);

        if($res){
            while($row = $res->fetch_assoc()){
                $id     = (int)$row['ProductoID'];
                $nombre = htmlspecialchars($row['Descripcion'], ENT_QUOTES, 'UTF-8');
                $precio = (float)$row['Precio'];

                $html .= '<option value="'.$id.'" data-precio="'.$precio.'">'.$nombre.'</option>';
            }
        }

        return $html;
    }

    //***************************** DETALLE **************************************
    public function get_detail_producto($id){
        $sql = "SELECT ProductoID, Descripcion, Precio, Imagen, Detalles
                FROM Productos
                WHERE ProductoID=$id;";
        $res = $this->con->query($sql);
        $row = $res->fetch_assoc();
        $num = $res->num_rows;

        if($num==0){
            $mensaje = "tratar de ver el detalle del producto con id= ".$id;
            echo $this->_message_error($mensaje);
        }else{
            $html = '
            <table border="1" align="center">
                <tr>
                    <th colspan="2">DETALLE PRODUCTO</th>
                </tr>
                <tr>
                    <td>Descripción:</td>
                    <td>'.$row['Descripcion'].'</td>
                </tr>
                <tr>
                    <td>Precio:</td>
                    <th>$'.number_format($row['Precio'],2).' USD</th>
                </tr>
                <tr>
                    <td>Detalles:</td>
                    <td>'.$row['Detalles'].'</td>
                </tr>
                <tr>
                    <th colspan="2">
                        <img src="imagenes/productos/'.$row['Imagen'].'" width="300px"/>
                    </th>
                </tr>
                <tr>
                    <th colspan="2"><a href="index.php">Regresar</a></th>
                </tr>
            </table>';

            return $html;
        }
    }

    //***************************** MENSAJES *************************************
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
                <th>El registro se ' . $tipo . ' correctamente</th>
            </tr>
            <tr>
                <th><a href="index.php">Regresar</a></th>
            </tr>
        </table>';
        return $html;
    }

} // FIN CLASE
?>
