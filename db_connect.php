<?php
// Datos de conexión para MySQL
$serverName = "localhost:3308"; // Cambia esto al servidor de tu hosting, si es diferente
$database = "aplicativo"; // Cambia por el nombre de tu base de datos
$user = "root"; // Cambia por el usuario de tu base de datos
$pass = ""; // Cambia por la contraseña del usuario

try {
    // Conexión usando PDO para MySQL
    $conn = new PDO("mysql:host=$serverName;dbname=$database;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexión exitosa a la base de datos"; // Descomentar para depuración
} catch (PDOException $e) {
    echo "Error en la conexión: " . $e->getMessage();
}

