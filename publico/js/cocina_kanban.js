
document.addEventListener("DOMContentLoaded", () => {
  const contenedor = document.getElementById("contenedor-comandas");
  const btnRefrescar = document.getElementById("btnRefrescarPedidos");
  const botonesFiltro = document.querySelectorAll(".btn-filtro");

  const API = "../../controladores/ControladorCocina.php";

  let filtroActivo = "todos";
  let listaComandas = [];
  let actualizando = false;

  // Convierte los estados de MySQL a identificadores para los filtros.
  function normalizarEstado(estado) {
    const estados = {
      "Pendiente": "pendiente",
      "En preparación": "en_preparacion",
      "Listo": "listo"
    };

    return estados[estado] || estado;
  }

  // Formatea fecha y calcula minutos transcurridos.
  function formatearTiempo(fechaStr) {
    if (!fechaStr) return { minutos: 0, hora: "--:--" };

    // MySQL devuelve DATETIME como "YYYY-MM-DD HH:mm:ss".
    const fecha = new Date(fechaStr.replace(" ", "T"));

    if (Number.isNaN(fecha.getTime())) {
      return { minutos: 0, hora: "--:--" };
    }

    const ahora = new Date();
    const minutos = Math.max(
      0,
      Math.floor((ahora - fecha) / 60000)
    );

    const hora = fecha.toLocaleTimeString("es-MX", {
      hour: "2-digit",
      minute: "2-digit",
      hour12: true
    });

    return { minutos, hora };
  }

  // Muestra mensajes dentro del tablero.
  function mostrarMensaje(texto) {
    contenedor.replaceChildren();

    const mensaje = document.createElement("p");
    mensaje.style.gridColumn = "1 / -1";
    mensaje.style.textAlign = "center";
    mensaje.textContent = texto;

    contenedor.appendChild(mensaje);
  }

  // Actualiza los contadores.
  function actualizarContadores() {
    document.getElementById("cont-todos").textContent =
      listaComandas.length;

    document.getElementById("cont-pendientes").textContent =
      listaComandas.filter(
        c => normalizarEstado(c.estado) === "pendiente"
      ).length;

    document.getElementById("cont-preparacion").textContent =
      listaComandas.filter(
        c => normalizarEstado(c.estado) === "en_preparacion"
      ).length;
  }

  // Construye las tarjetas.
  function renderizar() {
    contenedor.replaceChildren();
    actualizarContadores();

    const filtradas = listaComandas.filter(c => {
      if (filtroActivo === "todos") return true;
      return normalizarEstado(c.estado) === filtroActivo;
    });

    if (filtradas.length === 0) {
      mostrarMensaje("No hay pedidos en esta sección.");
      return;
    }

    filtradas.forEach(c => {
      const estado = normalizarEstado(c.estado);
      const esPendiente = estado === "pendiente";
      const esPreparacion = estado === "en_preparacion";

      const { minutos, hora } = formatearTiempo(c.enviada_en);

      const tarjeta = document.createElement("div");
      tarjeta.className = `tarjeta-ticket ${estado}`;

      const encabezado = document.createElement("div");
      encabezado.className = "ticket-header";

      const izquierda = document.createElement("div");
      izquierda.className = "header-izq";

      const titulo = document.createElement("h3");
      titulo.className = "ticket-mesa";
      titulo.textContent = c.mesa
        ? `🪑 Mesa ${c.mesa}`
        : "🛍️ Para llevar";

      const folio = document.createElement("div");
      folio.className = "ticket-orden";
      folio.textContent = `Orden #${c.folio || c.id}`;

      const horaTexto = document.createElement("div");
      horaTexto.className = "ticket-hora";
      horaTexto.textContent = hora;

      izquierda.append(titulo, folio, horaTexto);

      const derecha = document.createElement("div");
      derecha.className = "header-der";

      const tipo = document.createElement("span");
      tipo.className = "badge-tipo";
      tipo.textContent = c.tipo_pedido === "para_llevar"
        ? "Para llevar"
        : "En mesa";

      const tiempo = document.createElement("span");
      tiempo.className = "ticket-tiempo";
      tiempo.textContent = `${minutos} min`;

      derecha.append(tipo, tiempo);
      encabezado.append(izquierda, derecha);

      const cuerpo = document.createElement("div");
      cuerpo.className = "ticket-body";

      const lista = document.createElement("ul");
      lista.className = "lista-items";

      (c.platillos || []).forEach(p => {
        const item = document.createElement("li");

        const cantidad = document.createElement("span");
        cantidad.className = "item-cant";
        cantidad.textContent = `${p.cantidad}x`;

        const informacion = document.createElement("div");
        informacion.className = "item-info";

        const nombre = document.createElement("span");
        nombre.className = "item-nombre";
        nombre.textContent = p.nombre;

        informacion.appendChild(nombre);

        if (p.observaciones) {
          const observaciones = document.createElement("span");
          observaciones.className = "item-obs";
          observaciones.textContent = `⚠️ ${p.observaciones}`;
          informacion.appendChild(observaciones);
        }

        item.append(cantidad, informacion);
        lista.appendChild(item);
      });

      cuerpo.appendChild(lista);

      const pie = document.createElement("div");
      pie.className = "ticket-footer";

      const botonIniciar = document.createElement("button");
      botonIniciar.type = "button";
      botonIniciar.className =
        `btn-kds ${esPendiente ? "activo" : "bloqueado"}`;
      botonIniciar.textContent = "Iniciar preparación";
      botonIniciar.disabled = !esPendiente;

      botonIniciar.addEventListener("click", () => {
        actualizarEstadoComanda(c.id, "En preparación");
      });

      const botonFinalizar = document.createElement("button");
      botonFinalizar.type = "button";
      botonFinalizar.className =
        `btn-kds ${esPreparacion ? "activo" : "bloqueado"}`;
      botonFinalizar.textContent = "Marcar como listo";
      botonFinalizar.disabled = !esPreparacion;

      botonFinalizar.addEventListener("click", () => {
        actualizarEstadoComanda(c.id, "Listo");
      });

      pie.append(botonIniciar, botonFinalizar);
      tarjeta.append(encabezado, cuerpo, pie);
      contenedor.appendChild(tarjeta);
    });
  }

  // Consulta las comandas reales.
  async function consultarPedidos() {
    if (actualizando) return;
    actualizando = true;

    try {
      const respuesta = await fetch(
        `${API}?accion=obtener_pendientes`,
        { credentials: "same-origin" }
      );

      const datos = await respuesta.json();

      if (!respuesta.ok || datos.exito !== true) {
        throw new Error(
          datos.error || "No se pudieron consultar las comandas."
        );
      }

      if (!Array.isArray(datos.comandas)) {
        throw new Error("La respuesta no contiene una lista de comandas.");
      }

      listaComandas = datos.comandas;
      renderizar();

    } catch (error) {
      console.error("Error al obtener pedidos:", error);
      mostrarMensaje(error.message);
    } finally {
      actualizando = false;
    }
  }

  // Filtros del tablero.
  botonesFiltro.forEach(boton => {
    boton.addEventListener("click", () => {
      botonesFiltro.forEach(b => b.classList.remove("activo"));
      boton.classList.add("activo");

      filtroActivo = boton.dataset.filtro;
      renderizar();
    });
  });

  if (btnRefrescar) {
    btnRefrescar.addEventListener("click", consultarPedidos);
  }

  consultarPedidos();
  setInterval(consultarPedidos, 4000);

  // Permite refrescar la pantalla después de cambiar el estado.
  window.consultarPedidosCocina = consultarPedidos;
});

// Actualiza el estado en el backend.
async function actualizarEstadoComanda(comandaId, nuevoEstado) {
  try {
    const respuesta = await fetch(
      "../../controladores/ControladorCocina.php?accion=actualizar_estado",
      {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          comanda_id: comandaId,
          nuevo_estado: nuevoEstado
        })
      }
    );

    const datos = await respuesta.json();

    if (!respuesta.ok || datos.exito !== true) {
      throw new Error(
        datos.error || "No se pudo actualizar el estado."
      );
    }

    if (window.consultarPedidosCocina) {
      await window.consultarPedidosCocina();
    }

  } catch (error) {
    console.error("Error al actualizar la comanda:", error);
    alert(error.message);
  }
}
