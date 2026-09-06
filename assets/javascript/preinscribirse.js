(function () {
  "use strict";

  /** Error del campo validado. */
  function FormError(fieldId, message) {
    this.name = "FormError";
    this.fieldId = fieldId;
    this.message = message;
  }
  FormError.prototype = Object.create(Error.prototype);

  function getValue(id) {
    const el = document.getElementById(id);
    return el ? el.value.trim() : "";
  }

  function isChecked(id) {
    const el = document.getElementById(id);
    return Boolean(el && el.checked);
  }

  function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  function clearErrors(form) {
    form.querySelectorAll(".auth-field__error").forEach((el) => {
      el.textContent = "";
    });
    form.querySelectorAll(".auth-field__input").forEach((el) => {
      el.classList.remove("is-invalid");
    });
    form.querySelectorAll(".auth-terms").forEach((el) => el.classList.remove("is-invalid"));

    const generalMessage = document.getElementById("auth-form-message");
    if (generalMessage) {
      generalMessage.textContent = "";
      generalMessage.classList.remove("auth-form-message--success", "auth-form-message--error");
    }
  }

  function showFieldError(fieldId, message) {
    const errorEl = document.getElementById(fieldId + "-error");
    const inputEl = document.getElementById(fieldId);

    if (errorEl) errorEl.textContent = message;

    if (inputEl) {
      inputEl.classList.add("is-invalid");
      inputEl.focus();
    } else {
      // Checkboxes de "auth-terms" que no tienen su propio input con ese id exacto.
      const wrapper = document.getElementById(fieldId)?.closest(".auth-terms");
      if (wrapper) wrapper.classList.add("is-invalid");
    }
  }

  function showGeneralMessage(message, type) {
    const el = document.getElementById("auth-form-message");
    if (!el) return;
    el.textContent = message;
    el.classList.add(type === "success" ? "auth-form-message--success" : "auth-form-message--error");
  }

  /** Muestra/oculta el checkbox de autorización de un adulto según "Soy menor de edad". */
  function initToggleMenorDeEdad() {
    const checkboxMenor = document.getElementById("pre-menor");
    const wrapper = document.getElementById("pre-autorizacion-adulto-wrapper");
    if (!checkboxMenor || !wrapper) return;

    function sincronizar() {
      wrapper.hidden = !checkboxMenor.checked;
      if (!checkboxMenor.checked) {
        const checkboxAutorizacion = document.getElementById("pre-autorizacion-adulto");
        if (checkboxAutorizacion) checkboxAutorizacion.checked = false;
      }
    }
    checkboxMenor.addEventListener("change", sincronizar);
    sincronizar();
  }

  /* Valida los campos del lado del cliente (verificación dual: el backend repite todo esto). */
  function validateAndCollect() {
    const nombre = getValue("pre-nombre");
    if (!nombre) {
      throw new FormError("pre-nombre", "Ingresá tu nombre completo.");
    }

    const correo = getValue("pre-correo");
    if (!correo) {
      throw new FormError("pre-correo", "Ingresá tu correo electrónico.");
    }
    if (!isValidEmail(correo)) {
      throw new FormError("pre-correo", "El correo electrónico no es válido.");
    }

    const cedula = getValue("pre-cedula");
    if (!cedula) {
      throw new FormError("pre-cedula", "Ingresá tu cédula.");
    }

    const password = getValue("pre-password");
    if (!password) {
      throw new FormError("pre-password", "Creá una contraseña.");
    }

    const curso = getValue("pre-curso");
    if (!curso) {
      throw new FormError("pre-curso", "Seleccioná el curso al que te querés preinscribir.");
    }

    const paisCiudad = getValue("pre-pais-ciudad");
    if (!paisCiudad) {
      throw new FormError("pre-pais-ciudad", "Ingresá tu país y ciudad de residencia.");
    }

    const esMenor = isChecked("pre-menor");
    const autorizacionAdulto = isChecked("pre-autorizacion-adulto");
    if (esMenor && !autorizacionAdulto) {
      throw new FormError(
        "pre-autorizacion-adulto",
        "Si sos menor de edad, necesitás la autorización de un adulto responsable para continuar."
      );
    }

    if (!isChecked("pre-aviso-cupo")) {
      throw new FormError("pre-aviso-cupo", "Tenés que confirmar que entendiste que la preinscripción no garantiza un cupo.");
    }

    if (!isChecked("pre-terminos")) {
      throw new FormError("pre-terminos", "Tenés que aceptar las normas y la política de privacidad para continuar.");
    }

    // reCAPTCHA: requiere que el widget de Google esté cargado y resuelto.
    const recaptchaResponse = typeof grecaptcha !== "undefined" ? grecaptcha.getResponse() : "";
    if (!recaptchaResponse) {
      throw new FormError("pre-recaptcha", "Completá el reCAPTCHA para continuar.");
    }

    return { nombre, correo, cedula, password, curso, paisCiudad, esMenor, autorizacionAdulto };
  }

  function openModal() {
    const overlay = document.getElementById("preModalOverlay");
    if (!overlay) return;
    overlay.hidden = false;
    document.body.style.overflow = "hidden";
    const closeBtn = document.getElementById("preModalCloseBtn");
    if (closeBtn) closeBtn.focus();
  }

  function closeModal() {
    const overlay = document.getElementById("preModalOverlay");
    if (!overlay) return;
    overlay.hidden = true;
    document.body.style.overflow = "";
  }

  function initModalControls() {
    const overlay = document.getElementById("preModalOverlay");
    const closeBtn = document.getElementById("preModalCloseBtn");
    if (!overlay || !closeBtn) return;

    closeBtn.addEventListener("click", closeModal);

    overlay.addEventListener("click", (event) => {
      if (event.target === overlay) closeModal();
    });

    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && !overlay.hidden) closeModal();
    });
  }

  function initPreinscribirseForm() {
    const form = document.getElementById("preForm");
    if (!form) return;

    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      clearErrors(form);

      try {
        validateAndCollect();
      } catch (err) {
        if (err instanceof FormError) {
          showFieldError(err.fieldId, err.message);
          showGeneralMessage("Revisá los datos marcados en rojo.", "error");
        } else {
          console.error("Error inesperado en el formulario de preinscripción:", err);
          showGeneralMessage("Ocurrió un error inesperado. Intentá nuevamente.", "error");
        }
        return;
      }

      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;
      showGeneralMessage("Enviando tu preinscripción…", "success");

      try {
        // FormData: permite mandar el archivo "comprobante" junto con el resto de los campos.
        const formData = new FormData(form);
        // Los checkboxes sin marcar no viajan en FormData; normalizamos a "1"/"" explícito.
        ["aviso_cupo", "menor", "autorizacion_adulto", "consentimiento_imagen", "terminos"].forEach((campo) => {
          const checkbox = form.querySelector(`[name="${campo}"]`);
          if (checkbox) formData.set(campo, checkbox.checked ? "1" : "");
        });

        const respuesta = await FadeCodeAPI.postForm("api/formularios-interes.php", formData);

        if (respuesta.success) {
          form.reset();
          if (typeof grecaptcha !== "undefined") grecaptcha.reset();
          document.getElementById("pre-autorizacion-adulto-wrapper").hidden = true;
          openModal();
          return;
        }

        // Sistema de mensajes de error contextualizados. Los campos del
        // backend usan guion bajo (pais_ciudad, autorizacion_adulto); los ids
        // del formulario usan guion medio (pre-pais-ciudad): se traduce acá.
        FadeCodeAPI.aplicarErroresContextuales(respuesta.errors, "pre", (fieldId, mensaje) =>
          showFieldError(fieldId.replace(/_/g, "-"), mensaje)
        );
        showGeneralMessage(respuesta.message || "Revisá los datos marcados en rojo.", "error");
      } catch (err) {
        console.error("Error inesperado al enviar la preinscripción:", err);
        showGeneralMessage("Ocurrió un error inesperado. Intentá nuevamente.", "error");
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });

    form.addEventListener("reset", () => {
      if (typeof grecaptcha !== "undefined") grecaptcha.reset();
    });
  }

  document.addEventListener("DOMContentLoaded", () => {
    initPreinscribirseForm();
    initModalControls();
    initToggleMenorDeEdad();
  });
})();
