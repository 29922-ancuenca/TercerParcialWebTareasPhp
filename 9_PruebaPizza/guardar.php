<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// Detectar si viene desde FORM (sin AJAX)
$isForm = isset($_POST["action"]) && $_POST["action"] === "save_pizza";

// ===============================
// MODO FORM (sin JS/AJAX)
// ===============================
if ($isForm) {
    $nombrePizza = trim((string)($_POST["pizzaNombre"] ?? ""));
    $ids = $_POST["ingrediente_ids"] ?? [];
    $cants = $_POST["cantidades"] ?? [];

    if ($nombrePizza === "" || !is_array($ids) || !is_array($cants) || count($ids) === 0) {
        $_SESSION["flash"] = "Nombre o ingredientes inválidos";
        header("Location: index.php");
        exit;
    }

    // Armar items
    $items = [];
    for ($k = 0; $k < count($ids); $k++) {
        $ingId = trim((string)$ids[$k]);
        $cant = (int)($cants[$k] ?? 0);
        if ($ingId !== "" && $cant > 0) {
            $items[] = ["ingrediente_id" => $ingId, "cantidad" => $cant];
        }
    }

    if (count($items) === 0) {
        $_SESSION["flash"] = "Agrega al menos 1 ingrediente";
        header("Location: index.php");
        exit;
    }

    // Guardar en BD igual que antes
    $stmtExiste = mysqli_prepare($con, "SELECT 1 FROM ingredientes WHERE id = ?");
    if (!$stmtExiste) {
        $_SESSION["flash"] = "Error validación: " . mysqli_error($con);
        header("Location: index.php");
        exit;
    }

    $fechaGrupo = date('Y-m-d H:i:s');

    $stmtIns = mysqli_prepare(
        $con,
        "INSERT INTO pizza (id, nombre, fk_ingredientes, fecha)
         VALUES (?, ?, ?, STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s'))"
    );
    if (!$stmtIns) {
        $_SESSION["flash"] = "Error INSERT: " . mysqli_error($con);
        header("Location: index.php");
        exit;
    }

    mysqli_begin_transaction($con);

    try {
        foreach ($items as $it) {
            $ingId = $it["ingrediente_id"];
            $cant  = (int)$it["cantidad"];

            mysqli_stmt_bind_param($stmtExiste, "s", $ingId);
            mysqli_stmt_execute($stmtExiste);
            $res = mysqli_stmt_get_result($stmtExiste);
            $ok = $res ? mysqli_fetch_row($res) : null;
            if ($res) mysqli_free_result($res);

            if (!$ok) {
                throw new Exception("Ingrediente no existe: " . $ingId);
            }

            for ($i = 0; $i < $cant; $i++) {
                $idFila = generar_uuid();
                mysqli_stmt_bind_param($stmtIns, "ssss", $idFila, $nombrePizza, $ingId, $fechaGrupo);
                if (!mysqli_stmt_execute($stmtIns)) {
                    throw new Exception("Error al insertar: " . mysqli_error($con));
                }
            }
        }

        mysqli_commit($con);

        // Vaciar carrito en sesión
        $_SESSION["pizza_items"] = [];
        $_SESSION["flash"] = "Pizza guardada correctamente";

        mysqli_stmt_close($stmtExiste);
        mysqli_stmt_close($stmtIns);
        mysqli_close($con);

        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($con);
        mysqli_stmt_close($stmtExiste);
        mysqli_stmt_close($stmtIns);
        mysqli_close($con);

        $_SESSION["flash"] = $e->getMessage();
        header("Location: index.php");
        exit;
    }
}

// ===============================
// MODO JSON (tu modo actual con AJAX)
// ===============================
if (!isset($_POST['json']) || empty($_POST['json'])) {
    echo json_encode(["status" => "error", "message" => "No se recibieron datos"]);
    exit;
}

$payload = json_decode($_POST['json'], true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["status" => "error", "message" => "JSON inválido"]);
    exit;
}

$nombrePizza = trim($payload["nombrePizza"] ?? "");
$items = $payload["items"] ?? [];

if ($nombrePizza === "" || !is_array($items) || count($items) === 0) {
    echo json_encode(["status" => "error", "message" => "Nombre o ingredientes inválidos"]);
    exit;
}

$stmtExiste = mysqli_prepare($con, "SELECT 1 FROM ingredientes WHERE id = ?");
if (!$stmtExiste) {
    echo json_encode(["status" => "error", "message" => "No se pudo preparar validación: " . mysqli_error($con)]);
    exit;
}

$fechaGrupo = date('Y-m-d H:i:s');

$stmtIns = mysqli_prepare(
    $con,
    "INSERT INTO pizza (id, nombre, fk_ingredientes, fecha) VALUES (?, ?, ?, STR_TO_DATE(?, '%Y-%m-%d %H:%i:%s'))"
);
if (!$stmtIns) {
    echo json_encode(["status" => "error", "message" => "No se pudo preparar INSERT: " . mysqli_error($con)]);
    exit;
}

mysqli_begin_transaction($con);

try {
    foreach ($items as $it) {
        $ingId = $it["ingrediente_id"] ?? "";
        $cant = (int)($it["cantidad"] ?? 0);

        if ($ingId === "" || $cant < 1) {
            throw new Exception("Item inválido");
        }

        mysqli_stmt_bind_param($stmtExiste, "s", $ingId);
        mysqli_stmt_execute($stmtExiste);
        $res = mysqli_stmt_get_result($stmtExiste);
        $ok = mysqli_fetch_row($res);
        mysqli_free_result($res);

        if (!$ok) {
            throw new Exception("Ingrediente no existe: " . $ingId);
        }

        for ($i = 0; $i < $cant; $i++) {
            $idFila = generar_uuid();
            mysqli_stmt_bind_param($stmtIns, "ssss", $idFila, $nombrePizza, $ingId, $fechaGrupo);

            if (!mysqli_stmt_execute($stmtIns)) {
                throw new Exception("Error al insertar: " . mysqli_error($con));
            }
        }
    }

    mysqli_commit($con);

    mysqli_stmt_close($stmtExiste);
    mysqli_stmt_close($stmtIns);
    mysqli_close($con);

    echo json_encode(["status" => "success"]);
} catch (Exception $e) {
    mysqli_rollback($con);
    mysqli_stmt_close($stmtExiste);
    mysqli_stmt_close($stmtIns);
    mysqli_close($con);

    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

function generar_uuid() {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
