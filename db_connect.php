<?php
$serverName = "EDWIN\SQLEXPRESS";
$database = "aplicativo";
$user = "sa";
$pass = "EDWINROSAS";

try {
    $conn = new PDO("sqlsrv:server=$serverName;Database=$database", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexión exitosa a la base de datos"; // Descomentar para depuración
} catch (PDOException $e) {
    echo "Error en la conexión: " . $e->getMessage();
}

