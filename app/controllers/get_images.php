<?php
session_start();

// Activar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log function para depuración
function logError($message, $data = null) {
    $logFile = __DIR__ . '/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] DEBUG: " . $message;
    if ($data) {
        $logMessage .= " - DATA: " . print_r($data, true);
    }
    error_log($logMessage . PHP_EOL, 3, $logFile);
}

// Verificación de sesión
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    logError("Sesión no válida", $_SESSION);
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Acceso no autorizado', 'session_status' => session_status()]);
    exit();
}

try {
    // Incluir conexión a la base de datos
    require_once __DIR__ . '/../config/db_connect.php';
    
    if (!isset($_GET['justificacion_id'])) {
        throw new Exception('ID de justificación no proporcionado');
    }

    $justificacion_id = filter_var($_GET['justificacion_id'], FILTER_SANITIZE_NUMBER_INT);
    logError("ID de justificación recibido", $justificacion_id);

    // Primero verificamos si existe la justificación
    $checkJustificacion = $conn->prepare("SELECT JustificacionID FROM justificaciones WHERE JustificacionID = ?");
    $checkJustificacion->execute([$justificacion_id]);
    if ($checkJustificacion->rowCount() == 0) {
        throw new Exception('La justificación no existe');
    }

    // Verificar si la tabla existe y crearla si no existe
    $conn->query("CREATE TABLE IF NOT EXISTS Jimg (
        ImagenID INT PRIMARY KEY AUTO_INCREMENT,
        JustificacionID INT NOT NULL,
        RutaArchivo VARCHAR(255) NOT NULL,
        FechaCarga TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (JustificacionID) REFERENCES justificaciones(JustificacionID)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    )");
    
    logError("Tabla verificada/creada");

    // Consulta para obtener las imágenes
    $sql = "SELECT ImagenID, RutaArchivo FROM Jimg WHERE JustificacionID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$justificacion_id]);
    
    logError("Consulta ejecutada");

    $imagenes = [];
    while ($row = $stmt->fetch()) {
        // Asegúrate de que la ruta sea relativa al servidor web
        $rutaCompleta = '/../../public/imagenesJ/' . basename($row['RutaArchivo']);
        $imagenes[] = [
            'ImagenID' => $row['ImagenID'],
            'RutaArchivo' => $rutaCompleta
        ];
    }
    
    logError("Imágenes encontradas", count($imagenes));

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $imagenes,
        'count' => count($imagenes),
        'justificacion_id' => $justificacion_id
    ]);

} catch (PDOException $e) {
    logError("Error de PDO: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'error' => 'Error de base de datos',
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
} catch (Exception $e) {
    logError("Error general: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'error' => 'Error general',
        'message' => $e->getMessage()
    ]);
}