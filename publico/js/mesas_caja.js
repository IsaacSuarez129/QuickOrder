document.addEventListener('DOMContentLoaded', () => {
  const cuerpo = document.getElementById('cuerpoMesasOcupadas');
  const mensaje = document.getElementById('mensajeMesas');
  const botonActualizar = document.getElementById('btnActualizarMesas');
  if (!cuerpo || !mensaje || !botonActualizar) return;

  const endpoint = '../../controladores/ControladorComandas.php';

  function mostrarMensaje(texto, tipo = '') {
    mensaje.textContent = texto;
    mensaje.className = 'mensaje-mesas' + (tipo ? ' ' + tipo : '');
  }

  async function cargarMesas() {
    botonActualizar.disabled = true;
    mostrarMensaje('Cargando mesas ocupadas…');
    try {
      const respuesta = await fetch(`${endpoint}?accion=mesas_ocupadas`, {
        method: 'GET',
        headers: { 'Accept': 'application/json' },
        cache: 'no-store'
      });
      const datos = await respuesta.json();
      if (!respuesta.ok || !datos.exito) throw new Error(datos.error || 'No se pudieron consultar las mesas');

      cuerpo.replaceChildren();
      if (!datos.mesas.length) {
        mostrarMensaje('No hay mesas ocupadas en este momento.');
        return;
      }

      datos.mesas.forEach(mesa => {
        const fila = document.createElement('tr');
        const valores = [
          `Mesa ${mesa.numero}`,
          mesa.capacidad,
          mesa.comandas_activas
        ];
        valores.forEach(valor => {
          const celda = document.createElement('td');
          celda.textContent = valor;
          fila.appendChild(celda);
        });

        const celdaAccion = document.createElement('td');
        const boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'btn-liberar-mesa';
        boton.textContent = 'Liberar mesa';
        boton.addEventListener('click', () => liberarMesa(mesa.id, mesa.numero));
        celdaAccion.appendChild(boton);
        fila.appendChild(celdaAccion);
        cuerpo.appendChild(fila);
      });
      mostrarMensaje(`Se encontraron ${datos.mesas.length} mesa(s) ocupada(s).`);
    } catch (error) {
      console.error(error);
      mostrarMensaje(error.message || 'Ocurrió un error al cargar las mesas.', 'error');
    } finally {
      botonActualizar.disabled = false;
    }
  }

  async function liberarMesa(mesaId, numero) {
    const confirmar = window.confirm(`¿Confirmas que los clientes de la Mesa ${numero} se retiraron y deseas liberarla?`);
    if (!confirmar) return;

    try {
      mostrarMensaje(`Liberando Mesa ${numero}…`);
      const respuesta = await fetch(`${endpoint}?accion=liberar_mesa`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ mesa_id: mesaId })
      });
      const datos = await respuesta.json();
      if (!respuesta.ok || !datos.exito) throw new Error(datos.error || 'No se pudo liberar la mesa');
      mostrarMensaje(datos.mensaje, 'exito');
      await cargarMesas();
    } catch (error) {
      console.error(error);
      mostrarMensaje(error.message || 'Ocurrió un error al liberar la mesa.', 'error');
      window.alert(error.message || 'Ocurrió un error al liberar la mesa.');
    }
  }

  botonActualizar.addEventListener('click', cargarMesas);
  cargarMesas();
});
