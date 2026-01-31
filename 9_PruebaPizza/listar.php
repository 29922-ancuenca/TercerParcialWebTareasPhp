<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

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

$r = mysqli_query($con, $sql);

if (!$r) {
  echo json_encode([
    "status" => "error",
    "message" => "Error al listar pizzas: " . mysqli_error($con)
  ]);
  mysqli_close($con);
  exit;
}

$pizzas = [];

while ($row = mysqli_fetch_assoc($r)) {
  $nombre = $row["pizza_nombre"];
  $fecha  = $row["fecha_grupo"];
  $key = $nombre . "||" . $fecha;

  $precio = (float)$row["ingrediente_precio"];
  $cant   = (int)$row["cantidad"];
  $sub    = $precio * $cant;

  if (!isset($pizzas[$key])) {
    $pizzas[$key] = [
      "nombre" => $nombre,
      "fecha" => $fecha,
      "total" => 0.0,
      "ingredientes" => []
    ];
  }

  $pizzas[$key]["ingredientes"][] = [
    "id" => $row["ingrediente_id"],
    "nombre" => $row["ingrediente_nombre"],
    "precio" => $precio,
    "cantidad" => $cant,
    "subtotal" => $sub
  ];

  $pizzas[$key]["total"] += $sub;
}

mysqli_free_result($r);
mysqli_close($con);

echo json_encode([
  "status" => "ok",
  "data" => array_values($pizzas)
]);
