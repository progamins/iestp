<?php
session_start();

// Verificación de sesión
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Acceso no autorizado');
}

// Conexión a la base de datos
$serverName = "EDWIN\SQLEXPRESS";
$database = "aplicativo";
$user = "sa";
$pass = "EDWINROSAS";

try {
    $conn = new PDO("sqlsrv:server=$serverName;Database=$database", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if (!isset($_GET['justificacion_id'])) {
        throw new Exception('ID de justificación no proporcionado');
    }
    
    $justificacion_id = filter_var($_GET['justificacion_id'], FILTER_SANITIZE_NUMBER_INT);
    
    // Consulta para obtener las imágenes
    $sql = "SELECT ImagenID, RutaArchivo FROM Jimg WHERE JustificacionID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$justificacion_id]);
    
    $imagenes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Asegúrate de que la ruta sea relativa al servidor web
        $rutaCompleta = 'imagenesJ/' . basename($row['RutaArchivo']);
        $imagenes[] = [
            'ImagenID' => $row['ImagenID'],
            'RutaArchivo' => $rutaCompleta
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($imagenes);
    
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}