<?php
require_once 'db_connect.php';

// Verificar que la solicitud sea por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si se ha proporcionado un ID de unidad
    if (isset($_POST['unidad_id'])) {
        $unidad_id = $_POST['unidad_id'];

        try {
            // Preparar la consulta SQL para eliminar la unidad
            $sql = "DELETE FROM unidades_didacticas WHERE unidad_id = ?";
            $stmt = $conn->prepare($sql);
            
            // Ejecutar la consulta
            $resultado = $stmt->execute([$unidad_id]);

            // Verificar si se eliminó correctamente
            if ($resultado) {
                // Devolver respuesta de éxito en formato JSON
                echo json_encode([
                    'success' => true,
                    'message' => 'Unidad didáctica eliminada correctamente'
                ]);
            } else {
                // Devolver respuesta de error
                echo json_encode([
                    'success' => false,
                    'message' => 'No se pudo eliminar la unidad didáctica'
                ]);
            }
        } catch (PDOException $e) {
            // Manejar cualquier error de base de datos
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar la unidad didáctica: ' . $e->getMessage()
            ]);
            
            // Registrar el error en el log del servidor
            error_log("Error al eliminar unidad didáctica: " . $e->getMessage());
        }
    } else {
        // Si no se proporcionó un ID de unidad
        echo json_encode([
            'success' => false,
            'message' => 'No se proporcionó un ID de unidad válido'
        ]);
    }
} else {
    // Si la solicitud no es por POST
    echo json_encode([
        'success' => false,
        'message' => 'Método de solicitud no válido'
    ]);
}
?>