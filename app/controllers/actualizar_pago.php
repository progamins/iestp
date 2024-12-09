<?php
session_start();

// Verificar si el usuario ha iniciado sesión y tiene el rol correcto
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Dirección - Pagos') {
    header('Location: acceso_denegado.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db_connect.php';
    
    try {
        $sql = "UPDATE pagos SET 
                fecha = :fecha,
                numero_recibo_banco = :numero_recibo_banco,
                numero_recibo = :numero_recibo,
                nombres_apellidos = :nombres_apellidos,
                concepto = :concepto,
                importe = :importe,
                carrera = :carrera,
                observaciones = :observaciones
                WHERE numero_orden = :numero_orden";
        
        $stmt = $conn->prepare($sql);
        
        // Sanitizar y validar los datos
        $fecha = htmlspecialchars(strip_tags($_POST['fecha']));
        $numeroReciboBanco = htmlspecialchars(strip_tags($_POST['numero_recibo_banco']));
        $numeroRecibo = htmlspecialchars(strip_tags($_POST['numero_recibo']));
        $nombresApellidos = htmlspecialchars(strip_tags($_POST['nombres_apellidos']));
        $concepto = htmlspecialchars(strip_tags($_POST['concepto']));
        $importe = floatval($_POST['importe']);
        $carrera = htmlspecialchars(strip_tags($_POST['carrera']));
        $observaciones = htmlspecialchars(strip_tags($_POST['observaciones']));
        $numeroOrden = intval($_POST['numero_orden']);
        
        // Vincular parámetros
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':numero_recibo_banco', $numeroReciboBanco);
        $stmt->bindParam(':numero_recibo', $numeroRecibo);
        $stmt->bindParam(':nombres_apellidos', $nombresApellidos);
        $stmt->bindParam(':concepto', $concepto);
        $stmt->bindParam(':importe', $importe);
        $stmt->bindParam(':carrera', $carrera);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->bindParam(':numero_orden', $numeroOrden);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Pago actualizado correctamente";
        } else {
            $_SESSION['error'] = "Error al actualizar el pago";
        }
        
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error al actualizar el pago: " . $e->getMessage();
    }
    
    header('Location: administrar_pagos.php');
    exit();
}

// Si no es una petición POST, redirigir
header('Location: administrar_pagos.php');
exit();
?>