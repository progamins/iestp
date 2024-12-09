<?php
// obtener_unidad.php
require_once 'db_connect.php';

if (isset($_POST['unidad_id'])) {
    try {
        $stmt = $conn->prepare("
            SELECT unidad_id, nombre_unidad, programa_id, periodo_id, semestre_id 
            FROM unidades_didacticas 
            WHERE unidad_id = ?
        ");
        $stmt->execute([$_POST['unidad_id']]);
        $unidad = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode($unidad);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}