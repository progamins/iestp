<?php
ob_start();
session_start();

// Verificación de sesión y rol
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'Administrador') {
    header('Location: acceso_denegado.php');
    exit();
}

include 'db_connect.php'; // Asegúrate de que este archivo realiza correctamente la conexión a la base de datos


// Función para limpiar entradas
function limpiarEntrada($datos) {
    return htmlspecialchars(strip_tags(trim($datos)));
}

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'eliminar') {
    if (isset($_POST['justificacion_id'])) {
        $justificacion_id = limpiarEntrada($_POST['justificacion_id']);
        
        try {
            // Primero eliminamos las imágenes asociadas
            $sql_delete_images = "DELETE FROM Jimg WHERE JustificacionID = ?";
            $stmt = $conn->prepare($sql_delete_images);
            $stmt->execute([$justificacion_id]);
            
            // Luego eliminamos la justificación
            $sql_delete = "DELETE FROM justificaciones WHERE JustificacionID = ?";
            $stmt = $conn->prepare($sql_delete);
            $stmt->execute([$justificacion_id]);
            
            header('Location: ' . $_SERVER['PHP_SELF'] . '?mensaje=Justificación eliminada correctamente');
            exit();
        } catch (PDOException $e) {
            $error = "Error al eliminar la justificación: " . $e->getMessage();
        }
    }
}
// Procesar acciones de aceptar/rechazar
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && isset($_POST['justificacion_id'])) {
        $justificacion_id = limpiarEntrada($_POST['justificacion_id']);
        $estado = ($_POST['action'] === 'aceptar') ? 'Aceptada' : 'Rechazada';
        $motivo = limpiarEntrada($_POST['motivo_resolucion'] ?? '');
        
        try {
            $sql = "UPDATE justificaciones SET 
                    Estado = ?, 
                    MotivoResolucion = ?,
                    FechaRevision = GETDATE(),
                    RevisorID = ?
                    WHERE JustificacionID = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([$estado, $motivo, $_SESSION['user_id'], $justificacion_id]);
            
            header('Location: ' . $_SERVER['PHP_SELF'] . '?mensaje=Justificación actualizada correctamente');
            exit();
        } catch (PDOException $e) {
            $error = "Error al actualizar la justificación: " . $e->getMessage();
        }
    }
}

$where = "WHERE 1=1";
$params = array();

// Procesar filtros de búsqueda
if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET)) {
    if (!empty($_GET['dni'])) {
        $dni = limpiarEntrada($_GET['dni']);
        $where .= " AND j.dni_estudiante LIKE ?";
        $params[] = "%$dni%";
    }
    
    if (!empty($_GET['estado'])) {
        $estado = limpiarEntrada($_GET['estado']);
        $where .= " AND j.Estado = ?";
        $params[] = $estado;
    }
    
    if (!empty($_GET['fecha_inicio'])) {
        $fecha_inicio = limpiarEntrada($_GET['fecha_inicio']);
        $where .= " AND j.Fecha_Inicio >= ?";
        $params[] = $fecha_inicio;
    }
    
    if (!empty($_GET['fecha_fin'])) {
        $fecha_fin = limpiarEntrada($_GET['fecha_fin']);
        $where .= " AND j.Fecha_Fin <= ?";
        $params[] = $fecha_fin;
    }
}

// Consulta SQL base mejorada
$sql = "SELECT 
            j.JustificacionID,
            j.dni_estudiante,
            e.nombre AS nombre_estudiante,
            j.Fecha_Justificacion,
            tj.Nombre AS tipo_justificacion,
            j.MotivoEstudiante,
            j.Estado,
            j.Fecha_Inicio,
            j.Fecha_Fin,
            j.MotivoResolucion,
            j.FechaRevision,
            (SELECT COUNT(*) FROM Jimg WHERE JustificacionID = j.JustificacionID) as cantidad_imagenes
        FROM justificaciones j
        LEFT JOIN estudiantes e ON j.dni_estudiante = e.dni
        LEFT JOIN tipos_justificacion tj ON j.TipoJustificacionID = tj.TipoJustificacionID
        $where
        ORDER BY j.Fecha_Justificacion DESC";

?>

<!DOCTYPE html>
<html lang="es">
<head>
       <!-- basic -->
   <meta charset="utf-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <!-- mobile metas -->
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <meta name="viewport" content="initial-scale=1, maximum-scale=1">
   <!-- site metas -->
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
   <!-- fevicon -->
   <!-- Scrollbar Custom CSS -->
   <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
   <!-- Tweaks for older IEs-->
   <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css"
      media="screen">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Justificaciones</title>
    
    <!-- Add these after the existing CSS links -->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
     <!-- Bootstrap CSS -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <style>
        .gallery-img {
            max-width: 150px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .gallery-img:hover {
            transform: scale(1.1);
        }
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }
        .badge-accepted {
            background-color: #28a745;
            color: #fff;
        }
        .badge-rejected {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
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
    <div class="container mt-4">
        <h2>Búsqueda de Justificaciones</h2>
        
        <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['mensaje']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <!-- Formulario de búsqueda -->
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="dni" class="form-label">DNI Estudiante</label>
                    <input type="text" class="form-control" id="dni" name="dni" 
                           value="<?php echo isset($_GET['dni']) ? htmlspecialchars($_GET['dni']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="Pendiente" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="Aceptada" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Aceptada') ? 'selected' : ''; ?>>Aceptada</option>
                        <option value="Rechazada" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Rechazada') ? 'selected' : ''; ?>>Rechazada</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-secondary">Limpiar</a>
                </div>
            </div>
        </form>

        <!-- Tabla de resultados -->
        <div class="table-responsive">
        <table class="table table-striped" id="justificacionesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>DNI</th>
                        <th>Nombre</th>
                        <th>Fecha Justificación</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Período</th>
                        <th>Imágenes</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $stmt = $conn->prepare($sql);
                        $stmt->execute($params);
                        
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $badgeClass = '';
                            switch($row['Estado']) {
                                case 'Pendiente':
                                    $badgeClass = 'badge-pending';
                                    break;
                                case 'Aceptada':
                                    $badgeClass = 'badge-accepted';
                                    break;
                                case 'Rechazada':
                                    $badgeClass = 'badge-rejected';
                                    break;
                            }
                            
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['JustificacionID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['dni_estudiante']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nombre_estudiante']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Fecha_Justificacion']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['tipo_justificacion']) . "</td>";
                            echo "<td><span class='badge " . $badgeClass . "'>" . htmlspecialchars($row['Estado']) . "</span></td>";
                            echo "<td>" . htmlspecialchars($row['Fecha_Inicio']) . " al " . htmlspecialchars($row['Fecha_Fin']) . "</td>";
                            echo "<td>" . ($row['cantidad_imagenes'] > 0 ? "<span class='badge bg-info'>" . $row['cantidad_imagenes'] . " imágenes</span>" : "-") . "</td>";
                            echo "<td>
                                    <button type='button' class='btn btn-info btn-sm' 
                                            onclick='verDetalles(" . json_encode($row) . ")'><i class='bi bi-eye'></i> Ver</button>
                                            <button type='button' class='btn btn-danger btn-sm' 
                            onclick='confirmarEliminacion(" . $row['JustificacionID'] . ")'><i class='bi bi-trash'></i> Eliminar</button>
                                  </td>";
                            echo "</tr>";
                           
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='9' class='text-danger'>Error al obtener los datos: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>



    <!-- Modal de Detalles -->
    <div class="modal fade" id="detallesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles de la Justificación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Información del Estudiante</h6>
                            <p><strong>DNI:</strong> <span id="modal-dni"></span></p>
                            <p><strong>Nombre:</strong> <span id="modal-nombre"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Información de la Justificación</h6>
                            <p><strong>Tipo:</strong> <span id="modal-tipo"></span></p>
                            <p><strong>Período:</strong> <span id="modal-periodo"></span></p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Motivo del Estudiante</h6>
                            <p id="modal-motivo" class="border p-2 rounded bg-light"></p>
                        </div>
                    </div>
                    <div class="row mt-3" id="modal-imagenes">
                        <div class="col-12">
                            <h6>Documentos Adjuntos</h6>
                            <div id="gallery" class="d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                    <div id="seccion-resolucion" class="row mt-3">
                        <div class="col-12">
                            <h6>Resolución</h6>
                            <form id="form-resolucion" method="POST">
                                <input type="hidden" id="justificacion_id" name="justificacion_id">
                                <input type="hidden" id="action" name="action">
                                <div class="mb-3">
                                    <label for="motivo_resolucion" class="form-label">Motivo de la Resolución</label>
                                    <textarea class="form-control" id="motivo_resolucion" name="motivo_resolucion" rows="3" required></textarea>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success" onclick="resolverJustificacion('aceptar')">Aceptar</button>
                                    <button type="button" class="btn btn-danger" onclick="resolverJustificacion('rechazar')">Rechazar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Imagen -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Visualización de Imagen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Documento de justificación" style="max-width: 100%; height: auto;">
                </div>
            </div>
        </div>
    </div>
<!-- Agregar el formulario oculto para eliminación -->
<form id="form-eliminar" method="POST" style="display: none;">
    <input type="hidden" name="action" value="eliminar">
    <input type="hidden" name="justificacion_id" id="eliminar_justificacion_id">
</form>
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
<!-- Add these before your existing scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script>
        // Add this at the beginning of your script section
$(document).ready(function() {
    $('#justificacionesTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                }
            },
            {
                extend: 'csv',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                }
            },
            {
                extend: 'excel',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                }
            },
            {
                extend: 'pdf',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                }
            },
            {
                extend: 'print',
                exportOptions: {
                    columns: ':not(:first-child):not(:last-child)'
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        pageLength: 10,
        order: [[3, 'desc']], // Ordenar por fecha de justificación descendente
        columnDefs: [
            {
                targets: [-1], // Última columna (acciones)
                orderable: false,
                searchable: false
            }
        ]
    });
});
       // Variables globales para los modales
let detallesModal;
let imageModal;
let justificacionActual = null;

// Inicializar los modales cuando el documento esté listo
document.addEventListener('DOMContentLoaded', function() {
    detallesModal = new bootstrap.Modal(document.getElementById('detallesModal'));
    imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
});

// Función para confirmar eliminación
function confirmarEliminacion(justificacionId) {
    if (!justificacionId) {
        console.error('ID de justificación no válido');
        return;
    }

    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6'
    }).then((result) => {
        if (result.isConfirmed) {
            const formEliminar = document.getElementById('form-eliminar');
            const inputId = document.getElementById('eliminar_justificacion_id');
            if (formEliminar && inputId) {
                inputId.value = justificacionId;
                formEliminar.submit();
            } else {
                console.error('Elementos del formulario no encontrados');
            }
        }
    });
}

// Función para ver detalles de la justificación
async function verDetalles(justificacion) {
    try {
        if (!justificacion) {
            throw new Error('Datos de justificación no válidos');
        }

        justificacionActual = justificacion;
        
        // Actualizar información básica
        const elementos = {
            'modal-dni': justificacion.dni_estudiante,
            'modal-nombre': justificacion.nombre_estudiante,
            'modal-tipo': justificacion.tipo_justificacion,
            'modal-periodo': `${justificacion.Fecha_Inicio} al ${justificacion.Fecha_Fin}`,
            'modal-motivo': justificacion.MotivoEstudiante,
            'justificacion_id': justificacion.JustificacionID
        };

        // Actualizar cada elemento de forma segura
        for (const [id, valor] of Object.entries(elementos)) {
            const elemento = document.getElementById(id);
            if (elemento) {
                if (elemento.tagName === 'INPUT') {
                    elemento.value = valor || '';
                } else {
                    elemento.textContent = valor || '';
                }
            }
        }

        // Mostrar/ocultar sección de resolución según el estado
        const seccionResolucion = document.getElementById('seccion-resolucion');
        if (seccionResolucion) {
            seccionResolucion.style.display = justificacion.Estado === 'Pendiente' ? 'block' : 'none';
        }

        // Cargar imágenes
        await cargarImagenes(justificacion.JustificacionID);

        // Mostrar el modal
        if (detallesModal) {
            detallesModal.show();
        } else {
            throw new Error('Modal no inicializado');
        }

    } catch (error) {
        console.error('Error en verDetalles:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un problema al cargar los detalles de la justificación'
        });
    }
}

// Función separada para cargar imágenes
async function cargarImagenes(justificacionId) {
    const gallery = document.getElementById('gallery');
    if (!gallery) return;

    gallery.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';

    try {
        const response = await fetch(`get_images.php?justificacion_id=${justificacionId}`);
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const imagenes = await response.json();
        gallery.innerHTML = ''; // Limpiar el spinner

        if (imagenes && imagenes.length > 0) {
            imagenes.forEach(imagen => {
                if (!imagen.RutaArchivo) return;

                const imgContainer = document.createElement('div');
                imgContainer.className = 'position-relative m-2';
                
                const img = document.createElement('img');
                img.src = imagen.RutaArchivo;
                img.alt = 'Documento de justificación';
                img.className = 'gallery-img img-thumbnail';
                img.style.maxHeight = '150px';
                
                img.onerror = function() {
                    this.onerror = null;
                    this.src = 'assets/img/imagen-no-disponible.jpg';
                    console.error('Error al cargar la imagen:', imagen.RutaArchivo);
                };
                
                img.onclick = () => mostrarImagen(imagen.RutaArchivo);
                
                imgContainer.appendChild(img);
                gallery.appendChild(imgContainer);
            });
        } else {
            gallery.innerHTML = '<p class="text-muted">No hay documentos adjuntos</p>';
        }
    } catch (error) {
        console.error('Error al cargar las imágenes:', error);
        gallery.innerHTML = `<p class="text-danger">Error al cargar las imágenes: ${error.message}</p>`;
    }
}

// Función para mostrar imagen en modal
function mostrarImagen(rutaImagen) {
    if (!rutaImagen) {
        console.error('Ruta de imagen no válida');
        return;
    }

    const modalImage = document.getElementById('modalImage');
    if (modalImage && imageModal) {
        modalImage.onerror = function() {
            this.onerror = null;
            this.src = 'assets/img/imagen-no-disponible.jpg';
        };
        modalImage.src = rutaImagen;
        imageModal.show();
    }
}

// Función para resolver justificación
function resolverJustificacion(accion) {
    const motivo = document.getElementById('motivo_resolucion')?.value.trim();
    
    if (!motivo) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debe ingresar un motivo para la resolución'
        });
        return;
    }

    Swal.fire({
        title: '¿Está seguro?',
        text: `¿Desea ${accion === 'aceptar' ? 'aceptar' : 'rechazar'} esta justificación?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formResolucion = document.getElementById('form-resolucion');
            const actionInput = document.getElementById('action');
            
            if (formResolucion && actionInput) {
                actionInput.value = accion;
                formResolucion.submit();
            } else {
                console.error('Elementos del formulario no encontrados');
            }
        }
    });
}
    </script>
</body>
</html>