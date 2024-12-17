<?php
// Configuración para desarrollo local usando la URL pública de Railway
$serverName = "autorack.proxy.rlwy.net";
$port = "16484";
$database = "aplicativo";
$user = "root";
$pass = "UjiJRmWZqlGWPXqsVhQYsGpeFibuUlcq";

try {
    $conn = new PDO(
        "mysql:host=$serverName;port=$port;dbname=$database;charset=utf8mb4", 
        $user, 
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // Log del error para debugging
    error_log("Error de conexión DB: " . $e->getMessage());
    
    // Respuesta en formato JSON para mantener consistencia con la API
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit();
}