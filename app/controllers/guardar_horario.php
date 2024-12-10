<?php
ob_start();
session_start();
require_once __DIR__ . '/../config/db_connect.php';

// Verificación de autenticación
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Verificación de rol
if ($_SESSION['role'] !== 'Administrador') {
    header('Location: acceso_denegado.php');
    exit();
}

// Procesamiento del formulario de subida
if (isset($_POST['guardar_horario'])) {
    if (empty($_FILES['archivo']['name'])) {
        $_SESSION['error_message'] = "No se seleccionó ningún archivo.";
        header("Location:");
        exit();
    }

    $programa_id = $_POST['programa_id'];
    $archivo = $_FILES['archivo']['name'];
    $archivo_temp = $_FILES['archivo']['tmp_name'];
    
    // Verificación de extensión
    $allowed_extensions = ['jpg', 'jpeg', 'pdf'];
    $file_extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        $_SESSION['error_message'] = "Formato de archivo no permitido. Solo se permiten archivos .jpg, .jpeg, .pdf.";
        header("Location: /iestp/public/subir_horario.php");
        exit();
    }

    // Crear directorio si no existe
    $upload_dir = '../../../public/uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generar nombre único para el archivo
    $archivo_destino = $upload_dir . uniqid('horario_', true) . '.' . $file_extension;

    try {
        // Verificar si el directorio tiene permisos de escritura
        if (!is_writable($upload_dir)) {
            throw new Exception("El directorio de carga no tiene permisos de escritura.");
        }

        // Mover el archivo
        if (!move_uploaded_file($archivo_temp, $archivo_destino)) {
            throw new Exception("Error al mover el archivo.");
        }

        // Obtener nombre del programa
        $stmt = $conn->prepare("SELECT nombre_programa FROM programas_estudio WHERE programa_id = ?");
        $stmt->execute([$programa_id]);
        $nombre_programa = $stmt->fetchColumn();

        if (!$nombre_programa) {
            throw new Exception("Programa de estudio no encontrado.");
        }

        // Insertar en la base de datos
        $sql = "INSERT INTO horarios (programa_id, nombre, archivo, fecha_creacion) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$programa_id, $nombre_programa, $archivo_destino]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Horario subido exitosamente.";
        } else {
            throw new Exception("Error al guardar el horario en la base de datos.");
        }

    } catch (Exception $e) {
        // Si hay error, eliminar el archivo si se subió
        if (file_exists($archivo_destino)) {
            unlink($archivo_destino);
        }
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }

    header("Location: /iestp/public/subir_horario.php");
    exit();
}

// Consultar horarios existentes
try {
    $sql = "SELECT h.horario_id, p.nombre_programa, h.archivo, h.fecha_creacion 
            FROM horarios h 
            JOIN programas_estudio p ON h.programa_id = p.programa_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error en la consulta: " . $e->getMessage();
}
?>