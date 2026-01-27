<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/clases.php';

try {
    $repo = new IngredienteRepository($con);
    $ingredientes = $repo->obtenerTodos();

    echo json_encode([
        'status' => 'ok',
        'data'   => $ingredientes,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage(),
    ]);
}
