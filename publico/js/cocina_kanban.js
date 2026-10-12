document.addEventListener("DOMContentLoaded", () => {
  const contenedor = document.getElementById("contenedor-comandas");
  const btnRefrescar = document.getElementById("btnRefrescarPedidos");
  const botonesFiltro = document.querySelectorAll(".btn-filtro");

  let filtroActivo = "todos";
  let listaComandas = [];

  // Transforma fecha a hora 12h y calcula minutos transcurridos
  function formatearTiempo(fechaStr) {
    if (!fechaStr) return { minutos: 0, hora: "--:--" };
    const fecha = new Date(fechaStr);
    const ahora = new Date();
    const minutos = Math.max(0, Math.floor((ahora - fecha) / 60000));

    let h = fecha.getHours();
    const m = String(fecha.getMinutes()).padStart(2, "0");
    const ampm = h >= 12 ? "PM" : "AM";
    h = h % 12 || 12;
    const hora = `${String(h).padStart(2, "0")}:${m} ${ampm}`;

    return { minutos, hora };
  }

  // Pinta las tarjetas en pantalla
  function renderizar() {
    contenedor.innerHTML = "";

    const filtradas = listaComandas.filter((c) => {
      if (filtroActivo === "todos") return true;
      return c.estado === filtroActivo;
    });

    // Actualizar contadores
    document.getElementById("cont-todos").textContent = listaComandas.length;
    document.getElementById("cont-pendientes").textContent = listaComandas.filter((c) => c.estado === "pendiente").length;
    document.getElementById("cont-preparacion").textContent = listaComandas.filter((c) => c.estado === "en_preparacion").length;

    if (filtradas.length === 0) {
      contenedor.innerHTML = "<p style='grid-column: 1/-1; text-align: center; color: #888;'>No hay pedidos pendientes en esta sección.</p>";
      return;
    }

    filtradas.forEach((c) => {
      const { minutos, hora } = formatearTiempo(c.enviada_en);
      const esPendiente = c.estado === "pendiente";

      const tarjeta = document.createElement("div");
      tarjeta.className = `tarjeta-ticket ${c.estado}`;

      const platillosHtml = (c.platillos || [])
        .map(
          (p) => `
            <li>
              <span class="item-cant">${p.cantidad}x</span>
              <div class="item-info">
                <span class="item-nombre">${p.nombre}</span>
                ${p.observaciones ? `<span class="item-obs">⚠️ ${p.observaciones}</span>` : ""}
              </div>
            </li>
          `
        )
        .join("");

      tarjeta.innerHTML = `
        <div class="ticket-header">
          <div class="header-izq">
            <h3 class="ticket-mesa">🪑 ${c.mesa ? "Mesa " + c.mesa : "Para Llevar"}</h3>
            <div class="ticket-orden">Order #${c.folio || c.id}</div>
            <div class="ticket-hora">${hora}</div>
          </div>
          <div class="header-der">
            <span class="badge-tipo">${c.tipo_pedido || "Dine In"}</span>
            <span class="ticket-tiempo">${minutos} mins</span>
          </div>
        </div>

        <div class="ticket-body">
          <ul class="lista-items">
            ${platillosHtml}
          </ul>
        </div>

        <div class="ticket-footer">
          <button type="button" class="btn-kds ${esPendiente ? "activo" : "bloqueado"}" 
                  ${esPendiente ? `onclick="actualizarEstadoComanda(${c.id}, 'en_preparacion')"` : "disabled"}>
            Start
          </button>
          <button type="button" class="btn-kds ${!esPendiente ? "activo" : "bloqueado"}" 
                  ${!esPendiente ? `onclick="actualizarEstadoComanda(${c.id}, 'listo')"` : "disabled"}>
            Finish
          </button>
        </div>
      `;

      contenedor.appendChild(tarjeta);
    });
  }

  // Consulta al backend
  async function consultarPedidos() {
    try {
      const resp = await fetch("../../controladores/ControladorCocina.php?accion=obtener_pendientes");
      listaComandas = await resp.json();
      renderizar();
    } catch (err) {
      console.error("Error al obtener pedidos:", err);
    }
  }

  // Eventos de filtros
  botonesFiltro.forEach((btn) => {
    btn.addEventListener("click", () => {
      botonesFiltro.forEach((b) => b.classList.remove("activo"));
      btn.classList.add("activo");
      filtroActivo = btn.getAttribute("data-filtro");
      renderizar();
    });
  });

  if (btnRefrescar) {
    btnRefrescar.addEventListener("click", consultarPedidos);
  }

  // Carga inicial y Short Polling cada 4 segundos
  consultarPedidos();
  setInterval(consultarPedidos, 4000);
});

// Función global invocada por los botones Start y Finish
async function actualizarEstadoComanda(comandaId, nuevoEstado) {
  try {
    await fetch("../../controladores/ControladorCocina.php?accion=actualizar_estado", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ comanda_id: comandaId, nuevo_estado: nuevoEstado }),
    });
  } catch (err) {
    console.error("Error al actualizar estado:", err);
  }
}