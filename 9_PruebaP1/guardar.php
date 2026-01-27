<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/clases.php';

if (!isset($_POST['json']) || empty($_POST['json'])) {
    echo json_encode(['status' => 'error', 'message' => 'No se recibieron datos']);
    exit;
}

$payload = json_decode($_POST['json'], true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['status' => 'error', 'message' => 'JSON inválido']);
    exit;
}

$nombrePizza = trim(isset($payload['nombrePizza']) ? $payload['nombrePizza'] : '');
$items = isset($payload['items']) ? $payload['items'] : [];

$itemsNormalizados = [];
if (is_array($items)) {
    foreach ($items as $it) {
        $itemsNormalizados[] = [
            'ingrediente_id' => isset($it['ingrediente_id']) ? $it['ingrediente_id'] : '',
            'cantidad'       => isset($it['cantidad']) ? (int)$it['cantidad'] : 0,
        ];
    }
}

try {
    $repo = new PizzaRepository($con);
    $repo->guardarPizza($nombrePizza, $itemsNormalizados);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
