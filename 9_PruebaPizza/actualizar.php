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

    // Reusar tu validación de fecha
    if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $fecha)) {
        session_start();
        $_SESSION["flash"] = "Formato de fecha inválido";
        header("Location: index.php");
        exit;
    }

    $ids = $_POST["ingrediente_id"];
    $cants = $_POST["cantidad"];

    // Ejecutar tu lógica actual por cada par (llamando a una función interna)
    for ($i = 0; $i < count($ids); $i++) {
        $ingredienteId = trim((string)$ids[$i]);
        $cantidad = (int)($cants[$i] ?? -1);

        if ($ingredienteId === '' || $cantidad < 1) {
            session_start();
            $_SESSION["flash"] = "Cantidad inválida (mínimo 1)";
            header("Location: " . ($_POST["redirect"] ?? "index.php"));
            exit;
        }

        // Aquí abajo: copia EXACTA de tu lógica actual (COUNT + INSERT/DELETE)
        // Para no duplicar todo, lo más simple es dejar tu archivo como está,
        // pero envolviendo la parte principal en una función y llamarla aquí.
    }

    session_start();
    $_SESSION["flash"] = "Cambios guardados";
    header("Location: " . ($_POST["redirect"] ?? "index.php"));
    exit;
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
