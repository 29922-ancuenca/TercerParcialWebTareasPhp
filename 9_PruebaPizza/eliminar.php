<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_POST['nombre'], $_POST['fecha']) || $_POST['nombre'] === '' || $_POST['fecha'] === '') {
    echo json_encode(["status" => "error", "message" => "Parámetros inválidos"]);
    mysqli_close($con);
    exit;
}

$nombre = trim((string)$_POST['nombre']);
$fecha = trim((string)$_POST['fecha']);

// Esperado desde api_pizza_listar.php: YYYY-mm-dd HH:ii:ss
if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $fecha)) {
    echo json_encode(["status" => "error", "message" => "Formato de fecha inválido"]);
    mysqli_close($con);
    exit;
}

$sql = "
DELETE FROM pizza
WHERE nombre = ?
  AND DATE_FORMAT(fecha, '%Y-%m-%d %H:%i:%s') = ?
";

$stmt = mysqli_prepare($con, $sql);
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "No se pudo preparar DELETE: " . mysqli_error($con)]);
    mysqli_close($con);
    exit;
}

mysqli_stmt_bind_param($stmt, "ss", $nombre, $fecha);

if (!mysqli_stmt_execute($stmt)) {
    echo json_encode(["status" => "error", "message" => "Error al eliminar: " . mysqli_error($con)]);
    mysqli_stmt_close($stmt);
    mysqli_close($con);
    exit;
}

$afectadas = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);
mysqli_close($con);

echo json_encode([
    "status" => "success",
    "data" => ["deleted_rows" => $afectadas]
]);

// Si viene desde formulario, redirigir
if (isset($_POST["redirect"]) && $_POST["redirect"] !== "") {
    session_start();
    $_SESSION["flash"] = "Pizza eliminada";
    header("Location: " . $_POST["redirect"]);
    exit;
}
