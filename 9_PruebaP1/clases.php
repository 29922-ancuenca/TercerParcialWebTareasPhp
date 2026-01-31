<?php

require_once __DIR__ . '/conexion.php';


class IngredienteRepository
{
    private $con;

    public function __construct(mysqli $con)
    {
        $this->con  = $con;
    }

    /**
     * Devuelve todos los ingredientes como arreglo asociativo.
     */
    public function obtenerTodos()
    {
        $sql = "
        SELECT
          id,
          nombre,
          CAST(precio AS DECIMAL(10,2)) AS precio
        FROM ingredientes
        ORDER BY nombre ASC
        ";

        $r = mysqli_query($this->con, $sql);
        if (!$r) {
            throw new RuntimeException('Error al listar ingredientes: ' . mysqli_error($this->con));
        }

        $ingredientes = [];
        while ($row = mysqli_fetch_assoc($r)) {
            $ingredientes[] = [
                'id' => $row['id'],
                'nombre' => $row['nombre'],
                'precio' => (float)$row['precio'],
            ];
        }

        mysqli_free_result($r);
        return $ingredientes;
    }

    /**
     * Obtiene un ingrediente por ID o null si no existe.
     */
    public function obtenerPorId($id)
    {
        $sql = 'SELECT id, nombre, CAST(precio AS DECIMAL(10,2)) AS precio FROM ingredientes WHERE id = ?';
        $stmt = mysqli_prepare($this->con, $sql);
        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar SELECT ingrediente: ' . mysqli_error($this->con));
        }

        mysqli_stmt_bind_param($stmt, 's', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res) ?: null;
        mysqli_free_result($res);
        mysqli_stmt_close($stmt);

        if (!$row) {
            return null;
        }

        return [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'precio' => (float)$row['precio'],
        ];
    }
}

class PizzaRepository
{
    private $con;

    public function __construct(mysqli $con)
    {
        $this->con = $con;
    }

    /**
     * Lista pizzas agrupadas (misma lógica que listar.php) como arreglo PHP.
     */
    public function listarAgrupadas()
    {
        $sql = "
        SELECT
          p.nombre AS pizza_nombre,
          DATE_FORMAT(p.fecha, '%Y-%m-%d %H:%i:%s') AS fecha_grupo,
          i.id AS ingrediente_id,
          i.nombre AS ingrediente_nombre,
          CAST(i.precio AS DECIMAL(10,2)) AS ingrediente_precio,
          COUNT(*) AS cantidad
        FROM pizza p
        INNER JOIN ingredientes i ON i.id = p.fk_ingredientes
        GROUP BY p.nombre, fecha_grupo, i.id, i.nombre, i.precio
        ORDER BY fecha_grupo DESC, p.nombre ASC, i.nombre ASC
        ";

        $r = mysqli_query($this->con, $sql);
        if (!$r) {
            throw new RuntimeException('Error al listar pizzas: ' . mysqli_error($this->con));
        }

        $pizzas = [];

        while ($row = mysqli_fetch_assoc($r)) {
            $nombre = $row['pizza_nombre'];
            $fecha  = $row['fecha_grupo'];
            $key = $nombre . '||' . $fecha;

            $precio = (float)$row['ingrediente_precio'];
            $cant   = (int)$row['cantidad'];
            $sub    = $precio * $cant;

            if (!isset($pizzas[$key])) {
                $pizzas[$key] = [
                    'nombre' => $nombre,
                    'fecha' => $fecha,
                    'total' => 0.0,
                    'ingredientes' => [],
                ];
            }

            $pizzas[$key]['ingredientes'][] = [
                'id' => $row['ingrediente_id'],
                'nombre' => $row['ingrediente_nombre'],
                'precio' => $precio,
                'cantidad' => $cant,
                'subtotal' => $sub,
            ];

            $pizzas[$key]['total'] += $sub;
        }

        mysqli_free_result($r);

        return array_values($pizzas);
    }

    /**
     * Devuelve una pizza específica (nombre+fecha) o null.
     */
    public function obtenerPizza($nombre, $fecha)
    {
        $todas = $this->listarAgrupadas();
        foreach ($todas as $p) {
            if ($p['nombre'] === $nombre && $p['fecha'] === $fecha) {
                return $p;
            }
        }
        return null;
    }

    /**
     * Guarda una pizza nueva a partir de items [ ['ingrediente_id'=>..., 'cantidad'=>int], ... ].
     */
    public function guardarPizza($nombrePizza, $items)
    {
        $nombrePizza = trim($nombrePizza);
        if ($nombrePizza === '' || empty($items)) {
            throw new InvalidArgumentException('Nombre o ingredientes inválidos');
        }

        $stmtExiste = mysqli_prepare($this->con, 'SELECT 1 FROM ingredientes WHERE id = ?');
        if (!$stmtExiste) {
            throw new RuntimeException('No se pudo preparar validación: ' . mysqli_error($this->con));
        }

        $fechaGrupo = date('Y-m-d H:i:s');

        $stmtIns = mysqli_prepare(
            $this->con,
            "INSERT INTO pizza (id, nombre, fk_ingredientes, fecha) VALUES (?, ?, ?, STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s'))"
        );
        if (!$stmtIns) {
            mysqli_stmt_close($stmtExiste);
            throw new RuntimeException('No se pudo preparar INSERT: ' . mysqli_error($this->con));
        }

        mysqli_begin_transaction($this->con);

        try {
            foreach ($items as $it) {
                $ingId = isset($it['ingrediente_id']) ? $it['ingrediente_id'] : '';
                $cant  = isset($it['cantidad']) ? (int)$it['cantidad'] : 0;

                if ($ingId === '' || $cant < 1) {
                    throw new RuntimeException('Item inválido');
                }

                mysqli_stmt_bind_param($stmtExiste, 's', $ingId);
                mysqli_stmt_execute($stmtExiste);
                $res = mysqli_stmt_get_result($stmtExiste);
                $ok = mysqli_fetch_row($res);
                mysqli_free_result($res);

                if (!$ok) {
                    throw new RuntimeException('Ingrediente no existe: ' . $ingId);
                }

                for ($i = 0; $i < $cant; $i++) {
                    $idFila = $this->generarUuid();
                    mysqli_stmt_bind_param($stmtIns, 'ssss', $idFila, $nombrePizza, $ingId, $fechaGrupo);

                    if (!mysqli_stmt_execute($stmtIns)) {
                        throw new RuntimeException('Error al insertar: ' . mysqli_error($this->con));
                    }
                }
            }

            mysqli_commit($this->con);
            mysqli_stmt_close($stmtExiste);
            mysqli_stmt_close($stmtIns);
        } catch (Exception $e) {
            mysqli_rollback($this->con);
            mysqli_stmt_close($stmtExiste);
            mysqli_stmt_close($stmtIns);
            throw $e;
        }
    }

    /**
     * Elimina una pizza completa (todas sus filas) por nombre y fecha.
     */
    public function eliminarPizza($nombre, $fecha)
    {
        $nombre = trim($nombre);
        $fecha  = trim($fecha);

        if ($nombre === '' || $fecha === '') {
            throw new InvalidArgumentException('Parámetros inválidos para eliminar pizza');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $fecha)) {
            throw new InvalidArgumentException('Formato de fecha inválido');
        }

        $sql = 'DELETE FROM pizza WHERE nombre = ? AND DATE_FORMAT(fecha, "%Y-%m-%d %H:%i:%s") = ?';
        $stmt = mysqli_prepare($this->con, $sql);
        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar DELETE: ' . mysqli_error($this->con));
        }

        mysqli_stmt_bind_param($stmt, 'ss', $nombre, $fecha);
        mysqli_stmt_execute($stmt);
        $afectadas = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        return (int)$afectadas;
    }

    /**
     * Ajusta la cantidad de un ingrediente para una pizza (nombre+fecha).
     */
    public function actualizarCantidadIngrediente($nombre, $fecha, $ingredienteId, $cantidad)
    {
        $nombre = trim($nombre);
        $fecha  = trim($fecha);
        $ingredienteId = trim($ingredienteId);

        if ($nombre === '' || $fecha === '' || $ingredienteId === '') {
            throw new InvalidArgumentException('Parámetros inválidos');
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $fecha)) {
            throw new InvalidArgumentException('Formato de fecha inválido');
        }

        if ($cantidad < 1) {
            throw new InvalidArgumentException('Cantidad inválida (mínimo 1)');
        }

        // Validar ingrediente existe
        $stmtExiste = mysqli_prepare($this->con, 'SELECT 1 FROM ingredientes WHERE id = ?');
        if (!$stmtExiste) {
            throw new RuntimeException('No se pudo preparar validación: ' . mysqli_error($this->con));
        }

        mysqli_stmt_bind_param($stmtExiste, 's', $ingredienteId);
        mysqli_stmt_execute($stmtExiste);
        $resExiste = mysqli_stmt_get_result($stmtExiste);
        $okExiste = mysqli_fetch_row($resExiste);
        mysqli_free_result($resExiste);
        mysqli_stmt_close($stmtExiste);

        if (!$okExiste) {
            throw new RuntimeException('Ingrediente no existe');
        }

        // Cantidad actual
        $stmtCount = mysqli_prepare(
            $this->con,
            "SELECT COUNT(*) AS c
             FROM pizza
             WHERE nombre = ?
               AND fk_ingredientes = ?
               AND DATE_FORMAT(fecha, '%Y-%m-%d %H:%i:%s') = ?"
        );
        if (!$stmtCount) {
            throw new RuntimeException('No se pudo preparar COUNT: ' . mysqli_error($this->con));
        }

        mysqli_stmt_bind_param($stmtCount, 'sss', $nombre, $ingredienteId, $fecha);
        mysqli_stmt_execute($stmtCount);
        $resCount = mysqli_stmt_get_result($stmtCount);
        $rowCount = mysqli_fetch_assoc($resCount);
        mysqli_free_result($resCount);
        mysqli_stmt_close($stmtCount);

        $actual = isset($rowCount['c']) ? (int)$rowCount['c'] : 0;

        if ($actual === $cantidad) {
            return ['before' => $actual, 'after' => $cantidad];
        }

        mysqli_begin_transaction($this->con);

        try {
            if ($cantidad > $actual) {
                $agregar = $cantidad - $actual;

                $stmtIns = mysqli_prepare(
                    $this->con,
                    "INSERT INTO pizza (id, nombre, fk_ingredientes, fecha)
                     VALUES (?, ?, ?, STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s'))"
                );
                if (!$stmtIns) {
                    throw new RuntimeException('No se pudo preparar INSERT: ' . mysqli_error($this->con));
                }

                for ($i = 0; $i < $agregar; $i++) {
                    $idFila = $this->generarUuid();
                    mysqli_stmt_bind_param($stmtIns, 'ssss', $idFila, $nombre, $ingredienteId, $fecha);
                    if (!mysqli_stmt_execute($stmtIns)) {
                        throw new RuntimeException('Error al insertar: ' . mysqli_error($this->con));
                    }
                }

                mysqli_stmt_close($stmtIns);
            } else {
                $quitar = $actual - $cantidad;
                $quitar = (int)$quitar;

                $sqlDel =
                    "DELETE FROM pizza
                     WHERE nombre = ?
                       AND fk_ingredientes = ?
                       AND DATE_FORMAT(fecha, '%Y-%m-%d %H:%i:%s') = ?
                     LIMIT " . $quitar;

                $stmtDel = mysqli_prepare($this->con, $sqlDel);
                if (!$stmtDel) {
                    throw new RuntimeException('No se pudo preparar DELETE: ' . mysqli_error($this->con));
                }

                mysqli_stmt_bind_param($stmtDel, 'sss', $nombre, $ingredienteId, $fecha);
                if (!mysqli_stmt_execute($stmtDel)) {
                    throw new RuntimeException('Error al eliminar: ' . mysqli_error($this->con));
                }

                mysqli_stmt_close($stmtDel);
            }

            mysqli_commit($this->con);

            return ['before' => $actual, 'after' => $cantidad];
        } catch (Exception $e) {
            mysqli_rollback($this->con);
            throw $e;
        }
    }

    /**
     * Actualiza varias cantidades de una pizza.
     * $cantidades es ['ingrediente_id' => cantidad, ...]
     */
    public function guardarCambiosPizza($nombre, $fecha, $cantidades)
    {
        foreach ($cantidades as $ingId => $cant) {
            $cant = (int)$cant;
            if ($cant < 1) {
                continue;
            }
            $this->actualizarCantidadIngrediente($nombre, $fecha, (string)$ingId, $cant);
        }
    }

 	private function generarUuid()
    {
        // Genera 16 bytes pseudoaleatorios compatibles con PHP 5
        if (function_exists('random_bytes')) {
            $data = random_bytes(16);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $data = openssl_random_pseudo_bytes(16);
        } else {
            $data = '';
            for ($i = 0; $i < 16; $i++) {
                $data .= chr(mt_rand(0, 255));
            }
        }
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
