<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    // Validar y sanitizar las entradas
    $programa_id = filter_input(INPUT_POST, 'programa_id', FILTER_VALIDATE_INT);
    $periodo_id = filter_input(INPUT_POST, 'periodo_id', FILTER_VALIDATE_INT);
    $semestre_id = filter_input(INPUT_POST, 'semestre_id', FILTER_VALIDATE_INT);

    // Construir la consulta base
    $sql = "SELECT ud.unidad_id, ud.nombre_unidad, 
            pe.nombre_programa, pa.nombre as periodo_nombre,
            ts.nombre_semestre
            FROM unidades_didacticas ud
            INNER JOIN programas_estudio pe ON ud.programa_id = pe.programa_id
            INNER JOIN periodos_academicos pa ON ud.periodo_id = pa.periodo_id
            INNER JOIN tipo_semestre ts ON ud.semestre_id = ts.semestre_id
            WHERE 1=1";
    
    $params = [];

    // Agregar condiciones si los parámetros son válidos
    if ($programa_id !== false && $programa_id !== null) {
        $sql .= " AND ud.programa_id = ?";
        $params[] = $programa_id;
    }
    
    if ($periodo_id !== false && $periodo_id !== null) {
        $sql .= " AND ud.periodo_id = ?";
        $params[] = $periodo_id;
    }
    
    if ($semestre_id !== false && $semestre_id !== null) {
        $sql .= " AND ud.semestre_id = ?";
        $params[] = $semestre_id;
    }

    // Agregar ordenamiento
    $sql .= " ORDER BY ud.nombre_unidad ASC";

    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar si se encontraron resultados
    if (empty($unidades)) {
        echo json_encode([
            'success' => true,
            'message' => 'No se encontraron unidades didácticas con los criterios especificados',
            'data' => []
        ]);
        exit;
    }

    // Devolver resultados
    echo json_encode([
        'success' => true,
        'data' => $unidades
    ]);

} catch (PDOException $e) {
    // Log del error
    error_log("Error en get_unidades.php: " . $e->getMessage());
    
    // Respuesta de error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener las unidades didácticas',
        'error' => $e->getMessage()
    ]);
}