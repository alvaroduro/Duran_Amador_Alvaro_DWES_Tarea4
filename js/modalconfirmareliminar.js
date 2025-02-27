document.addEventListener("DOMContentLoaded", function () {
  var confirmarEliminarModal = document.getElementById(
    "confirmarEliminarModal"
  );

  confirmarEliminarModal.addEventListener("show.bs.modal", function (event) {
    var button = event.relatedTarget; // Botón que activó la modal
    var id = button.getAttribute("data-id"); // Obtener ID de la entrada
    var titulo = button.getAttribute("data-titulo"); // Obtener título

    // Mensaje dinámico en la modal
    var mensaje =
      "¿Estás seguro de que deseas eliminar la entrada '<b>" +
      titulo + "," + id
      "</b>'?";
    document.getElementById("mensajeConfirmacion").innerHTML = mensaje;

    // Modificar el enlace del botón "Eliminar"
    var btnConfirmarEliminar = document.getElementById("btnConfirmarEliminar");
    btnConfirmarEliminar.href = "index.php?accion=eliminarentrada&id=" + id;
  });
});
