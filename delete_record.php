<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Iniciar una transacción
        $conn->beginTransaction();

        // Obtener el dni del estudiante para eliminar los archivos correspondientes
        $sqlGetDNI = "SELECT dni FROM estudiantes WHERE id = :id";
        $stmtGetDNI = $conn->prepare($sqlGetDNI);
        $stmtGetDNI->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtGetDNI->execute();
        $dni = $stmtGetDNI->fetchColumn();

        if ($dni) {
            // Eliminar el archivo QR del estudiante
            $qrFilePath = "qr_codes/{$dni}.png";
            if (file_exists($qrFilePath)) {
                unlink($qrFilePath); // Eliminar el archivo QR
            }

            // Eliminar el archivo Excel del estudiante
            $excelFilePath = "uploads/{$dni}.xlsx";
            if (file_exists($excelFilePath)) {
                unlink($excelFilePath); // Eliminar el archivo Excel
            }
        }

        // Eliminar registros relacionados en la tabla `qr_codes`
        $sqlDeleteQRCode = "DELETE FROM qr_codes WHERE dni_estudiante = :dni";
        $stmtDeleteQRCode = $conn->prepare($sqlDeleteQRCode);
        $stmtDeleteQRCode->bindValue(':dni', $dni, PDO::PARAM_STR);
        $stmtDeleteQRCode->execute();

        // Eliminar registros relacionados en la tabla `asistencias`
        $sqlDeleteAsistencias = "DELETE FROM asistencias WHERE dni_estudiante = :dni";
        $stmtDeleteAsistencias = $conn->prepare($sqlDeleteAsistencias);
        $stmtDeleteAsistencias->bindValue(':dni', $dni, PDO::PARAM_STR);
        $stmtDeleteAsistencias->execute();

        // Eliminar registros relacionados en la tabla `justificaciones`
        $sqlDeleteJustificaciones = "DELETE FROM justificaciones WHERE dni_estudiante = :dni";
        $stmtDeleteJustificaciones = $conn->prepare($sqlDeleteJustificaciones);
        $stmtDeleteJustificaciones->bindValue(':dni', $dni, PDO::PARAM_STR);
        $stmtDeleteJustificaciones->execute();

        // Eliminar registros relacionados en la tabla `carnet`
        $sqlDeleteCarnet = "DELETE FROM carnet WHERE id_estudiante = :id";
        $stmtDeleteCarnet = $conn->prepare($sqlDeleteCarnet);
        $stmtDeleteCarnet->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtDeleteCarnet->execute();

        // Finalmente, eliminar el registro en la tabla `estudiantes`
        $sqlDeleteEstudiante = "DELETE FROM estudiantes WHERE id = :id";
        $stmtDeleteEstudiante = $conn->prepare($sqlDeleteEstudiante);
        $stmtDeleteEstudiante->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtDeleteEstudiante->execute();

        // Confirmar la transacción
        $conn->commit();

        // Mostrar alerta de éxito
        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Redirigiendo...</title>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: '¡Eliminado!',
                    text: 'El registro ha sido eliminado.',
                    icon: 'success'
                }).then(() => {
                    window.location.href = 'ver_registros.php';
                });
            </script>
        </body>
        </html>";

    } catch (Exception $e) {
        // En caso de error, revertir la transacción
        $conn->rollBack();

        // Mostrar alerta de error
        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Error</title>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo eliminar el registro. Detalles: {$e->getMessage()}',
                    icon: 'error'
                }).then(() => {
                    window.location.href = 'ver_registros.php';
                });
            </script>
        </body>
        </html>";
    }
}

$conn = null; // Cerramos la conexión PDO
?>
