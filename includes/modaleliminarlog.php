<!-- MODAL DE CONFIRMACIÓN (Guardado en includes/modalconfirmareliminar.php) -->
<div class="modal fade" id="confirmarEliminarModal" tabindex="-1" aria-labelledby="confirmarEliminarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmarEliminarLabel">¿Confirmar eliminación registro Logs?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p id="mensajeConfirmacion"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a id="btnConfirmarEliminar" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
</div>