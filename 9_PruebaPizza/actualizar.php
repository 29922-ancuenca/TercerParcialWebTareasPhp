<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// MODO FORM BATCH: ingrediente_id[] y cantidad[]
if (isset($_POST["ingrediente_id"]) && is_array($_POST["ingrediente_id"]) && isset($_POST["cantidad"]) && is_array($_POST["cantidad"])) {
    $nombre = trim((string)($_POST['nombre'] ?? ''));
    $fecha  = trim((string)($_POST['fecha'] ?? ''));

    if ($nombre === '' || $fecha === '') {
        session_start();
        $_SESSION["flash"] = "Parámetros inválidos";
        header("Location: index.php");
        exit;
    }

    // Validar formato de fecha (esperado YYYY-mm-dd HH:ii:ss)
    if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $fecha)) {
        session_start();
        $_SESSION["flash"] = "Formato de fecha inválido";
        header("Location: index.php");
        exit;
    }

    $ids   = $_POST["ingrediente_id"];
    $cants = $_POST["cantidad"];

    // Normalizar y validar datos antes de tocar la BD
    $items = [];
    for ($i = 0; $i < count($ids); $i++) {
        $ingredienteId = trim((string)$ids[$i]);
        $cantidad      = (int)($cants[$i] ?? -1);

        if ($ingredienteId === '' || $cantidad < 1) {
            session_start();
            $_SESSION["flash"] = "Cantidad inválida (mínimo 1)";
            header("Location: " . ($_POST["redirect"] ?? "index.php"));
            exit;
        }

        $items[] = [
            'ingrediente_id' => $ingredienteId,
            'cantidad'       => $cantidad
        ];
    }

    // Preparar sentencias comunes
    $stmtExiste = mysqli_prepare($con, "SELECT 1 FROM ingredientes WHERE id = ?");
    if (!$stmtExiste) {
        session_start();
        $_SESSION["flash"] = "Error validación: " . mysqli_error($con);
        header("Location: " . ($_POST["redirect"] ?? "index.php"));
        exit;
    }

    $stmtCount = mysqli_prepare(
        $con,
        "SELECT COUNT(*) AS c
         FROM pizza
         WHERE nombre = ?
           AND fk_ingredientes = ?
           AND DATE_FORMAT(fecha, '%Y-%m-%d %H:%i:%s') = ?"
    );
    if (!$stmtCount) {
        session_start();
        $_SESSION["flash"] = "Error COUNT: " . mysqli_error($con);
        mysqli_stmt_close($stmtExiste);
        header("Location: " . ($_POST["redirect"] ?? "index.php"));
        exit;
    }

    $stmtIns = mysqli_prepare(
        $con,
        "INSERT INTO pizza (id, nombre, fk_ingredientes, fecha)
         VALUES (?, ?, ?, STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s'))"
    );
    if (!$stmtIns) {
        session_start();
        $_SESSION["flash"] = "Error INSERT: " . mysqli_error($con);
        mysqli_stmt_close($stmtExiste);
        mysqli_stmt_close($stmtCount);
        header("Location: " . ($_POST["redirect"] ?? "index.php"));
        exit;
    }

    mysqli_begin_transaction($con);

    try {
        foreach ($items as $it) {
            $ingredienteId = $it['ingrediente_id'];
            $cantidad      = (int)$it['cantidad'];

            // Validar que el ingrediente exista
            mysqli_stmt_bind_param($stmtExiste, "s", $ingredienteId);
            mysqli_stmt_execute($stmtExiste);
            $resExiste = mysqli_stmt_get_result($stmtExiste);
            $okExiste  = $resExiste ? mysqli_fetch_row($resExiste) : null;
            if ($resExiste) {
                mysqli_free_result($resExiste);
            }

            if (!$okExiste) {
                throw new Exception("Ingrediente no existe: " . $ingredienteId);
            }

            // Cantidad actual en BD
            mysqli_stmt_bind_param($stmtCount, "sss", $nombre, $ingredienteId, $fecha);
            mysqli_stmt_execute($stmtCount);
            $resCount = mysqli_stmt_get_result($stmtCount);
            $rowCount = $resCount ? mysqli_fetch_assoc($resCount) : null;
            if ($resCount) {
                mysqli_free_result($resCount);
            }

            $actual = (int)($rowCount['c'] ?? 0);

            if ($actual === $cantidad) {
                continue; // Nada que cambiar para este ingrediente
            }

            if ($cantidad > $actual) {
                $agregar = $cantidad - $actual;

                for ($k = 0; $k < $agregar; $k++) {
                    $idFila = generar_uuid();
                    mysqli_stmt_bind_param($stmtIns, "ssss", $idFila, $nombre, $ingredienteId, $fecha);
                    if (!mysqli_stmt_execute($stmtIns)) {
                        throw new Exception("Error al insertar: " . mysqli_error($con));
                    }
                }
            } else {
                $quitar = $actual - $cantidad;
                $quitar = (int)$quitar;

                $sqlDel =
                    "DELETE FROM pizza
                     WHERE nombre = ?
                       AND fk_ingredientes = ?
                       AND DATE_FORMAT(fecha, '%Y-%m-%d %H:%i:%s') = ?
                     LIMIT " . $quitar;

                $stmtDel = mysqli_prepare($con, $sqlDel);
                if (!$stmtDel) {
                    throw new Exception("Error DELETE: " . mysqli_error($con));
                }

                mysqli_stmt_bind_param($stmtDel, "sss", $nombre, $ingredienteId, $fecha);
                if (!mysqli_stmt_execute($stmtDel)) {
                    mysqli_stmt_close($stmtDel);
                    throw new Exception("Error al eliminar: " . mysqli_error($con));
                }

                mysqli_stmt_close($stmtDel);
            }
        }

        mysqli_commit($con);

        mysqli_stmt_close($stmtExiste);
        mysqli_stmt_close($stmtCount);
        mysqli_stmt_close($stmtIns);
        mysqli_close($con);

        session_start();
        $_SESSION["flash"] = "Cambios guardados";
        header("Location: " . ($_POST["redirect"] ?? "index.php"));
        exit;
    } catch (Exception $e) {
        mysqli_rollback($con);

        if (isset($stmtExiste) && $stmtExiste) {
            mysqli_stmt_close($stmtExiste);
        }
        if (isset($stmtCount) && $stmtCount) {
            mysqli_stmt_close($stmtCount);
        }
        if (isset($stmtIns) && $stmtIns) {
            mysqli_stmt_close($stmtIns);
        }

        mysqli_close($con);

        session_start();
        $_SESSION["flash"] = $e->getMessage();
        header("Location: " . ($_POST["redirect"] ?? "index.php"));
        exit;
    }
}


$nombre = trim((string)($_POST['nombre'] ?? ''));
$fecha = trim((string)($_POST['fecha'] ?? ''));
$ingredienteId = trim((string)($_POST['ingrediente_id'] ?? ''));
$cantidad = (int)($_POST['cantidad'] ?? -1);

if ($nombre === '' || $fecha === '' || $ingredienteId === '') {
    echo json_encode(["status" => "error", "message" => "Parámetros inválidos"]);
    mysqli_close($con);
    exit;
}

// Esperado desde listar.php: YYYY-mm-dd HH:ii:ss
if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $fecha)) {
    echo json_encode(["status" => "error", "message" => "Formato de fecha inválido"]);
    mysqli_close($con);
    exit;
}

if ($cantidad < 1) {
    echo json_encode(["status" => "error", "message" => "Cantidad inválida (mínimo 1)"]);
    mysqli_close($con);
    exit;
}

// Validar ingrediente existe
$stmtExiste = mysqli_prepare($con, "SELECT 1 FROM ingredientes WHERE id = ?");
if (!$stmtExiste) {
    echo json_encode(["status" => "error", "message" => "No se pudo preparar validación: " . mysqli_error($con)]);
    mysqli_close($con);
    exit;
}

mysqli_stmt_bind_param($stmtExiste, "s", $ingredienteId);
mysqli_stmt_execute($stmtExiste);
$resExiste = mysqli_stmt_get_result($stmtExiste);
$okExiste = mysqli_fetch_row($resExiste);
mysqli_free_result($resExiste);
mysqli_stmt_close($stmtExiste);

if (!$okExiste) {
    echo json_encode(["status" => "error", "message" => "Ingrediente no existe"]);
    mysqli_close($con);
    exit;
}

// Cantidad actual (cuenta de filas)
$stmtCount = mysqli_prepare(
    $con,
    "SELECT COUNT(*) AS c
     FROM pizza
     WHERE nombre = ?
       AND fk_ingredientes = ?
       AND DATE_FORMAT(fecha, '%Y-%m-%d %H:%i:%s') = ?"
);
if (!$stmtCount) {
    echo json_encode(["status" => "error", "message" => "No se pudo preparar COUNT: " . mysqli_error($con)]);
    mysqli_close($con);
    exit;
}

mysqli_stmt_bind_param($stmtCount, "sss", $nombre, $ingredienteId, $fecha);
mysqli_stmt_execute($stmtCount);
$resCount = mysqli_stmt_get_result($stmtCount);
$rowCount = mysqli_fetch_assoc($resCount);
mysqli_free_result($resCount);
mysqli_stmt_close($stmtCount);

$actual = (int)($rowCount['c'] ?? 0);

if ($actual === $cantidad) {
    echo json_encode(["status" => "success", "data" => ["before" => $actual, "after" => $cantidad]]);
    mysqli_close($con);
    exit;
}

mysqli_begin_transaction($con);

try {
    if ($cantidad > $actual) {
        $agregar = $cantidad - $actual;

        $stmtIns = mysqli_prepare(
            $con,
            "INSERT INTO pizza (id, nombre, fk_ingredientes, fecha)
             VALUES (?, ?, ?, STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s'))"
        );
        if (!$stmtIns) {
            throw new Exception("No se pudo preparar INSERT: " . mysqli_error($con));
        }

        for ($i = 0; $i < $agregar; $i++) {
            $idFila = generar_uuid();
            mysqli_stmt_bind_param($stmtIns, "ssss", $idFila, $nombre, $ingredienteId, $fecha);
            if (!mysqli_stmt_execute($stmtIns)) {
                throw new Exception("Error al insertar: " . mysqli_error($con));
            }
        }

        mysqli_stmt_close($stmtIns);
    } else {
        $quitar = $actual - $cantidad;

        // LIMIT no siempre es parametrizable en todas las configuraciones; lo inyectamos tras validar int.
        $quitar = (int)$quitar;
        $sqlDel =
            "DELETE FROM pizza
             WHERE nombre = ?
               AND fk_ingredientes = ?
               AND DATE_FORMAT(fecha, '%Y-%m-%d %H:%i:%s') = ?
             LIMIT " . $quitar;

        $stmtDel = mysqli_prepare($con, $sqlDel);
        if (!$stmtDel) {
            throw new Exception("No se pudo preparar DELETE: " . mysqli_error($con));
        }

        mysqli_stmt_bind_param($stmtDel, "sss", $nombre, $ingredienteId, $fecha);
        if (!mysqli_stmt_execute($stmtDel)) {
            throw new Exception("Error al eliminar: " . mysqli_error($con));
        }

        mysqli_stmt_close($stmtDel);
    }

    mysqli_commit($con);
    mysqli_close($con);

    echo json_encode(["status" => "success", "data" => ["before" => $actual, "after" => $cantidad]]);
} catch (Exception $e) {
    mysqli_rollback($con);
    mysqli_close($con);

    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

function generar_uuid() {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
