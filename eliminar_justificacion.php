<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrador') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de justificación inválido']);
    exit();
}

include 'db_connect.php';

try {
    $conn->beginTransaction();

    // Primero obtenemos los nombres de las imágenes
    $sql_images = "SELECT NombreArchivo FROM Jimg WHERE JustificacionID = ?";
    $stmt = $conn->prepare($sql_images);
    $stmt->execute([$_POST['id']]);
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Eliminamos los registros de imágenes
    $sql_delete_images = "DELETE FROM Jimg WHERE JustificacionID = ?";
    $stmt = $conn->prepare($sql_delete_images);
    $stmt->execute([$_POST['id']]);

    // Eliminamos la justificación
    $sql_delete_justification = "DELETE FROM justificaciones WHERE JustificacionID = ?";
    $stmt = $conn->prepare($sql_delete_justification);
    $stmt->execute([$_POST['id']]);

    // Si todo salió bien, eliminamos los archivos físicos
    foreach ($images as $image) {
        $image_path = 'uploads/justificaciones/' . trim($image);
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
}
?>