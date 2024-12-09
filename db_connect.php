<?php
// Datos de conexión para MySQL en Railway
$serverName = "localhost"; // MySQL se está ejecutando en el puerto predeterminado
$database = "railway"; // Este es el nombre de la base de datos que se creó según los logs
$user = "root"; // Usuario root como se muestra en los logs
$pass = ""; // Contraseña vacía según los logs, pero esto debería cambiarse

try {
    // Conexión usando PDO para MySQL con opciones adicionales de seguridad
    $conn = new PDO(
        "mysql:host=$serverName;dbname=$database;charset=utf8mb4",
        $user,
        $pass,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, // Debido al certificado auto-firmado
            PDO::ATTR_EMULATE_PREPARES => false
        )
    );
    
    // echo "Conexión exitosa a la base de datos"; // Descomentar para depuración
} catch (PDOException $e) {
    // Manejo de error más seguro para producción
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    die("No se pudo conectar a la base de datos. Por favor, contacte al administrador.");
}