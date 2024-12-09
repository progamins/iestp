<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $ie_procedencia = $_POST['ie_procedencia'];
    $programa = $_POST['programa'];
    $anio_ingreso = $_POST['anio_ingreso'];
    $celular = $_POST['celular'];

    $sql = "UPDATE estudiantes SET 
            nombre = :nombre, 
            ie_procedencia = :ie_procedencia, 
            programa = :programa, 
            anio_ingreso = :anio_ingreso, 
            celular = :celular 
            WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':ie_procedencia', $ie_procedencia);
    $stmt->bindParam(':programa', $programa);
    $stmt->bindParam(':anio_ingreso', $anio_ingreso);
    $stmt->bindParam(':celular', $celular);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
} else {
    echo "Invalid request";
}
?>