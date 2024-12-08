<?php
ob_start();

// Inicia la sesión.
session_start();

// Verifica si el usuario ha iniciado sesión y tiene el rol adecuado.
if (!isset($_SESSION['user_id'], $_SESSION['email'], $_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'Dirección - Pagos') {
    header('Location: acceso_denegado.php');
    exit();
}

require 'vendor/autoload.php'; 
require 'db_connect.php'; 
use PhpOffice\PhpSpreadsheet\IOFactory; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file']['name'])) { 
    $fileTmpName = $_FILES['excel_file']['tmp_name']; 
    $uploadDirectory = 'uploads/';

    // Crear el directorio si no existe
    if (!is_dir($uploadDirectory)) { 
        mkdir($uploadDirectory, 0777, true); 
    }

    $filePath = $uploadDirectory . basename($_FILES['excel_file']['name']);
    if (move_uploaded_file($fileTmpName, $filePath)) { 
        try { 
            $spreadsheet = IOFactory::load($filePath); 
            $sheet = $spreadsheet->getActiveSheet(); 
            $rows = $sheet->toArray(null, true, true, true); // Usar letras de columna como claves

            $sql = "INSERT INTO pagos (numero_orden, fecha, numero_recibo_banco, numero_recibo, nombres_apellidos, concepto, importe, carrera, observaciones)  
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
            $stmt = $conn->prepare($sql); 

            $insertedCount = 0;
            $errorCount = 0;

            foreach ($rows as $key => $row) { 
                if ($key <= 4) continue; // Omitir filas de encabezado 

                // Asignar variables y verificar campos obligatorios
                $numero_orden = isset($row['A']) ? $row['A'] : null;
                $fecha = isset($row['B']) && !empty($row['B']) ? date('Y-m-d', strtotime($row['B'])) : null;
                $nombres_apellidos = isset($row['E']) ? $row['E'] : null;

                if (is_null($numero_orden) || is_null($fecha) || is_null($nombres_apellidos)) {
                    $errorCount++;
                    continue; // Saltar a la siguiente iteración si hay errores
                }

                // Ejecutar la inserción
                try {
                    $stmt->execute([
                        $numero_orden,
                        $fecha,
                        $row['C'] ?? null,
                        $row['D'] ?? null,
                        $nombres_apellidos,
                        $row['F'] ?? null,
                        isset($row['G']) ? floatval($row['G']) : 0.00,
                        $row['H'] ?? null,
                        $row['I'] ?? null
                    ]);
                    $insertedCount++;
                } catch (PDOException $e) {
                    $errorCount++;
                }
            }

            echo "Proceso completado. $insertedCount filas insertadas correctamente. $errorCount errores encontrados."; 
        } catch (Exception $e) { 
            echo "Error al procesar el archivo: " . htmlspecialchars($e->getMessage()); 
        } 
    } else { 
        echo "Error al subir el archivo."; 
    } 
}

$conn = null; 
?> 
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- basic -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- mobile metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- site metas -->
    <title>Asistencia</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="icon" href="images/fevicon.png" type="image/gif" />
    <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css" media="screen">
</head>

<body class="main-layout">
    <!-- loader -->
    <div class="loader_bg">
        <div class="loader"><img src="images/loading.gif" alt="#" /></div>
    </div>
    <!-- end loader -->

    <!-- header -->
    <header>
        <div class="header">
            <div class="header_midil">
                <div class="container">
                    <div class="row d_flex">
                        <div class="col-md-4 col-sm-4 d_none">
                            <ul class="conta_icon">
                                <li><a href="#"><i class="fa fa-phone" aria-hidden="true"></i> Contactanos : +51 073 458 018</a></li>
                            </ul>
                        </div>
                        <div class="col-md-4 col-sm-4 ">
                            <a class="logo" href="#"><img src="images/logo.png" alt="#" width="150" height="150" /></a>
                        </div>
                        <div class="col-md-4 col-sm-4 d_none">
                            <ul class="conta_icon ">
                                <li><a href="mailto:mesadepartes@iestpsullana.edu.pe"><i class="fa fa-envelope" aria-hidden="true"></i> mesadepartes@iestpsullana.edu.pe</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <nav>
                <div class="logo">
                    <h1>IESTP SULLANA</h1>
                </div>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <?php
                    // Mostrar apartados según el rol del usuario
                    if ($_SESSION['role'] == 'Administrador') {
                        echo '<li><a href="registro.php">R.Estudiantes</a></li>';
                    }

                    if (in_array($_SESSION['role'], ['Administrador', 'Director'])) {
                        echo '<li><a href="ver_registros.php">A.Estudiantes</a></li>
                              <li><a href="justificaciones.php">Justificaciones</a></li>
                              <li><a href="asistencias.php">Asistencias</a></li>
                              <li><a href="subir_horario.php">Horarios</a></li>';
                    } elseif ($_SESSION['role'] == 'Profesor') {
                        echo '<li><a href="asistencias.php">Asistencias</a></li>
                              <li><a href="subir_horario.php">Horarios</a></li>
                              <li><a href="insertar_notas.php">Insertar Notas</a></li>';
                    } elseif ($_SESSION['role'] == 'Dirección - Pagos') {
                        echo '<li><a href="registrar_pagos.php">Registrar Pagos</a></li>
                              <li><a href="administrar_pagos.php">Administrar Pagos</a></li>';
                    } elseif ($_SESSION['role'] == 'Dirección - Notas') {
                        echo '<li><a href="registrar_notas.php">Registrar Notas</a></li>
                              <li><a href="administrar_notas.php">Administrar Notas</a></li>';
                    }
                    ?>
                    <li><a href="logout.php" id="logout-link">Cerrar sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <form method="POST" enctype="multipart/form-data" class="container my-5">
        <div class="form-group">
            <label for="excel_file" class="form-label">Seleccionar archivo Excel</label>
            <input type="file" name="excel_file" id="excel_file" accept=".xlsx, .xls" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary mt-3">Subir y Registrar</button>
    </form>

    <div class="container text-center my-5">
        <a href="plantillas/plantilla_pagos.xlsx" class="btn btn-success">Descargar Plantilla</a>
    </div>
    <footer>
      <div class="footer">
         <div class="container">
            <div class="row justify-content-center text-center">
               <div class="col-md-12">
                  <a class="logo2" href="#"><img src="images/logo.png" alt="#" width="100" height="100" /></a>
               </div>
               <div class="col-lg-5 col-md-6 col-sm-6">
                  <h3>Contactos</h3>
                  <ul class="location_icon">
                     <li><a href="#"><i class="fa fa-map-marker" aria-hidden="true"></i></a> Km 06 - Carretera Sullana
                        Tambogrande</li>
                     <li><a href="#"><i class="fa fa-envelope"
                              aria-hidden="true"></i></a>mesadepartes@iestpsullana.edu.pe</li>
                     <li><a href="#"><i class="fa fa-volume-control-phone" aria-hidden="true"></i></a>+51 073 458 018
                     </li>
                  </ul>
                  <ul class="social_icon">
                     <li><a href="https://www.facebook.com/iestsullanaoficial"><i class="fa fa-facebook-f"></i></a></li>
                     <li><a href="https://www.youtube.com/@iestpsullana"><i class="fa fa-youtube"
                              aria-hidden="true"></i></a></li>
                  </ul>
               </div>
               <div class="col-lg-2 col-md-6 col-sm-6">
                  <h3>Menus</h3>
                  <ul class="link_icon">
                     <li class="active"><a href="index.html">Inicio</a></li>
                     <li><a href="about.html">R.Estudiantes</a></li>
                     <li><a href="service.html">A.Estudiantes</a></li>
                     <li><a href="team.html">Justificaciones</a></li>
                     <li><a href="client.html">Asistencias</a></li>
                  </ul>
               </div>
            </div>
         </div>
         <div class="copyright">
            <div class="container">
               <div class="row justify-content-center text-center">
                  <div class="col-md-12">
                     <p>Copyright © 2024 IDEX IESTP Sullana - Adaptación de Diseño: Alumnos: Edwin Raul Rosas A. -
                        Devora Tavara.</p>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </footer>
    <script src="js/jquery.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/custom.js"></script>
</body>
</html>
