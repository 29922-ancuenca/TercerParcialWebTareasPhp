<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// Devuelve el catálogo de ingredientes para el <select> del frontend
$sql = "
SELECT
  id,
  nombre,
  CAST(precio AS DECIMAL(10,2)) AS precio
FROM ingredientes
ORDER BY nombre ASC
";

$r = mysqli_query($con, $sql);

if (!$r) {
  echo json_encode([
    "status" => "error",
    "message" => "Error al listar ingredientes: " . mysqli_error($con)
  ]);
  mysqli_close($con);
  exit;
}

$ingredientes = [];
while ($row = mysqli_fetch_assoc($r)) {
  $ingredientes[] = [
    "id" => $row["id"],
    "nombre" => $row["nombre"],
    "precio" => $row["precio"],
  ];
}

mysqli_free_result($r);
mysqli_close($con);

echo json_encode([
  "status" => "ok",
  "data" => $ingredientes
]);
