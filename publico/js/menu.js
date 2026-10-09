(() => {
  const productos = [
    {id:1,nombre:'Hamburguesa clásica',descripcion:'Carne, queso, lechuga y tomate',precio:89,categoria:'Platillos',disponible:true,icono:'🍔'},
    {id:2,nombre:'Pizza pepperoni',descripcion:'Pizza con pepperoni y queso mozzarella',precio:145,categoria:'Platillos',disponible:true,icono:'🍕'},
    {id:3,nombre:'Ensalada César',descripcion:'Lechuga fresca, aderezo y crutones',precio:75,categoria:'Entradas',disponible:true,icono:'🥗'},
    {id:4,nombre:'Papas a la francesa',descripcion:'Porción de papas crujientes',precio:49,categoria:'Entradas',disponible:true,icono:'🍟'},
    {id:5,nombre:'Limonada',descripcion:'Limonada natural de la casa',precio:35,categoria:'Bebidas',disponible:true,icono:'🍋'},
    {id:6,nombre:'Pastel de chocolate',descripcion:'Rebanada de pastel de chocolate',precio:55,categoria:'Postres',disponible:false,icono:'🍰'}
  ];
  let siguienteId = 7;
  const $ = id => document.getElementById(id);
  const formatoPrecio = n => new Intl.NumberFormat('es-MX',{style:'currency',currency:'MXN'}).format(n);
  const escapar = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  function render() {
    const buscar = $('buscarProducto').value.trim().toLocaleLowerCase('es-MX');
    const categoria = $('filtroCategoria').value;
    const estado = $('filtroEstado').value;
    const lista = productos.filter(p => (!buscar || `${p.nombre} ${p.descripcion}`.toLocaleLowerCase('es-MX').includes(buscar)) && (!categoria || p.categoria === categoria) && (!estado || (estado === 'disponible' ? p.disponible : !p.disponible)));
    $('tablaProductos').innerHTML = lista.map(p => `<tr>
      <td><div class="qo-product"><span class="qo-product-icon">${p.icono || '🍽️'}</span><div><strong>${escapar(p.nombre)}</strong><small>${escapar(p.descripcion || 'Sin descripción')}</small></div></div></td>
      <td><span class="qo-category">${escapar(p.categoria)}</span></td><td class="qo-price">${formatoPrecio(p.precio)}</td>
      <td><span class="qo-status ${p.disponible?'available':'unavailable'}">${p.disponible?'Disponible':'No disponible'}</span></td>
      <td><div class="qo-actions"><button class="qo-action" data-editar="${p.id}" type="button">Editar</button><button class="qo-action" data-toggle="${p.id}" type="button">${p.disponible?'Desactivar':'Activar'}</button></div></td></tr>`).join('');
    if (!lista.length) $('tablaProductos').innerHTML = '<tr><td colspan="5" style="text-align:center;padding:30px;color:#737982">No se encontraron productos con esos filtros.</td></tr>';
    $('totalProductos').textContent = productos.length;
    $('totalDisponibles').textContent = productos.filter(p=>p.disponible).length;
    $('totalNoDisponibles').textContent = productos.filter(p=>!p.disponible).length;
    $('textoResultados').textContent = `Mostrando ${lista.length} de ${productos.length} productos`;
  }
  function abrirModal(p = null) {
    $('formProducto').reset(); $('productoId').value = p ? p.id : ''; $('tituloModal').textContent = p ? 'Editar producto' : 'Agregar producto';
    $('nombreProducto').value = p?.nombre || ''; $('descripcionProducto').value = p?.descripcion || ''; $('precioProducto').value = p?.precio ?? ''; $('categoriaProducto').value = p?.categoria || ''; $('disponibleProducto').checked = p ? p.disponible : true;
    $('modalProducto').hidden = false; document.body.style.overflow = 'hidden'; $('nombreProducto').focus();
  }
  function cerrarModal(){ $('modalProducto').hidden = true; document.body.style.overflow = ''; }
  let toastTimeout;
  function notificar(texto){ const t=$('mensajeToast'); t.textContent=texto; t.classList.add('show'); clearTimeout(toastTimeout); toastTimeout=setTimeout(()=>t.classList.remove('show'),2600); }
  $('btnNuevo').addEventListener('click',()=>abrirModal());
  $('btnCerrar').addEventListener('click',cerrarModal); $('btnCancelar').addEventListener('click',cerrarModal);
  $('modalProducto').addEventListener('click',e=>{if(e.target===$('modalProducto'))cerrarModal();});
  document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!$('modalProducto').hidden)cerrarModal();});
  ['buscarProducto','filtroCategoria','filtroEstado'].forEach(id=>$(id).addEventListener(id==='buscarProducto'?'input':'change',render));
  $('tablaProductos').addEventListener('click',e=>{
    const editar=e.target.closest('[data-editar]'); const toggle=e.target.closest('[data-toggle]');
    if(editar){const p=productos.find(x=>x.id===Number(editar.dataset.editar));if(p)abrirModal(p);}
    if(toggle){const p=productos.find(x=>x.id===Number(toggle.dataset.toggle));if(p){p.disponible=!p.disponible;render();notificar(`${p.nombre}: ${p.disponible?'disponible':'no disponible'}.`);}}
  });
  $('formProducto').addEventListener('submit',e=>{
    e.preventDefault(); const nombre=$('nombreProducto').value.trim(); const precio=Number($('precioProducto').value); const categoria=$('categoriaProducto').value;
    if(!nombre||!categoria||!Number.isFinite(precio)||precio<=0){notificar('Revisa los campos obligatorios y el precio.');return;}
    const id=Number($('productoId').value); const datos={nombre,descripcion:$('descripcionProducto').value.trim(),precio,categoria,disponible:$('disponibleProducto').checked,icono:'🍽️'};
    if(id){Object.assign(productos.find(p=>p.id===id),datos);notificar('Producto actualizado en la demostración.');}
    else{productos.push({id:siguienteId++,...datos});notificar('Producto agregado a la demostración.');}
    cerrarModal();render();
  });
  render();
})();
