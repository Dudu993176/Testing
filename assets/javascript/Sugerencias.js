(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("sugForm");
    const textarea = document.getElementById("sug-texto");
    const textoError = document.getElementById("sug-texto-error");
    const formMessage = document.getElementById("sug-form-message");
    const deleteBtn = document.getElementById("sugDeleteBtn");

    if (!form || !textarea) return;

    function limpiarMensajes() {
      textoError.textContent = "";
      textarea.classList.remove("is-invalid");
      formMessage.textContent = "";
      formMessage.classList.remove("sug-form-message--success", "sug-form-message--error");
    }

    // Limpia el texto de la sugerencia.
    deleteBtn.addEventListener("click", () => {
      textarea.value = "";
      limpiarMensajes();
      textarea.focus();
    });

    // Envío del formulario (validación dual: esto ya validó, el backend lo repite).
    form.addEventListener("submit", async (event) => {
      event.preventDefault(); // evita que recargue la página

      const texto = textarea.value.trim();

      limpiarMensajes();

      if (texto === "") {
        textoError.textContent = "Por favor, escribí una sugerencia antes de enviar.";
        textarea.classList.add("is-invalid");
        textarea.focus();
        return;
      }

      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      try {
        const respuesta = await FadeCodeAPI.postJson("api/sugerencias.php", { sugerencia: texto });

        if (respuesta.success) {
          formMessage.textContent = respuesta.message || "¡Tu sugerencia fue enviada correctamente!";
          formMessage.classList.add("sug-form-message--success");
          form.reset();
          return;
        }

        // Mensaje de error contextualizado: el backend puede rechazar por
        // texto vacío, muy largo, o por límite de sugerencias anónimas.
        const mensajeCampo = respuesta.errors && respuesta.errors.sugerencia;
        if (mensajeCampo) {
          textoError.textContent = mensajeCampo;
          textarea.classList.add("is-invalid");
        }
        formMessage.textContent = mensajeCampo || respuesta.message || "No se pudo enviar la sugerencia.";
        formMessage.classList.add("sug-form-message--error");
      } catch (err) {
        console.error("Error inesperado al enviar la sugerencia:", err);
        formMessage.textContent = "Ocurrió un error inesperado. Intentá nuevamente.";
        formMessage.classList.add("sug-form-message--error");
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  });
})();
