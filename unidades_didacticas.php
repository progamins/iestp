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

require_once 'db_connect.php';
function obtenerProgramasEstudio($conn, $programa_id = null)
{
    try {
        // Si se proporciona un programa_id específico
        if ($programa_id !== null) {
            $sql = "SELECT programa_id, nombre_programa, descripcion, estado 
                    FROM programas_estudio 
                    WHERE programa_id = ? AND estado = 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$programa_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Si no se proporciona programa_id, devolver todos los programas activos
        $sql = "SELECT programa_id, nombre_programa, descripcion, estado 
                FROM programas_estudio 
                WHERE estado = 1 
                ORDER BY nombre_programa ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return [];
        }
    } catch (PDOException $e) {
        // Registrar el error y establecer mensaje de error en la sesión
        error_log("Error en obtenerProgramasEstudio: " . $e->getMessage());
        $_SESSION['error_message'] = "Error al obtener los programas de estudio: " . $e->getMessage();
        return false;
    }
}

// Función para validar programa de estudio
function validarProgramaEstudio($conn, $programa_id)
{
    try {
        $sql = "SELECT nombre_programa 
                FROM programas_estudio 
                WHERE programa_id = ? AND estado = 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$programa_id]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error en validarProgramaEstudio: " . $e->getMessage());
        $_SESSION['error_message'] = "Error al validar el programa de estudio: " . $e->getMessage();
        return false;
    }
}
// Función para obtener todos los periodos académicos activos
function obtenerPeriodosAcademicos($conn)
{
    try {
        $stmt = $conn->query("SELECT periodo_id, nombre FROM periodos_academicos WHERE estado = 1");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}

// Función para obtener todos los semestres
function obtenerSemestres($conn)
{
    try {
        $stmt = $conn->query("SELECT semestre_id, nombre_semestre FROM tipo_semestre");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}

// Función para obtener unidades didácticas
function obtenerUnidadesDidacticas($conn)
{
    try {
        $sql = "SELECT ud.unidad_id, ud.nombre_unidad, 
                pe.nombre_programa, pa.nombre as periodo_nombre,
                ts.nombre_semestre
                FROM unidades_didacticas ud
                INNER JOIN programas_estudio pe ON ud.programa_id = pe.programa_id
                INNER JOIN periodos_academicos pa ON ud.periodo_id = pa.periodo_id
                INNER JOIN tipo_semestre ts ON ud.semestre_id = ts.semestre_id
                ORDER BY pa.nombre DESC, pe.nombre_programa, ts.nombre_semestre";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}

// Agregar una nueva función para obtener unidades por grupo
function obtenerUnidadesPorGrupo($conn, $programa_id = null, $periodo_id = null, $semestre_id = null)
{
    try {
        $sql = "SELECT unidad_id, nombre_unidad, programa_id, periodo_id, semestre_id
                FROM unidades_didacticas 
                WHERE 1=1";

        $params = [];

        if ($programa_id !== null) {
            $sql .= " AND programa_id = ?";
            $params[] = $programa_id;
        }

        if ($periodo_id !== null) {
            $sql .= " AND periodo_id = ?";
            $params[] = $periodo_id;
        }

        if ($semestre_id !== null) {
            $sql .= " AND semestre_id = ?";
            $params[] = $semestre_id;
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en obtenerUnidadesPorGrupo: " . $e->getMessage());
        return [];
    }
}
// Procesar el formulario de agregar/editar unidad didáctica
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'agregar_multiple') {
                // Preparar la sentencia de inserción
                $stmt = $conn->prepare("INSERT INTO unidades_didacticas (nombre_unidad, programa_id, periodo_id, semestre_id) VALUES (?, ?, ?, ?)");

                // Obtener valores comunes
                $programa_id = $_POST['programa_id'];
                $periodo_id = $_POST['periodo_id'];
                $semestre_id = $_POST['semestre_id'];

                // Array para almacenar las unidades
                $unidades = explode("\n", trim($_POST['nombres_unidades']));

                // Contador de unidades insertadas
                $insertadas = 0;

                // Iniciar transacción
                $conn->beginTransaction();

                // Insertar cada unidad
                foreach ($unidades as $nombre_unidad) {
                    $nombre_unidad = trim($nombre_unidad);
                    if (!empty($nombre_unidad)) {
                        $stmt->execute([
                            $nombre_unidad,
                            $programa_id,
                            $periodo_id,
                            $semestre_id
                        ]);
                        $insertadas++;
                    }
                }

                // Confirmar transacción
                $conn->commit();

                // Establecer mensaje de éxito en sesión
                session_start();
                $_SESSION['mensaje'] = "Se insertaron $insertadas unidades didácticas correctamente.";
                $_SESSION['tipo_mensaje'] = 'success';

                // Redirigir para prevenir reenvío de formulario
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    } catch (PDOException $e) {
        // Revertir transacción en caso de error
        $conn->rollBack();

        // Establecer mensaje de error en sesión
        session_start();
        $_SESSION['mensaje'] = "Error al insertar unidades: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = 'danger';

        // Redirigir
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
// Obtener datos para los selectores
$programas = obtenerProgramasEstudio($conn);
$periodos = obtenerPeriodosAcademicos($conn);
$semestres = obtenerSemestres($conn);
$unidades = obtenerUnidadesDidacticas($conn);
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
   <link rel="icon" href="images/fevicon.png" type="image/gif" />
   <!-- Scrollbar Custom CSS -->
   <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
   <!-- Tweaks for older IEs-->
   <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css"
      media="screen">
    <title>Gestión de Unidades Didácticas</title>
    <link rel="stylesheet" href="css/unidades.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

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
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12">

                <!-- Edición por Grupo de Unidades Didácticas -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h5 class="card-title mb-0">Edición por Grupo de Unidades Didácticas</h5>
                    </div>
                    <div class="card-body">
                        <form id="grupoUnidadesForm" class="row g-3">
                            <div class="col-md-4">
                                <label for="grupo_programa_id" class="form-label">Programa de Estudio</label>
                                <select class="form-control" id="grupo_programa_id">
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

                            <div class="col-md-4">
                                <label for="grupo_periodo_id" class="form-label">Período Académico</label>
                                <select class="form-select" id="grupo_periodo_id">
                                    <option value="">Seleccione un período</option>
                                    <?php foreach ($periodos as $periodo): ?>
                                        <option value="<?= $periodo['periodo_id'] ?>"><?= htmlspecialchars($periodo['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="grupo_semestre_id" class="form-label">Semestre</label>
                                <select class="form-select" id="grupo_semestre_id">
                                    <option value="">Seleccione un semestre</option>
                                    <?php foreach ($semestres as $semestre): ?>
                                        <option value="<?= $semestre['semestre_id'] ?>"><?= htmlspecialchars($semestre['nombre_semestre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="button" class="btn btn-primary" onclick="buscarUnidadesPorGrupo()">
                                    <i class="fas fa-search me-2"></i>Buscar Unidades
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Formulario para agregar nuevas unidades -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h5 class="card-title mb-0" id="formTitle">Agregar Nueva Unidad Didáctica</h5>
                    </div>
                    <div class="card-body">
                        <form id="unidadForm" method="POST" class="row g-3">
                            <input type="hidden" name="action" value="agregar_multiple">

                            <div class="col-md-6">
                                <label for="nombres_unidades" class="form-label">Nombres de Unidades (una por línea)</label>
                                <textarea class="form-control" id="nombres_unidades" name="nombres_unidades" rows="5" required
                                    placeholder="Ingrese los nombres de las unidades&#10;Un nombre por línea&#10;Ejemplo:&#10;Matemáticas I&#10;Física Aplicada&#10;Programación Básica"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="programa_id" class="form-label">Programa de Estudio</label>
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

                            <div class="col-md-6">
                                <label for="periodo_id" class="form-label">Período Académico</label>
                                <select class="form-select" id="periodo_id" name="periodo_id" required>
                                    <option value="">Seleccione un período</option>
                                    <?php foreach ($periodos as $periodo): ?>
                                        <option value="<?= $periodo['periodo_id'] ?>"><?= htmlspecialchars($periodo['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="semestre_id" class="form-label">Semestre</label>
                                <select class="form-select" id="semestre_id" name="semestre_id" required>
                                    <option value="">Seleccione un semestre</option>
                                    <?php foreach ($semestres as $semestre): ?>
                                        <option value="<?= $semestre['semestre_id'] ?>"><?= htmlspecialchars($semestre['nombre_semestre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Guardar Múltiples Unidades
                                </button>
                                <button type="button" class="btn btn-secondary ms-2" onclick="resetForm()">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- Modal para edición de grupo -->
<div class="modal fade" id="editarGrupoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Grupo de Unidades Didácticas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editarGrupoForm">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Nuevo Período Académico</label>
                            <select class="form-select" id="nuevo_periodo_grupo">
                                <option value="">Seleccione un período</option>
                                <?php foreach ($periodos as $periodo): ?>
                                    <option value="<?= $periodo['periodo_id'] ?>"><?= htmlspecialchars($periodo['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nuevo Semestre</label>
                            <select class="form-select" id="nuevo_semestre_grupo">
                                <option value="">Seleccione un semestre</option>
                                <?php foreach ($semestres as $semestre): ?>
                                    <option value="<?= $semestre['semestre_id'] ?>"><?= htmlspecialchars($semestre['nombre_semestre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="seleccionar_todos">
                            <label class="form-check-label" for="seleccionar_todos">
                                Seleccionar/Deseleccionar Todos
                            </label>
                        </div>
                        <div id="unidades_grupo_container" class="mt-2">
                            <!-- Aquí se insertarán las unidades dinámicamente -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarCambiosGrupo()">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

                <!-- Tabla -->
                <div class="card">
                    <div class="card-header py-3">
                        <h5 class="card-title mb-0">Unidades Didácticas Registradas</h5>
                    </div>
                    <div class="card-body">
                        <table id="unidadesTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Programa</th>
                                    <th>Período</th>
                                    <th>Semestre</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($unidades as $unidad): ?>
                                    <tr>
                                        <td><?= $unidad['unidad_id'] ?></td>
                                        <td><?= htmlspecialchars($unidad['nombre_unidad']) ?></td>
                                        <td><?= htmlspecialchars($unidad['nombre_programa']) ?></td>
                                        <td><?= htmlspecialchars($unidad['periodo_nombre']) ?></td>
                                        <td><?= htmlspecialchars($unidad['nombre_semestre']) ?></td>
                                        <td>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="eliminarUnidad(<?= $unidad['unidad_id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script>
        function buscarUnidadesPorGrupo() {
    const programa_id = $('#grupo_programa_id').val();
    const periodo_id = $('#grupo_periodo_id').val();
    const semestre_id = $('#grupo_semestre_id').val();

    // Validar que al menos un filtro esté seleccionado
    if (!programa_id && !periodo_id && !semestre_id) {
        alert('Seleccione al menos un filtro para buscar unidades');
        return;
    }

    $.ajax({
        url: 'buscar_unidades_grupo.php', // Necesitarás crear este archivo
        type: 'POST',
        data: {
            programa_id: programa_id,
            periodo_id: periodo_id,
            semestre_id: semestre_id
        },
        success: function(response) {
            const unidades = JSON.parse(response);
            
            if (unidades.length === 0) {
                alert('No se encontraron unidades con los criterios seleccionados');
                return;
            }

            // Limpiar contenedor de unidades
            $('#unidades_grupo_container').empty();

            // Generar checkboxes para cada unidad
            unidades.forEach(unidad => {
                const checkboxHtml = `
                    <div class="form-check">
                        <input class="form-check-input unidad-checkbox" 
                               type="checkbox" 
                               value="${unidad.unidad_id}" 
                               id="unidad_${unidad.unidad_id}">
                        <label class="form-check-label" for="unidad_${unidad.unidad_id}">
                            ${unidad.nombre_unidad}
                        </label>
                    </div>
                `;
                $('#unidades_grupo_container').append(checkboxHtml);
            });

            // Mostrar modal
            const editarGrupoModal = new bootstrap.Modal(document.getElementById('editarGrupoModal'));
            editarGrupoModal.show();
        }
    });
}

// Seleccionar/Deseleccionar todos
$('#seleccionar_todos').on('change', function() {
    $('.unidad-checkbox').prop('checked', $(this).prop('checked'));
});

function guardarCambiosGrupo() {
    const unidades_seleccionadas = $('.unidad-checkbox:checked').map(function() {
        return this.value;
    }).get();

    const nuevo_periodo = $('#nuevo_periodo_grupo').val();
    const nuevo_semestre = $('#nuevo_semestre_grupo').val();

    if (unidades_seleccionadas.length === 0) {
        alert('Seleccione al menos una unidad para modificar');
        return;
    }

    if (!nuevo_periodo && !nuevo_semestre) {
        alert('Seleccione al menos un campo para modificar');
        return;
    }

    $.ajax({
        url: 'actualizar_grupo_unidades.php', // Necesitarás crear este archivo
        type: 'POST',
        data: {
            unidades: unidades_seleccionadas,
            nuevo_periodo: nuevo_periodo,
            nuevo_semestre: nuevo_semestre
        },
        success: function(response) {
            const result = JSON.parse(response);
            if (result.success) {
                alert(`Se actualizaron ${result.actualizadas} unidades`);
                location.reload(); // Recargar página para mostrar cambios
            } else {
                alert('Error al actualizar las unidades: ' + result.message);
            }
        }
    });
}
        $(document).ready(function() {
            // Inicializar DataTable con botones de exportación
            $('#unidadesTable').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel me-2"></i>Excel',
                        className: 'btn btn-success',
                        exportOptions: {
                            columns: [1, 2, 3, 4] // Exportar todas las columnas excepto ID y Acciones
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf me-2"></i>PDF',
                        className: 'btn btn-danger',
                        exportOptions: {
                            columns: [1, 2, 3, 4]
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print me-2"></i>Imprimir',
                        className: 'btn btn-info',
                        exportOptions: {
                            columns: [1, 2, 3, 4]
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });
        });

        function editarUnidad(unidadId) {
            // Realizar petición AJAX para obtener los datos de la unidad
            $.ajax({
                url: 'obtener_unidad.php', // Necesitarás crear este archivo
                type: 'POST',
                data: {
                    unidad_id: unidadId
                },
                success: function(response) {
                    const unidad = JSON.parse(response);

                    // Llenar el formulario con los datos
                    $('#unidad_id').val(unidad.unidad_id);
                    $('#nombre_unidad').val(unidad.nombre_unidad);
                    $('#programa_id').val(unidad.programa_id);
                    $('#periodo_id').val(unidad.periodo_id);
                    $('#semestre_id').val(unidad.semestre_id);

                    // Cambiar el modo del formulario a edición
                    $('[name="action"]').val('editar');
                    $('#formTitle').text('Editar Unidad Didáctica');

                    // Hacer scroll al formulario
                    $('html, body').animate({
                        scrollTop: $("#unidadForm").offset().top - 100
                    }, 500);
                }
            });
        }

        function eliminarUnidad(unidadId) {
            if (confirm('¿Está seguro de que desea eliminar esta unidad didáctica?')) {
                $.ajax({
                    url: 'eliminar_unidad.php', // Necesitarás crear este archivo
                    type: 'POST',
                    data: {
                        unidad_id: unidadId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Recargar la tabla
                            location.reload();
                        } else {
                            alert('Error al eliminar la unidad didáctica');
                        }
                    }
                });
            }
        }

        function resetForm() {
            $('#unidadForm')[0].reset();
            $('[name="action"]').val('agregar');
            $('#formTitle').text('Agregar Nueva Unidad Didáctica');
            $('#unidad_id').val('');
        }
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