<?php
require_once 'db_connect.php';

$programa_id = $_POST['programa_id'] ?? null;
$periodo_id = $_POST['periodo_id'] ?? null;
$semestre_id = $_POST['semestre_id'] ?? null;

try {
    $sql = "SELECT unidad_id, nombre_unidad FROM unidades_didacticas WHERE 1=1";
    $params = [];

    if ($programa_id) {
        $sql .= " AND programa_id = ?";
        $params[] = $programa_id;
    }
    if ($periodo_id) {
        $sql .= " AND periodo_id = ?";
        $params[] = $periodo_id;
    }
    if ($semestre_id) {
        $sql .= " AND semestre_id = ?";
        $params[] = $semestre_id;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($unidades);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}