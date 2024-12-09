<?php
require 'vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Obtener variables de entorno (funciona tanto en local como en Railway)
$serverName = getenv('DB_HOST') ?: getenv('MYSQLHOST');
$port = getenv('DB_PORT') ?: getenv('MYSQLPORT');
$database = getenv('DB_DATABASE') ?: getenv('MYSQL_DATABASE');
$user = getenv('DB_USERNAME') ?: getenv('MYSQLUSER');
$pass = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD');

try {
    // Conexión usando PDO para MySQL
    $conn = new PDO(
        "mysql:host=$serverName;port=$port;dbname=$database;charset=utf8",
        $user,
        $pass
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexión exitosa"; // Para debugging
} catch (PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error al conectar con la base de datos");
}