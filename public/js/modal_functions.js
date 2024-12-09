document.addEventListener('DOMContentLoaded', function() {
    var editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var dni = button.getAttribute('data-dni');
        var nombre = button.getAttribute('data-nombre');
        var apellido = button.getAttribute('data-apellido');
        var fechanacimiento = button.getAttribute('data-fechanacimiento');
        var direccion = button.getAttribute('data-direccion');
        var telefono = button.getAttribute('data-telefono');
        var email = button.getAttribute('data-email');
        var programa = button.getAttribute('data-programa');
        var horario = button.getAttribute('data-horario');

        var modalIdInput = editModal.querySelector('#edit-id');
        var modalDniInput = editModal.querySelector('#edit-dni');
        var modalNombreInput = editModal.querySelector('#edit-nombre');
        var modalApellidoInput = editModal.querySelector('#edit-apellido');
        var modalFechaNacimientoInput = editModal.querySelector('#edit-fechanacimiento');
        var modalDireccionInput = editModal.querySelector('#edit-direccion');
        var modalTelefonoInput = editModal.querySelector('#edit-telefono');
        var modalEmailInput = editModal.querySelector('#edit-email');
        var modalProgramaInput = editModal.querySelector('#edit-programa');
        var modalHorarioInput = editModal.querySelector('#edit-horario');

        modalIdInput.value = id;
        modalDniInput.value = dni;
        modalNombreInput.value = nombre;
        modalApellidoInput.value = apellido;
        modalFechaNacimientoInput.value = fechanacimiento;
        modalDireccionInput.value = direccion;
        modalTelefonoInput.value = telefono;
        modalEmailInput.value = email;
        modalProgramaInput.value = programa;
        modalHorarioInput.value = horario;
    });
});
