<?php
session_start();

// Cargar variables de entorno
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Priorizar variables de Railway, si no existen usar variables locales
$DB_HOST = getenv('MYSQLHOST') ?: getenv('DB_HOST');
$DB_USER = getenv('MYSQLUSER') ?: getenv('DB_USER');
$DB_PASSWORD = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD');
$DB_NAME = getenv('MYSQL_DATABASE') ?: getenv('DB_NAME');
$DB_PORT = getenv('MYSQLPORT') ?: getenv('DB_PORT');

try {
    $db = mysqli_connect(
        $DB_HOST,
        $DB_USER,
        $DB_PASSWORD,
        $DB_NAME,
        $DB_PORT
    );

    if (!$db) {
        throw new Exception("Error de conexión: " . mysqli_connect_error());
    }

    // Configurar charset
    mysqli_set_charset($db, "utf8mb4");

} catch (Exception $e) {
    error_log($e->getMessage());
    die("Error de conexión a la base de datos");
}
?>