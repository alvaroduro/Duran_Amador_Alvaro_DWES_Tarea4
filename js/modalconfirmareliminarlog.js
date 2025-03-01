document.addEventListener("DOMContentLoaded", function () {
  var confirmarEliminarModal = document.getElementById(
    "confirmarEliminarModal"
  );

  confirmarEliminarModal.addEventListener("show.bs.modal", function (event) {
    var button = event.relatedTarget; // Botón que activó la modal
    var id = button.getAttribute("data-id"); // Obtener ID de la entrada
    var operacion = button.getAttribute("data-op"); // Obtener operacion

    // Mensaje dinámico en la modal
    var mensaje =
      "¿Estás seguro de que deseas eliminar la entrada '<b>" +
      operacion +
      "," +
      id;
    ("</b>'?");
    document.getElementById("mensajeConfirmacion").innerHTML = mensaje;

    // Modificar el enlace del botón "Eliminar"
    var btnConfirmarEliminar = document.getElementById("btnConfirmarEliminar");
    btnConfirmarEliminar.href = "index.php?accion=eliminarentradalog&id=" + id;
  });
});
