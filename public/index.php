<?php
// Inicia la salida de búfer para evitar problemas con redirecciones.
ob_start();

// Inicia la sesión.
session_start();

// Verifica si el usuario ha iniciado sesión, comprobando si las variables de sesión están definidas.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email']) || !isset($_SESSION['role'])) {
   // Si no están definidas, redirige a la página de inicio de sesión.
   header('Location: login.php');
   exit(); // Asegúrate de detener la ejecución después de la redirección.
}

// Si el usuario ha iniciado sesión, puedes mostrar contenido de bienvenida u otros elementos del dashboard.
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
   <title>Asistencia</title>
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
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

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
                  <div class="col-md-4 col-sm-4">
                     <span class="logo"><img src="images/logo.png" alt="#" width="150" height="150" /></span>
                  </div>

                  <div class="col-md-4 col-sm-4 d_none">
                     <ul class="conta_icon">
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
   <!-- end header inner -->
   <!-- end header -->
   <!-- banner -->
   <section class="banner_main">
      <div class="container">
         <div class="row">
            <div class="col-md-7 col-lg-7">
               <div class="text-bg">
                  <h1>Bienvenido <br>al sistema de asistencias</h1>
                  <span>acceso solo para personal autorizado</span>
                  <p>sistema creado por Edwin Raul Rosas Albinez</p>
                  <a href="https://wa.me/51933826949">contactar</a>
               </div>
            </div>
            <div class="col-md-5 col-lg-5">
               <div class="ban_img">
                  <figure><img src="images/ba_ing.png" alt="#" /></figure>
               </div>
            </div>
         </div>
      </div>
   </section>
   <!-- end banner -->
   <!-- about section -->
   <div id="about" class="about">
      <div class="container-fluid">
         <div class="row">
            <div class="col-md-12 col-lg-7">
               <div class="about_box">
                  <div class="titlepage">
                     <h2><strong class="yellow">HOLA</strong><br> Señor administrador</h2>
                  </div>
                  <h3>Aqui se te mostrara un pequeño tutorial</h3>
                  <span>Queremos que sepas usar el sistema<br> listo dale play</span>
                  <p>Recuerda estas manejando informacion confidencial de los estudiantes.</p>
                  <span class="try">Terminos y condiciones</span>
                  <a class="read_morea" href="#">Clic aqui<i class="fa fa-angle-right" aria-hidden="true"></i></a>
               </div>
            </div>
            <div class="col-md-12 col-lg-5">
               <div class="about_img">

                  <div class="embed-responsive embed-responsive-16by9">
                     <iframe width="1903" height="782" src="https://www.youtube.com/embed/C0OXsyR2mMM"
                        title="Está canción es sinónimo de amor ♡" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- about section -->
      <!-- service section -->
      <div id="service" class="service">
         <div class="container">
            <div class="row">
               <div class="col-md-7">
                  <div class="titlepage">
                     <h2><strong class="yellow">Servicios</strong><br> acciones principales</h2>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-4 col-sm-6">
                  <div id="ho_color" class="service_box">
                     <img src="images/service_icon1.png" alt="#" />
                     <h3>Registro de Estudiantes</h3>
                     <p>
                        En este apartado se podrá registrar a los estudiantes mediantes dos opciones.
                        De manera manual o importando un Excel.
                     </p>
                  </div>
               </div>
               <div class="col-md-4 col-sm-6">
                  <div id="ho_color" class="service_box">
                     <img src="images/service_icon2.png" alt="#" />
                     <h3>Administrar Estudiantes</h3>
                     <p>
                        La principal función de este apartado es darte visibilidad como administrador sobre la
                        información de los estudiantes registrados y también tendrás la posibilidad de hacer cambios si
                        la situación lo amerita.
                     </p>
                  </div>
               </div>
               <div class="col-md-4 col-sm-6">
                  <div id="ho_color" class="service_box">
                     <img src="images/service_icon3.png" alt="#" />
                     <h3>Ver asistencias</h3>
                     <p>
                        En este apartado, el administrador tendrá plena visión sobre las asistencias en el transcurso
                        del.
                        Del semestre, teniendo la posibilidad de ver si es el alumno llegó temprano o tuvo tardanza.
                     </p>
                  </div>
               </div>
               <div class="col-md-4 col-sm-6">
                  <div id="ho_color" class="service_box">
                     <img src="images/service_icon4.png" alt="#" />
                     <h3>Ver justifiaciones</h3>
                     <p>
                        Aquí el administrador tendrá visión sobre las justificaciones enviadas por los alumnos.
                        Dependientemente de esto, envía su justificación. Obviamente, este apartado está fuertemente
                        relacionado con las asistencias.
                     </p>
                  </div>
               </div>
               <div class="col-md-4 col-sm-6">
                  <div id="ho_color" class="service_box">
                     <img src="images/service_icon5.png" alt="#" />
                     <h3>Reportes</h3>
                     <p>
                        En este apartado, el administrador podrá sacar los reportes de cada estudiante, semestre,
                        programa de estudio en PDF y Excel.
                     </p>
                  </div>
               </div>
               <div class="col-md-4 col-sm-6">
                  <div id="ho_color" class="service_box">
                     <img src="images/service_icon6.png" alt="#" />
                     <h3>Adicionales</h3>
                     <p>
                        Son opciones que tienes como administrador sobre la aplicacion.
                     </p>
                  </div>
               </div>
               <div class="col-md-12">
                  <a class="read_more" href="#">Leer mas</a>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- service section -->


   <!-- team  section -->
   <div id="team" class="team">
      <div class="container">
         <div class="row">
            <div class="col-md-12">
               <div class="titlepage">
                  <h2><strong class="yellow">Equipo de desarrollo</strong><br>integrantes grupo 6 diseño y programacion
                     web</h2>
               </div>
            </div>
         </div>
         <div class="row">
            <div class="col-md-12">
               <div id="team" class="carousel slide team_Carousel " data-ride="carousel">

                  <div class="carousel-inner">
                     <div class="carousel-item active">
                        <div class="container">
                           <div class="carousel-caption ">
                              <div class="row">
                                 <div class="col-md-4 col-sm-6">
                                    <div id="ho_bg" class="team_img">
                                       <img src="images/team1.png" alt="#" />
                                       <div class="ho_socal">
                                          <ul class="social_icont">
                                             <li> <a href="#"><i class="fa fa-facebook-f"></i></a></li>
                                             <li> <a href="#"><i class="fa fa-twitter"></i></a></li>
                                             <li> <a href="#"> <i class="fa fa-linkedin" aria-hidden="true"></i></a>
                                             </li>
                                             <li> <a href="#"><i class="fa fa-instagram"></i></a></li>
                                          </ul>
                                       </div>
                                    </div>
                                 </div>

                                 <div class="col-md-4 col-sm-6">
                                    <div id="ho_bg" class="team_img ">
                                       <img src="images/team3.png" alt="#" />
                                       <div class="ho_socal">
                                          <ul class="social_icont">
                                             <li> <a href="#"><i class="fa fa-facebook-f"></i></a></li>
                                             <li> <a href="#"><i class="fa fa-twitter"></i></a></li>
                                             <li> <a href="#"> <i class="fa fa-linkedin" aria-hidden="true"></i></a>
                                             </li>
                                             <li> <a href="#"><i class="fa fa-instagram"></i></a></li>
                                          </ul>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>


                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- end team  section -->
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
   <script src="js/script.js"></script>
   <script>
     document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const menu = document.querySelector('.menu');
    const dropdowns = document.querySelectorAll('.dropdown');

    menuToggle.addEventListener('click', function() {
        menu.classList.toggle('active');
    });

    // Para dispositivos móviles
    dropdowns.forEach(dropdown => {
        const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
        
        dropdownToggle.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                dropdown.classList.toggle('active');
            }
        });
    });
});
   </script>

</body>

</html>