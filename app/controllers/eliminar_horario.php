<?php
require_once __DIR__ . '/../config/db_connect.php';

if (isset($_GET['id'])) {
    $horario_id = $_GET['id'];

    try {
        // Obtener la ruta del archivo desde la base de datos
        $sql = "SELECT archivo FROM horarios WHERE horario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$horario_id]);
        $archivo = $stmt->fetchColumn();
        
        // Verificar si se obtuvo la ruta del archivo
        if ($archivo) {
            // Construir la ruta completa del archivo
            $ruta_completa = '../../../public/uploads/' . basename($archivo); // Asegúrate de que la ruta sea correcta
            
            // Eliminar el archivo del servidor si existe
            if (file_exists($ruta_completa)) {
                if (!unlink($ruta_completa)) {
                    echo "Error al eliminar el archivo.";
                    exit();
                }
            } else {
                echo "El archivo no existe.";
                exit();
            }

            // Eliminar el registro de la base de datos
            $sql = "DELETE FROM horarios WHERE horario_id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute([$horario_id])) {
                header("Location: /iestp/public/subir_horario.php");
                exit();
            } else {
                echo "Error al eliminar el horario: " . $stmt->errorInfo()[2];
            }
        } else {
            echo "No se encontró el archivo en la base de datos.";
        }
    } catch (PDOException $e) {
        echo "Error en la operación: " . $e->getMessage();
    }
}
?>
