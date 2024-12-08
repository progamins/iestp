<?php
header('Content-Type: application/json');

try {
    // Obtener y decodificar los datos JSON
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData);
    
    if (!$data || !isset($data->dni_estudiante)) {
        throw new Exception('Datos inválidos');
    }
    
    include 'db_connect.php';
    
    // Verificar si el estudiante existe
    $stmt = $conn->prepare("
        SELECT e.id, e.nombre, e.programa_id 
        FROM estudiantes e 
        WHERE e.dni = ?
    ");
    $stmt->execute([$data->dni_estudiante]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$estudiante) {
        throw new Exception('Estudiante no encontrado');
    }
    
    // Verificar si ya existe una asistencia para este horario y estudiante
    $stmt = $conn->prepare("
        SELECT id 
        FROM asistencias 
        WHERE dni_estudiante = ? 
        AND horario_id = ? 
        AND CONVERT(date, fecha_hora) = CONVERT(date, GETDATE())
    ");
    $stmt->execute([$data->dni_estudiante, $data->horario_id]);
    
    if ($stmt->fetch()) {
        throw new Exception('Ya se registró asistencia para este horario hoy');
    }
    
    // Obtener la hora actual en formato 24 horas
    $currentHour = intval(date('H'));
    $currentMinute = intval(date('i'));
    
    // Convertir a minutos desde medianoche
    $currentMinutes = ($currentHour * 60) + $currentMinute;
    
    // Definir los límites de tiempo para la mañana (7:00 AM - 7:55 AM)
    $startTime = (7 * 60);        // 7:00 AM = 420 minutos
    $puntualLimit = (7 * 60) + 30;  // 7:30 AM = 450 minutos
    $tardanzaLimit = (7 * 60) + 55; // 7:55 AM = 475 minutos
    
    // Verificar si estamos dentro del horario de la mañana (7:00 AM - 7:55 AM)
    if ($currentHour >= 0 && $currentHour < 7) {
        $estado_id = 5; // Falta - Demasiado temprano
    } 
    elseif ($currentHour >= 8 || ($currentHour == 7 && $currentMinute > 55)) {
        $estado_id = 5; // Falta - Demasiado tarde
    }
    else {
        // Estamos dentro del rango de 7:00 AM a 7:55 AM
        if ($currentMinutes <= $puntualLimit) {
            $estado_id = 3; // Puntual (7:00 AM - 7:30 AM)
        } 
        elseif ($currentMinutes <= $tardanzaLimit) {
            $estado_id = 4; // Tardanza (7:31 AM - 7:55 AM)
        } 
        else {
            $estado_id = 5; // Falta (después de 7:55 AM)
        }
    }
    
    // Para debugging - Agregar información de tiempo al mensaje
    $timeInfo = sprintf(
        "Hora actual: %02d:%02d (%d minutos desde medianoche)", 
        $currentHour, 
        $currentMinute, 
        $currentMinutes
    );
    
    // Verificar si existe el horario
    $stmt = $conn->prepare("
        SELECT horario_id 
        FROM horarios 
        WHERE horario_id = ?
    ");
    $stmt->execute([$data->horario_id]);
    
    if (!$stmt->fetch()) {
        throw new Exception('El horario especificado no existe');
    }
    
    // Registrar la asistencia con el estado calculado
    $stmt = $conn->prepare("
        INSERT INTO asistencias (dni_estudiante, fecha_hora, horario_id, estado_id)
        VALUES (?, GETDATE(), ?, ?)
    ");
    $stmt->execute([$data->dni_estudiante, $data->horario_id, $estado_id]);
    
    // Obtener el estado de asistencia para el mensaje
    $stmt = $conn->prepare("
        SELECT estado 
        FROM estado_asistencia 
        WHERE estado_id = ?
    ");
    $stmt->execute([$estado_id]);
    $estadoInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => "Asistencia registrada: {$estudiante['nombre']} - Estado: {$estadoInfo['estado']} \nDetalles: {$timeInfo}"
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}