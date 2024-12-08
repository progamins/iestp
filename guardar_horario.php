<?php
session_start();
include 'db_connect.php'; // Asegúrate de que este archivo tenga la configuración correcta para SQL Server

if (isset($_POST['guardar_horario'])) {
    if (!empty($_FILES['archivo']['name'])) {
        $programa_id = $_POST['programa_id'];
        $archivo = $_FILES['archivo']['name'];
        $archivo_temp = $_FILES['archivo']['tmp_name'];

        // Verificar si el archivo tiene una extensión permitida (.jpg, .jpeg, .pdf)
        $allowed_extensions = ['jpg', 'jpeg', 'pdf'];
        $file_extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));

        if (in_array($file_extension, $allowed_extensions)) {
            // Guardar el archivo en la carpeta 'uploads'
            $upload_dir = 'uploads/';
            $archivo_destino = $upload_dir . uniqid('horario_', true) . '.' . $file_extension;

            if (move_uploaded_file($archivo_temp, $archivo_destino)) {
                try {
                    // Obtener el nombre del programa de estudio desde la base de datos
                    $sql = "SELECT nombre_programa FROM programas_estudio WHERE programa_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$programa_id]);
                    $nombre_programa = $stmt->fetchColumn();

                    if ($nombre_programa) {
                        // Insertar los datos en la tabla 'horarios'
                        $sql = "INSERT INTO horarios (programa_id, nombre, archivo, fecha_creacion) VALUES (?, ?, ?, GETDATE())";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$programa_id, $nombre_programa, $archivo_destino]);

                        if ($stmt->rowCount() > 0) {
                            // Redirigir a subir_horario.php con un parámetro de éxito
                            $_SESSION['success_message'] = "Horario subido exitosamente.";
                            header("Location: subir_horario.php");
                            exit();
                        } else {
                            $_SESSION['error_message'] = "Error al guardar el horario en la base de datos.";
                        }
                    } else {
                        $_SESSION['error_message'] = "Programa de estudio no encontrado.";
                    }
                } catch (PDOException $e) {
                    $_SESSION['error_message'] = "Error en la operación: " . $e->getMessage();
                }
            } else {
                $_SESSION['error_message'] = "Error al mover el archivo.";
            }
        } else {
            $_SESSION['error_message'] = "Formato de archivo no permitido. Solo se permiten archivos .jpg, .jpeg, .pdf.";
        }
    } else {
        $_SESSION['error_message'] = "No se seleccionó ningún archivo.";
    }

    // Redirigir de vuelta a la página para mostrar el mensaje de error
    header("Location: subir_horario.php");
    exit();
}

