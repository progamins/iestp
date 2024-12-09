<?php
include 'db_connect.php'; // Archivo que establece la conexión a la base de datos

// Activar el reporte de errores solo durante el desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Preparar y ejecutar la consulta
    $sql = "SELECT a.id, a.dni_estudiante, e.nombre, a.fecha_hora, ea.estado
            FROM asistencias a
            JOIN estudiantes e ON a.dni_estudiante = e.dni
            JOIN estado_asistencia ea ON a.estado_id = ea.estado_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Obtener todas las asistencias
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar si hay asistencias
    if ($asistencias) {
        // Construir las filas de la tabla HTML
        foreach ($asistencias as $row) {
            // Formatear la fecha y hora
            $fecha_hora_formateada = (new DateTime($row['fecha_hora']))->format('Y-m-d H:i:s');

            echo "<tr>
                <td>{$row['dni_estudiante']}</td>
                <td>{$row['nombre']}</td>
                <td>{$fecha_hora_formateada}</td>
                <td>{$row['estado']}</td>

            </tr>";
        }
    } else {
        // Mensaje si no hay asistencias
        echo "<tr><td colspan='5' class='text-center'>No hay asistencias registradas</td></tr>";
    }
} catch (PDOException $e) {
    // Manejo de errores
    echo "<tr><td colspan='5' class='text-center'>Error en la consulta: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
}
?>
