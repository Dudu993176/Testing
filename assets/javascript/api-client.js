/**
 * Cliente HTTP mínimo, compartido por todos los formularios del sitio.
 *
 * Centraliza el "Comunicación con el Servidor (Fetch/Async-Await)": cada
 * formulario sólo llama a FadeCodeAPI.postJson/postForm/... y recibe siempre
 * la misma forma de respuesta, la que ya arma backend/src/core/Response.php:
 *
 *   { success, message, data, errors }
 *
 * Se carga como <script src="assets/javascript/api-client.js"></script>
 * ANTES del script propio de cada página (Registrarse.js, Login-in.js, etc.),
 * que consume `window.FadeCodeAPI`.
 */
(function (global) {
  "use strict";

  async function pedir(url, opciones) {
    let respuesta;
    try {
      respuesta = await fetch(url, opciones);
    } catch (errorRed) {
      // Sin conexión, servidor caído, CORS, etc.
      return {
        ok: false,
        status: 0,
        success: false,
        message: "No se pudo conectar con el servidor. Revisá tu conexión e intentá nuevamente.",
        data: {},
        errors: {},
      };
    }

    let cuerpo;
    try {
      cuerpo = await respuesta.json();
    } catch (errorJson) {
      return {
        ok: false,
        status: respuesta.status,
        success: false,
        message: "El servidor respondió con un formato inesperado.",
        data: {},
        errors: {},
      };
    }

    return {
      ok: respuesta.ok,
      status: respuesta.status,
      success: Boolean(cuerpo.success),
      message: cuerpo.message || "",
      data: cuerpo.data || {},
      errors: cuerpo.errors || {},
    };
  }

  function postJson(url, datos) {
    return pedir(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "same-origin",
      body: JSON.stringify(datos),
    });
  }

  function putJson(url, datos) {
    return pedir(url, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      credentials: "same-origin",
      body: JSON.stringify(datos),
    });
  }

  function patchJson(url, datos) {
    return pedir(url, {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      credentials: "same-origin",
      body: JSON.stringify(datos),
    });
  }

  function postForm(url, formData) {
    // Sin cabecera Content-Type manual: el navegador arma el boundary del
    // multipart/form-data automáticamente (necesario para el archivo adjunto).
    return pedir(url, {
      method: "POST",
      credentials: "same-origin",
      body: formData,
    });
  }

  function getJson(url) {
    return pedir(url, { method: "GET", credentials: "same-origin" });
  }

  function deleteJson(url) {
    return pedir(url, { method: "DELETE", credentials: "same-origin" });
  }

  /**
   * Aplica un objeto de errores contextuales { nombre_campo: mensaje } del
   * backend a los `<p class="...-error">` de un formulario, reutilizando el
   * mismo patrón id="prefijo-campo" / id="prefijo-campo-error" que ya usan
   * los formularios del sitio.
   */
  function aplicarErroresContextuales(errores, prefijo, mostrarCampo) {
    const claves = Object.keys(errores || {});
    claves.forEach((campo) => {
      mostrarCampo(prefijo ? `${prefijo}-${campo}` : campo, errores[campo]);
    });
    return claves.length > 0;
  }

  global.FadeCodeAPI = {
    postJson,
    putJson,
    patchJson,
    postForm,
    getJson,
    deleteJson,
    aplicarErroresContextuales,
  };
})(window);
