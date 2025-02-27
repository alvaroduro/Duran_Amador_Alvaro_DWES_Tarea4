/** Lógica para realizar el formulario una vez actualizado los campos */
document
  .getElementById("btnConfirmarGuardar")
  .addEventListener("click", function () {
    document.getElementById("miFormulario").submit(); // Envia los datos del formulario
  });
