<?php
include 'db_connect.php';
include 'lib/phpqrcode/qrlib.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$sql = "SELECT e.*, q.qr_code_path 
        FROM estudiantes e
        LEFT JOIN qr_codes q ON e.dni = q.dni_estudiante";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $qr_image_path = isset($row['qr_code_path']) ? $row['qr_code_path'] : 'img/placeholder.png';

        echo "<tr>
                <td class='text-center'>{$row['dni']}</td>
                <td>{$row['nombre']}</td>
                <td>{$row['ie_procedencia']}</td>
                <td>{$row['programa']}</td>
                <td>{$row['anio_ingreso']}</td>
                <td>{$row['celular']}</td>
                <td>{$row['usuario']}</td>
                <td class='password-container'>
                    <span class='password-field'>********</span>
                    <span class='real-password' style='display:none;'>{$row['clave']}</span>
                    <img src='images/dejar_de_ver_contraseña.png' class='toggle-password' alt='Toggle password' style='cursor:pointer; width:20px; height:20px;'/>
                </td>
                <td class='text-center'><img src='{$qr_image_path}' alt='QR Code' style='width:100px; height:100px;'/></td>
                <td class='text-center'>
                    <button class='btn btn-warning btn-sm' data-bs-toggle='modal' data-bs-target='#editModal'
 data-id='{$row['id']}' data-dni='{$row['dni']}' data-nombre='{$row['nombre']}'
 data-ieprocedencia='{$row['ie_procedencia']}' data-programa='{$row['programa']}'
 data-anioingreso='{$row['anio_ingreso']}' data-celular='{$row['celular']}'>
    Editar
</button>
                </td>
            </tr>";
    }
} else {
    echo "<tr><td colspan='11' class='text-center'>No se encontraron registros</td></tr>";
}
?>

<script>
    $(document).ready(function () {
    $(document).on('click', '.toggle-password', function () {
        var passwordField = $(this).siblings('.password-field');
        var realPassword = $(this).siblings('.real-password');
        var eyeIcon = $(this);

        // Desactivar el botón durante la animación
        eyeIcon.prop('disabled', true);

        if (passwordField.is(":visible")) {
            // Ocultar contraseña
            passwordField.fadeOut(200, function () {
                realPassword.fadeIn(200, function() {
                    eyeIcon.prop('disabled', false);
                });
            });

            eyeIcon.addClass('rotate-eye');
            setTimeout(function() {
                eyeIcon.attr({
                    'src': 'images/ver_contraseña.png',
                    'alt': 'Ocultar contraseña'
                }).removeClass('rotate-eye');
            }, 150);
        } else {
            // Mostrar contraseña
            realPassword.fadeOut(200, function () {
                passwordField.fadeIn(200, function() {
                    eyeIcon.prop('disabled', false);
                });
            });

            eyeIcon.addClass('rotate-eye-reverse');
            setTimeout(function() {
                eyeIcon.attr({
                    'src': 'images/dejar_de_ver_contraseña.png',
                    'alt': 'Ver contraseña'
                }).removeClass('rotate-eye-reverse');
            }, 150);
        }
    });
});
</script>