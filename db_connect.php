<?php
// Variables de entorno de Railway con valores específicos
$database = "railway";  // MYSQL_DATABASE
$host = getenv('RAILWAY_PRIVATE_DOMAIN');    // MYSQLHOST
$password = "PGsViGdJGhrleQMDsXUODqQlgLnfvwPD"; // MYSQL_ROOT_PASSWORD 
$port = "3306";  // MYSQLPORT 
$user = "root";  // MYSQLUSER

try {
   // Conexión usando PDO para MySQL con valores específicos de Railway
   $conn = new PDO(
       "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
       $user,
       $password,
       array(
           PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
           PDO::ATTR_PERSISTENT => false,
           PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
           PDO::ATTR_EMULATE_PREPARES => false
       )
   );
   
   // Verificar conexión
   echo "Conexión exitosa a la base de datos"; 
   
} catch (PDOException $e) {
   // Log del error específico para debugging
   error_log("Error de conexión a la base de datos en Railway: " . $e->getMessage());
   
   // Mensaje genérico para el usuario
   die("Error al conectar con la base de datos. Por favor, contacte al administrador.");
}