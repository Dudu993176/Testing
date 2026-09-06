(function () {
  "use strict";

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

  function clearErrors(form) {
    form.querySelectorAll(".auth-field__error").forEach((el) => {
      el.textContent = "";
    });
    const recaptchaError = document.getElementById("login-recaptcha-error");
    if (recaptchaError) recaptchaError.textContent = "";
    form.querySelectorAll(".auth-field__input").forEach((el) => {
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
    }
  }

  function showGeneralMessage(message, type) {
    const el = document.getElementById("auth-form-message");
    if (!el) return;
    el.textContent = message;
    el.classList.add(type === "success" ? "auth-form-message--success" : "auth-form-message--error");
  }

  function validateRecaptcha(errorElId) {
    if (typeof grecaptcha === "undefined" || !grecaptcha.getResponse()) {
      const errorEl = document.getElementById(errorElId);
      if (errorEl) errorEl.textContent = "Completá el reCAPTCHA antes de continuar.";
      throw new FormError("login-recaptcha", "Completá el reCAPTCHA antes de continuar.");
    }
    return grecaptcha.getResponse();
  }

  function validateAndCollect() {
    const cedula = getValue("login-cedula");
    if (!cedula) {
      throw new FormError("login-cedula", "Ingresá tu cédula.");
    }

    const password = getValue("login-password");
    if (!password) {
      throw new FormError("login-password", "Ingresá tu contraseña.");
    }

    const recaptchaToken = validateRecaptcha("login-recaptcha-error");

    return { cedula, password, recaptchaToken };
  }

  function initLoginForm() {
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
          console.error("Error inesperado en el formulario de login:", err);
          showGeneralMessage("Ocurrió un error inesperado. Intentá nuevamente.", "error");
        }
        return;
      }

      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;
      showGeneralMessage("Verificando datos…", "success");

      try {
        const respuesta = await FadeCodeAPI.postJson("api/auth/login.php", {
          cedula: datos.cedula,
          password: datos.password,
        });

        if (respuesta.success) {
          const usuario = respuesta.data.usuario || {};
          showGeneralMessage("¡Bienvenido/a, " + (usuario.nom_usuario || "") + "! Redirigiendo…", "success");
          window.setTimeout(() => {
            window.location.href = usuario.redireccion || "index.html";
          }, 800);
          return;
        }

        FadeCodeAPI.aplicarErroresContextuales(respuesta.errors, "login", showFieldError);
        showGeneralMessage(respuesta.message || "Revisá los datos marcados en rojo.", "error");
        if (typeof grecaptcha !== "undefined") grecaptcha.reset();
      } catch (err) {
        console.error("Error inesperado al iniciar sesión:", err);
        showGeneralMessage("Ocurrió un error inesperado. Intentá nuevamente.", "error");
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }

  document.addEventListener("DOMContentLoaded", initLoginForm);
})();
