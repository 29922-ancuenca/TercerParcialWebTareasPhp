<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/clases.php';

if (!isset($_POST['nombre'], $_POST['fecha']) || $_POST['nombre'] === '' || $_POST['fecha'] === '') {
    echo json_encode(['status' => 'error', 'message' => 'Parámetros inválidos']);
    exit;
}

$nombre = trim((string)$_POST['nombre']);
$fecha  = trim((string)$_POST['fecha']);

try {
    $repo = new PizzaRepository($con);
    $afectadas = $repo->eliminarPizza($nombre, $fecha);
    echo json_encode([
        'status' => 'success',
        'data'   => ['deleted_rows' => $afectadas],
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage(),
    ]);
}
