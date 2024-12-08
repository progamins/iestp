
<?php
ob_start();
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'Dirección - Pagos') {
    header('Location: acceso_denegado.php');
    exit();
}

require 'db_connect.php';

$sql = "SELECT numero_orden, fecha, numero_recibo_banco, numero_recibo, nombres_apellidos, concepto, importe, carrera, observaciones FROM pagos";
$stmt = $conn->prepare($sql);
$stmt->execute();
$pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$conn = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>A-Estudiantes</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <link rel="icon" href="images/fevicon.png" type="image/gif" />
    
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1 0 auto;
            padding: 2rem 0;
        }
        
        .footer {
            flex-shrink: 0;
            margin-top: auto;
        }
        
        .table-container {
            margin: 2rem auto;
            padding: 0 1rem;
            max-width: 95%;
        }
        
        #pagosTable {
            width: 100% !important;
        }
        
        #pagosTable th, #pagosTable td {
            padding: 12px 8px;
            vertical-align: middle;
        }
        
        .modal-content {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            padding: 0.375rem 1rem;
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        
        .edit-btn {
            white-space: nowrap;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #0d6efd !important;
            color: white !important;
            border: 1px solid #0d6efd !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #0b5ed7 !important;
            color: white !important;
            border: 1px solid #0b5ed7 !important;
        }
    </style>
</head>

<body>
 
<!-- body -->
<body class="main-layout">
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
                    if ($_SESSION['role'] == 'Administrador') {
                        ?>
                        <li><a href="registro.php">R.Estudiantes</a></li>
                        <?php
                    }
                    if ($_SESSION['role'] == 'Administrador' || $_SESSION['role'] == 'Director') {
                        ?>
                        <li><a href="ver_registros.php">A.Estudiantes</a></li>
                        <li><a href="justificaciones.php">Justificaciones</a></li>
                        <li><a href="asistencias.php">Asistencias</a></li>
                        <li><a href="subir_horario.php">Horarios</a></li>
                        <?php
                    } elseif ($_SESSION['role'] == 'Profesor') {
                        ?>
                        <li><a href="asistencias.php">Asistencias</a></li>
                        <li><a href="subir_horario.php">Horarios</a></li>
                        <li><a href="insertar_notas.php">Insertar Notas</a></li>
                        <?php
                    } elseif ($_SESSION['role'] == 'Dirección - Pagos') {
                        ?>
                        <li><a href="registrar_pagos.php">Registrar Pagos</a></li>
                        <li><a href="administrar_pagos.php">Administrar Pagos</a></li>
                        <?php
                    } elseif ($_SESSION['role'] == 'Dirección - Notas') {
                        ?>
                        <li><a href="registrar_notas.php">Registrar Notas</a></li>
                        <li><a href="administrar_notas.php">Administrar Notas</a></li>
                        <?php
                    }
                    ?>
                    <li><a href="logout.php" id="logout-link">Cerrar sesión</a></li>
                </ul>
            </nav>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="table-container">
            <h2 class="mb-4">Pagos Realizados</h2>
            <div class="table-responsive">
                <table id="pagosTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>N° Orden</th>
                            <th>Fecha</th>
                            <th>N° Recibo Banco</th>
                            <th>N° Recibo</th>
                            <th>Nombres y Apellidos</th>
                            <th>Concepto</th>
                            <th>Importe</th>
                            <th>Carrera</th>
                            <th>Observaciones</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pagos) > 0): ?>
                            <?php foreach ($pagos as $pago): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pago['numero_orden']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($pago['fecha']))); ?></td>
                                    <td><?php echo htmlspecialchars($pago['numero_recibo_banco']); ?></td>
                                    <td><?php echo htmlspecialchars($pago['numero_recibo']); ?></td>
                                    <td><?php echo htmlspecialchars($pago['nombres_apellidos']); ?></td>
                                    <td><?php echo htmlspecialchars($pago['concepto']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($pago['importe'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($pago['carrera']); ?></td>
                                    <td><?php echo htmlspecialchars($pago['observaciones']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm edit-btn" 
                                                data-numero-orden="<?php echo htmlspecialchars($pago['numero_orden']); ?>"
                                                data-fecha="<?php echo htmlspecialchars($pago['fecha']); ?>"
                                                data-numero-recibo-banco="<?php echo htmlspecialchars($pago['numero_recibo_banco']); ?>"
                                                data-numero-recibo="<?php echo htmlspecialchars($pago['numero_recibo']); ?>"
                                                data-nombres-apellidos="<?php echo htmlspecialchars($pago['nombres_apellidos']); ?>"
                                                data-concepto="<?php echo htmlspecialchars($pago['concepto']); ?>"
                                                data-importe="<?php echo htmlspecialchars($pago['importe']); ?>"
                                                data-carrera="<?php echo htmlspecialchars($pago['carrera']); ?>"
                                                data-observaciones="<?php echo htmlspecialchars($pago['observaciones']); ?>">
                                            <i class="fa fa-edit"></i> Editar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Editar Pago</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST" action="actualizar_pago.php">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="hidden" id="edit_numero_orden" name="numero_orden">
                                
                                <div class="mb-3">
                                    <label for="edit_fecha" class="form-label">Fecha</label>
                                    <input type="date" class="form-control" id="edit_fecha" name="fecha" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_numero_recibo_banco" class="form-label">N° Recibo Banco</label>
                                    <input type="text" class="form-control" id="edit_numero_recibo_banco" name="numero_recibo_banco" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_numero_recibo" class="form-label">N° Recibo</label>
                                    <input type="text" class="form-control" id="edit_numero_recibo" name="numero_recibo" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_nombres_apellidos" class="form-label">Nombres y Apellidos</label>
                                    <input type="text" class="form-control" id="edit_nombres_apellidos" name="nombres_apellidos" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_concepto" class="form-label">Concepto</label>
                                    <input type="text" class="form-control" id="edit_concepto" name="concepto" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_importe" class="form-label">Importe</label>
                                    <input type="number" step="0.01" class="form-control" id="edit_importe" name="importe" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_carrera" class="form-label">Carrera</label>
                                    <input type="text" class="form-control" id="edit_carrera" name="carrera" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="edit_observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="edit_observaciones" name="observaciones" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
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
                            <li><a href="#"><i class="fa fa-map-marker" aria-hidden="true"></i></a> Km 06 - Carretera Sullana Tambogrande</li>
                            <li><a href="#"><i class="fa fa-envelope" aria-hidden="true"></i></a>mesadepartes@iestpsullana.edu.pe</li>
                            <li><a href="#"><i class="fa fa-volume-control-phone" aria-hidden="true"></i></a>+51 073 458 018</li>
                        </ul>
                        <ul class="social_icon">
                            <li><a href="https://www.facebook.com/iestsullanaoficial"><i class="fa fa-facebook-f" aria-hidden="true"></i></a></li>
                            <li><a href="https://www.instagram.com/iestpsullana/"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>
                            <li><a href="https://twitter.com/iestsullana"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                            <li><a href="#"><i class="fa fa-youtube" aria-hidden="true"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>


    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#pagosTable').DataTable({
                "language": {
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "zeroRecords": "No se encontraron registros",
                    "info": "Mostrando página _PAGE_ de _PAGES_",
                    "infoEmpty": "No hay registros disponibles",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                    "search": "Buscar:",
                    "paginate": {
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },
                "responsive": true,
                "pageLength": 10,
                "order": [[0, "desc"]]
            });

            // Manejador para el botón de editar
            $('.edit-btn').click(function() {
                var numeroOrden = $(this).data('numero-orden');
                var fecha = $(this).data('fecha');
                var numeroReciboBanco = $(this).data('numero-recibo-banco');
                var numeroRecibo = $(this).data('numero-recibo');
                var nombresApellidos = $(this).data('nombres-apellidos');
                var concepto = $(this).data('concepto');
                var importe = $(this).data('importe');
                var carrera = $(this).data('carrera');
                var observaciones = $(this).data('observaciones');
                
                var formatoFecha = new Date(fecha).toISOString().split('T')[0];
                
                $('#edit_numero_orden').val(numeroOrden);
                $('#edit_fecha').val(formatoFecha);
                $('#edit_numero_recibo_banco').val(numeroReciboBanco);
                $('#edit_numero_recibo').val(numeroRecibo);
                $('#edit_nombres_apellidos').val(nombresApellidos);
                $('#edit_concepto').val(concepto);
                $('#edit_importe').val(importe);
                $('#edit_carrera').val(carrera);
                $('#edit_observaciones').val(observaciones);
                
                $('#editModal').modal('show');
            });
        });
    </script>
</body>
</html>