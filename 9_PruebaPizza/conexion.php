<?php
// Datos de conexión
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '123');      // ajusta si tu clave es distinta
define('DB_NAME', 'pizzabd');  // nombre EXACTO de tu base de datos

// Crear conexión
$con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if (!$con) {
    die("Error de conexión a la base de datos: " . mysqli_connect_error());
}

// Configurar charset (tildes, ñ, etc.)
mysqli_set_charset($con, 'utf8mb4');
?>
