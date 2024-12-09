<!-- Modal de edición -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Editar Estudiante</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editForm">
          <input type="hidden" id="editId" name="id">
          <div class="mb-3">
            <label for="editDni" class="form-label">DNI</label>
            <input type="text" class="form-control" id="editDni" name="dni" readonly>
          </div>
          <div class="mb-3">
            <label for="editNombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="editNombre" name="nombre" required>
          </div>
          <div class="mb-3">
            <label for="editIeProcedencia" class="form-label">Institución de Procedencia</label>
            <input type="text" class="form-control" id="editIeProcedencia" name="ie_procedencia" required>
          </div>
          <div class="mb-3">
            <label for="editPrograma" class="form-label">Programa</label>
            <input type="text" class="form-control" id="editPrograma" name="programa" required>
          </div>
          <div class="mb-3">
            <label for="editAnioIngreso" class="form-label">Año de Ingreso</label>
            <input type="text" class="form-control" id="editAnioIngreso" name="anio_ingreso" required>
          </div>
          <div class="mb-3">
            <label for="editCelular" class="form-label">Celular</label>
            <input type="text" class="form-control" id="editCelular" name="celular" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="saveChanges">Guardar cambios</button>
      </div>
    </div>
  </div>
</div>