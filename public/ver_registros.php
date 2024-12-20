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

include '../app/config/db_connect.php'; // Asegúrate de que este archivo realiza correctamente la conexión a la base de datos
include 'lib/phpqrcode/qrlib.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Consulta SQL para obtener todos los registros de la tabla `estudiantes`
$sql = "SELECT id, dni, nombre, ie_procedencia, programa, anio_ingreso, celular, usuario, clave FROM estudiantes";
$result = $conn->query($sql);

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
   <title>A-Estudiantes</title>
   <meta name="keywords" content="">
   <meta name="description" content="">
   <meta name="author" content="">
   <!-- bootstrap css -->
   <link rel="stylesheet" href="css/bootstrap.min.css">
   <!-- style css -->
   <link rel="stylesheet" href="css/style.css">

   <!-- Responsive-->
   <link rel="stylesheet" href="css/responsive.css">
   <link rel="stylesheet" href="css/owl.carousel.min.css">
   <!-- Scrollbar Custom CSS -->
   <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
   <!-- Tweaks for older IEs-->
   <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css"
   
      media="screen">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<?php include '../app/controllers/edit_modal.php'; ?>
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
   <div class="container-contact100">
      <div class="wrap-contact100">
         <span class="contact100-form-title">Registros de Estudiantes</span>

         <!-- Botones para generar reportes -->
         <div class="mb-3">
            <a href="../app/controllers/generar_reporte.php" class="btn btn-primary">Generar Reporte PDF</a>
            <a href="../app/controllers/reporte_excel.php" class="btn btn-success">Generar Reporte Excel</a>
            <!-- Cambié el nombre a 'reporte_excel.php' -->
         </div>

         <!-- Tabla responsiva -->
         <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped table-sm">
               <thead class="table-dark">
                  <tr class="text-center">
                     <th>DNI</th>
                     <th>Nombre</th>
                     <th>Institución de Procedencia</th>
                     <th>Programa de Estudio</th>
                     <th>Año de Ingreso</th>
                     <th>Celular</th>
                     <th>Usuario</th>
                     <th>Contraseña</th>
                     <th>QR</th>
                     <th>Acciones</th>
                  </tr>
               </thead>
               <tbody id="estudiantesTableBody">
                  <!-- Aquí se insertarán los datos con AJAX -->
               </tbody>
            </table>
         </div>

      </div>
   </div>
   <!-- end contact  section -->
    
   <!-- footer -->
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
   <script src="js/plugin.js"></script>
   <script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
   <script src="js/custom.js"></script>
   <script src="js/owl.carousel.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <script>
        // Función para confirmar eliminación
        function confirmDelete(id) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: "¡No podrás revertir esto!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, eliminarlo",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../app/Controllers/delete_record.php',
                        type: 'POST',
                        data: { id: id },
                        success: function(response) {
                            if(response === 'success') {
                                Swal.fire(
                                    '¡Eliminado!',
                                    'El registro ha sido eliminado.',
                                    'success'
                                );
                                loadTableData();
                            } else {
                                Swal.fire(
                                    'Error',
                                    'No se pudo eliminar el registro.',
                                    'error'
                                );
                            }
                        },
                        error: function() {
                            Swal.fire(
                                'Error',
                                'Hubo un problema con el servidor.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        // Función para cargar datos de la tabla
        function loadTableData() {
            $.ajax({
                url: '../app/Controllers/fetch_estudiantes.php',
                type: 'GET',
                beforeSend: function() {
                    $('#estudiantesTableBody').html('<tr><td colspan="10" class="text-center">Cargando...</td></tr>');
                },
                success: function(data) {
                    $('#estudiantesTableBody').html(data);
                    // Inicializar tooltips después de cargar los datos
                    $('[data-bs-toggle="tooltip"]').tooltip();
                },
                error: function() {
                    $('#estudiantesTableBody').html('<tr><td colspan="10" class="text-center text-danger">Error al cargar los datos.</td></tr>');
                }
            });
        }

        // Cargar programas de estudio
        function loadProgramas() {
            $.ajax({
                url: '../app/Controllers/get_programas.php',
                type: 'GET',
                success: function(data) {
                    $('#editPrograma').html(data);
                }
            });
        }

        $(document).ready(function() {
            // Cargar datos iniciales
            loadTableData();
            loadProgramas();

            // Recargar datos cada 30 segundos
            setInterval(loadTableData, 30000);

            // Manejar apertura del modal
            $('#editModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const modal = $(this);

                // Llenar el formulario
                modal.find('#editId').val(button.data('id'));
                modal.find('#editDni').val(button.data('dni'));
                modal.find('#editNombre').val(button.data('nombre'));
                modal.find('#editIeProcedencia').val(button.data('ieprocedencia'));
                modal.find('#editPrograma').val(button.data('programa-id'));
                modal.find('#editAnioIngreso').val(button.data('anioingreso'));
                modal.find('#editCelular').val(button.data('celular'));
            });

            // Manejar guardado de cambios
            $('#saveChanges').click(function() {
                const form = $('#editForm');
                if (!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return;
                }

                $.ajax({
                    url: '../app/Controllers/update_estudiante.php',
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if(response === 'success') {
                            $('#editModal').modal('hide');
                            Swal.fire({
                                title: '¡Éxito!',
                                text: 'Los datos del estudiante han sido actualizados.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadTableData();
                        } else {
                            Swal.fire('Error', 'Hubo un problema al actualizar los datos.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Hubo un problema al conectar con el servidor.', 'error');
                    }
                });
            });
        });

        // Manejo del menú responsive
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const navList = document.querySelector('nav ul');

            if (menuToggle && navList) {
                menuToggle.addEventListener('click', function() {
                    navList.classList.toggle('active');
                });

                document.querySelectorAll('nav ul li a').forEach(link => {
                    link.addEventListener('click', () => {
                        if (window.innerWidth <= 991) {
                            navList.classList.remove('active');
                        }
                    });
                });
            }
        });
    </script>

</body>

</html>