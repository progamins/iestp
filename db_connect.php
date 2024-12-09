<?php
// Variables de entorno de Railway
$database = getenv('MYSQL_DATABASE') ?: 'railway';  // Usa la variable de entorno o el valor por defecto
$host = getenv('MYSQL_HOST') ?: getenv('RAILWAY_PRIVATE_DOMAIN');  
$password = getenv('MYSQL_ROOT_PASSWORD') ?: 'PGsViGdJGhrleQMDsXUODqQlgLnfvwPD';
$port = getenv('MYSQL_PORT') ?: '3306';
$user = getenv('MYSQL_USER') ?: 'root';

try {
   // Conexión usando PDO para MySQL
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
   
   echo "Conexión exitosa a la base de datos"; 
   
} catch (PDOException $e) {
   error_log("Error de conexión a la base de datos en Railway: " . $e->getMessage());
   die("Error al conectar con la base de datos. Por favor, contacte al administrador.");
}