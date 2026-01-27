<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/clases.php';

try {
    $repo = new PizzaRepository($con);
    $data = $repo->listarAgrupadas();

    echo json_encode([
        'status' => 'ok',
        'data'   => $data,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage(),
    ]);
}
