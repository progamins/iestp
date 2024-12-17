<?php
// Clase para manejar la configuración de la base de datos
class DatabaseConfig {
    private static function getEnvironmentVariables() {
        // Priorizar variables de entorno de Railway
        if (getenv('RAILWAY_ENVIRONMENT') === 'production') {
            return [
                'host' => getenv('MYSQLHOST') ?: 'mysql.railway.internal',
                'port' => getenv('MYSQLPORT') ?: '3306',
                'database' => getenv('MYSQLDATABASE') ?: 'railway',
                'user' => getenv('MYSQLUSER') ?: 'root',
                'password' => getenv('MYSQLPASSWORD') ?: 'UjiJRmWZqlGWPXqsVhQYsGpeFibuUlcq'
            ];
        }
        
        // Configuración para el proxy público de Railway (desarrollo/pruebas)
        return [
            'host' => 'autorack.proxy.rlwy.net',
            'port' => '16484',
            'database' => 'railway',
            'user' => 'root',
            'password' => 'UjiJRmWZqlGWPXqsVhQYsGpeFibuUlcq'
        ];
    }

    public static function getDSN() {
        $config = self::getEnvironmentVariables();
        return sprintf(
            "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
            $config['host'],
            $config['port'],
            $config['database']
        );
    }

    public static function getCredentials() {
        $config = self::getEnvironmentVariables();
        return [
            'user' => $config['user'],
            'password' => $config['password']
        ];
    }
}

// Opciones PDO
$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

try {
    // Obtener configuración
    $dsn = DatabaseConfig::getDSN();
    $credentials = DatabaseConfig::getCredentials();
    
    // Crear conexión
    $conn = new PDO(
        $dsn,
        $credentials['user'],
        $credentials['password'],
        $pdoOptions
    );

    // Configurar zona horaria
    $conn->exec("SET time_zone = 'America/Lima'");
    
} catch (PDOException $e) {
    // Log detallado del error
    error_log(sprintf(
        "Error de conexión a la base de datos: %s\nTrace: %s",
        $e->getMessage(),
        $e->getTraceAsString()
    ));
    
    // Determinar tipo de respuesta basado en el contexto
    if (php_sapi_name() === 'cli') {
        // Respuesta para CLI
        die("Error de conexión a la base de datos: " . $e->getMessage());
    } elseif (!headers_sent()) {
        // Respuesta JSON para API
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'Error de conexión a la base de datos',
            'message' => $e->getMessage(),
            'code' => $e->getCode()
        ]);
    } else {
        // Respuesta HTML para navegador
        echo "<div style='color:red;'>Error de conexión a la base de datos. Por favor, intente más tarde.</div>";
    }
    exit();
}

// Función helper para verificar la conexión
function testDatabaseConnection($conn) {
    try {
        $conn->query('SELECT 1');
        return true;
    } catch (PDOException $e) {
        error_log("Error en test de conexión: " . $e->getMessage());
        return false;
    }
}
?>