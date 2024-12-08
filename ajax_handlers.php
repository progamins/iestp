<?php
// ajax_handlers.php - Manejadores para las peticiones AJAX

require_once 'db_connect.php';

// Verificar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

// Obtener la acción solicitada
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'obtener_unidad':
        if (isset($_POST['unidad_id'])) {
            $stmt = $conn->prepare("SELECT * FROM unidades_didacticas WHERE unidad_id = ?");
            $stmt->execute([$_POST['unidad_id']]);
            $unidad = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($unidad);
        }
        break;

    case 'eliminar_unidad':
        if (isset($_POST['unidad_id'])) {
            try {
                $stmt = $conn->prepare("DELETE FROM unidades_didacticas WHERE unidad_id = ?");
                $stmt->execute([$_POST['unidad_id']]);
                echo json_encode(['success' => true]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
        break;
}