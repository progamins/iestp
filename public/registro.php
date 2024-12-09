<?php
ob_start();

// Inicia la sesión.
session_start();

// Verifica si el usuario ha iniciado sesión, comprobando si las variables de sesión están definidas.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email']) || !isset($_SESSION['role'])) {
   // Si no están definidas, redirige a la página de inicio de sesión.
   header('Location: login.php');
   exit(); // Asegúrate de detener la ejecución después de la redirección.
}

// Verifica si el rol del usuario es 'Administrador'.
if ($_SESSION['role'] !== 'Administrador') {
   // Si el usuario no es administrador, redirige a una página de acceso denegado o a la página principal.
   header('Location: acceso_denegado.php'); // Crea esta página para mostrar el mensaje de error o redirige a donde prefieras.
   exit();
}

include '../app/config/db_connect.php';
include 'lib/phpqrcode/qrlib.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../vendor/autoload.php'; // Para PhpSpreadsheet y phpqrcode
use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   $target_dir = "uploads/";
   $target_file = $target_dir . basename($_FILES["file"]["name"]);
   $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

   // Verificar si el archivo es un Excel
   if ($fileType == 'xlsx') {
      move_uploaded_file($_FILES["file"]["tmp_name"], $target_file);

      // Procesar archivo Excel
      $spreadsheet = IOFactory::load($target_file);
      $sheet = $spreadsheet->getActiveSheet();
      $rows = $sheet->toArray();

      // Saltar la primera fila si es un encabezado
      array_shift($rows);

      foreach ($rows as $row) {
         // Asignar valores de la fila del Excel a las variables correspondientes
         $numero = $row[0];
         $nombre = $row[1];
         $dni = $row[2];
         $anio_ingreso = $row[3];  // Fecha en formato mm/dd/yyyy o similar
         $celular = $row[4];
         $email = $row[5];
         $email_corporativo = $row[6];
         $ie_procedencia = $row[7];
         $direccion = $row[8];
         $distrito = $row[9];
         $trabaja = $row[10];
         $dependiente = $row[11];
         $apoderado = $row[12];
         $celular_apoderado = $row[13];
         $programa = $row[14];

         // Validar que el valor de 'numero' sea un número entero
         if (!is_numeric($numero)) {
            continue;
         }

         // Convertir el valor de 'anio_ingreso' a formato 'YYYY-MM-DD'
         $anio_ingreso_formato = null;
         if ($anio_ingreso) {
            try {
               $anio_ingreso_formato = DateTime::createFromFormat('m/d/Y', $anio_ingreso)->format('Y-m-d');
            } catch (Exception $e) {
               // Si no se puede convertir la fecha, continuar a la siguiente fila
               continue;
            }
         }
         // Generar el usuario y la clave
         $nombre_partes = explode(' ', $nombre);
         $primer_apellido = $nombre_partes[count($nombre_partes) - 1];
         $primer_nombre = $nombre_partes[0];
         $usuario = strtolower($primer_apellido . '_' . $primer_nombre);

         $clave = $dni . $primer_apellido;
         // Insertar datos del estudiante
         $stmt = $conn->prepare("
    INSERT INTO estudiantes (numero, nombre, dni, anio_ingreso, celular, email, email_corporativo, ie_procedencia, direccion, distrito, trabaja, dependiente, apoderado, celular_apoderado, programa, usuario, clave) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
         $stmt->execute([
            $numero,
            $nombre,
            $dni,
            $anio_ingreso_formato,
            $celular,
            $email,
            $email_corporativo,
            $ie_procedencia,
            $direccion,
            $distrito,
            $trabaja,
            $dependiente,
            $apoderado,
            $celular_apoderado,
            $programa,
            $usuario,
            $clave
         ]);


         // Obtener el id del estudiante recién insertado
         $id_estudiante = $conn->lastInsertId();

         // Generar código QR
         $qr_data = "DNI: $dni, Nombre: $nombre, Programa: $programa";
         $qr_file = "qr_codes/$dni.png";
         QRcode::png($qr_data, $qr_file);

         // Insertar los datos del código QR en la tabla 'qr_codes'
         $stmt_qr = $conn->prepare("
                INSERT INTO qr_codes (dni_estudiante, qr_code_path) 
                VALUES (?, ?)
            ");
         $stmt_qr->execute([$dni, $qr_file]);

         // Insertar los datos del carnet en la tabla 'carnet'
         $fecha_emision = date('Y-m-d'); // Fecha actual
         $stmt_carnet = $conn->prepare("
                INSERT INTO carnet (id_estudiante, nombre_completo, programa_estudio, dni, fecha_emision) 
                VALUES (?, ?, ?, ?, ?)
            ");
         $stmt_carnet->execute([
            $id_estudiante,
            $nombre,
            $programa,
            $dni,
            $fecha_emision
         ]);

         // Obtener el id del carnet recién insertado
         $carnet_id = $conn->lastInsertId();

         // Actualizar la tabla 'estudiantes' con el 'carnet_id'
         $stmt_update = $conn->prepare("
                UPDATE estudiantes 
                SET carnet_id = ? 
                WHERE id = ?
            ");
         $stmt_update->execute([$carnet_id, $id_estudiante]);
      }

      echo "Datos insertados correctamente.";
   } else {
      echo "Solo se permiten archivos .xlsx.";
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <!-- basic -->
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <!-- mobile metas -->
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <meta name="viewport" content="initial-scale=1, maximum-scale=1">
   <!-- site metas -->
   <title>Registro</title>
   <meta name="keywords" content="">
   <meta name="description" content="">
   <meta name="author" content="">
   <!-- bootstrap css -->
   <link rel="stylesheet" href="css/bootstrap.min.css">
   <!-- style css -->
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/forms.css">

   <!-- Responsive-->
   <link rel="stylesheet" href="css/responsive.css">
   <link rel="stylesheet" href="css/owl.carousel.min.css">

   <!-- Scrollbar Custom CSS -->
   <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
   <!-- Tweaks for older IEs-->
   <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css"
      media="screen">
   <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
</head>
<!-- body -->

<body class="main-layout">
   <!-- loader  -->
   <div class="loader_bg">
      <div class="loader"><img src="images/loading.gif" alt="#" /></div>
   </div>
   <!-- end loader -->
   <!-- header -->
   <header>
      <!-- header inner -->
      <div class="header">

         <div class="header_midil">
            <div class="container">
               <div class="row d_flex">
                  <div class="col-md-4 col-sm-4 d_none">
                     <ul class="conta_icon">
                        <li><a href="#"><i class="fa fa-phone" aria-hidden="true"></i> Contactanos : +51 073 458 018</a>
                        </li>
                     </ul>
                  </div>
                  <div class="col-md-4 col-sm-4 ">
                     <a class="logo" href="#"><img src="images/logo.png" alt="#" width="150" height="150" /></a>
                  </div>
                  <div class="col-md-4 col-sm-4 d_none">
                     <ul class="conta_icon ">
                        <li><a href="mailto:mesadepartes@iestpsullana.edu.pe"><i class="fa fa-envelope"
                                 aria-hidden="true"></i> mesadepartes@iestpsullana.edu.pe</a></li>
                     </ul>
                  </div>
               </div>
            </div>
         </div>
         <nav>
            <div class="nav-wrapper">
               <div class="menu-toggle">
                  <span></span>
                  <span></span>
                  <span></span>
               </div>

               <div class="logo">
                  <h1>IESTP SULLANA</h1>
               </div>

               <ul class="menu">
                  <li><a href="index.php">Inicio</a></li>
                  <?php if ($_SESSION['role'] == 'Administrador') { ?>
                     <li class="dropdown">
                        <a href="#" class="dropdown-toggle">Estudiantes</a>
                        <ul class="submenu">
                           <li><a href="registro.php">Registro de Estudiantes</a></li>
                           <li><a href="ver_registros.php">Administrar Estudiantes</a></li>
                        </ul>
                     </li>
                  <?php }

                  if ($_SESSION['role'] == 'Administrador' || $_SESSION['role'] == 'Director') { ?>
                     <li class="dropdown">
                        <a href="#" class="dropdown-toggle">Gestión Académica</a>
                        <ul class="submenu">
                           <li><a href="justificaciones.php">Justificaciones</a></li>
                           <li><a href="asistencias.php">Asistencias</a></li>
                           <li><a href="subir_horario.php">Horarios</a></li>
                        </ul>
                     </li>
                     <li class="dropdown">
                        <a href="#" class="dropdown-toggle">Configuración</a>
                        <ul class="submenu">
                           <li><a href="periodos_academicos.php">Periodos Académicos</a></li>
                           <li><a href="unidades_didacticas.php">Unidades Didácticas</a></li>
                           <li><a href="gestionar_usuarios.php">Gestionar Usuarios</a></li>
                        </ul>
                     </li>
                  <?php } elseif ($_SESSION['role'] == 'Profesor') { ?>
                     <li class="dropdown">
                        <a href="#" class="dropdown-toggle">Gestión de Clase</a>
                        <ul class="submenu">
                           <?php if ($_SESSION['email'] == 'Profesor_DPW1@gmail.com') { ?>
                              <li><a href="asistencias.php?profesor=1">Asistencias DPW1</a></li>
                              <li><a href="justificaciones.php?profesor=1">Justificaciones DPW1</a></li>
                           <?php } elseif ($_SESSION['email'] == 'Profesor_DPW2@gmail.com') { ?>
                              <li><a href="asistencias.php?profesor=2">Asistencias DPW2</a></li>
                              <li><a href="justificaciones.php?profesor=2">Justificaciones DPW2</a></li>
                           <?php } elseif ($_SESSION['email'] == 'Profesor_DPW3@gmail.com') { ?>
                              <li><a href="asistencias.php?profesor=3">Asistencias DPW3</a></li>
                              <li><a href="justificaciones.php?profesor=3">Justificaciones DPW3</a></li>
                           <?php } ?>
                           <li><a href="subir_horario.php">Horarios</a></li>
                           <li><a href="insertar_notas.php">Insertar Notas</a></li>
                        </ul>
                     </li>
                  <?php } elseif ($_SESSION['role'] == 'Dirección - Pagos') { ?>
                     <li class="dropdown">
                        <a href="#" class="dropdown-toggle">Gestión de Pagos</a>
                        <ul class="submenu">
                           <li><a href="registrar_pagos.php">Registrar Pagos</a></li>
                           <li><a href="administrar_pagos.php">Administrar Pagos</a></li>
                        </ul>
                     </li>
                  <?php } elseif ($_SESSION['role'] == 'Dirección - Notas') { ?>
                     <li class="dropdown">
                        <a href="#" class="dropdown-toggle">Gestión de Notas</a>
                        <ul class="submenu">
                           <li><a href="registrar_notas.php">Registrar Notas</a></li>
                           <li><a href="administrar_notas.php">Administrar Notas</a></li>
                        </ul>
                     </li>
                  <?php } ?>
                  <li><a href="logout.php" id="logout-link">Cerrar sesión</a></li>
               </ul>
            </div>
         </nav>

   </header>
   <br>
   <!-- banner -->
   <div class="container my-5">
      <h1 class="text-center">Cargar Archivo Excel</h1>
      <div class="row justify-content-center">
         <div class="col-md-6">
            <form method="post" enctype="multipart/form-data">
               <div class="form-group">
                  <label for="file">Seleccione el archivo Excel</label>
                  <input type="file" class="form-control-file" id="file" name="file" required>
               </div>
               <button type="submit" class="btn btn-primary btn-block">Subir</button>
            </form>
         </div>
      </div>
   </div>
   <div class="container text-center my-5">
      <a href="plantillas/plantilla_estudiantes.xlsx" download class="btn btn-success">Descargar Plantilla</a>
   </div>

   <!-- end contact  section -->
   <!--  footer -->
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
                     <li><a href="https://www.facebook.com/iestsullanaoficial"><i class="fa fa-facebook"></i></a></li>
                     <li><a href="https://www.youtube.com/@iestpsullana"><i class="fa fa-youtube"
                              aria-hidden="true"></i></a></li>
                  </ul>
               </div>
               <div class="col-lg-2 col-md-6 col-sm-6">
                  <h3>Menus</h3>
                  <ul class="link_icon">
                     <li class="active"><a href="index.html">Inicio</a></li>
                     <li><a href="about.html">R.Estudiantes</a></li>
                     <li><a href="ver_registros.php">A.Estudiantes</a></li>
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

   <!-- end footer -->
   <!-- Javascript files-->
   <script src="js/jquery.min.js"></script>
   <script src="js/popper.min.js"></script>
   <script src="js/bootstrap.bundle.min.js"></script>
   <script src="js/jquery-3.0.0.min.js"></script>
   <script src="js/owl.carousel.min.js"></script>
   <!-- sidebar -->
   <script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
   <script src="js/custom.js"></script>
   <script src="js/scripts.js"></script>
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

   <script>
      $(document).ready(function() {
         function generarCarnetID(programa) {
            var abreviatura = programa.substr(0, 2).toUpperCase();
            var codigo_unico = abreviatura + Math.floor(10000 + Math.random() * 90000);
            $('#carnet_id').val(codigo_unico); // Guardar el CarnetID en el campo oculto
         }

         $('#programa_estudio').on('change', function() {
            var programa = $(this).val();
            if (programa) {
               generarCarnetID(programa);
               $.ajax({
                  type: 'POST',
                  url: 'get_horario.php',
                  data: {
                     'programa_estudio': programa
                  },
                  success: function(response) {
                     $('#horario_id').html(response);
                  }
               });
            } else {
               $('#horario_id').html('<option value="">Seleccione un programa primero</option>');
            }
         });
      });
   </script>

   <script>
      document.addEventListener('DOMContentLoaded', function() {
         const menuToggle = document.querySelector('.menu-toggle');
         const navList = document.querySelector('nav ul');

         menuToggle.addEventListener('click', function() {
            navList.classList.toggle('active');
         });

         // Cerrar menú al hacer clic en un enlace (para móviles)
         const navLinks = document.querySelectorAll('nav ul li a');
         navLinks.forEach(link => {
            link.addEventListener('click', () => {
               if (window.innerWidth <= 991) {
                  navList.classList.remove('active');
               }
            });
         });
      });
   </script>
</body>

</html>