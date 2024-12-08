<?php
include 'db_connect.php'; // Archivo que establece la conexión a la base de datos

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar que se ha enviado un ID para eliminar
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID de asistencia no especificado.";
    exit;
}

$id = $_GET['id'];

try {
    // Consulta SQL para eliminar la asistencia
    $sql = "DELETE FROM asistencias WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $id]);

    // Redirigir a la página principal después de eliminar
    header('Location: asistencias.php');
    exit;
} catch (PDOException $e) {
    echo "Error al eliminar asistencia: " . $e->getMessage();
}
?>
