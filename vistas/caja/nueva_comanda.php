
<?php
require_once "../../controladores/Controlador_seguridad.php";
verificarAcceso(['mesero', 'admin']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva comanda | QuickOrder</title>
    <link rel="stylesheet" href="../../publico/css/style.css">
    <style>
        .qo-comanda {
            max-width: 1100px;
            margin: 25px auto;
            padding: 20px;
        }

        .qo-panel {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,.08);
        }

        .qo-campo {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            margin: 7px 0 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .qo-productos {
            display: block;
        }

        .qo-producto {
            display: inline-block;
            vertical-align: top;
            box-sizing: border-box;
            width: 30%;
            min-width: 190px;
            margin: 1%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 9px;
        }

        .qo-producto h3 {
            margin-top: 0;
        }

        .qo-precio {
            font-weight: bold;
            color: #8a1538;
        }

        .qo-boton {
            display: inline-block;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            margin: 4px;
        }

        .qo-principal {
            background: #8a1538;
            color: #fff;
        }

        .qo-secundario {
            background: #eee;
            color: #222;
        }

        .qo-carrito-item {
            padding: 12px 0;
            border-bottom: 1px solid #ddd;
        }

        .qo-cantidad {
            width: 70px;
            padding: 7px;
        }

        .qo-observaciones {
            display: block;
            box-sizing: border-box;
            width: 100%;
            padding: 8px;
            margin-top: 8px;
        }

        #qo-mensaje {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        [hidden] {
            display: none !important;
        }

        @media (max-width: 650px) {
            .qo-producto {
                width: 100%;
                margin: 8px 0;
            }

            .qo-comanda {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="cont_header">
        <h1>QuickOrder | Nueva comanda</h1>
    </div>
</header>

<main class="qo-comanda">

    <div class="qo-panel">
        <a href="caja.php" class="qo-boton qo-secundario">
            ← Volver a caja
        </a>
        <h2>Registrar pedido</h2>
        <p>Selecciona el tipo de pedido y agrega los productos.</p>

        <div id="qo-mensaje" hidden role="status" aria-live="polite"></div>

        <label for="qo-tipo"><strong>Tipo de pedido</strong></label>
        <select id="qo-tipo" class="qo-campo">
            <option value="mesa">Consumo en mesa</option>
            <option value="para_llevar">Para llevar</option>
        </select>

        <div id="qo-seccion-mesa">
            <label for="qo-mesa"><strong>Mesa disponible</strong></label>
            <select id="qo-mesa" class="qo-campo">
                <option value="">Cargando mesas...</option>
            </select>
        </div>
    </div>

    <div class="qo-panel">
        <h2>Menú disponible</h2>
        <div id="qo-productos" class="qo-productos">
            Cargando productos...
        </div>
    </div>

    <div class="qo-panel">
        <h2>Detalle de la comanda</h2>
        <div id="qo-carrito">
            <p>Aún no has agregado productos.</p>
        </div>

        <h3>Total: <span id="qo-total">$0.00</span></h3>

        <button type="button" id="qo-enviar" class="qo-boton qo-principal">
            Enviar comanda a cocina
        </button>
    </div>

</main>

<?php include "../../vistas/plantillas/footer.php"; ?>

<script src="../../publico/js/dropdown_usuario.js"></script>
<script>
(() => {
    const API = "../../controladores/ControladorComandas.php";

    const tipo = document.getElementById("qo-tipo");
    const seccionMesa = document.getElementById("qo-seccion-mesa");
    const mesa = document.getElementById("qo-mesa");
    const productosCont = document.getElementById("qo-productos");
    const carritoCont = document.getElementById("qo-carrito");
    const totalCont = document.getElementById("qo-total");
    const mensaje = document.getElementById("qo-mensaje");
    const botonEnviar = document.getElementById("qo-enviar");

    let productos = [];
    let carrito = [];

    function mostrarMensaje(texto, error = false) {
        mensaje.hidden = false;
        mensaje.textContent = texto;
        mensaje.style.background = error ? "#ffe5e5" : "#e4f7e9";
        mensaje.style.color = error ? "#8b0000" : "#17652a";
    }

    async function consultar(accion) {
        const respuesta = await fetch(
            `${API}?accion=${encodeURIComponent(accion)}`,
            { credentials: "same-origin" }
        );

        const datos = await respuesta.json();

        if (!respuesta.ok || datos.exito === false) {
            throw new Error(datos.error || "No se pudo consultar la información.");
        }

        return datos;
    }

    async function cargarMesas() {
        const datos = await consultar("mesas_disponibles");

        mesa.replaceChildren();

        const mesas = Array.isArray(datos) ? datos : datos.mesas;

        if (!Array.isArray(mesas) || mesas.length === 0) {
            mesa.add(new Option("No hay mesas disponibles", ""));
            return;
        }

        mesa.add(new Option("Selecciona una mesa", ""));

        mesas.forEach(m => {
            mesa.add(new Option(
                `Mesa ${m.numero} · ${m.capacidad} personas`,
                m.id
            ));
        });
    }

    async function cargarProductos() {
        const datos = await consultar("productos_activos");
        productos = Array.isArray(datos) ? datos : datos.productos;

        if (!Array.isArray(productos)) {
            throw new Error("El controlador no devolvió una lista de productos.");
        }

        productosCont.replaceChildren();

        if (productos.length === 0) {
            productosCont.textContent = "No hay productos disponibles.";
            return;
        }

        productos.forEach(p => {
            const tarjeta = document.createElement("article");
            tarjeta.className = "qo-producto";

            const nombre = document.createElement("h3");
            nombre.textContent = p.nombre;

            const descripcion = document.createElement("p");
            descripcion.textContent = p.descripcion || "Sin descripción";

            const precio = document.createElement("p");
            precio.className = "qo-precio";
            precio.textContent = Number(p.precio).toLocaleString(
                "es-MX",
                { style: "currency", currency: "MXN" }
            );

            const boton = document.createElement("button");
            boton.type = "button";
            boton.className = "qo-boton qo-principal";
            boton.textContent = "Agregar";
            boton.addEventListener("click", () => agregarProducto(p.id));

            tarjeta.append(nombre, descripcion, precio, boton);
            productosCont.appendChild(tarjeta);
        });
    }

    function agregarProducto(id) {
        const producto = productos.find(p => Number(p.id) === Number(id));
        if (!producto) return;

        const existente = carrito.find(
            item => Number(item.producto_id) === Number(id)
        );

        if (existente) {
            existente.cantidad++;
        } else {
            carrito.push({
                producto_id: Number(producto.id),
                nombre: producto.nombre,
                precio: Number(producto.precio),
                cantidad: 1,
                observaciones: ""
            });
        }

        renderCarrito();
    }

    function renderCarrito() {
        carritoCont.replaceChildren();

        if (carrito.length === 0) {
            carritoCont.textContent = "Aún no has agregado productos.";
        }

        carrito.forEach((item, indice) => {
            const fila = document.createElement("div");
            fila.className = "qo-carrito-item";

            const nombre = document.createElement("strong");
            nombre.textContent = item.nombre;

            const cantidad = document.createElement("input");
            cantidad.type = "number";
            cantidad.min = "1";
            cantidad.max = "99";
            cantidad.step = "1";
            cantidad.value = item.cantidad;
            cantidad.className = "qo-cantidad";
            cantidad.setAttribute("aria-label", "Cantidad de " + item.nombre);

            cantidad.addEventListener("change", () => {
                const n = Number(cantidad.value);

                if (!Number.isInteger(n) || n < 1 || n > 99) {
                    cantidad.value = item.cantidad;
                    return;
                }

                item.cantidad = n;
                renderCarrito();
            });

            const subtotal = document.createElement("p");
            subtotal.textContent = "Subtotal visual: " +
                (item.precio * item.cantidad).toLocaleString(
                    "es-MX",
                    { style: "currency", currency: "MXN" }
                );

            const observaciones = document.createElement("textarea");
            observaciones.className = "qo-observaciones";
            observaciones.maxLength = 500;
            observaciones.placeholder = "Observaciones (opcional)";
            observaciones.value = item.observaciones;
            observaciones.setAttribute(
                "aria-label",
                "Observaciones de " + item.nombre
            );
            observaciones.addEventListener("input", () => {
                item.observaciones = observaciones.value;
            });

            const quitar = document.createElement("button");
            quitar.type = "button";
            quitar.className = "qo-boton qo-secundario";
            quitar.textContent = "Quitar";
            quitar.addEventListener("click", () => {
                carrito.splice(indice, 1);
                renderCarrito();
            });

            fila.append(
                nombre,
                document.createElement("br"),
                cantidad,
                subtotal,
                observaciones,
                quitar
            );

            carritoCont.appendChild(fila);
        });

        const total = carrito.reduce(
            (suma, item) => suma + item.precio * item.cantidad, 0
        );

        totalCont.textContent = total.toLocaleString(
            "es-MX",
            { style: "currency", currency: "MXN" }
        );
    }

    tipo.addEventListener("change", () => {
        seccionMesa.hidden = tipo.value !== "mesa";
    });

    botonEnviar.addEventListener("click", async () => {
        if (carrito.length === 0) {
            mostrarMensaje("Agrega al menos un producto.", true);
            return;
        }

        if (tipo.value === "mesa" && !mesa.value) {
            mostrarMensaje("Selecciona una mesa disponible.", true);
            return;
        }

        const pedido = {
            tipo_pedido: tipo.value,
            mesa_id: tipo.value === "mesa" ? Number(mesa.value) : null,
            productos: carrito.map(item => ({
                producto_id: item.producto_id,
                cantidad: item.cantidad,
                observaciones: item.observaciones
            }))
        };

        botonEnviar.disabled = true;
        botonEnviar.textContent = "Registrando...";

        try {
            const respuesta = await fetch(`${API}?accion=crear`, {
                method: "POST",
                credentials: "same-origin",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(pedido)
            });

            const datos = await respuesta.json();

            if (!respuesta.ok || datos.exito !== true) {
                throw new Error(datos.error || datos.mensaje ||
                    "No se pudo registrar la comanda.");
            }

            carrito = [];
            renderCarrito();
            await cargarMesas();

            mostrarMensaje(
                `¡Comanda registrada! Folio: ${datos.folio}. Total: ` +
                Number(datos.total).toLocaleString(
                    "es-MX",
                    { style: "currency", currency: "MXN" }
                )
            );

            tipo.value = "mesa";
            seccionMesa.hidden = false;
            mesa.value = "";
        } catch (error) {
            mostrarMensaje(error.message, true);
        } finally {
            botonEnviar.disabled = false;
            botonEnviar.textContent = "Enviar comanda a cocina";
        }
    });

    async function iniciar() {
        try {
            await Promise.all([cargarMesas(), cargarProductos()]);
        } catch (error) {
            mostrarMensaje(
                "No se pudo cargar la información: " + error.message,
                true
            );
            productosCont.textContent = "No se pudieron cargar los productos.";
        }
    }

    iniciar();
})();
</script>

</body>
</html>
