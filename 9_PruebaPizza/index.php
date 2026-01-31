<?php
session_start();
require_once "conexion.php";

// CARRITO EN SESION
if (!isset($_SESSION["pizza_items"])) {
  $_SESSION["pizza_items"] = []; // key: ingrediente_id => ["id","nombre","precio","cantidad","subtotal"]
}

// MENSAJES
$flash = $_SESSION["flash"] ?? "";
unset($_SESSION["flash"]);

// Cargar ingredientes (igual que ingredientes.php pero directo)
$ingredientes = [];
$sqlIng = "SELECT id, nombre, CAST(precio AS DECIMAL(10,2)) AS precio FROM ingredientes ORDER BY nombre ASC";
$rIng = mysqli_query($con, $sqlIng);
if ($rIng) {
  while ($row = mysqli_fetch_assoc($rIng)) {
    $ingredientes[] = [
      "id" => $row["id"],
      "nombre" => $row["nombre"],
      "precio" => (float)$row["precio"]
    ];
  }
  mysqli_free_result($rIng);
}

// Procesar acciones del carrito
$action = $_POST["action"] ?? "";
if ($action === "add_item") {
  $ingId = trim((string)($_POST["ingrediente_id"] ?? ""));
  $cant = (int)($_POST["cantidad"] ?? 0);

  if ($ingId === "" || $cant < 1) {
    $_SESSION["flash"] = "Selecciona ingrediente y cantidad válida";
    header("Location: index.php");
    exit;
  }

  // Buscar ingrediente en el catálogo ya cargado
  $found = null;
  foreach ($ingredientes as $i) {
    if ($i["id"] === $ingId) { $found = $i; break; }
  }

  if (!$found) {
    $_SESSION["flash"] = "Ingrediente no encontrado";
    header("Location: index.php");
    exit;
  }

  // Si existe en carrito, suma
  if (isset($_SESSION["pizza_items"][$ingId])) {
    $_SESSION["pizza_items"][$ingId]["cantidad"] += $cant;
    $_SESSION["pizza_items"][$ingId]["subtotal"] = $_SESSION["pizza_items"][$ingId]["cantidad"] * $_SESSION["pizza_items"][$ingId]["precio"];
  } else {
    $_SESSION["pizza_items"][$ingId] = [
      "id" => $found["id"],
      "nombre" => $found["nombre"],
      "precio" => (float)$found["precio"],
      "cantidad" => $cant,
      "subtotal" => (float)$found["precio"] * $cant
    ];
  }

  header("Location: index.php");
  exit;
}

if ($action === "remove_item") {
  $ingId = trim((string)($_POST["ingrediente_id"] ?? ""));
  if ($ingId !== "" && isset($_SESSION["pizza_items"][$ingId])) {
    unset($_SESSION["pizza_items"][$ingId]);
  }
  header("Location: index.php");
  exit;
}

if ($action === "clear_cart") {
  $_SESSION["pizza_items"] = [];
  header("Location: index.php");
  exit;
}

// Total carrito
$totalCarrito = 0.0;
foreach ($_SESSION["pizza_items"] as $it) {
  $totalCarrito += (float)$it["subtotal"];
}

// Listar pizzas guardadas (igual que listar.php pero directo)
$pizzas = [];
$sqlList = "
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
$rList = mysqli_query($con, $sqlList);
if ($rList) {
  while ($row = mysqli_fetch_assoc($rList)) {
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
  mysqli_free_result($rList);
}
mysqli_close($con);

// Panel edición: se abre si vienen por GET
$editNombre = trim((string)($_GET["edit_nombre"] ?? ""));
$editFecha  = trim((string)($_GET["edit_fecha"] ?? ""));
$editKey = ($editNombre !== "" && $editFecha !== "") ? ($editNombre . "||" . $editFecha) : "";
$editPizza = ($editKey !== "" && isset($pizzas[$editKey])) ? $pizzas[$editKey] : null;

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pizza Builder</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="styles.css?v=20260115">
</head>
<body>

<div class="header-box">
  <h2>Armar Pizza</h2>
  <p style="margin:0; opacity:.9;">Selecciona ingredientes, calcula total y guarda</p>
</div>

<div class="container" style="max-width: 900px;">
  <div class="main-card">

    <?php if ($flash !== ""): ?>
      <div class="alert alert-warning"><?= h($flash) ?></div>
    <?php endif; ?>

    <!-- FORM PRINCIPAL: agregar ingrediente al carrito -->
    <form method="POST" class="mb-2">
      <input type="hidden" name="action" value="add_item">

      <div class="form-row">
        <div class="col-md-6 mb-3">
          <label>Nombre de la Pizza</label>
          <!-- Este nombre se usará al GUARDAR (en guardar.php) -->
          <input name="pizzaNombre" class="form-control" placeholder="Ej: Pizza Especial" required>
        </div>

        <div class="col-md-4 mb-3">
          <label>Ingrediente</label>
          <select name="ingrediente_id" class="form-control" required>
            <?php foreach ($ingredientes as $i): ?>
              <option value="<?= h($i["id"]) ?>">
                <?= h($i["nombre"]) ?> ($<?= number_format($i["precio"], 2) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-2 mb-3">
          <label>Cantidad</label>
          <input name="cantidad" type="number" class="form-control" min="1" value="1" required>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-2">
          <button type="submit" class="btn btn-add btn-block text-white">+ Añadir ingrediente</button>
        </div>

        <div class="col-md-6 mb-2">
          <!-- Guardar: enviamos carrito a guardar.php en modo FORM -->
          <button
            type="submit"
            class="btn btn-save btn-block text-white"
            formaction="guardar.php"
            formmethod="POST"
            name="action"
            value="save_pizza"
          >Guardar Pizza</button>
        </div>
      </div>

      <!-- Enviar también el carrito en arrays -->
      <?php foreach ($_SESSION["pizza_items"] as $it): ?>
        <input type="hidden" name="ingrediente_ids[]" value="<?= h($it["id"]) ?>">
        <input type="hidden" name="cantidades[]" value="<?= (int)$it["cantidad"] ?>">
      <?php endforeach; ?>
    </form>

    <hr>

    <!-- TABLA CARRITO -->
    <div class="table-responsive">
      <table class="table table-bordered text-center" id="tablaPizza">
        <thead>
          <tr>
            <th>Ingrediente</th>
            <th>Precio Unit</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($_SESSION["pizza_items"] as $it): ?>
            <tr>
              <td><?= h($it["nombre"]) ?></td>
              <td><?= number_format((float)$it["precio"], 2) ?></td>
              <td><?= (int)$it["cantidad"] ?></td>
              <td><?= number_format((float)$it["subtotal"], 2) ?></td>
              <td>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="action" value="remove_item">
                  <input type="hidden" name="ingrediente_id" value="<?= h($it["id"]) ?>">
                  <button class="btn btn-danger btn-sm" type="submit">Quitar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (count($_SESSION["pizza_items"]) === 0): ?>
            <tr><td colspan="5">Sin ingredientes aún</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div id="total" class="text-right">
      VALOR TOTAL: $ <?= number_format($totalCarrito, 2) ?>
    </div>

    <form method="POST" class="mt-2">
      <input type="hidden" name="action" value="clear_cart">
      <button type="submit" class="btn btn-outline-secondary btn-sm">Vaciar</button>
    </form>

    <hr>

    <h5>Pizzas guardadas</h5>
    <div class="table-responsive">
      <table class="table table-bordered" id="tablaPizzasGuardadas">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Detalle</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pizzas as $p): ?>
            <tr>
              <td><?= h($p["nombre"]) ?></td>
              <td><?= h($p["fecha"]) ?></td>
              <td>$<?= number_format((float)$p["total"], 2) ?></td>
              <td>
                <?php foreach ($p["ingredientes"] as $d): ?>
                  <?= h($d["nombre"]) ?> x<?= (int)$d["cantidad"] ?>
                  ($<?= number_format((float)$d["subtotal"], 2) ?>)
                  <br>
                <?php endforeach; ?>
              </td>
              <td>
                <!-- Eliminar -->
                <form method="POST" action="eliminar.php" style="display:inline;">
                  <input type="hidden" name="nombre" value="<?= h($p["nombre"]) ?>">
                  <input type="hidden" name="fecha" value="<?= h($p["fecha"]) ?>">
                  <input type="hidden" name="redirect" value="index.php">
                  <button type="submit" class="btn btn-danger btn-sm"
                    onclick="return confirm('¿Eliminar la pizza <?= h($p['nombre']) ?> (<?= h($p['fecha']) ?>)?')"
                  >Eliminar</button>
                </form>

                <!-- Editar (abre panel con GET) -->
                <a class="btn btn-warning btn-sm"
                   href="index.php?edit_nombre=<?= urlencode($p["nombre"]) ?>&edit_fecha=<?= urlencode($p["fecha"]) ?>#editPizzaPanel">
                  Editar
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (count($pizzas) === 0): ?>
            <tr><td colspan="5">No hay pizzas guardadas</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- PANEL EDICION INLINE -->
    <?php if ($editPizza): ?>
      <div id="editPizzaPanel" class="main-card mt-4" style="padding:16px;">
        <div class="d-flex align-items-center justify-content-between" style="gap:12px;">
          <h5 class="mb-0">Editar: <?= h($editPizza["nombre"]) ?></h5>
          <a href="index.php" class="btn btn-outline-secondary btn-sm">Cerrar</a>
        </div>

        <div class="mt-2">
          <strong>Pizza:</strong> <?= h($editPizza["nombre"]) ?><br>
          <strong>Fecha:</strong> <?= h($editPizza["fecha"]) ?>
        </div>

        <form method="POST" action="actualizar.php" class="mt-3">
          <input type="hidden" name="nombre" value="<?= h($editPizza["nombre"]) ?>">
          <input type="hidden" name="fecha" value="<?= h($editPizza["fecha"]) ?>">
          <input type="hidden" name="redirect" value="index.php?edit_nombre=<?= urlencode($editPizza["nombre"]) ?>&edit_fecha=<?= urlencode($editPizza["fecha"]) ?>#editPizzaPanel">

          <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
              <thead>
                <tr>
                  <th>Ingrediente</th>
                  <th>Precio Unit</th>
                  <th style="width:140px;">Cantidad</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($editPizza["ingredientes"] as $d): ?>
                  <tr>
                    <td><?= h($d["nombre"]) ?></td>
                    <td>$<?= number_format((float)$d["precio"], 2) ?></td>
                    <td>
                      <!-- arrays para actualizar varios ingredientes en un submit -->
                      <input type="hidden" name="ingrediente_id[]" value="<?= h($d["id"]) ?>">
                      <input type="number" min="1" class="form-control form-control-sm"
                             name="cantidad[]" value="<?= (int)$d["cantidad"] ?>">
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          </div>
        </form>
      </div>
    <?php endif; ?>

  </div>
</div>

</body>
</html>
