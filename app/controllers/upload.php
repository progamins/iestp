<?php
// Configurar el reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Definir la ruta absoluta del directorio de imágenes
$uploadDir = __DIR__ . '/../../public/imagenesJ/';

// Función para logging
function logError($message) {
    error_log(date('Y-m-d H:i:s') . " - " . $message . "\n", 3, __DIR__ . '/upload_errors.log');
}

try {
    // Verificar si se recibió la imagen
    if (!isset($_FILES['imagen'])) {
        throw new Exception('No se recibió ninguna imagen');
    }

    $file = $_FILES['imagen'];
    
    // Verificar errores de carga
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error en la carga del archivo: ' . $file['error']);
    }

    // Generar nombre único
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileName = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $uploadDir . $fileName;

    // Asegurarse de que el directorio existe
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception('No se pudo crear el directorio de carga');
        }
    }

    // Mover el archivo
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        logError("Error al mover archivo. Permisos del directorio: " . decoct(fileperms($uploadDir)));
        throw new Exception('Error al mover el archivo');
    }

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Imagen subida exitosamente',
        'filename' => $fileName,
        'path' => '/imagenesJ/' . $fileName // Ruta relativa para el navegador
    ]);

} catch (Exception $e) {
    logError($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'directory' => $uploadDir
        ]
    ]);
}