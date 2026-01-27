<?php
session_start();

require_once __DIR__ . '/clases.php';

$ingredRepo = new IngredienteRepository($con);
$pizzaRepo  = new PizzaRepository($con);

$mensaje = null;
$error   = null;
$editPizza = null;

if (!isset($_SESSION['pizza_items'])) {
  $_SESSION['pizza_items'] = [];
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : null);

try {
    switch ($action) {
      case 'add_ingrediente':
        $nombrePizza = trim((string)(isset($_POST['pizzaNombre']) ? $_POST['pizzaNombre'] : ''));
        $ingId       = trim((string)(isset($_POST['ingredienteSelect']) ? $_POST['ingredienteSelect'] : ''));
        $cant        = isset($_POST['ingredienteCant']) ? (int)$_POST['ingredienteCant'] : 0;

            if ($nombrePizza === '' || $ingId === '' || $cant < 1) {
                throw new RuntimeException('Completa nombre, ingrediente y cantidad válida');
            }

            $ing = $ingredRepo->obtenerPorId($ingId);
            if (!$ing) {
                throw new RuntimeException('Ingrediente no encontrado');
            }

            // Guardamos nombre de pizza en sesión para no perderlo
            $_SESSION['pizza_nombre'] = $nombrePizza;

            $items = &$_SESSION['pizza_items'];
            $encontrado = false;
            foreach ($items as &$it) {
                if ($it['ingrediente_id'] === $ing['id']) {
                    $it['cantidad'] += $cant;
                    $encontrado = true;
                    break;
                }
            }
            unset($it);

            if (!$encontrado) {
                $items[] = [
                    'ingrediente_id' => $ing['id'],
                    'nombre'        => $ing['nombre'],
                    'precio'        => $ing['precio'],
                    'cantidad'      => $cant,
                ];
            }

            $mensaje = 'Ingrediente añadido a la pizza actual.';
            break;

        case 'quitar_ingrediente':
          $ingId = trim((string)(isset($_POST['ingrediente_id']) ? $_POST['ingrediente_id'] : ''));
            if ($ingId !== '') {
              $_SESSION['pizza_items'] = array_values(array_filter(
                $_SESSION['pizza_items'],
                function ($it) use ($ingId) {
                  return $it['ingrediente_id'] !== $ingId;
                }
              ));
            }
            $mensaje = 'Ingrediente quitado.';
            break;

        case 'guardar_pizza':
          $tmpNombre = '';
          if (isset($_POST['pizzaNombre'])) {
            $tmpNombre = $_POST['pizzaNombre'];
          } elseif (isset($_SESSION['pizza_nombre'])) {
            $tmpNombre = $_SESSION['pizza_nombre'];
          }
          $nombrePizza = trim((string)$tmpNombre);
            $items = $_SESSION['pizza_items'];

            if ($nombrePizza === '') {
                throw new RuntimeException('Escribe el nombre de la pizza');
            }
            if (empty($items)) {
                throw new RuntimeException('Agrega al menos un ingrediente');
            }

            $pizzaRepo->guardarPizza($nombrePizza, array_map(function ($it) {
                return [
                    'ingrediente_id' => $it['ingrediente_id'],
                    'cantidad'       => $it['cantidad'],
                ];
            }, $items));

            $_SESSION['pizza_items'] = [];
            $_SESSION['pizza_nombre'] = '';
            $mensaje = 'Pizza guardada correctamente.';
            break;

        case 'eliminar_pizza':
          $nombre = trim((string)(isset($_POST['nombre']) ? $_POST['nombre'] : ''));
          $fecha  = trim((string)(isset($_POST['fecha']) ? $_POST['fecha'] : ''));
            $pizzaRepo->eliminarPizza($nombre, $fecha);
            $mensaje = 'Pizza eliminada.';
            break;

        case 'editar_pizza':
          $nombre = '';
          if (isset($_POST['nombre'])) {
            $nombre = $_POST['nombre'];
          } elseif (isset($_GET['nombre'])) {
            $nombre = $_GET['nombre'];
          }
          $fecha = '';
          if (isset($_POST['fecha'])) {
            $fecha = $_POST['fecha'];
          } elseif (isset($_GET['fecha'])) {
            $fecha = $_GET['fecha'];
          }
          $nombre = trim((string)$nombre);
          $fecha  = trim((string)$fecha);
            $editPizza = $pizzaRepo->obtenerPizza($nombre, $fecha);
            if (!$editPizza) {
                throw new RuntimeException('No se encontró la pizza para editar');
            }
            break;

        case 'guardar_edicion_pizza':
          $nombre = trim((string)(isset($_POST['nombre']) ? $_POST['nombre'] : ''));
          $fecha  = trim((string)(isset($_POST['fecha']) ? $_POST['fecha'] : ''));
          $cantidades = isset($_POST['cantidades']) ? $_POST['cantidades'] : array();
            if (empty($cantidades)) {
                throw new RuntimeException('No hay cantidades para actualizar');
            }
            $pizzaRepo->guardarCambiosPizza($nombre, $fecha, $cantidades);
            $mensaje = 'Pizza actualizada.';
            break;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

try {
    $ingredientes = $ingredRepo->obtenerTodos();
} catch (Exception $e) {
    $ingredientes = [];
    $error = $error ?: $e->getMessage();
}

try {
    $pizzasGuardadas = $pizzaRepo->listarAgrupadas();
} catch (Exception $e) {
    $pizzasGuardadas = [];
    $error = $error ?: $e->getMessage();
}

$pizzaItems = $_SESSION['pizza_items'];
$pizzaNombreActual = trim((string)(isset($_SESSION['pizza_nombre']) ? $_SESSION['pizza_nombre'] : ''));

function totalActual($items)
{
  $tot = 0.0;
  foreach ($items as $it) {
    $tot += (float)$it['precio'] * (int)$it['cantidad'];
  }
  return $tot;
}

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pizza Builder (PHP)</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="styles.css?v=20260115">
</head>
<body>

<div class="header-box">
  <h2>Armar Pizza</h2>
  <p style="margin:0; opacity:.9;">Selecciona ingredientes, calcula total y guarda (sin JS)</p>
</div>

<div class="container" style="max-width: 900px;">
  <div class="main-card">

    <?php if ($mensaje): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="post" class="mb-3">
      <input type="hidden" name="action" value="add_ingrediente">
      <div class="form-row">
        <div class="col-md-6 mb-3">
          <label>Nombre de la Pizza</label>
          <input name="pizzaNombre" class="form-control" value="<?php echo htmlspecialchars($pizzaNombreActual, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <div class="col-md-4 mb-3">
          <label>Ingrediente</label>
          <select name="ingredienteSelect" class="form-control" required>
            <option value="">-- Seleccione --</option>
            <?php foreach ($ingredientes as $ing): ?>
              <option value="<?php echo htmlspecialchars($ing['id'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($ing['nombre'], ENT_QUOTES, 'UTF-8'); ?> ($<?php echo number_format($ing['precio'], 2); ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-2 mb-3">
          <label>Cantidad</label>
          <input name="ingredienteCant" type="number" class="form-control" min="1" value="1" required>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-2">
          <button type="submit" class="btn btn-add btn-block text-white">+ Añadir ingrediente</button>
        </div>
      </div>
    </form>

    <form method="post">
      <input type="hidden" name="action" value="guardar_pizza">
      <input type="hidden" name="pizzaNombre" value="<?php echo htmlspecialchars($pizzaNombreActual, ENT_QUOTES, 'UTF-8'); ?>">

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
          <?php if (empty($pizzaItems)): ?>
            <tr><td colspan="5">Sin ingredientes seleccionados.</td></tr>
          <?php else: ?>
            <?php foreach ($pizzaItems as $it): ?>
              <tr>
                <td><?php echo htmlspecialchars($it['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo number_format((float)$it['precio'], 2); ?></td>
                <td><?php echo (int)$it['cantidad']; ?></td>
                <td><?php echo number_format((float)$it['precio'] * (int)$it['cantidad'], 2); ?></td>
                <td>
                  <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="quitar_ingrediente">
                    <input type="hidden" name="ingrediente_id" value="<?php echo htmlspecialchars($it['ingrediente_id'], ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Quitar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div id="total" class="text-right">
        <?php $tot = totalActual($pizzaItems); ?>
        VALOR TOTAL: $ <?php echo number_format($tot, 2); ?>
      </div>

      <div class="row mt-3">
        <div class="col-md-6 mb-2">
          <button type="submit" class="btn btn-save btn-block text-white">Guardar Pizza</button>
        </div>
      </div>
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
        <?php if (empty($pizzasGuardadas)): ?>
          <tr><td colspan="5">No hay pizzas guardadas.</td></tr>
        <?php else: ?>
          <?php foreach ($pizzasGuardadas as $p): ?>
            <tr>
              <td><?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars($p['fecha'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td>$<?php echo number_format((float)$p['total'], 2); ?></td>
              <td>
                <?php $ingList = isset($p['ingredientes']) ? $p['ingredientes'] : array(); ?>
                <?php foreach ($ingList as $d): ?>
                  <?php
                    $cant = isset($d['cantidad']) ? (int)$d['cantidad'] : 0;
                    $sub  = isset($d['subtotal']) ? (float)$d['subtotal'] : 0.0;
                  ?>
                  <?php echo htmlspecialchars($d['nombre'], ENT_QUOTES, 'UTF-8'); ?> x<?php echo $cant; ?> ($<?php echo number_format($sub, 2); ?>)<br>
                <?php endforeach; ?>
              </td>
              <td>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="action" value="eliminar_pizza">
                  <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="fecha" value="<?php echo htmlspecialchars($p['fecha'], ENT_QUOTES, 'UTF-8'); ?>">
                  <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar la pizza seleccionada?');">Eliminar</button>
                </form>

                <form method="post" style="display:inline;">
                  <input type="hidden" name="action" value="editar_pizza">
                  <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="fecha" value="<?php echo htmlspecialchars($p['fecha'], ENT_QUOTES, 'UTF-8'); ?>">
                  <button type="submit" class="btn btn-warning btn-sm">Editar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($editPizza): ?>
      <div id="editPizzaPanel" class="main-card mt-4" style="padding:16px;">
        <h5 class="mb-2">Editar: <?php echo htmlspecialchars($editPizza['nombre'], ENT_QUOTES, 'UTF-8'); ?></h5>
        <div class="mb-2">
          <strong>Fecha:</strong> <?php echo htmlspecialchars($editPizza['fecha'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <form method="post">
          <input type="hidden" name="action" value="guardar_edicion_pizza">
          <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($editPizza['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="fecha" value="<?php echo htmlspecialchars($editPizza['fecha'], ENT_QUOTES, 'UTF-8'); ?>">

          <div class="table-responsive mt-3">
            <table class="table table-sm table-bordered mb-0">
              <thead>
                <tr>
                  <th>Ingrediente</th>
                  <th>Precio Unit</th>
                  <th style="width:140px;">Cantidad</th>
                </tr>
              </thead>
              <tbody>
              <?php $editIngList = isset($editPizza['ingredientes']) ? $editPizza['ingredientes'] : array(); ?>
              <?php foreach ($editIngList as $d): ?>
                <?php $cant = isset($d['cantidad']) ? (int)$d['cantidad'] : 0; ?>
                <tr>
                  <td><?php echo htmlspecialchars($d['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td>$<?php echo number_format((float)$d['precio'], 2); ?></td>
                  <td>
                    <input
                      type="number"
                      min="1"
                      class="form-control form-control-sm"
                      name="cantidades[<?php echo htmlspecialchars($d['id'], ENT_QUOTES, 'UTF-8'); ?>]"
                      value="<?php echo $cant; ?>"
                    />
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
