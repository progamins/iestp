<?php
// Configuración de la conexión a Railway
$hostname = "autorack.proxy.rlwy.net";
$port = "16484";
$database = "aplicativo";
$username = "root";
$password = "UjiJRmWZqlGWPXqsVhQYsGpeFibuUlcq";

try {
    // Creamos el DSN para la conexión
    $dsn = "mysql:host=$hostname;port=$port;dbname=$database;charset=utf8mb4";
    
    // Establecemos la conexión con opciones optimizadas
    $conexion = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Si llegamos aquí, la conexión fue exitosa
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }
    
    // Mensaje simple de conexión exitosa
    // Comenta esta línea en producción si no quieres ver el mensaje
    echo "Conexión exitosa a la base de datos aplicativo";

} catch(PDOException $e) {
    // Log del error para debugging
    error_log("Error de conexión DB: " . $e->getMessage());
    
    // En caso de una petición API, devolvemos JSON
    if (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Error de conexión a la base de datos']);
    } else {
        // Para peticiones normales, mensaje de error básico
        http_response_code(500);
        echo "Error de conexión a la base de datos. Por favor, intente más tarde.";
    }
    exit();
}

// Función de ayuda para verificar la conexión
function testConexion($conexion) {
    try {
        $conexion->query("SELECT 1");
        return true;
    } catch(PDOException $e) {
        error_log("Error en test de conexión: " . $e->getMessage());
        return false;
    }
}
?>