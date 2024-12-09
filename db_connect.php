<?php
// Obtener variables de entorno exactamente como están definidas en Railway
$database = getenv('MYSQL_DATABASE');         // "railway"
$host = getenv('MYSQLHOST');                 // ${RAILWAY_PRIVATE_DOMAIN}
$password = getenv('MYSQLPASSWORD');         // ${MYSQL_ROOT_PASSWORD}
$port = getenv('MYSQLPORT');                 // "3306"
$user = getenv('MYSQLUSER');                 // "root"

try {
    // Conexión usando PDO para MySQL
    $conn = new PDO(
        "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
        $user,
        $password,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            PDO::ATTR_EMULATE_PREPARES => false
        )
    );
    echo "Conexión exitosa a la base de datos"; // Para verificar la conexión
} catch (PDOException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    die("No se pudo conectar a la base de datos. Por favor, contacte al administrador.");
}