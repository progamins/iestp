<?php
require_once 'db_connect.php';

$unidades = $_POST['unidades'] ?? [];
$nuevo_periodo = $_POST['nuevo_periodo'] ?? null;
$nuevo_semestre = $_POST['nuevo_semestre'] ?? null;

try {
    $conn->beginTransaction();

    $sql = "UPDATE unidades_didacticas SET ";
    $params = [];
    $updates = [];

    if ($nuevo_periodo) {
        $updates[] = "periodo_id = ?";
        $params[] = $nuevo_periodo;
    }
    if ($nuevo_semestre) {
        $updates[] = "semestre_id = ?";
        $params[] = $nuevo_semestre;
    }

    $sql .= implode(', ', $updates);
    $sql .= " WHERE unidad_id IN (" . implode(',', array_fill(0, count($unidades), '?')) . ")";
    
    $params = array_merge($params, $unidades);

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $actualizadas = $stmt->rowCount();

    $conn->commit();

    echo json_encode([
        'success' => true, 
        'actualizadas' => $actualizadas
    ]);
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}