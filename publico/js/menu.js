(() => {
  let productos = [];
  const URL_API = "../../controladores/ControladorMenu.php";

  const $ = (id) => document.getElementById(id);

  const formatoPrecio = (n) =>
    new Intl.NumberFormat("es-MX", {
      style: "currency",
      currency: "MXN",
    }).format(n);

  const escapar = (s) =>
    String(s || "").replace(
      /[&<>"']/g,
      (c) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
        })[c],
    );

  // 1. Cargar datos reales desde la Base de Datos
  async function cargarProductos() {
    try {
      const res = await fetch(`${URL_API}?accion=listar`);

      if (!res.ok) {
        throw new Error(`Error HTTP: ${res.status}`);
      }

      const datos = await res.json();

      // Si el backend devolvió un error de conexión
      if (datos.error) {
        console.error("Error devuelto por el backend:", datos.error);
        $("tablaProductos").innerHTML =
          `<tr><td colspan="5" style="text-align:center;padding:30px;color:#d62f0f">Error de base de datos: ${escapar(datos.error)}</td></tr>`;
        return;
      }

      // Mapear los datos de la base de datos a los tipos que usa la vista
      productos = datos.map((p) => ({
        id: Number(p.id || p.id_producto),
        nombre: p.nombre,
        descripcion: p.descripcion,
        precio: Number(p.precio),
        categoria: p.categoria,
        disponible: Boolean(Number(p.disponible)),
        icono: "🍽️",
      }));

      render();
    } catch (e) {
      console.error("Error al cargar productos:", e);
      $("tablaProductos").innerHTML =
        `<tr><td colspan="5" style="text-align:center;padding:30px;color:#d62f0f">No se pudo conectar con el servidor. Revisa la consola (F12).</td></tr>`;
    }
  }

  // 2. Renderizar tabla y contadores
  function render() {
    const buscar = $("buscarProducto").value.trim().toLocaleLowerCase("es-MX");
    const categoria = $("filtroCategoria").value;
    const estado = $("filtroEstado").value;

    const lista = productos.filter(
      (p) =>
        (!buscar ||
          `${p.nombre} ${p.descripcion}`
            .toLocaleLowerCase("es-MX")
            .includes(buscar)) &&
        (!categoria || p.categoria === categoria) &&
        (!estado || (estado === "disponible" ? p.disponible : !p.disponible)),
    );

    $("tablaProductos").innerHTML = lista
      .map(
        (p) => `<tr>
      <td>
        <div class="qo-product">
          <span class="qo-product-icon">${p.icono}</span>
          <div>
            <strong>${escapar(p.nombre)}</strong>
            <small>${escapar(p.descripcion || "Sin descripción")}</small>
          </div>
        </div>
      </td>
      <td><span class="qo-category">${escapar(p.categoria)}</span></td>
      <td class="qo-price">${formatoPrecio(p.precio)}</td>
      <td>
        <span class="qo-status ${p.disponible ? "available" : "unavailable"}">
          ${p.disponible ? "Disponible" : "No disponible"}
        </span>
      </td>
      <td>
        <div class="qo-actions">
          <button class="qo-action" data-editar="${p.id}" type="button">Editar</button>
          <button class="qo-action" data-toggle="${p.id}" type="button">
            ${p.disponible ? "Desactivar" : "Activar"}
          </button>
        </div>
      </td>
    </tr>`,
      )
      .join("");

    if (!lista.length) {
      $("tablaProductos").innerHTML =
        '<tr><td colspan="5" style="text-align:center;padding:30px;color:#737982">No se encontraron productos con esos filtros.</td></tr>';
    }

    // Actualizar contadores superiores
    if ($("totalProductos")) $("totalProductos").textContent = productos.length;
    if ($("totalDisponibles"))
      $("totalDisponibles").textContent = productos.filter(
        (p) => p.disponible,
      ).length;
    if ($("totalNoDisponibles"))
      $("totalNoDisponibles").textContent = productos.filter(
        (p) => !p.disponible,
      ).length;
    if ($("textoResultados"))
      $("textoResultados").textContent =
        `Mostrando ${lista.length} de ${productos.length} productos`;
  }

  // 3. Control del Modal
  function abrirModal(p = null) {
    $("formProducto").reset();
    $("productoId").value = p ? p.id : "";
    $("tituloModal").textContent = p ? "Editar producto" : "Agregar producto";
    $("nombreProducto").value = p?.nombre || "";
    $("descripcionProducto").value = p?.descripcion || "";
    $("precioProducto").value = p?.precio ?? "";
    $("categoriaProducto").value = p?.categoria || "";
    $("disponibleProducto").checked = p ? p.disponible : true;
    $("modalProducto").hidden = false;
    document.body.style.overflow = "hidden";
    $("nombreProducto").focus();
  }

  function cerrarModal() {
    $("modalProducto").hidden = true;
    document.body.style.overflow = "";
  }

  // Eventos de botones del modal
  $("btnNuevo").addEventListener("click", () => abrirModal());
  $("btnCerrar").addEventListener("click", cerrarModal);
  $("btnCancelar").addEventListener("click", cerrarModal);

  $("modalProducto").addEventListener("click", (e) => {
    if (e.target === $("modalProducto")) cerrarModal();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !$("modalProducto").hidden) cerrarModal();
  });

  // Filtros de búsqueda
  ["buscarProducto", "filtroCategoria", "filtroEstado"].forEach((id) => {
    const elemento = $(id);
    if (elemento) {
      elemento.addEventListener(
        id === "buscarProducto" ? "input" : "change",
        render,
      );
    }
  });

  // 4. Delegación de eventos para la tabla (Editar / Alternar disponibilidad)
  $("tablaProductos").addEventListener("click", async (e) => {
    const editar = e.target.closest("[data-editar]");
    const toggle = e.target.closest("[data-toggle]");

    if (editar) {
      const p = productos.find((x) => x.id === Number(editar.dataset.editar));
      if (p) abrirModal(p);
    }

    if (toggle) {
      const id = Number(toggle.dataset.toggle);
      const p = productos.find((x) => x.id === id);
      if (p) {
        const estadoActual = p.disponible ? 1 : 0;
        try {
          await fetch(
            `${URL_API}?accion=toggle&id=${id}&estado=${estadoActual}`,
          );
          await cargarProductos();
        } catch (error) {
          console.error("Error al alternar disponibilidad:", error);
        }
      }
    }
  });

  // 5. Enviar formulario (Crear o Modificar)
  $("formProducto").addEventListener("submit", async (e) => {
    e.preventDefault();
    const id = $("productoId").value;
    const datos = {
      id: id ? Number(id) : null,
      nombre: $("nombreProducto").value.trim(),
      descripcion: $("descripcionProducto").value.trim(),
      precio: Number($("precioProducto").value),
      categoria: $("categoriaProducto").value,
      disponible: $("disponibleProducto").checked ? 1 : 0,
    };

    try {
      const res = await fetch(`${URL_API}?accion=guardar`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(datos),
      });

      const respuesta = await res.json();
      if (respuesta.error) {
        alert("Error al guardar: " + respuesta.error);
        return;
      }

      cerrarModal();
      await cargarProductos();
    } catch (error) {
      console.error("Error al enviar el formulario:", error);
      alert("Ocurrió un error al guardar el producto.");
    }
  });

  // Ejecución inicial al cargar la página
  cargarProductos();
})();
