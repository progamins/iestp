<?php
// Configuración de error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de la conexión a Railway
$hostname = "autorack.proxy.rlwy.net";
$port = "16484";
$database = "aplicativo";
$username = "root";
$password = "UjiJRmWZqlGWPXqsVhQYsGpeFibuUlcq";

try {
    // Creamos la conexión a la base de datos
    $conn = new PDO(
        "mysql:host=$hostname;port=$port;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    // Log del error
    error_log("Error de conexión DB: " . $e->getMessage());
    
    // Si es una petición AJAX, devolver error en JSON
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(500);
        die(json_encode(['error' => 'Error de conexión a la base de datos']));
    }
    
    // Para peticiones normales
    die("Error de conexión a la base de datos. Por favor, intente más tarde.");
}

// Función útil para verificar el estado de la conexión
function testConnection($conn) {
    try {
        $conn->query("SELECT 1");
        return true;
    } catch(PDOException $e) {
        error_log("Error en test de conexión: " . $e->getMessage());
        return false;
    }
}
?>