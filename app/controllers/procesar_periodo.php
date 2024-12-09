<?php
include 'db_connect.php';
session_start();

// Verificar si el usuario tiene permisos de administrador
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrador') {
    header('Location: acceso_denegado.php');
    exit();
}

// Validar y procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener los datos del formulario
        $anio = intval($_POST['anio']);
        $semestre = trim($_POST['semestre']);
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];
        $estado = isset($_POST['estado']) ? 1 : 0;

        // Crear el nombre del período (Ej: 2024-I)
        $nombre_periodo = $anio . '-' . $semestre;

        // Validar que no exista un período duplicado
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM periodos_academicos WHERE nombre = :nombre");
        $stmt_check->execute([':nombre' => $nombre_periodo]);
        $periodo_existe = $stmt_check->fetchColumn();

        if ($periodo_existe > 0) {
            throw new Exception("El período académico $nombre_periodo ya existe.");
        }

        // Validar que las fechas sean coherentes
        if (strtotime($fecha_inicio) >= strtotime($fecha_fin)) {
            throw new Exception("La fecha de inicio debe ser anterior a la fecha de fin.");
        }

        // Preparar la consulta SQL
        $stmt = $conn->prepare("INSERT INTO periodos_academicos 
            (nombre, fecha_inicio, fecha_fin, estado, semestres) 
            VALUES (:nombre, :fecha_inicio, :fecha_fin, :estado, :semestres)");

        // Convertir el semestre en un array JSON
        $semestres_json = json_encode([$semestre]);

        // Ejecutar la consulta
        $stmt->execute([
            ':nombre' => $nombre_periodo,
            ':fecha_inicio' => $fecha_inicio,
            ':fecha_fin' => $fecha_fin,
            ':estado' => $estado,
            ':semestres' => $semestres_json
        ]);

        // Redirigir con mensaje de éxito
        header("Location: periodos_academicos.php?success=Período académico registrado exitosamente");
        exit();

    } catch (Exception $e) {
        // Redirigir con mensaje de error
        header("Location: periodos_academicos.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Si se intenta acceder directamente sin POST
    header('Location: periodos_academicos.php');
    exit();
}