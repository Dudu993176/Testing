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

  function isValidEmail(value) {
    // Valida el formato del correo.
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  /** Edad en años a partir de una fecha "YYYY-MM-DD" (según el reloj del navegador). */
  function calcularEdad(fechaNacimiento) {
    const nacimiento = new Date(fechaNacimiento + "T00:00:00");
    if (Number.isNaN(nacimiento.getTime())) return NaN;
    const hoy = new Date();
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const aunNoCumplio =
      hoy.getMonth() < nacimiento.getMonth() ||
      (hoy.getMonth() === nacimiento.getMonth() && hoy.getDate() < nacimiento.getDate());
    if (aunNoCumplio) edad--;
    return edad;
  }

  function clearErrors(form) {
    form.querySelectorAll(".auth-field__error").forEach((el) => {
      el.textContent = "";
    });
    const recaptchaError = document.getElementById("reg-recaptcha-error");
    if (recaptchaError) recaptchaError.textContent = "";
    form.querySelectorAll(".auth-field__input, .auth-terms").forEach((el) => {
      el.classList.remove("is-invalid");
    });
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
    } else if (fieldId === "reg-terminos") {
      const termsBlock = document.querySelector(".auth-terms");
      if (termsBlock) termsBlock.classList.add("is-invalid");
      document.getElementById("reg-terminos").focus();
    }
  }

  function showGeneralMessage(message, type) {
    const el = document.getElementById("auth-form-message");
    if (!el) return;
    el.textContent = message;
    el.classList.add(type === "success" ? "auth-form-message--success" : "auth-form-message--error");
  }

  function validateAndCollect() {
    const nombre = getValue("reg-nombre");
    if (!nombre) {
      throw new FormError("reg-nombre", "Ingresá tu nombre.");
    }

    const apellido = getValue("reg-apellido");
    if (!apellido) {
      throw new FormError("reg-apellido", "Ingresá tu apellido.");
    }

    const cedula = getValue("reg-cedula");
    if (!cedula) {
      throw new FormError("reg-cedula", "Ingresá tu cédula.");
    }

    const correo = getValue("reg-correo");
    if (!correo) {
      throw new FormError("reg-correo", "Ingresá tu correo electrónico.");
    }
    if (!isValidEmail(correo)) {
      throw new FormError("reg-correo", "El correo electrónico no es válido.");
    }

    const password = getValue("reg-password");
    if (!password) {
      throw new FormError("reg-password", "Creá una contraseña.");
    }
    if (password.length < 6) {
      throw new FormError("reg-password", "La contraseña debe tener al menos 6 caracteres.");
    }

    const confirmPassword = getValue("reg-confirm-password");
    if (!confirmPassword) {
      throw new FormError("reg-confirm-password", "Confirmá tu contraseña.");
    }
    if (confirmPassword !== password) {
      throw new FormError("reg-confirm-password", "Las contraseñas no coinciden.");
    }

    const fechaNacimiento = getValue("reg-fecha-nacimiento");
    if (!fechaNacimiento) {
      throw new FormError("reg-fecha-nacimiento", "Ingresá tu fecha de nacimiento.");
    }
    const edad = calcularEdad(fechaNacimiento);
    if (Number.isNaN(edad) || edad < 0 || edad > 120) {
      throw new FormError("reg-fecha-nacimiento", "Ingresá una fecha de nacimiento válida.");
    }

    const terminosEl = document.getElementById("reg-terminos");
    if (!terminosEl || !terminosEl.checked) {
      throw new FormError("reg-terminos", "Tenés que aceptar los Términos y Condiciones para continuar.");
    }

    if (typeof grecaptcha === "undefined" || !grecaptcha.getResponse()) {
      const recaptchaError = document.getElementById("reg-recaptcha-error");
      if (recaptchaError) recaptchaError.textContent = "Completá el reCAPTCHA antes de continuar.";
      throw new FormError("reg-recaptcha", "Completá el reCAPTCHA antes de continuar.");
    }

    return { nombre, apellido, cedula, correo, password, confirmar_password: confirmPassword, fecha_nacimiento: fechaNacimiento, terminos: true };
  }

  function initRegistroForm() {
    const form = document.querySelector(".auth-card");
    if (!form) return;

    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      clearErrors(form);

      let datos;
      try {
        datos = validateAndCollect();
      } catch (err) {
        if (err instanceof FormError) {
          showFieldError(err.fieldId, err.message);
          showGeneralMessage("Revisá los datos marcados en rojo.", "error");
        } else {
          console.error("Error inesperado en el formulario de registro:", err);
          showGeneralMessage("Ocurrió un error inesperado. Intentá nuevamente.", "error");
        }
        return;
      }

      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;
      showGeneralMessage("Enviando tu registro…", "success");

      try {
        // Comunicación asíncrona con el servidor (fetch + async/await).
        const respuesta = await FadeCodeAPI.postJson("api/auth/registro.php", {
          nombre: datos.nombre,
          apellido: datos.apellido,
          cedula: datos.cedula,
          correo: datos.correo,
          password: datos.password,
          confirmar_password: datos.confirmar_password,
          fecha_nacimiento: datos.fecha_nacimiento,
          terminos: datos.terminos,
        });

        if (respuesta.success) {
          showGeneralMessage("¡Registro completado correctamente! Redirigiendo…", "success");
          form.reset();
          if (typeof grecaptcha !== "undefined") grecaptcha.reset();
          window.setTimeout(() => {
            window.location.href = "index.html";
          }, 1200);
          return;
        }

        // Sistema de mensajes de error contextualizados: cada error del
        // backend (PHP) se pinta junto a su campo, igual que las validaciones
        // del propio navegador. Los nombres de campo del backend coinciden
        // con el "name" de cada input, salvo "confirmar_password" y
        // "general", que se traducen al id real del DOM.
        const CAMPO_A_ID = { confirmar_password: "confirm-password", general: "recaptcha" };
        FadeCodeAPI.aplicarErroresContextuales(respuesta.errors, "reg", (fieldId, mensaje) => {
          const campo = fieldId.replace(/^reg-/, "");
          showFieldError(`reg-${CAMPO_A_ID[campo] || campo}`, mensaje);
        });
        showGeneralMessage(respuesta.message || "Revisá los datos marcados en rojo.", "error");
      } catch (err) {
        console.error("Error inesperado al registrar:", err);
        showGeneralMessage("Ocurrió un error inesperado. Intentá nuevamente.", "error");
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }

  document.addEventListener("DOMContentLoaded", initRegistroForm);
})();
