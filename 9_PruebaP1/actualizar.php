<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/clases.php';

$nombre       = trim((string)(isset($_POST['nombre']) ? $_POST['nombre'] : ''));
$fecha        = trim((string)(isset($_POST['fecha']) ? $_POST['fecha'] : ''));
$ingredienteId = trim((string)(isset($_POST['ingrediente_id']) ? $_POST['ingrediente_id'] : ''));
$cantidad     = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : -1;

try {
    $repo = new PizzaRepository($con);
    $data = $repo->actualizarCantidadIngrediente($nombre, $fecha, $ingredienteId, $cantidad);
    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
