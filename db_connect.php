<?php
// Obtener variables de entorno
$serverName = getenv('MYSQLHOST');
$port = getenv('MYSQLPORT');
$database = getenv('MYSQL_DATABASE');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQL_ROOT_PASSWORD');

try {
    // Conexión usando PDO para MySQL
    $conn = new PDO(
        "mysql:host=$serverName;port=$port;dbname=$database;charset=utf8mb4",
        $user,
        $pass,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            PDO::ATTR_EMULATE_PREPARES => false
        )
    );
    // echo "Conexión exitosa a la base de datos"; // Descomentar para depuración
} catch (PDOException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    die("No se pudo conectar a la base de datos. Por favor, contacte al administrador.");
}