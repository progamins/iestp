<?php
session_start();

// Verificación de sesión
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso no autorizado');
}

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
    
    if (!isset($_GET['justificacion_id'])) {
        throw new Exception('ID de justificación no proporcionado');
    }
    
    $justificacion_id = filter_var($_GET['justificacion_id'], FILTER_SANITIZE_NUMBER_INT);
    
    // Consulta para obtener las imágenes - adaptada para MySQL
    $sql = "SELECT ImagenID, RutaArchivo FROM Jimg WHERE JustificacionID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$justificacion_id]);
    
    $imagenes = [];
    while ($row = $stmt->fetch()) {
        // Asegúrate de que la ruta sea relativa al servidor web
        $rutaCompleta = 'imagenesJ/' . basename($row['RutaArchivo']);
        $imagenes[] = [
            'ImagenID' => $row['ImagenID'],
            'RutaArchivo' => $rutaCompleta
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($imagenes);
    
} catch (PDOException $e) {
    // Log del error para debugging
    error_log("Error de conexión DB: " . $e->getMessage());
    
    // Respuesta en formato JSON para mantener consistencia con la API
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit();
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}