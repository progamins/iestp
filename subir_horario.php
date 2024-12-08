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

include 'db_connect.php'; // Asegúrate de que este archivo tenga la configuración correcta para SQL Server

// Consultar los horarios
try {
   $sql = "SELECT h.horario_id, p.nombre_programa, h.archivo, h.fecha_creacion 
            FROM horarios h
            JOIN programas_estudio p ON h.programa_id = p.programa_id";
   $stmt = $conn->prepare($sql);
   $stmt->execute();
   $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
   echo "Error en la consulta: " . $e->getMessage();
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
   <title>Gestion de horarios</title>
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
   <!-- fevicon -->
   <link rel="icon" href="images/fevicon.png" type="image/gif" />
   <!-- Scrollbar Custom CSS -->
   <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css"
      media="screen">
   <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]-->
</head>
<link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body>

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
                           <li><a href="#"><i class="fa fa-phone" aria-hidden="true"></i> Contactanos : +51 073 458
                                 018</a>
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
      <div class="container mt-5">
         <h2>Gestión de Horarios</h2>
         <!-- Botón para abrir el modal -->
         <button type="button" class="btn btn-info mb-3" data-bs-toggle="modal"
            data-bs-target="#modalRegistroHorario">Registrar Horario</button>

         <!-- Modal para registrar horario -->
         <div class="modal fade" id="modalRegistroHorario" tabindex="-1" aria-labelledby="modalRegistroHorarioLabel"
            aria-hidden="true">
            <div class="modal-dialog">
               <div class="modal-content">
                  <div class="modal-header">
                     <h5 class="modal-title" id="modalRegistroHorarioLabel">Registrar Horario</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                     <form action="guardar_horario.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                           <label for="programa_id" class="form-label">Nombre del Programa de Estudio</label>
                           <select class="form-control" id="programa_id" name="programa_id" required>
                              <option value="">Seleccione un programa</option>
                              <?php
                              $sql = "SELECT programa_id, nombre_programa FROM programas_estudio";
                              $stmt = $conn->prepare($sql);
                              $stmt->execute();
                              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                 echo "<option value='" . $row['programa_id'] . "'>" . $row['nombre_programa'] . "</option>";
                              }
                              ?>
                           </select>
                        </div>
                        <div class="mb-3">
                           <label for="archivo" class="form-label">Subir Archivo (solo .jpg, .jpeg, .pdf)</label>
                           <input type="file" class="form-control" id="archivo" name="archivo" required>
                        </div>
                        <button type="submit" name="guardar_horario" class="btn btn-primary">Guardar Horario</button>
                     </form>

                  </div>
               </div>
            </div>
         </div>

         <h3 class="mt-5">Horarios Subidos</h3>
         <table id="tablaHorarios" class="table table-striped">
            <thead>
               <tr>
                  <!-- Eliminamos la columna de ID -->
                  <th>Programa de Estudio</th>
                  <th>Archivo</th>
                  <th>Fecha de Creación</th>
                  <th>Acciones</th>
               </tr>
            </thead>
            <tbody>
               <?php
               foreach ($horarios as $row) {
                  echo "<tr>";
                  // Eliminamos la fila de horario_id
                  echo "<td>" . $row['nombre_programa'] . "</td>";
                  echo "<td>" . basename($row['archivo']) . "</td>";
                  echo "<td>" . $row['fecha_creacion'] . "</td>";
                  echo "<td>";
                  if (strtolower(pathinfo($row['archivo'], PATHINFO_EXTENSION)) == 'pdf') {
                     echo "<a href='" . $row['archivo'] . "' class='btn btn-sm btn-primary' download>Descargar PDF</a> ";
                     echo "<a href='" . $row['archivo'] . "' class='btn btn-sm btn-secondary' target='_blank'>Vista Previa</a> ";
                  } else {
                     echo "<span>No disponible</span> ";
                  }
                  echo "<a href='eliminar_horario.php?id=" . $row['horario_id'] . "' class='btn btn-sm btn-danger'>Eliminar</a>";
                  echo "</td>";
                  echo "</tr>";
               }
               ?>
            </tbody>

         </table>
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
                        <li><a href="#"><i class="fa fa-map-marker" aria-hidden="true"></i></a> Km 06 - Carretera
                           Sullana Tambogrande</li>
                        <li><a href="#"><i class="fa fa-envelope"
                                 aria-hidden="true"></i></a>mesadepartes@iestpsullana.edu.pe</li>
                        <li><a href="#"><i class="fa fa-volume-control-phone" aria-hidden="true"></i></a>+51 073 458 018
                        </li>
                     </ul>
                     <ul class="social_icon">
                     <li><a href="https://www.facebook.com/iestsullanaoficial"><i class="fa fa-facebook"></i></a></li>
                     </li>
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
<!-- Eliminar el script anidado y organizar todos los scripts al final del body -->
<script src="js/jquery.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/jquery-3.0.0.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
<script src="js/custom.js"></script>
<script src="js/scripts.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('#tablaHorarios').DataTable();
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