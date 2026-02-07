<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '123');
define('DB_NAME', 'Productos_BD'); // Nombre de la base de datos de productos

// 1. Conectar al servidor MySQL (Sin seleccionar base de datos todavía)
$con = @mysqli_connect(DB_HOST, DB_USER, DB_PASS);

if (!$con) {
    die("Error crítico: No se pudo conectar a MySQL. " . mysqli_connect_error());
}

// 2. CREAR LA BASE DE DATOS SI NO EXISTE
// Esto soluciona el error "Unknown database 'prueba'"
$sql_db = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
if (!mysqli_query($con, $sql_db)) {
    die("Error al crear base de datos: " . mysqli_error($con));
}

// 3. SELECCIONAR LA BASE DE DATOS
// Esto soluciona el error "No database selected"
if (!mysqli_select_db($con, DB_NAME)) {
    die("Error al seleccionar la base de datos: " . mysqli_error($con));
}

// 4. CREAR LA TABLA SI NO EXISTE
// Esto soluciona el error "Table 'Productos_BD.detalle_factura' doesn't exist"
$sql_tabla = "CREATE TABLE IF NOT EXISTS detalle_factura (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    cantidad INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL
)";

if (!mysqli_query($con, $sql_tabla)) {
    die("Error al crear la tabla: " . mysqli_error($con));
}

// 5. Configurar caracteres especiales (tildes, ñ)
mysqli_set_charset($con, 'utf8');

// ¡Listo! Ya está conectado y con la tabla lista.
?>