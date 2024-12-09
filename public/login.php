<?php
// Configuración de error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include '../app/config/db_connect.php'; // Archivo de conexión a la base de datos
$usuarios_permitidos = [
    'Profesor_DPW1@gmail.com' => [
        'password' => 'DPWHENRRYcespedes/1',
        'role' => 'Profesor'
    ],
    'Profesor_DPW2@gmail.com' => [
        'password' => 'DPWLITANOsilupu/2',
        'role' => 'Profesor'
    ],
    'Profesor_DPW3@gmail.com' => [
        'password' => 'DPWYESSENIAfarfan/3',
        'role' => 'Profesor'
    ]
];

$response = []; // Array para devolver la respuesta al cliente en formato JSON
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verificar que los campos no estén vacíos
    if (!empty($email) && !empty($password)) {
        // Primero verificar si es uno de los profesores predefinidos
        if (isset($usuarios_permitidos[$email])) {
            if ($password === $usuarios_permitidos[$email]['password']) {
                // Iniciar sesión y almacenar los datos del profesor
                $_SESSION['user_id'] = md5($email); // Generamos un ID único basado en el email
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $usuarios_permitidos[$email]['role'];

                $response['success'] = true;
                $response['redirect'] = 'index.php';
            } else {
                $response['error'] = 'Contraseña incorrecta para el profesor.';
            }
        } else {
            // Si no es un profesor, verificar en la base de datos para otros roles
            include '../app/config/db_connect.php';
            
            $query = "SELECT u.user_id, u.email, u.password, r.role_name
                      FROM listado_usuarios u
                      JOIN roles r ON u.role_id = r.role_id
                      WHERE u.email = :email";

            try {
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && $password === $user['password']) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role_name'];

                    $response['success'] = true;
                    $response['redirect'] = 'index.php';
                } else {
                    $response['error'] = 'Credenciales incorrectas. Verifica tu email o contraseña.';
                }
            } catch (PDOException $e) {
                $response['error'] = 'Error en la consulta: ' . $e->getMessage();
            }
        }
    } else {
        $response['error'] = 'Por favor, completa todos los campos.';
    }

    echo json_encode($response);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - IESTP SULLANA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="login-wrapper">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 illustration">
                    <img src="images/logo_login.png" alt="Ilustración IESTP SULLANA" class="img-fluid">
                </div>
                <div class="col-md-6 login-container">
                    <h1>INICIAR SESIÓN</h1>
                    <br>
                    <div id="message"></div>
                    <form id="formulario">
                        <div class="mb-4">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Ingresar correo" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Ingresar contraseña" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
                    </form>
                    <div class="mt-4 text-center">
                        <p><a href="https://wa.me/933826949" class="custom-link">¿Olvidaste tu cuenta? <b>¡Contáctanos!</b></a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#formulario').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    type: 'POST',
                    url: 'login.php',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: "¡Bienvenido!",
                                text: "Inicio de sesión exitoso.",
                                icon: "success",
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = response.redirect;
                            });
                        } else if (response.error) {
                            Swal.fire({
                                title: "Error",
                                text: response.error,
                                icon: "error",
                                confirmButtonText: "Intentar de nuevo"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: "Error",
                            text: "Ha ocurrido un error. Intenta nuevamente.",
                            icon: "error",
                            confirmButtonText: "Cerrar"
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>