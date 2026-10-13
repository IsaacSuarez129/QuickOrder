document.addEventListener('DOMContentLoaded', () => {
  const API = '../../controladores/ControladorComandas.php';
  const cuerpo = document.getElementById('cuerpoComandas');
  const mensaje = document.getElementById('mensajeComandas');
  const filtroEstado = document.getElementById('filtroEstado');
  const buscar = document.getElementById('buscarComanda');
  const modalDetalle = document.getElementById('modalDetalle');
  const modalAdmin = document.getElementById('modalAdmin');
  const filasProductos = document.getElementById('filasProductos');
  let comandas = [];
  let productosActivos = [];
  let operacionPendiente = null;
  let comandaEnEdicion = null;

  const dinero = n => new Intl.NumberFormat('es-MX', {style:'currency', currency:'MXN'}).format(Number(n || 0));
  const escapar = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const estadoClase = e => 'estado-' + String(e || '').toLowerCase().replace(/\s+/g, '-');

  async function jsonFetch(url, options = {}) {
    const r = await fetch(url, options);
    const data = await r.json();
    if (!r.ok || data.exito === false) throw new Error(data.error || 'No se pudo completar la solicitud');
    return data;
  }

  async function cargar() {
    mensaje.textContent = 'Cargando comandas…';
    try {
      const [resComandas, resProductos] = await Promise.all([
        jsonFetch(`${API}?accion=listar`),
        jsonFetch(`${API}?accion=productos_activos`)
      ]);
      comandas = resComandas.comandas || [];
      productosActivos = Array.isArray(resProductos) ? resProductos : (resProductos.productos || []);
      renderizar();
      mensaje.textContent = comandas.length ? `Se encontraron ${comandas.length} comandas.` : 'Todavía no hay comandas registradas.';
    } catch (e) {
      mensaje.textContent = e.message;
    }
  }

  function renderizar() {
    const q = buscar.value.trim().toLowerCase();
    const estado = filtroEstado.value;
    const visibles = comandas.filter(c => {
      const texto = `${c.folio} ${c.mesa ? 'mesa '+c.mesa : 'para llevar'} ${c.mesero || ''}`.toLowerCase();
      return (estado === 'todos' || c.estado === estado) && texto.includes(q);
    });
    cuerpo.innerHTML = visibles.map(c => {
      const pedido = c.tipo_pedido === 'mesa' ? `Mesa ${escapar(c.mesa || '—')}` : 'Para llevar';
      const fecha = c.fecha_registro ? new Date(String(c.fecha_registro).replace(' ', 'T')).toLocaleString('es-MX') : '—';
      const editable = ['Pendiente','En preparación'].includes(c.estado);
      const autorizacion = c.estado === 'En preparación';
      return `<tr><td><strong>${escapar(c.folio)}</strong></td><td>${pedido}</td><td>${escapar(c.mesero || '—')}</td><td>${escapar(fecha)}</td><td><span class="estado-etiqueta ${estadoClase(c.estado)}">${escapar(c.estado)}</span></td><td><strong>${dinero(c.total)}</strong></td><td><button class="btn-tabla" data-accion="ver" data-id="${c.id}">Ver</button><button class="btn-tabla" data-accion="editar" data-id="${c.id}" ${editable?'':'disabled'}>Editar</button><button class="btn-tabla cancelar" data-accion="cancelar" data-id="${c.id}" ${editable?'':'disabled'}>Cancelar</button>${autorizacion?'<small class="sin-acciones"> Requiere admin</small>':''}</td></tr>`;
    }).join('');
    if (!visibles.length) cuerpo.innerHTML = '<tr><td colspan="7">No hay comandas que coincidan con la búsqueda.</td></tr>';
  }

  function crearFila(item = {}) {
    const fila = document.createElement('div');
    fila.className = 'fila-producto-edicion';
    const opciones = productosActivos.map(p => `<option value="${p.id}" ${Number(item.producto_id) === Number(p.id) ? 'selected':''}>${escapar(p.nombre)} — ${dinero(p.precio)}</option>`).join('');
    fila.innerHTML = `<select class="producto-edicion" required><option value="">Selecciona producto</option>${opciones}</select><input class="cantidad-edicion" type="number" min="1" step="1" value="${Math.max(1, Number(item.cantidad || 1))}" required aria-label="Cantidad"><input class="observacion-edicion" maxlength="500" value="${escapar(item.observaciones || '')}" placeholder="Observaciones" aria-label="Observaciones"><button type="button" class="quitar-producto" aria-label="Quitar producto">×</button>`;
    fila.querySelectorAll('select,input').forEach(el => el.addEventListener('input', calcularTotal));
    fila.querySelector('.quitar-producto').addEventListener('click', () => { fila.remove(); calcularTotal(); });
    filasProductos.appendChild(fila);
  }

  function calcularTotal() {
    let total = 0;
    filasProductos.querySelectorAll('.fila-producto-edicion').forEach(f => {
      const p = productosActivos.find(x => Number(x.id) === Number(f.querySelector('.producto-edicion').value));
      total += (p ? Number(p.precio) : 0) * Math.max(0, Number(f.querySelector('.cantidad-edicion').value || 0));
    });
    document.getElementById('totalEdicion').textContent = dinero(total);
  }

  function abrirEdicion(c, soloVer = false) {
    comandaEnEdicion = c;
    document.getElementById('idComandaEditar').value = c.id;
    document.getElementById('tituloModalDetalle').textContent = soloVer ? `Detalle ${c.folio}` : `Editar ${c.folio}`;
    document.getElementById('avisoEstado').textContent = c.estado === 'En preparación' ? 'Esta comanda requiere autorización del administrador para guardar cambios.' : `Estado actual: ${c.estado}`;
    filasProductos.innerHTML = '';
    (c.productos || []).forEach(p => crearFila(p));
    if (!(c.productos || []).length) crearFila();
    document.getElementById('btnAgregarProducto').hidden = soloVer;
    document.querySelector('#formEditarComanda button[type="submit"]').hidden = soloVer;
    filasProductos.querySelectorAll('select,input,button').forEach(el => el.disabled = soloVer);
    calcularTotal();
    modalDetalle.hidden = false;
  }

  function pedirAdmin(continuar) {
    operacionPendiente = continuar;
    document.getElementById('formAdmin').reset();
    document.getElementById('errorAdmin').textContent = '';
    modalAdmin.hidden = false;
    document.getElementById('usuarioAdmin').focus();
  }

  async function ejecutarEdicion(autorizacion_admin = null) {
    const productos = [...filasProductos.querySelectorAll('.fila-producto-edicion')].map(f => ({
      producto_id: Number(f.querySelector('.producto-edicion').value),
      cantidad: Number(f.querySelector('.cantidad-edicion').value),
      observaciones: f.querySelector('.observacion-edicion').value.trim()
    }));
    if (!productos.length || productos.some(p => !p.producto_id || !Number.isInteger(p.cantidad) || p.cantidad < 1)) throw new Error('Agrega productos y cantidades válidas.');
    const body = {comanda_id:Number(comandaEnEdicion.id), productos};
    if (autorizacion_admin) body.autorizacion_admin = autorizacion_admin;
    await jsonFetch(`${API}?accion=editar`, {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    modalDetalle.hidden = true;
    modalAdmin.hidden = true;
    await cargar();
    alert('Comanda actualizada correctamente.');
  }

  async function cancelar(c) {
    if (!confirm(`¿Deseas cancelar la comanda ${c.folio}?`)) return;
    const ejecutar = async (autorizacion_admin = null) => {
      const body = {comanda_id:Number(c.id)};
      if (autorizacion_admin) body.autorizacion_admin = autorizacion_admin;
      await jsonFetch(`${API}?accion=cancelar`, {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
      modalAdmin.hidden = true;
      await cargar();
      alert('Comanda cancelada. Cocina verá el cambio en su actualización.');
    };
    if (c.estado === 'En preparación') pedirAdmin(ejecutar);
    else {
      try { await ejecutar(); } catch(e) { alert(e.message); }
    }
  }

  cuerpo.addEventListener('click', async e => {
    const b = e.target.closest('button[data-accion]');
    if (!b) return;
    const c = comandas.find(x => Number(x.id) === Number(b.dataset.id));
    if (!c) return;
    if (b.dataset.accion === 'ver') abrirEdicion(c, true);
    if (b.dataset.accion === 'editar') {
      // Primero se editan los datos; si está en preparación, la autorización se solicita al guardar.
      abrirEdicion(c, false);
    }
    if (b.dataset.accion === 'cancelar') { try { await cancelar(c); } catch(err) { alert(err.message); } }
  });

  document.getElementById('btnAgregarProducto').addEventListener('click', () => crearFila());
  document.getElementById('formEditarComanda').addEventListener('submit', async e => {
    e.preventDefault();
    try {
      if (comandaEnEdicion?.estado === 'En preparación') {
        pedirAdmin(async credenciales => { modalAdmin.hidden = true; await ejecutarEdicion(credenciales); });
      } else await ejecutarEdicion();
    } catch(err) { alert(err.message); }
  });
  document.getElementById('formAdmin').addEventListener('submit', async e => {
    e.preventDefault();
    const credenciales = {usuario:document.getElementById('usuarioAdmin').value.trim(), contrasena:document.getElementById('contrasenaAdmin').value};
    try {
      if (!operacionPendiente) return;
      const fn = operacionPendiente;
      await fn(credenciales);
      operacionPendiente = null;
      document.getElementById('formAdmin').reset();
    } catch(err) {
      document.getElementById('errorAdmin').textContent = err.message;
      // Si la credencial fue incorrecta, el servidor no modifica la comanda.
    }
  });
  document.querySelectorAll('[data-cerrar]').forEach(b => b.addEventListener('click', () => { document.getElementById(b.dataset.cerrar).hidden = true; operacionPendiente = null; }));
  document.getElementById('btnActualizar').addEventListener('click', cargar);
  filtroEstado.addEventListener('change', renderizar);
  buscar.addEventListener('input', renderizar);
  cargar();
});
