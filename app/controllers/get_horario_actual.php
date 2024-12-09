<?php
header('Content-Type: application/json');

try {
    include 'db_connect.php';
    
    // Obtener el horario actual basado en la fecha y hora
    $stmt = $conn->prepare("
        SELECT TOP 1 horario_id
        FROM horarios
        WHERE fecha_creacion <= GETDATE()
        ORDER BY fecha_creacion DESC
    ");
    
    $stmt->execute();
    $horario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($horario) {
        echo json_encode([
            'success' => true,
            'horario_id' => $horario['horario_id']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No hay horarios configurados'
        ]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}