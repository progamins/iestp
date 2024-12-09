<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$uploadDir = 'imagenesJ/';

// Asegurarse de que el directorio existe
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

try {
    if (!isset($_FILES['imagen'])) {
        throw new Exception('No se recibió ninguna imagen');
    }

    $file = $_FILES['imagen'];
    $fileName = basename($file['name']);
    $targetPath = $uploadDir . $fileName;

    // Mover el archivo a la carpeta de destino
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Imagen subida exitosamente',
            'filename' => $fileName
        ]);
    } else {
        throw new Exception('Error al mover el archivo');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>