
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Definimos las credenciales de Aiven
define('DB_HOST', getenv('DB_HOST') ?: 'compu-magic-sistema-ventas.k.aivencloud.com');
define('DB_PORT', (int) (getenv('DB_PORT') ?: '23735')); // Puerto personalizado de Aiven
define('DB_USER', getenv('DB_USER') ?: 'avnadmin');
define('DB_PASS', getenv('DB_PASS') ?: ''); // Completa esta contraseña en tu entorno local o define la variable DB_PASS

// Si importaste tu base de datos en 'defaultdb', déjalo así. 
// Si creaste una base de datos con otro nombre en Workbench, pon ese nombre aquí.
define('DB_NAME', getenv('DB_NAME') ?: 'sistema_ventas'); 

// 2. Iniciamos el objeto mysqli sin conectar inmediatamente para poder configurar SSL
$conexion = mysqli_init();

if (!$conexion) {
    die("Error al inicializar mysqli");
}

// 3. Activamos el modo SSL obligatorio para cumplir con los requisitos de Aiven
$conexion->ssl_set(NULL, NULL, NULL, NULL, NULL);

// 4. Realizamos la conexión (incluyendo el puerto de Aiven al final)
$resultado_conexion = $conexion->real_connect(
    DB_HOST, 
    DB_USER, 
    DB_PASS, 
    DB_NAME, 
    DB_PORT, 
    NULL, 
    MYSQLI_CLIENT_SSL
);

if (!$resultado_conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

$conexion->set_charset("utf8mb4");