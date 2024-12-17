<?php
require_once '../config/db_connect.php';
require_once '../../public/lib/phpqrcode/qrlib.php';

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función para manejar las rutas de los QR
function getQRPath($qr_code_path) {
    // Detectar si estamos en Railway o local
    $isRailway = strpos($_SERVER['HTTP_HOST'], 'railway.app') !== false;
    
    if ($isRailway) {
        // Convertir la ruta local a URL de Railway
        $baseUrl = 'https://iestp-production.up.railway.app';
        // Remover '/iestp/public' del path si existe
        $qr_path = str_replace('/iestp/public/', '', $qr_code_path);
        return $baseUrl . '/' . $qr_path;
    } else {
        // En local, mantener la ruta original
        return '/iestp/public/' . $qr_code_path;
    }
}

try {
    $sql = "
        SELECT 
            e.*,
            q.qr_code_path,
            p.nombre_programa,
            c.id as carnet_id
        FROM 
            estudiantes e
        LEFT JOIN qr_codes q ON e.dni = q.dni_estudiante
        LEFT JOIN programas_estudio p ON e.programa_id = p.programa_id
        LEFT JOIN carnet c ON e.id = c.id_estudiante
        ORDER BY e.nombre ASC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Manejar la ruta del QR
            $qr_image_path = !empty($row['qr_code_path']) 
                ? getQRPath($row['qr_code_path'])
                : 'img/placeholder.png';
            
            // Formatear la fecha
            $anio_ingreso = !empty($row['anio_ingreso']) 
                ? date('Y-m-d', strtotime($row['anio_ingreso']))
                : '';

            // Sanitizar datos
            $dni = htmlspecialchars($row['dni']);
            $nombre = htmlspecialchars($row['nombre']);
            $ie_procedencia = htmlspecialchars($row['ie_procedencia'] ?? '');
            $programa = htmlspecialchars($row['programa'] ?? $row['nombre_programa'] ?? '');
            $celular = htmlspecialchars($row['celular'] ?? '');
            $usuario = htmlspecialchars($row['usuario']);
            $clave = htmlspecialchars($row['clave']);
            
            echo "
            <tr>
                <td class='text-center'>{$dni}</td>
                <td>{$nombre}</td>
                <td>{$ie_procedencia}</td>
                <td>{$programa}</td>
                <td>{$anio_ingreso}</td>
                <td>{$celular}</td>
                <td>{$usuario}</td>
                <td class='password-container'>
                    <span class='password-field'>********</span>
                    <span class='real-password' style='display:none;'>{$clave}</span>
                    <button type='button' class='btn btn-link toggle-password p-0'>
                        <img src='images/dejar_de_ver_contraseña.png' alt='Toggle password' 
                             style='width:20px; height:20px;'/>
                    </button>
                </td>
                <td class='text-center'>
                    <div class='qr-container'>
                        <img src='{$qr_image_path}' alt='QR Code' 
                             class='img-fluid qr-code' 
                             onerror='this.src=\"img/placeholder.png\";this.onerror=null;'
                             data-bs-toggle='tooltip'
                             data-bs-placement='top'
                             title='QR de {$nombre}'/>
                    </div>
                </td>
                <td class='text-center'>
                    <button class='btn btn-warning btn-sm edit-student' 
                            data-bs-toggle='modal' 
                            data-bs-target='#editModal'
                            data-id='" . htmlspecialchars($row['id']) . "'
                            data-dni='{$dni}'
                            data-nombre='{$nombre}'
                            data-ieprocedencia='{$ie_procedencia}'
                            data-programa='{$programa}'
                            data-anioingreso='{$anio_ingreso}'
                            data-celular='{$celular}'
                            data-programa-id='" . htmlspecialchars($row['programa_id'] ?? '') . "'>
                        <i class='fas fa-edit'></i> Editar
                    </button>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='10' class='text-center'>No se encontraron estudiantes registrados</td></tr>";
    }
} catch (PDOException $e) {
    error_log("Error en la consulta: " . $e->getMessage());
    echo "<tr><td colspan='10' class='text-center text-danger'>
            Ocurrió un error al cargar los datos. Por favor, intente más tarde.
          </td></tr>";
}
?>

<script>
$(document).ready(function() {
    // Manejo del toggle de contraseña
    $(document).on('click', '.toggle-password', function() {
        const container = $(this).closest('.password-container');
        const passwordField = container.find('.password-field');
        const realPassword = container.find('.real-password');
        const eyeIcon = container.find('img');
        
        // Prevenir múltiples clics durante la animación
        if (container.data('animating')) return;
        container.data('animating', true);
        
        const isPasswordVisible = passwordField.is(':visible');
        
        // Configurar la animación
        const fadeOut = isPasswordVisible ? passwordField : realPassword;
        const fadeIn = isPasswordVisible ? realPassword : passwordField;
        
        fadeOut.fadeOut(200, function() {
            fadeIn.fadeIn(200, function() {
                container.data('animating', false);
            });
        });
        
        // Cambiar el ícono
        eyeIcon
            .toggleClass('rotate-eye', isPasswordVisible)
            .toggleClass('rotate-eye-reverse', !isPasswordVisible)
            .attr({
                'src': isPasswordVisible ? 'images/ver_contraseña.png' : 'images/dejar_de_ver_contraseña.png',
                'alt': isPasswordVisible ? 'Ocultar contraseña' : 'Ver contraseña'
            });
    });
    
    // Inicialización del modal de edición
    $('.edit-student').click(function() {
        const button = $(this);
        const modal = $('#editModal');
        
        // Llenar el modal con los datos del estudiante
        modal.find('[name="id"]').val(button.data('id'));
        modal.find('[name="dni"]').val(button.data('dni'));
        modal.find('[name="nombre"]').val(button.data('nombre'));
        modal.find('[name="ie_procedencia"]').val(button.data('ieprocedencia'));
        modal.find('[name="programa"]').val(button.data('programa-id'));
        modal.find('[name="anio_ingreso"]').val(button.data('anioingreso'));
        modal.find('[name="celular"]').val(button.data('celular'));
    });
});

// Añadir estilos CSS para las animaciones
const style = document.createElement('style');
style.textContent = `
    .rotate-eye {
        transform: rotate(180deg);
        transition: transform 0.2s ease;
    }
    .rotate-eye-reverse {
        transform: rotate(-180deg);
        transition: transform 0.2s ease;
    }
    .password-container {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
`;
document.head.appendChild(style);
</script>